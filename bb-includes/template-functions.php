<?php

function bb_load_template( $file, $globals = false ) {
	global $bb, $bbdb, $bb_current_user, $page, $bb_cache,
		$posts, $bb_post, $post_id, $topics, $topic, $topic_id,
		$forums, $forum, $forum_id, $tags, $tag, $tag_name, $user, $user_id, $view;

	if ( $globals )
		foreach ( $globals as $global => $v )
			if ( !is_numeric($global) )
				$$global = $v;
			else
				global $$v;

	if ( file_exists( bb_get_active_theme_folder() .  $file) ) {
		$template = bb_get_active_theme_folder() .  $file;
	} else {
		$template = BBPATH . "bb-templates/kakumei/$file";
	}

	$template = apply_filters( 'bb_template', $template, $file );
	include($template);
}

function bb_get_template( $file ) {
	if ( file_exists( bb_get_active_theme_folder() .  $file) )
		return bb_get_active_theme_folder() .  $file;
	return BBPATH . "bb-templates/kakumei/$file";
}

function bb_get_header() {
	bb_load_template( 'header.php' );
}

function bb_language_attributes( $xhtml = 0 ) {
	$output = '';
	if ( $dir = bb_get_option('text_direction') )
		$output = "dir=\"$dir\" ";
	if ( $lang = bb_get_option('language') ) {
		$output .= "xml:lang=\"$lang\" ";
		if ( $xhtml < '1.1' )
			$output .= "lang=\"$lang\"";
	}

	echo rtrim($output);
}

function bb_stylesheet_uri( $stylesheet = '' ) {
	echo bb_get_stylesheet_uri( $stylesheet );
}

function bb_get_stylesheet_uri( $stylesheet = '' ) {
	if ( 'rtl' == $stylesheet )
		$css_file = 'style-rtl.css';
	else
		$css_file = 'style.css';

	$active_theme = bb_get_active_theme_folder();

	if ( file_exists( $active_theme . 'style.css' ) )
		$r = bb_get_active_theme_uri() . $css_file;
	else
		$r = bb_get_option( 'uri' ) . "bb-templates/kakumei/$css_file";
	return apply_filters( 'bb_get_stylesheet_uri', $r, $stylesheet );
}

function bb_active_theme_uri() {
	echo bb_get_active_theme_uri();
}

function bb_get_active_theme_uri() {
	return apply_filters( 'bb_get_active_theme_uri', bb_path_to_url( bb_get_active_theme_folder() ) );
}

function bb_get_footer() {
	bb_load_template( 'footer.php' );
}

function bb_head() {
        do_action('bb_head');
}

function profile_menu() {
	global $bbdb, $bb_current_user, $user_id, $profile_menu, $self, $profile_page_title;
	$list  = "<ul id='profile-menu'>";
	$list .= "\n\t<li" . ( ( $self ) ? '' : ' class="current"' ) . '><a href="' . get_user_profile_link( $user_id ) . '">' . __('Profile') . '</a></li>';
	foreach ($profile_menu as $item) {
		// 0 = name, 1 = users cap, 2 = others cap, 3 = file
		$class = '';
		if ( $item[3] == $self ) {
			$class = ' class="current"';
			$profile_page_title = $item[0];
		}
		if ( can_access_tab( $item, $bb_current_user->ID, $user_id ) )
			if ( file_exists($item[3]) || is_callable($item[3]) )
				$list .= "\n\t<li$class><a href='" . wp_specialchars( get_profile_tab_link($user_id, $item[4]) ) . "'>{$item[0]}</a></li>";
	}
	$list .= "\n</ul>";
	echo $list;
}

function login_form() {
	global $bb_current_user;
	if ( bb_is_user_logged_in() ) {
        printf('<p class="login">'. __('Welcome, %1$s!'). ' <a href="' . get_user_profile_link( $bb_current_user->ID ) . '">'. __('View your profile') ."&raquo;</a>\n<small>(",get_user_name( $bb_current_user->ID ));
	if ( bb_current_user_can('moderate') )
		echo "<a href='" . bb_get_option( 'uri' ) . "bb-admin/'>Admin</a> | ";
	echo "<a href='" . bb_get_option( 'uri' ) . "bb-login.php?logout'>". __('Log out') ."</a>)</small></p>";
	} else
		bb_load_template( 'login-form.php' );
}

function search_form( $q = '' ) {
	bb_load_template( 'search-form.php', array('q' => $q) );
}

function bb_post_template() {
	bb_load_template( 'post.php' );
}

function post_form( $h2 = '' ) {
	global $bb_current_user, $bb, $page, $topic, $forum;
	$add = topic_pages_add();
	if ( empty($h2) && false !== $h2 ) {
		if ( is_topic() )
			$h2 =  __('Reply');
		elseif ( is_forum() )
			$h2 = __('New Topic in this Forum');
		elseif ( is_tag() || is_front() )
			$h2 = __('Add New Topic');
	}

	$last_page = get_page_number( $topic->topic_posts + $add );

	if ( !empty($h2) ) {
		if ( $page != $last_page )
			$h2 = $h2 . ' <a href="' . get_topic_link( 0, $last_page ) . '#postform">&raquo;</a>';
		echo "<h2 class='post-form'>$h2</h2>\n";
	}

	do_action('pre_post_form');

	if ( ( is_topic() && bb_current_user_can( 'write_post', $topic->topic_id ) && $page == $last_page ) || ( !is_topic() && bb_current_user_can( 'write_topic', $forum->forum_id ) ) ) {
		echo "<form class='postform' name='postform' id='postform' method='post' action='" . bb_get_option('uri') . "bb-post.php'>\n";
		bb_load_template( 'post-form.php', array('h2' => $h2) );
		bb_nonce_field( is_topic() ? 'create-post_' . $topic->topic_id : 'create-topic' );
		if ( is_forum() )
			echo "<input type='hidden' name='forum_id' value='$forum->forum_id' />\n";
		else if ( is_topic() )
			echo "<input type='hidden' name='topic_id' value='$topic->topic_id' />\n";
		do_action('post_form');	
		echo "\n</form>";
	} elseif ( !bb_is_user_logged_in() ) {
		echo '<p>';
		printf(__('You must <a href="%s">log in</a> to post.'), bb_get_option('uri') . 'bb-login.php');
		echo '</p>';
	}
	do_action('post_post_form');
}

function edit_form() {
	global $bb_post, $topic_title;
	echo "<form name='post' id='post' method='post' action='" . bb_get_option('uri')  . "bb-edit.php'>\n";
	bb_load_template( 'edit-form.php', array('topic_title') );
	bb_nonce_field( 'edit-post_' . $bb_post->post_id );
	echo "\n</form>";
}

function alt_class( $key, $others = '' ) {
	echo get_alt_class( $key, $others );
}

function get_alt_class( $key, $others = '' ) {
	global $bb_alt;
	$class = '';
	if ( !isset( $bb_alt[$key] ) ) $bb_alt[$key] = -1;
	++$bb_alt[$key];
	if ( $others xor $bb_alt[$key] % 2 )
		$class = ' class="' . ( ($others) ? $others : 'alt' ) . '"';
	elseif ( $others && $bb_alt[$key] % 2 )
		$class = ' class="' . $others . ' alt"';
	return $class;
}

function bb_location() {
	echo apply_filters( 'bb_location', get_bb_location() );
}

function get_bb_location() { // Not for display.  Do not internationalize.
	$file = '';
	foreach ( array($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_FILENAME'], $_SERVER['SCRIPT_NAME']) as $name )
		if ( false !== strpos($name, '.php') )
			$file = $name;

	switch ( bb_find_filename( $file ) ) :
	case 'index.php' :
		return 'front-page';
		break;
	case 'forum.php' :
		return 'forum-page';
		break;
	case 'tags.php' :
		return 'tag-page';
		break;
	case 'topic.php' :
		return 'topic-page';
		break;
	case 'rss.php' :
		return 'feed-page';
		break;
	case 'search.php' :
		return 'search-page';
		break;
	case 'profile.php' :
		return 'profile-page';
		break;
	case 'favorites.php' :
		return 'favorites-page';
		break;
	case 'view.php' :
		return 'view-page';
		break;
	case 'statistics.php' :
		return 'stats-page';
		break;
	case 'bb-login.php' :
		return 'login-page';
		break;
	default:
		return apply_filters( 'get_bb_location', '' );
		break;
	endswitch;
}

function is_front() {
	if ( 'front-page' == get_bb_location() )
		return true;
	else
		return false;
}

function is_forum() {
	if ( 'forum-page' == get_bb_location() )
		return true;
	else
		return false;
}

function is_tag() {
	if ( 'tag-page' == get_bb_location() )
		return true;
	else
		return false;
}

function is_topic() {
	if ( 'topic-page' == get_bb_location() )
		return true;
	else
		return false;
}

function is_bb_feed() {
	if ( 'feed-page' == get_bb_location() )
		return true;
	else
		return false;
}

function is_bb_search() {
	if ( 'search-page' == get_bb_location() )
		return true;
	else
		return false;
}

function is_bb_profile() {
	if ( 'profile-page' == get_bb_location() )
		return true;
	else
		return false;
}

function is_bb_favorites() {
	if ( 'favorites-page' == get_bb_location() )
		return true;
	else
		return false;
}

function is_view() {
	if ( 'view-page' == get_bb_location() )
		return true;
	else
		return false;
}

function is_bb_stats() {
	if ( 'stats-page' == get_bb_location() )
		return true;
	else
		return false;
}

function bb_title() {
	echo apply_filters( 'bb_title', bb_get_title() );
}

function bb_get_title() {
	global $topic, $forum, $tag, $user;
	$title = '';
	if ( is_topic() )
		$title = get_topic_title(). ' &laquo; ';
	if ( is_forum() )
		$title = get_forum_name() . ' &laquo; ';
	if ( is_tag() )
		$title = wp_specialchars( get_tag_name() ). ' &laquo; ' . __('Tags') . ' &laquo; ';
	if ( is_bb_profile() )
		$title = get_user_name( $user->ID ) . ' &laquo; ';
	if ( $st = bb_get_option( 'static_title' ) )
		$title = $st;
	$title .= bb_get_option('name');
	return apply_filters( 'bb_get_title', $title );
}

function bb_feed_head() {
	global $tag;
	$feed_link = '';
	if ( is_topic() )
		$feed_link = '<link rel="alternate" type="application/rss+xml" title="' . __('Topic') . ': '  . wp_specialchars( get_topic_title(), 1 ) . '" href="' . get_topic_rss_link() . '" />';
	elseif ( is_tag() && $tag )
		$feed_link = '<link rel="alternate" type="application/rss+xml" title="' . __('Tag') . ': ' . wp_specialchars( get_tag_name(), 1 ) . '" href="' . get_tag_rss_link() . '" />';
	elseif ( is_forum() )
		$feed_link = '<link rel="alternate" type="application/rss+xml" title="' . __('Forum') . ': ' . wp_specialchars( get_forum_name(), 1) . '" href="' . get_forum_rss_link() . '" />';
	elseif ( is_front() )
		$feed_link = '<link rel="alternate" type="application/rss+xml" title="' . __('Recent Posts') . '" href="' . get_recent_rss_link() . '" />';
	echo apply_filters('bb_feed_head', $feed_link);
}

function get_recent_rss_link() {
	if ( bb_get_option( 'mod_rewrite' ) )
		$link = bb_get_option( 'uri' ) . 'rss/';
	else
		$link = bb_get_option( 'uri' ) . "rss.php";
	return apply_filters( 'get_recent_rss_link', $link );
}

// FORUMS

function forum_link( $forum_id = 0, $page = 1 ) {
	echo apply_filters('forum_link', get_forum_link( $forum_id, $page ), $forum_id );
}

function get_forum_link( $forum_id = 0, $page = 1 ) {
	global $forum;

	if ( $forum_id )
		$forum = get_forum( $forum_id );
	if ( bb_get_option( 'mod_rewrite' ) )
		$link = bb_get_option( 'uri' ) . "forum/$forum->forum_id" . ( 1 < $page ? "/page/$page" : '' );
	else {
		$args = array();
		$link = bb_get_option( 'uri' ) . 'forum.php';
		$args['id'] = $forum->forum_id;
		$args['page'] = 1 < $page ? $page : '';
		$link = add_query_arg( $args, $link );
	}

	return apply_filters( 'get_forum_link', $link, $forum->forum_id );
}

function forum_name( $forum_id = 0 ) {
	echo apply_filters( 'forum_name', get_forum_name( $forum_id ), $forum_id );
}

function get_forum_name( $forum_id = 0 ) {
	global $forum;
	if ( $forum_id )
		$forum = get_forum( $forum_id );
	return apply_filters( 'get_forum_name', $forum->forum_name, $forum->forum_id );
}

function forum_id() {
	echo apply_filters( 'forum_id', get_forum_id() );
}

function get_forum_id() {
	global $forum;
	return $forum->forum_id;
}

function forum_description( $forum_id = 0 ) {
	echo apply_filters( 'forum_description', get_forum_description( $forum_id ), $forum_id );
}

function get_forum_description( $forum_id = 0 ) {
	global $forum;
	if ( $forum_id )
		$forum = get_forum( $forum_id );
	return apply_filters( 'get_forum_description', $forum->forum_desc, $forum->forum_id );
}

function forum_topics( $forum_id = 0 ) {
	echo apply_filters( 'forum_topics', get_forum_topics( $forum_id ), $forum_id );
}

function get_forum_topics( $forum_id = 0 ) {
	global $forum;
	if ( $forum_id )
		$forum = get_forum( $forum_id );
	return apply_filters( 'get_forum_topics', $forum->topics, $forum->forum_id );
}

function forum_posts( $forum_id = 0 ) {
	echo apply_filters( 'forum_posts', get_forum_posts( $forum_id ), $forum_id );
}

function get_forum_posts( $forum_id = 0 ) {
	global $forum;
	if ( $forum_id )
		$forum = get_forum( $forum_id );
	return apply_filters( 'get_forum_posts', $forum->posts, $forum->forum_id );
}

function forum_pages() {
	global $forum, $page;
	echo apply_filters( 'forum_pages', get_page_number_links( $page, $forum->topics ), $forum->forum_topics );
}

function forum_rss_link( $forum_id = 0 ) {
	echo apply_filters('forum_rss_link', get_forum_rss_link( $forum_id ) );
}

function get_forum_rss_link( $forum_id = 0 ) {
	global $forum;

	if ( $forum_id )
		$forum = get_forum( $forum_id );

	if ( bb_get_option('mod_rewrite') )
		$link = bb_get_option('uri') . "rss/forum/$forum->forum_id";
	else
		$link = bb_get_option('uri') . "rss.php?forum=$forum->forum_id";

	return apply_filters( 'get_forum_rss_link', $link, $forum_id );
}

// TOPICS
function topic_id() {
	echo apply_filters( 'topic_id', get_topic_id() );
}

function get_topic_id() {
	global $topic;
	return $topic->topic_id;
}

function topic_link( $id = 0, $page = 1 ) {
	echo apply_filters( 'topic_link', get_topic_link($id), $id );
}

function get_topic_link( $id = 0, $page = 1 ) {
	global $topic;

	if ( $id )
		$topic = get_topic( $id );

	$args = array();

	if ( bb_get_option('mod_rewrite') )
		$link = bb_get_option('uri') . "topic/$topic->topic_id" . ( 1 < $page ? "/page/$page" : '' );
	else {
		$link = bb_get_option('uri') . 'topic.php';
		$args['id'] = $topic->topic_id;
		$args['page'] = 1 < $page ? $page : '';
	}

	if ( bb_current_user_can('write_posts') )
		$args['replies'] = $topic->topic_posts;
	if ( $args )
		$link = add_query_arg( $args, $link );

	return apply_filters( 'get_topic_link', $link, $topic->topic_id );
}

function topic_rss_link( $id = 0 ) {
	echo apply_filters('topic_rss_link', get_topic_rss_link($id), $id );
}

function get_topic_rss_link( $id = 0 ) {
	global $topic;

	if ( $id )
		$topic = get_topic( $id );

	if ( bb_get_option('mod_rewrite') )
		$link = bb_get_option('uri') . "rss/topic/$topic->topic_id";
	else
		$link = bb_get_option('uri') . "rss.php?topic=$topic->topic_id";

	return apply_filters( 'get_topic_rss_link', $link, $topic->topic_id );
}

function topic_title( $id = 0 ) {
	echo apply_filters( 'topic_title', get_topic_title( $id ), $id );
}

function get_topic_title( $id = 0 ) {
	global $topic;
	if ( $id )
		$topic = get_topic( $id );
	return apply_filters( 'get_topic_title', $topic->topic_title, $topic->topic_id );
}

function topic_posts( $id = 0 ) {
	echo apply_filters( 'topic_posts', get_topic_posts(), $id );
}

function get_topic_posts() {
	global $topic;
	if ( $id )
		$topic = get_topic( $id );
	return apply_filters( 'get_topic_posts', $topic->topic_posts, $topic->topic_id );
}

function get_topic_deleted_posts( $id = 0 ) {
	global $topic;
	if ( $id )
		$topic = get_topic( $id );
	return apply_filters( 'get_topic_deleted_posts', $topic->deleted_posts, $topic->topic_id );
}

function topic_noreply( $title ) {
	if ( 1 == get_topic_posts() && ( is_front() || is_forum() ) )
		$title = "<strong>$title</strong>";
	return $title;
}

function topic_last_poster( $id = 0 ) {
	global $topic;
	if ( $id )
		$topic = get_topic( $id );
	echo apply_filters( 'topic_last_poster', get_topic_last_poster( $id ), $topic->topic_last_poster ); // Last arg = user ID
}

function get_topic_last_poster( $id = 0 ) {
	global $topic;
	if ( $id )
		$topic = get_topic( $id );
	return apply_filters( 'get_topic_last_poster', $topic->topic_last_poster_name, $topic->topic_last_poster ); // Last arg = user ID
}

function topic_author( $id = 0 ) {
	global $topic;
	if ( $id )
		$topic = get_topic( $id );
	echo apply_filters( 'topic_author', get_topic_author( $id ), $topic->topic_poster ); // Last arg = user ID
}

function get_topic_author( $id = 0 ) {
	global $topic;
	if ( $id )
		$topic = get_topic( $id );
	return apply_filters( 'get_topic_author', $topic->topic_poster_name, $topic->topic_poster ); // Last arg = user ID
}

// Filters expect the format to by mysql on both topic_time and get_topic_time
function topic_time( $args = '' ) {
	$args = _bb_parse_time_function_args( $args );
	$time = apply_filters( 'topic_time', get_topic_time( array('format' => 'mysql') + $args), $args );
	echo _bb_time_function_return( $time, $args );
}

function get_topic_time( $args = '' ) {
	$args = _bb_parse_time_function_args( $args );

	global $topic;
	if ( $args['id'] )
		$_topic = get_topic( $args['id'] );
	else
		$_topic =& $topic; 

	$time = apply_filters( 'get_topic_time', $_topic->topic_time, $args );

	return _bb_time_function_return( $time, $args );
}

function topic_start_time( $args = '' ) {
	$args = _bb_parse_time_function_args( $args );
	$time = apply_filters( 'topic_start_time', get_topic_start_time( array('format' => 'mysql') + $args), $args );
	echo _bb_time_function_return( $time, $args );
}

function get_topic_start_time( $args = '' ) {
	$args = _bb_parse_time_function_args( $args );

	global $topic;
	if ( $args['id'] )
		$_topic = get_topic( $args['id'] );
	else
		$_topic =& $topic;

	$time = apply_filters( 'get_topic_start_time', $_topic->topic_start_time, $args );

	return _bb_time_function_return( $time, $args );
}

function topic_last_post_link( $id = 0 ) {
	global $topic;
	echo apply_filters( 'topic_last_post_link', get_topic_last_post_link( $id ));
}

function get_topic_last_post_link( $id = 0 ){
	global $topic;
	if ( $id )
		$topic = get_topic( $id );
	$page = get_page_number( $topic->topic_posts );
	return apply_filters( 'get_post_link', get_topic_link( $topic->topic_id, $page ) . "#post-$topic->topic_last_post_id", $topic->topic_last_post_id );
}

function topic_pages() {
	global $topic, $page;
	$add = topic_pages_add();
	echo apply_filters( 'topic_pages', get_page_number_links( $page, $topic->topic_posts + $add ), $topic->topic_id );
}

function topic_pages_add() {
	global $topic;
	if ( isset($_GET['view']) && 'all' == $_GET['view'] && bb_current_user_can('browse_deleted') ) :
		$add += $topic->deleted_posts;
	endif;
	return apply_filters( 'topic_pages_add', $add, $topic->topic_id );
}

function get_page_number_links($page, $total) {
	$r = '';
	$args = array();
	$uri = $_SERVER['REQUEST_URI'];
	if ( bb_get_option('mod_rewrite') ) :
		if ( 1 == $page ) :
			if ( false === $pos = strpos($uri, '?') )
				$uri = $uri . '%_%';
			else
				$uri = substr_replace($uri, '%_%', $pos, 0);
		else :
			$uri = preg_replace('|/page/[0-9]+|', '%_%', $uri);
		endif;
	else :
		$uri = add_query_arg( 'page', '%_%', $uri );
	endif;

	if ( isset($_GET['view']) && in_array($_GET['view'], get_views()) )
		$args['view'] = $_GET['view'];

	return paginate_links( array(
		'base' => $uri,
		'format' => bb_get_option('mod_rewrite') ? '/page/%#%' : '%#%',
		'total' => ceil($total/bb_get_option('page_topics')),
		'current' => $page,
		'add_args' => $args
	) );
}

function topic_delete_link( $args = '' ) {
	$defaults = array( 'id' => 0, 'pre' => '[', 'post' => ']' );
	extract(bb_parse_args( $args, $defaults ));
	$id = (int) $id;

	global $topic;
	if ( $id )
		$_topic = get_topic( $id );
	else
		$_topic =& $topic;

	if ( !$_topic || !bb_current_user_can( 'delete_topic', $_topic->topic_id ) )
		return;

	if ( 0 == $_topic->topic_status )
		echo "$pre<a href='" . bb_nonce_url( bb_get_option('uri') . 'bb-admin/delete-topic.php?id=' . $_topic->topic_id , 'delete-topic_' . $_topic->topic_id ) . "' onclick=\"return confirm('" . __('Are you sure you wanna delete that?') . "')\">" . __('Delete entire topic') . "</a>$post";
	else
		echo "$pre<a href='" . bb_nonce_url( bb_get_option('uri') . 'bb-admin/delete-topic.php?id=' . $_topic->topic_id . '&view=all', 'delete-topic_' . $_topic->topic_id ) . "' onclick=\"return confirm('" . __('Are you sure you wanna undelete that?') . "')\">" . __('Undelete entire topic') . "</a>$post";
}

function topic_close_link( $args = '' ) {
	$defaults = array( 'id' => 0, 'pre' => '[', 'post' => ']' );
	extract(bb_parse_args( $args, $defaults ));
	$id = (int) $id;

	global $topic;
	if ( $id )
		$_topic = get_topic( $id );
	else
		$_topic =& $topic;

	if ( !$topic || !bb_current_user_can( 'close_topic', $_topic->topic_id ) )
		return;

	if ( topic_is_open( $_topic->id ) )
		$text = __('Close topic');
	else
		$text = __('Open topic');
	echo "$pre<a href='" . bb_nonce_url( bb_get_option('uri') . 'bb-admin/topic-toggle.php?id=' . $_topic->topic_id, 'close-topic_' . $_topic->topic_id ) . "'>$text</a>$post";
}

function topic_sticky_link() {
	$defaults = array( 'id' => 0, 'pre' => '[', 'post' => ']' );
	extract(bb_parse_args( $args, $defaults ));
	$id = (int) $id;

	global $topic;
	if ( $id )
		$_topic = get_topic( $id );
	else
		$_topic =& $topic;

	if ( !$_topic || !bb_current_user_can( 'stick_topic', $_topic->topic_id ) )
		return;

	if ( topic_is_sticky( $_topic->topic_id ) )
		echo "$pre<a href='" . bb_nonce_url( bb_get_option('uri') . 'bb-admin/sticky.php?id=' . $_topic->topic_id, 'stick-topic_' . $_topic->topic_id ) . "'>". __('Unstick topic') ."</a>$post";
	else
		echo "$pre<a href='" . bb_nonce_url( bb_get_option('uri') . 'bb-admin/sticky.php?id=' . $_topic->topic_id, 'stick-topic_' . $_topic->topic_id ) . "'>". __('Stick topic') . "</a> (<a href='" . bb_nonce_url( bb_get_option('uri') . 'bb-admin/sticky.php?id=' . $_topic->topic_id . '&super=1', 'stick-topic_' . $topic->topic_id ) . "'>" . __('to front') . "</a>)$post";
}

function topic_show_all_link() {
	global $bb_current_user;
	if ( !bb_current_user_can('browse_deleted') )
		return;
	if ( 'all' == @$_GET['view'] )
		echo "<a href='" . get_topic_link() . "'>". __('View normal posts') ."</a>";
	else
		echo "<a href='" . wp_specialchars( add_query_arg( 'view', 'all', get_topic_link() ) ) . "'>". __('View all posts') ."</a>";
}

function topic_posts_link() {
	global $bb_current_user, $topic;
	$post_num = get_topic_posts();
	$posts = sprintf(__ngettext( '%s post', '%s posts', $post_num ), $post_num);
	if ( 'all' == @$_GET['view'] && bb_current_user_can('browse_deleted') )
		echo "<a href='" . get_topic_link() . "'>$posts</a>";
	else
		echo $posts;

	if ( bb_current_user_can('browse_deleted') ) {
		if ( isset($topic->bozos[$bb_current_user->ID]) && 'all' != @$_GET['view'] )
			add_filter('get_topic_deleted_posts', create_function('$a', "\$a -= {$topic->bozos[$bb_current_user->ID]}; return \$a;") );
		if ( $deleted = get_topic_deleted_posts() ) {
			$extra = sprintf(__('+%d more'), $deleted);
			if ( 'all' == @$_GET['view'] )
				echo " $extra";
			else
				echo " <a href='" . wp_specialchars( add_query_arg( 'view', 'all', get_topic_link() ) ) . "'>$extra</a>";
		}
	}
}

function topic_move_dropdown() {
	global $bb_current_user, $forum_id, $topic;
	if ( !bb_current_user_can( 'move_topic', get_topic_id() ) )
		return;
	$forum_id = $topic->forum_id;

	echo '<form id="topic-move" method="post" action="' . bb_get_option('uri') . 'bb-admin/topic-move.php"><div>' . "\n\t";
	echo '<input type="hidden" name="topic_id" value="' . get_topic_id() . '" />' . "\n\t";
	echo '<label for="forum_id">'. __('Move this topic to the selected forum:');
	forum_dropdown();
	echo "</label>\n\t";
	bb_nonce_field( 'move-topic_' . $topic->topic_id );
	echo "<input type='submit' name='Submit' value='". __('Move') ."' />\n</div></form>";
}

function topic_class( $class = '', $key = 'topic' ) {
	global $topic;
	$class = $class ? explode(' ', $class ) : array();
	if ( '1' === $topic->topic_status && bb_current_user_can( 'browse_deleted' ) )
		$class[] = 'deleted';
	elseif ( 1 < $topic->topic_status && bb_current_user_can( 'browse_deleted' ) )
		$class[] = 'bozo';
	if ( '0' === $topic->topic_open )
		$class[] = 'closed';
	if ( 1 == $topic->topic_sticky && is_forum() )
		$class[] = 'sticky';
	elseif ( 2 == $topic->topic_sticky && ( is_front() || is_forum() ) )
		$class[] = 'sticky super-sticky';
	$class = apply_filters( 'topic_class', $class, $topic->topic_id );
	$class = join(' ', $class);
	alt_class( $key, $class );
}

function new_topic( $text = false ) {
	global $forum;
	if ( !$text )
		$text = __('Add New &raquo;');

	if ( is_forum() || is_tag() )
		$url = '#postform';
	elseif ( is_front() )
		$url = add_query_arg( 'new', '1', bb_get_option( 'uri' ) );
	if ( !bb_is_user_logged_in() )
		$url = add_query_arg( 're', urlencode($url), bb_get_option( 'uri' ) . 'bb-login.php' );
	elseif ( is_forum() ) {
		if ( !bb_current_user_can( 'write_topic', $forum->forum_id ) )
			return;
	} else {
		if ( !bb_current_user_can( 'write_topics' ) )
			return;
	}

	if ( $url = apply_filters( 'new_topic_url', $url ) )
		echo "<a href='$url' class='new-topic'>$text</a>\n";
}

// POSTS

function post_id() {
	global $bb_post;
	echo $bb_post->post_id;
}

function get_post_id() {
	global $bb_post;
	return $bb_post->post_id;
}

function post_anchor_link( $force_full = false ) {
	if ( defined('DOING_AJAX') || $force_full )
		post_link();
	else
		echo '#post-' . get_post_id();
}


function post_author() {
	echo apply_filters('post_author', get_post_author() );
}

function get_post_author() {
	global $bbdb;
	$id = get_post_author_id();
	if ( $id )
		if ( $user = bb_get_user( $id ) )
			return apply_filters( 'get_post_author', $user->user_login, $id );
	else
		return __('Anonymous');
}

function post_author_link() {
	if ( get_user_link( get_post_author_id() ) ) {
		echo '<a href="' . get_user_link( get_post_author_id() ) . '">' . get_post_author() . '</a>';
	} else {
		post_author();
	}
}

function post_text() {
	echo apply_filters( 'post_text', get_post_text() );
}

function get_post_text() {
	global $bb_post;
	return $bb_post->post_text;
}

function bb_post_time( $args = '' ) {
	$args = _bb_parse_time_function_args( $args );
	$time = apply_filters( 'bb_post_time', bb_get_post_time( array('format' => 'mysql') + $args ), $args );
	echo _bb_time_function_return( $time, $args );
}

function bb_get_post_time( $args = '' ) {
	$args = _bb_parse_time_function_args( $args );

	global $bb_post;
	if ( $args['id'] )
		$_bb_post = bb_get_post( $args['id'] );
	else
		$_bb_post =& $bb_post;

	$time = apply_filters( 'bb_get_post_time', $_bb_post->post_time, $args );

	return _bb_time_function_return( $time, $args );
}

function post_ip() {
	if ( bb_current_user_can( 'view_by_ip' ) )
		echo apply_filters( 'post_ip', get_post_ip(), get_post_id() );
}

function get_post_ip() {
	global $bb_post;
	return $bb_post->poster_ip;
}

function post_ip_link() {
	if ( !bb_current_user_can( 'view_by_ip' ) )
		return;
	$link = '<a href="' . bb_get_option('uri') . 'bb-admin/view-ip.php?ip=' . get_post_ip() . '">' . get_post_ip() . '</a>';
	echo apply_filters( 'post_ip_link', $link, get_post_id() );
}

function post_edit_link() {
	global $bb_post;

	if ( bb_current_user_can( 'edit_post', $bb_post->post_id ) )
		echo "<a href='" . apply_filters( 'post_edit_uri', bb_get_option('uri') . 'edit.php?id=' . get_post_id(), $bb_post->post_id ) . "'>". __('Edit') ."</a>";
}

function post_del_class() {
	global $bb_current_user, $bb_post;
	switch ( $bb_post->post_status ) :
	case 0 : return ''; break;
	case 1 : return 'deleted'; break;
	default: return apply_filters( 'post_del_class', $bb_post->post_status, $bb_post->post_id );
	endswitch;
}

function post_delete_link() {
	global $bb_current_user, $bb_post;
	if ( !bb_current_user_can( 'delete_post', get_post_id() ) )
		return;

	if ( 1 == $bb_post->post_status )
		$r = "<a href='" . bb_nonce_url( bb_get_option('uri') . 'bb-admin/delete-post.php?id=' . get_post_id() . '&status=0&view=all', 'delete-post_' . get_post_id() ) . "' onclick='return confirm(\" ". __('Are you sure you wanna undelete that?') ." \");'>". __('Undelete') ."</a>";
	else
		$r = "<a href='" . bb_nonce_url( bb_get_option('uri') . 'bb-admin/delete-post.php?id=' . get_post_id() . '&status=1', 'delete-post_' . get_post_id() ) .  "' onclick='return ajaxPostDelete(" . get_post_id() . ", \"" . get_post_author() . "\");'>". __('Delete') ."</a>";
	$r = apply_filters( 'post_delete_link', $r, $bb_post->post_status, $bb_post->post_id );
	echo $r;
}

function post_author_id() {
	echo apply_filters('post_author_id', get_post_author_id() );
}

function get_post_author_id() {
	global $bb_post;
	return $bb_post->poster_id;
}

function post_author_title() {
	$title = get_post_author_title();
	if ( false === $title )
		$r = __('Unregistered'); // This should never happen
	else
		$r = '<a href="' . get_user_profile_link( get_post_author_id() ) . '">' . $title . '</a>';

	echo apply_filters( 'post_author_title', $r );
}

function get_post_author_title() {
	return get_user_title( get_post_author_id() );
}

function post_author_type() {
	$type = get_user_type( get_post_author_id() );
	if ( false === $type )
		$r = __('Unregistered'); // This should never happen
	else
		$r = '<a href="' . get_user_profile_link( get_post_author_id() ) . '">' . $type . '</a>';

	echo apply_filters( 'post_author_type', $r );
}

function allowed_markup( $args = '' ) {
	echo apply_filters( 'allowed_markup', get_allowed_markup( $args ) );
}

// format=list or array( 'format' => 'list' )
function get_allowed_markup( $args = '' ) {
	if ( is_array($args) )
		$a = &$args;
	else
		parse_str($args, $a);

	$format = 'flat';
	extract($a);

	$tags = array_keys(bb_allowed_tags());
	switch ( $format ) :
	case 'array' :
		$r = $tags;
		break;
	case 'list' :
		$r = "<ul class='allowed-markup'>\n\t<li>";
		$r .= join("</li>\n\t<li>", $tags);
		$r .= "</li>\n</ul>\n";
		break;
	default :
		$r = join(' ', $tags);
		break;
	endswitch;
	return apply_filters( 'get_allowed_markup', $r, $format );
}

// USERS
function user_profile_link( $id, $page = 1 ) {
	echo apply_filters( 'user_profile_link', get_user_profile_link( $id ), $id );
}

function get_user_profile_link( $id, $page = 1 ) {
	if ( bb_get_option('mod_rewrite') ) {
		$r = bb_get_option('uri') . "profile/$id" . ( 1 < $page ? "/page/$page" : '' );
	} else {
		$r = bb_get_option('uri') . "profile.php?id=$id" . ( 1 < $page ? "&page=$page" : '' );
	}
	return apply_filters( 'get_user_profile_link', $r, $id );
}

function user_delete_button() {
	if ( bb_current_user_can('edit_users') ) 
		echo apply_filters( 'user_delete_button', get_user_delete_button());
}

function get_user_delete_button() {
	$r  = '<input type="submit" class="delete" name="delete-user" value="' . __('Delete User &raquo;') . '" ';
	$r .= 'onclick="return confirm(\'' . js_escape(__('Are you sure you want to delete this user?')) . '\')" />';
	return apply_filters( 'get_user_delete_button', $r);
}

function profile_tab_link( $id, $tab, $page = 1 ) {
	echo apply_filters( 'profile_tab_link', get_profile_tab_link( $id, $tab ) );
}

function get_profile_tab_link( $id, $tab, $page = 1 ) {
	$tab = tag_sanitize($tab);
	if ( bb_get_option('mod_rewrite') )
		$r = get_user_profile_link( $id ) . "/$tab" . ( 1 < $page ? "/page/$page" : '' );
	else {
		$args = array('tab' => $tab);
		if ( 1 < $page )
			$args['page'] = $page;
		$r = add_query_arg( $args, get_user_profile_link( $id ) );
	}
	return apply_filters( 'get_profile_tab_link', $r, $id );
}

function user_link( $id ) {
	echo apply_filters( 'user_link', get_user_link($id), $user_id );
}

function get_user_link( $user_id ) {
	global $bbdb;
	if ( $user_id )
		if ( $user = bb_get_user( $user_id ) )
			return apply_filters( 'get_user_link', $user->user_url, $user_id );
}

function full_user_link( $id ) {
	echo get_full_user_link( $id );
}

function get_full_user_link( $id ) {
	if ( get_user_link( $id ) )
		$r = '<a href="' . get_user_link( $id ) . '">' . get_user_name( $id ) . '</a>';
	else
		$r = get_user_name( $id );
	return $r;
}

function user_type_label( $type ) {
	echo apply_filters( 'user_type_label', get_user_type_label( $type ), $type );
}

function get_user_type_label( $type ) {
	global $bb_roles;
	if ( $bb_roles->is_role( $type ) )
		return apply_filters( 'get_user_type_label', $bb_roles->role_names[$type], $type );
}

function user_type( $id ) {
	echo apply_filters( 'user_type', get_user_type($id) );
}

function get_user_type( $id ) {
	$user = bb_get_user( $id );

	if ( $id && false !== $user ) :
		@$caps = array_keys($user->capabilities);
		if ( !$caps )
			$caps[] = 'inactive';

		$type = get_user_type_label( $caps[0] ); //Just support one role for now.
	else :
		$type = false;
	endif;
	return apply_filters( 'get_user_type', $type, $user->ID );
}

function get_user_name( $id ) {
	$user = bb_get_user( $id );
	return apply_filters( 'get_user_name', $user->user_login, $user->ID );
}

function user_title( $id ) {
	echo apply_filters( 'user_title', get_user_title( $id ), $id );
}

function get_user_title( $id ) {
	$user = bb_get_user( $id );

	if ( !empty( $user->title ) )
		return apply_filters( 'get_user_title', $user->title, $id );
	else
		return get_user_type( $id );
}

function profile_pages() {
	global $user, $page;
	$add = 0;
	$add = apply_filters( 'profile_pages_add', $add );
	echo apply_filters( 'topic_pages', get_page_number_links( $page, $user->topics_replied + $add ) );
}

function bb_profile_data() {
	global $user_id;

	$user = bb_get_user( $user_id );
	$reg_time = bb_gmtstrtotime( $user->user_registered );
	$profile_info_keys = get_profile_info_keys();
	echo "<dl id='userinfo'>\n";
	echo "\t<dt>" . __('Member Since') . "</dt>\n";
	echo "\t<dd>" . gmdate(__('F j, Y'), $reg_time) . ' (' . bb_since($reg_time) . ")</dd>\n";
	if ( is_array( $profile_info_keys ) ) {
		foreach ( $profile_info_keys as $key => $label ) {
			if ( 'user_email' != $key && isset($user->$key) && '' !== $user->$key && 'http://' != $user->$key ) {
				echo "\t<dt>{$label[1]}</dt>\n";
				echo "\t<dd>" . make_clickable($user->$key) . "</dd>\n";
			}
		}
	}
	echo "</dl>\n";
}

function bb_profile_base_content() {
	global $self;
	if ( !is_callable( $self ) )
		return; // should never happen
	call_user_func( $self );
}

//TAGS
function topic_tags() {
	global $tags, $tag, $topic_tag_cache, $user_tags, $other_tags, $bb_current_user, $topic;
	if ( is_array( $tags ) || bb_current_user_can( 'edit_tag_by_on', $bb_current_user->ID, $topic->topic_id ) )
		bb_load_template( 'topic-tags.php', array('user_tags', 'other_tags') );
}

function tag_page_link() {
	echo get_tag_page_link();
}

function get_tag_page_link() {
	if ( bb_get_option('mod_rewrite') )
		return bb_get_option( 'domain' ) . bb_get_option( 'tagpath' ) . 'tags/';
	else
		return bb_get_option( 'domain' ) . bb_get_option( 'tagpath' ) . 'tags.php';
}

function tag_link( $id = 0, $page = 1 ) {
	echo get_tag_link( $id );
}

function get_tag_link( $tag_name = 0, $page = 1 ) {
	global $tag;
	if ( $tag_name )
		$_tag = get_tag_by_name( $tag_name );
	else
		$_tag =& $tag;
	if ( bb_get_option('mod_rewrite') )
		return bb_get_option('domain') . bb_get_option( 'tagpath' ) . "tags/$_tag->tag" . ( 1 < $page ? "/page/$page" : '' );
	else
		return bb_get_option('domain') . bb_get_option( 'tagpath' ) . "tags.php?tag=$_tag->tag" . ( 1 < $page ? "&page=$page" : '' );
}

function tag_link_base() {
	echo get_tag_link_base();
}

function get_tag_link_base() {
	if ( bb_get_option('mod_rewrite') )
		return bb_get_option('domain') . bb_get_option( 'tagpath' ) . 'tags/';
	else
		return bb_get_option('domain') . bb_get_option( 'tagpath' ) . 'tags.php?tag=';
}

function tag_name( $id = 0 ) {
	echo wp_specialchars( get_tag_name( $id ) );
}

function get_tag_name( $id = 0 ) {
	global $tag;
	$id = (int) $id;
	if ( $id )
		$_tag = get_tag( $id );
	else
		$_tag =& $tag;
	return $_tag->raw_tag;
}

function tag_rss_link( $id = 0 ) {
	echo apply_filters( 'tag_rss_link', get_tag_rss_link($id), $id );
}

function get_tag_rss_link( $tag_id = 0 ) {
	global $tag;
	$tag_id = (int) $tag_id;
	if ( $tag_id )
		$_tag = get_tag( $tag_id );
	else
		$_tag =& $tag;

	if ( bb_get_option('mod_rewrite') )
		$link = bb_get_option('uri') . "rss/tags/$_tag->tag";
	else
		$link = bb_get_option('uri') . "rss.php?tag=$_tag->tag";

	return apply_filters( 'get_tag_rss_link', $link, $tag_id );
}

function tag_form() {
	global $topic, $bb_current_user;
	if ( !bb_current_user_can( 'edit_tag_by_on', $bb_current_user->ID, $topic->topic_id ) )
		return false;
	echo "<form id='tag-form' method='post' action='" . bb_get_option('uri') . "tag-add.php'>\n";
	bb_load_template( 'tag-form.php' );
	bb_nonce_field( 'add-tag_' . $topic->topic_id );
	echo "</form>";
}

function manage_tags_forms() {
	global $tag, $bb_current_user;
	if ( !bb_current_user_can('manage_tags') )
		return false;
	$form  = "<ul id='manage-tags'>\n ";
	$form .= "<li id='tag-rename'>". __('Rename tag:') ."\n\t";
	$form .= "<form method='post' action='" . bb_get_option('uri') . "bb-admin/tag-rename.php'><div>\n\t";
	$form .= "<input type='text' name='tag' size='10' maxlength='30' />\n\t";
	$form .= "<input type='hidden' name='id' value='$tag->tag_id' />\n\t";
	$form .= "<input type='submit' name='Submit' value='". __('Rename') ."' />\n\t";
	echo $form;
	bb_nonce_field( 'rename-tag_' . $tag->tag_id );
	echo "\n\t</div></form>\n  </li>\n ";
	$form  = "<li id='tag-merge'>". __('Merge this tag into:') ."\n\t";
	$form .= "<form method='post' action='" . bb_get_option('uri') . "bb-admin/tag-merge.php'><div>\n\t";
	$form .= "<input type='text' name='tag' size='10' maxlength='30' />\n\t";
	$form .= "<input type='hidden' name='id' value='$tag->tag_id' />\n\t";
	$form .= "<input type='submit' name='Submit' value='". __('Merge') ."' ";
	$form .= "onclick='return confirm(\" ". sprintf(__('Are you sure you want to merge the &#039;%s&#039; tag into the tag you specified? This is permanent and cannot be undone.'), wp_specialchars( $tag->raw_tag )) ."\")' />\n\t";
	echo $form;
	bb_nonce_field( 'merge-tag_' . $tag->tag_id );
	echo "\n\t</div></form>\n  </li>\n ";
	$form  = "<li id='tag-destroy'>". __('Destroy tag:') ."\n\t";
	$form .= "<form method='post' action='" . bb_get_option('uri') . "bb-admin/tag-destroy.php'><div>\n\t";
	$form .= "<input type='hidden' name='id' value='$tag->tag_id' />\n\t";
	$form .= "<input type='submit' name='Submit' value='". __('Destroy') ."' ";
	$form .= "onclick='return confirm(\" ". sprintf(__('Are you sure you want to destroy the &#039;%s&#039; tag? This is permanent and cannot be undone.'), wp_specialchars( $tag->raw_tag )) ."\")' />\n\t";
	echo $form;
	bb_nonce_field( 'destroy-tag_' . $tag->tag_id );
	echo "\n\t</div></form>\n  </li>\n</ul>";
}

function tag_remove_link() {
	echo get_tag_remove_link();
}

function get_tag_remove_link() {
	global $tag, $bb_current_user, $topic;
	if ( !bb_current_user_can( 'edit_tag_by_on', $tag->user_id, $topic->topic_id ) )
		return false;
	$url = add_query_arg( array('tag' => $tag->tag_id, 'user' => $tag->user_id, 'topic' => $tag->topic_id), bb_get_option('uri') . 'tag-remove.php' );
	$r = '[<a href="' . bb_nonce_url( $url, 'remove-tag_' . $tag->tag_id . '|' . $tag->topic_id) . '" onclick="return ajaxDelTag(' . $tag->tag_id . ', ' . $tag->user_id . ', \'' . js_escape($tag->raw_tag) . '\');" title="'. __('Remove this tag') .'">x</a>]';
	return $r;
}

function tag_heat_map( $args = '' ) {
	$defaults = array( 'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'limit' => 45 );
	$args = bb_parse_args( $args, $defaults );

	if ( 1 < $fn = func_num_args() ) : // For back compat
		$args['smallest'] = func_get_arg(0);
		$args['largest']  = func_get_arg(1);
		$args['unit']     = 2 < $fn ? func_get_arg(2) : $unit;
		$args['limit']    = 3 < $fn ? func_get_arg(3) : $limit;
	endif;

	extract($args);

	$tags = get_top_tags( false, $limit );

	if ( empty($tags) )
		return;

	$r = bb_get_tag_heat_map( $tags, $args );
	echo apply_filters( 'tag_heat_map', $r, $args );
}

function bb_related_tags_heat_map( $args = '' ) {
	if ( $args && is_string($args) && false === strpos($args, '=') || is_numeric($args) )
		$args = array( 'tag' => $args );

	$defaults = array( 'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'limit' => 45, 'tag' => false );
	$args = bb_parse_args( $args, $defaults );

	if ( 1 < $fn = func_num_args() ) : // For back compat
		$args['smallest'] = func_get_arg(0);
		$args['largest']  = func_get_arg(1);
		$args['unit']     = 2 < $fn ? func_get_arg(2) : $unit;
		$args['limit']    = 3 < $fn ? func_get_arg(3) : $limit;
	endif;

	extract($args);

	$tags = bb_related_tags( $tag, $limit );

	if ( empty($tags) )
		return;

	$r = bb_get_tag_heat_map( $tags, $args );
	echo apply_filters( 'bb_related_tags_heat_map', $r, $args );
}

function bb_get_tag_heat_map( $tags, $args = '' ) {
	$defaults = array( 'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'limit' => 45 );
	$args = bb_parse_args( $args, $defaults );
	extract($args);

	if ( !$tags )
		return;

	foreach ( (array) $tags as $tag ) {
		$counts{$tag->raw_tag} = $tag->tag_count;
		$taglinks{$tag->raw_tag} = get_tag_link( $tag->tag );
	}

	$min_count = min($counts);
	$spread = max($counts) - $min_count;
	if ( $spread <= 0 )
		$spread = 1;
	$fontspread = $largest - $smallest;
	if ( $fontspread <= 0 )
		$fontspread = 1;
	$fontstep = $fontspread / $spread;

	do_action_ref_array( 'sort_tag_heat_map', array(&$counts) );

	$r = '';

	foreach ( $counts as $tag => $count ) {
		$taglink = $taglinks{$tag};
		$tag = str_replace(' ', '&nbsp;', wp_specialchars( $tag ));
		$r .= "<a href='$taglink' title='$count topics' rel='tag' style='font-size: " .
			( $smallest + ( ( $count - $min_count ) * $fontstep ) )
			. "$unit;'>$tag</a>\n";
	}

	return apply_filters( 'bb_get_tag_heat_map', $r, $tags, $args );
}

function bb_sort_tag_heat_map( &$tag_counts ) {
	uksort($tag_counts, 'strnatcasecmp');
}

function tag_pages() {
	global $page, $tagged_topic_count;
	echo apply_filters( 'topic_pages', get_page_number_links( $page, $tagged_topic_count ) );
}

function forum_dropdown( $callback = false, $callback_args = false ) {
	global $forum_id;
	$forums = get_forums();
	echo '<select name="forum_id" id="forum_id" tabindex="5">';

	foreach ( $forums as $forum ) :
		if ( is_callable($callback) && false == call_user_func( $callback, $forum->forum_id, $callback_args ) )
			continue;
		$selected = ( $forum_id == $forum->forum_id ) ? " selected='selected'" : '';
		echo "<option value='$forum->forum_id'$selected>$forum->forum_name</option>";
	endforeach;
	echo '</select>';
}

//FAVORITES
function favorites_link( $user_id = 0 ) {
	echo apply_filters( 'favorites_link', get_favorites_link( $user_id ) );
}

function get_favorites_link( $user_id = 0 ) {
	global $bb_current_user;
	if ( !$user_id )
		$user_id = $bb_current_user->ID;
	return apply_filters( 'get_favorites_link', get_profile_tab_link($user_id, 'favorites'), $user_id );
}

function user_favorites_link($add = array(), $rem = array(), $user_id = 0) {
	global $topic, $bb_current_user;
	if ( empty($add) || !is_array($add) )
		$add = array('mid' => __('Add this topic to your favorites'), 'post' => __(' (%?%)'));
	if ( empty($rem) || !is_array($rem) )
		$rem = array( 'pre' => __('This topic is one of your %favorites% ['), 'mid' => __('x'), 'post' => __(']'));
	if ( $user_id ) :
		if ( !bb_current_user_can( 'edit_favorites_of', (int) $user_id ) )
			return false;
		if ( !$user = bb_get_user( $user_id ) ) :
			return false;
		endif;
	else :
		if ( !bb_current_user_can('edit_favorites') )
			return false;
		$user =& $bb_current_user->data;
	endif;

	if ( 1 == $is_fav = is_user_favorite( $user->ID, $topic->topic_id ) ) :
		$rem = preg_replace('|%(.+)%|', "<a href='" . get_favorites_link( $user_id ) . "'>$1</a>", $rem);
		$favs = array('fav' => '0', 'topic_id' => $topic->topic_id);
		$pre  = ( is_array($rem) && isset($rem['pre'])  ) ? $rem['pre']  : '';
		$mid  = ( is_array($rem) && isset($rem['mid'])  ) ? $rem['mid']  : ( is_string($rem) ? $rem : '' );
		$post = ( is_array($rem) && isset($rem['post']) ) ? $rem['post'] : '';
	elseif ( 0 === $is_fav ) :
		$add = preg_replace('|%(.+)%|', "<a href='" . get_favorites_link( $user_id ) . "'>$1</a>", $add);
		$favs = array('fav' => '1', 'topic_id' => $topic->topic_id);
		$pre  = ( is_array($add) && isset($add['pre'])  ) ? $add['pre']  : '';
		$mid  = ( is_array($add) && isset($add['mid'])  ) ? $add['mid']  : ( is_string($add) ? $add : '' );
		$post = ( is_array($add) && isset($add['post']) ) ? $add['post'] : '';
	endif;
	if ( false !== $is_fav )
		echo "$pre<a href='" . bb_nonce_url( add_query_arg( $favs, get_favorites_link( $user_id ) ), 'toggle-favorite_' . $topic->topic_id ) . "'>$mid</a>$post";
}

function favorites_rss_link( $id = 0 ) {
	echo apply_filters('favorites_rss_link', get_favorites_rss_link( $id ));
}

function get_favorites_rss_link( $id = 0 ) {
	global $user;
	if ( $id )
		$user = bb_get_user( $id );

	if ( bb_get_option('mod_rewrite') )
		$link = bb_get_option('uri') . "rss/profile/$user->ID";
	else
		$link = bb_get_option('uri') . "rss.php?profile=$user->ID";

	return apply_filters( 'get_favorites_rss_link', $link, $user_id );
}

function favorites_pages() {
	global $page, $user, $favorites_total;
	echo apply_filters( 'favorites_pages', get_page_number_links( $page, $favorites_total ), $user->user_id );
}

//VIEWS
function view_name() { // Filtration should be done at get_views() level
	echo get_view_name();
}

function get_view_name() {
	global $view;
	$views = get_views();
	return $views[$view];
}

function view_pages() {
	global $page, $view_count;
	echo apply_filters( 'view_pages', get_page_number_links( $page, $view_count ) );
}

function view_link( $_view = false, $page = 1 ) {
	echo get_view_link( $_view, $page );
}

function get_view_link( $_view = false, $page = 1 ) {
	global $view;
	if ( $_view )
		$v =& $_view;
	else
		$v =& $view;
	$views = get_views();
	if ( !array_key_exists($v, $views) )
		return bb_get_option('uri');
	if ( bb_get_option('mod_rewrite') )
		$link = bb_get_option('uri') . 'view/' . $v . ( 1 < $page ? "/page/$page" : '' );
	else
		$link = bb_get_option('uri') . "view.php?view=$v" . ( 1 < $page ? "&page=$page" : '');

	return apply_filters( 'get_view_link', $link, $v, $page );
}

function _bb_parse_time_function_args( $args ) {
	if ( is_numeric($args) )
		$args = array('id' => $args);
	elseif ( $args && is_string($args) && false === strpos($args, '=') )
		$args = array('format' => $args);

	$defaults = array( 'id' => 0, 'format' => 'since', 'more' => 0 );
	return bb_parse_args( $args, $defaults );
}

function _bb_time_function_return( $time, $args ) {
	$time = bb_gmtstrtotime( $time );

	switch ( $format = $args['format'] ) :
	case 'since' :
		return bb_since( $time, $args['more'] );
		break;
	case 'timestamp' :
		$format = 'U';
		break;
	case 'mysql' :
		$format = 'Y-m-d H:i:s';
		break;
	endswitch;

	return gmdate( $format, $time );
}

?>
