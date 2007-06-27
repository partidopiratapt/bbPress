<?php
require_once('./bb-load.php');

if ( !$q = trim( @$_GET['search'] ) )
	$q = trim( @$_GET['q'] );

$likeit = preg_replace('/\s+/', '%', $q);

if ( $likeit ) {
	$recent = $bbdb->get_results("SELECT $bbdb->posts.*, MAX(post_time) as post_time FROM $bbdb->posts RIGHT JOIN $bbdb->topics ON $bbdb->topics.topic_id = $bbdb->posts.topic_id
				WHERE LOWER(post_text) LIKE ('%$likeit%') AND post_status = 0 AND topic_status = 0
				GROUP BY $bbdb->topics.topic_id ORDER BY post_time DESC LIMIT 5");
}

$q = stripslashes( $q );

$bb_query_form = new BB_Query_Form;

if ( $q ) {
	$bb_query_form->BB_Query_Form( 'topic', array( 'search' => $q ), array( 'post_status' => 0, 'topic_status' => 0, 'search', 'forum_id', 'tag', 'topic_author' )  );
	$relevant = $bb_query_form->results;

	$q = $bb_query_form->get( 'search' );
}

do_action( 'do_search', $q );

// Cache topics
if ( $recent ) :
	$topic_ids = array();
	foreach ($recent as $bb_post) {
		$topic_ids[] = (int) $bb_post->topic_id;
		$bb_post_cache[$bb_post->post_id] = $bb_post;
	}
	$topic_ids = join($topic_ids);
	if ( $topics = $bbdb->get_results("SELECT * FROM $bbdb->topics WHERE topic_id IN ($topic_ids)") )
		$topics = bb_append_meta( $topics, 'topic' );
endif;

bb_load_template( 'search.php', array('q', 'recent', 'relevant') );

?>
