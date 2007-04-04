<?php

function bb_install() {
	require_once( BBPATH . 'bb-admin/upgrade-schema.php');
	bb_make_db_current();
	bb_update_db_version();
}

function bb_upgrade_all() {
	if ( !ini_get('safe_mode') )
		set_time_limit(600);
	$bb_upgrade = 0;
	$bb_upgrade += bb_upgrade_160(); // Break blocked users
	$bb_upgrade += bb_upgrade_170(); // Escaping in usermeta
	$bb_upgrade += bb_upgrade_180(); // Delete users for real
	$bb_upgrade += bb_upgrade_190(); // Move topic_resolved to topicmeta
	require_once( BBPATH . 'bb-admin/upgrade-schema.php');
	bb_make_db_current();
	$bb_upgrade += bb_upgrade_200(); // Make forum and topic slugs
	bb_update_db_version();
	return $bb_upgrade;
}

function bb_dbDelta($queries, $execute = true) {
	global $bbdb;
	
	// Seperate individual queries into an array
	if( !is_array($queries) ) {
		$queries = explode( ';', $queries );
		if('' == $queries[count($queries) - 1]) array_pop($queries);
	}
	
	$cqueries = array(); // Creation Queries
	$iqueries = array(); // Insertion Queries
	$for_update = array();
	
	// Create a tablename index for an array ($cqueries) of queries
	foreach($queries as $qry) {
		if(preg_match("|CREATE TABLE ([^ ]*)|", $qry, $matches)) {
			$cqueries[strtolower($matches[1])] = $qry;
			$for_update[$matches[1]] = 'Created table '.$matches[1];
		}
		else if(preg_match("|CREATE DATABASE ([^ ]*)|", $qry, $matches)) {
			array_unshift($cqueries, $qry);
		}
		else if(preg_match("|INSERT INTO ([^ ]*)|", $qry, $matches)) {
			$iqueries[] = $qry;
		}
		else if(preg_match("|UPDATE ([^ ]*)|", $qry, $matches)) {
			$iqueries[] = $qry;
		}
		else {
			// Unrecognized query type
		}
	}	

	// Check to see which tables and fields exist
	if($tables = (array) $bbdb->get_col('SHOW TABLES;')) {
		// For every table in the database
		foreach($tables as $table) {
			// If a table query exists for the database table...
			if( array_key_exists(strtolower($table), $cqueries) ) {
				// Clear the field and index arrays
				unset($cfields);
				unset($indices);
				// Get all of the field names in the query from between the parens
				preg_match("|\((.*)\)|ms", $cqueries[strtolower($table)], $match2);
				$qryline = trim($match2[1]);

				// Separate field lines into an array
				$flds = explode("\n", $qryline);

				//echo "<hr/><pre>\n".print_r(strtolower($table), true).":\n".print_r($cqueries, true)."</pre><hr/>";
				
				// For every field line specified in the query
				foreach($flds as $fld) {
					// Extract the field name
					preg_match("|^([^ ]*)|", trim($fld), $fvals);
					$fieldname = $fvals[1];
					
					// Verify the found field name
					$validfield = true;
					switch(strtolower($fieldname))
					{
					case '':
					case 'primary':
					case 'index':
					case 'fulltext':
					case 'unique':
					case 'key':
						$validfield = false;
						$indices[] = trim(trim($fld), ", \n");
						break;
					}
					$fld = trim($fld);
					
					// If it's a valid field, add it to the field array
					if($validfield) {
						$cfields[strtolower($fieldname)] = trim($fld, ", \n");
					}
				}
				
				// Fetch the table column structure from the database
				$tablefields = $bbdb->get_results("DESCRIBE {$table};");
								
				// For every field in the table
				foreach($tablefields as $tablefield) {				
					// If the table field exists in the field array...
					if(array_key_exists(strtolower($tablefield->Field), $cfields)) {
						// Get the field type from the query
						preg_match("|".$tablefield->Field." ([^ ]*( unsigned)?)|i", $cfields[strtolower($tablefield->Field)], $matches);
						$fieldtype = $matches[1];

						// Is actual field type different from the field type in query?
						if($tablefield->Type != $fieldtype) {
							// Add a query to change the column type
							$cqueries[] = "ALTER TABLE {$table} CHANGE COLUMN {$tablefield->Field} " . $cfields[strtolower($tablefield->Field)];
							$for_update[$table.'.'.$tablefield->Field] = "Changed type of {$table}.{$tablefield->Field} from {$tablefield->Type} to {$fieldtype}";
						}
						
						// Get the default value from the array
							//echo "{$cfields[strtolower($tablefield->Field)]}<br>";
						if(preg_match("| DEFAULT '(.*)'|i", $cfields[strtolower($tablefield->Field)], $matches)) {
							$default_value = $matches[1];
							if($tablefield->Default != $default_value)
							{
								// Add a query to change the column's default value
								$cqueries[] = "ALTER TABLE {$table} ALTER COLUMN {$tablefield->Field} SET DEFAULT '{$default_value}'";
								$for_update[$table.'.'.$tablefield->Field] = "Changed default value of {$table}.{$tablefield->Field} from {$tablefield->Default} to {$default_value}";
							}
						}

						// Remove the field from the array (so it's not added)
						unset($cfields[strtolower($tablefield->Field)]);
					}
					else {
						// This field exists in the table, but not in the creation queries?
					}
				}

				// For every remaining field specified for the table
				foreach($cfields as $fieldname => $fielddef) {
					// Push a query line into $cqueries that adds the field to that table
					$cqueries[] = "ALTER TABLE {$table} ADD COLUMN $fielddef";
					$for_update[$table.'.'.$fieldname] = 'Added column '.$table.'.'.$fieldname;
				}
				
				// Index stuff goes here
				// Fetch the table index structure from the database
				$tableindices = $bbdb->get_results("SHOW INDEX FROM {$table};");
				
				if($tableindices) {
					// Clear the index array
					unset($index_ary);

					// For every index in the table
					foreach($tableindices as $tableindex) {
						// Add the index to the index data array
						$keyname = $tableindex->Key_name;
						$index_ary[$keyname]['columns'][] = array('fieldname' => $tableindex->Column_name, 'subpart' => $tableindex->Sub_part);
						$index_ary[$keyname]['unique'] = ($tableindex->Non_unique == 0)?true:false;
						$index_ary[$keyname]['type'] = ('BTREE' == $tableindex->Index_type)?false:$tableindex->Index_type;
						if(!$index_ary[$keyname]['type']) {
							$index_ary[$keyname]['type'] = (strpos($tableindex->Comment, 'FULLTEXT') === false)?false:'FULLTEXT';
						}
					}

					// For each actual index in the index array
					foreach($index_ary as $index_name => $index_data) {
						// Build a create string to compare to the query
						$index_string = '';
						if($index_name == 'PRIMARY') {
							$index_string .= 'PRIMARY ';
						}
						else if($index_data['unique']) {
							$index_string .= 'UNIQUE ';
						}
						if($index_data['type']) {
							$index_string .= $index_data['type'] . ' ';
						}
						$index_string .= 'KEY ';
						if($index_name != 'PRIMARY') {
							$index_string .= $index_name;
						}
						$index_columns = '';
						// For each column in the index
						foreach($index_data['columns'] as $column_data) {					
							if($index_columns != '') $index_columns .= ',';
							// Add the field to the column list string
							$index_columns .= $column_data['fieldname'];
							if($column_data['subpart'] != '') {
								$index_columns .= '('.$column_data['subpart'].')';
							}
						}
						// Add the column list to the index create string 
						$index_string .= ' ('.$index_columns.')';

						if(!(($aindex = array_search($index_string, $indices)) === false)) {
							unset($indices[$aindex]);
							//echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br/>Found index:".$index_string."</pre>\n";
						}
						//else echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br/><b>Did not find index:</b>".$index_string."<br/>".print_r($indices, true)."</pre>\n";
					}
				}

				// For every remaining index specified for the table
				foreach($indices as $index) {
					// Push a query line into $cqueries that adds the index to that table
					$cqueries[] = "ALTER TABLE {$table} ADD $index";
					$for_update[$table.'.'.$fieldname] = 'Added index '.$table.' '.$index;
				}

				// Remove the original table creation query from processing
				unset($cqueries[strtolower($table)]);
				unset($for_update[strtolower($table)]);
			} else {
				// This table exists in the database, but not in the creation queries?
			}
		}
	}

	$allqueries = array_merge($cqueries, $iqueries);
	if($execute) {
		foreach($allqueries as $query) {
			//echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">".print_r($query, true)."</pre>\n";
			$bbdb->query($query);
		}
	}

	return $for_update;
}

/**
 ** bb_maybe_add_column()
 ** Add column to db table if it doesn't exist.
 ** Returns:  true if already exists or on successful completion
 **           false on error
 */
function bb_maybe_add_column( $table_name, $column_name, $create_ddl ) {
	global $bbdb, $debug;
	foreach ($bbdb->get_col("DESC $table_name", 0) as $column ) {
		if ($debug) echo("checking $column == $column_name<br />");
		if ($column == $column_name) {
			return true;
		}
	}
	// didn't find it try to create it.
	$q = $bbdb->query($create_ddl);
	// we cannot directly tell that whether this succeeded!
	foreach ($bbdb->get_col("DESC $table_name", 0) as $column ) {
		if ($column == $column_name) {
			return true;
		}
	}
	return false;
}

function bb_make_db_current() {
	global $bb_queries;

	$alterations = bb_dbDelta($bb_queries);
	echo "<ol>\n";
	foreach($alterations as $alteration) {
		echo "<li>$alteration</li>\n";
		flush();
		}
	echo "</ol>\n";
}

// Reversibly break passwords of blocked users.
function bb_upgrade_160() {
	if ( ( $dbv = bb_get_option_from_db( 'bb_db_version' ) ) && $dbv >= 535 )
		return 0;

	require_once('admin-functions.php');
	$blocked = bb_get_ids_by_role( 'blocked' );
	foreach ( $blocked as $b )
		bb_break_password( $b );
	return 1;
}

function bb_upgrade_170() {
	if ( ( $dbv = bb_get_option_from_db( 'bb_db_version' ) ) && $dbv >= 536 )
		return 0;

	global $bbdb;
	foreach ( (array) $bbdb->get_results("SELECT * FROM $bbdb->usermeta WHERE meta_value LIKE '%&quot;%' OR meta_value LIKE '%&#039;%'") as $meta ) {
		$value = str_replace(array('&quot;', '&#039;'), array('"', "'"), $meta->meta_value);
		$value = stripslashes($value);
		bb_update_usermeta( $meta->user_id, $meta->meta_key, $value);
	}
	bb_update_option( 'bb_db_version', 536 );
	echo "Done updating usermeta<br />";
	return 1;
}

function bb_upgrade_180() {
	if ( ( $dbv = bb_get_option_from_db( 'bb_db_version' ) ) && $dbv >= 559 )
		return 0;

	global $bbdb;

	foreach ( (array) $bbdb->get_col("SELECT ID FROM $bbdb->users WHERE user_status = 1") as $user_id )
		bb_delete_user( $user_id );
	bb_update_option( 'bb_db_version', 559 );
	echo "Done clearing deleted users<br />";
	return 1;
}

function bb_upgrade_190() {
	if ( ( $dbv = bb_get_option_from_db( 'bb_db_version' ) ) && $dbv >= 630 )
		return 0;

	global $bbdb;

	$exists = false;
	foreach ( (array) $bbdb->get_col("DESC $bbdb->topics") as $col )
		if ( 'topic_resolved' == $col )
			$exists = true;
	if ( !$exists )
		return 0;

	$topics = (array) $bbdb->get_results("SELECT topic_id, topic_resolved FROM $bbdb->topics" );
	foreach ( $topics  as $topic )
		bb_update_topicmeta( $topic->topic_id, 'topic_resolved', $topic->topic_resolved );
	unset($topics,$topic);

	$bbdb->query("ALTER TABLE $bbdb->topics DROP topic_resolved");

	bb_update_option( 'bb_db_version', 630 );

	echo "Done converting topic_resolved.<br />";
	return 1;
}

function bb_upgrade_200() {
	if ( ( $dbv = bb_get_option_from_db( 'bb_db_version' ) ) && $dbv >= 788 )
		return 0;
	
	global $bbdb;
	
	$forums = (array) $bbdb->get_results("SELECT forum_id, forum_name, forum_slug FROM $bbdb->forums ORDER BY forum_order ASC" );
	foreach ($forums  as $forum) {
		$slug = bb_slug_sanitize(trim($forum->forum_name));
		$forum_slugs[$slug][] = $forum->forum_id;
	}
	foreach ($forum_slugs as $slug => $forums) {
		foreach ($forums as $count => $forum_id) {
			if ($count > 0) {
				$increment = '-' . ($count + 1);
			} else {
				$increment = null;
			}
			$slug .= $increment;
			$bbdb->query("UPDATE $bbdb->forums SET forum_slug = '$slug' WHERE forum_id = $forum_id;");
		}
	}
	unset($forums,$forum,$forum_slugs,$slug,$forum_id,$increment,$count);
	
	$topics = (array) $bbdb->get_results("SELECT topic_id, topic_title, topic_slug FROM $bbdb->topics ORDER BY topic_start_time ASC" );
	foreach ($topics  as $topic) {
		$slug = bb_slug_sanitize(trim($topic->topic_title));
		$topic_slugs[$slug][] = $topic->topic_id;
	}
	foreach ($topic_slugs as $slug => $topics) {
		foreach ($topics as $count => $topic_id) {
			if ($count > 0) {
				$increment = '-' . ($count + 1);
			} else {
				$increment = null;
			}
			$slug .= $increment;
			$bbdb->query("UPDATE $bbdb->topics SET topic_slug = '$slug' WHERE topic_id = $topic_id;");
		}
	}
	unset($topics,$topic,$topic_slugs,$slug,$topic_id,$increment,$count);
	
	bb_update_option( 'bb_db_version', 788 );
	
	echo "Done adding slugs.<br />";
	return 1;
}

function bb_deslash($content) {
    // Note: \\\ inside a regex denotes a single backslash.

    // Replace one or more backslashes followed by a single quote with
    // a single quote.
    $content = preg_replace("/\\\+'/", "'", $content);

    // Replace one or more backslashes followed by a double quote with
    // a double quote.
    $content = preg_replace('/\\\+"/', '"', $content);

    // Replace one or more backslashes with one backslash.
    $content = preg_replace("/\\\+/", "\\", $content);

    return $content;
}

function bb_update_db_version() {
	bb_update_option( 'bb_db_version', bb_get_option( 'bb_db_version' ) );
}
?>
