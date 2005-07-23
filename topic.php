<?php
require_once('bb-config.php');

$topic_id = $page = 0;

bb_repermalink();

if ( isset( $_GET['page'] ) )
	$page = (int) abs( $_GET['page'] );

if ( !$topic )
	die('Topic not found.');
$posts = get_thread( $topic_id, $page );
$forum = get_forum ( $topic->forum_id );

$tags  = get_topic_tags ( $topic_id );
if ( $current_user && $tags ) {
	$user_tags  = get_user_tags  ( $topic_id, $current_user->ID );
	$other_tags = get_other_tags ( $topic_id, $current_user->ID );
} elseif ( is_array($tags) ) {
	$user_tags  = false;
	$other_tags = get_public_tags( $topic_id );
} else {
	$user_tags  = false;
	$other_tags = false;
}

$list_start = $page * bb_get_option('page_topics') + 1;

post_author_cache($posts);

bb_do_action( 'bb_topic.php', $topic_id );

include('bb-templates/topic.php');

?>
