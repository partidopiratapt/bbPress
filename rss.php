<?php
require('./bb-load.php');

if ( isset($_GET['topic']) )
	$topic_id = (int) $_GET['topic'];
elseif ( 'topic' == get_path() )
	$topic_id = (int) get_path(2);

if ( isset($_GET['profile']) )
	$user_id = (int) $_GET['profile'];
elseif ( 'profile' == get_path() )
	$user_id = (int) get_path(2);

if ( isset($_GET['tag']) )
	$tag = $_GET['tag'];
elseif ( 'tags' == get_path() )
	$tag = get_path(2);

$bb_db_override = false;
bb_do_action( 'bb_rss.php_pre_db', '' );

if ( !$bb_db_override ) :
if ( isset($topic_id) ) {
	if ( !$topic = get_topic ( $topic_id ) )
		die();
	if ( !$posts = get_thread( $topic_id, 0, 1 ) )
		die();
	$title = bb_specialchars(bb_get_option('name') . ' '. __('Thread:') .' ' . get_topic_title());
} elseif ( isset($user_id) ) {
	if ( !$user = bb_get_user( $user_id ) )
		die();
	if ( !$posts = get_user_favorites( $user->ID ) )
		die();
	$title = bb_specialchars(bb_get_option('name') . ' '. __('User Favorites:') .' ' . $user->user_login);
} elseif ( isset($tag) ) {
	if ( !$tag = get_tag_by_name($tag) )
		die();
	if ( !$posts = get_tagged_topic_posts( $tag->tag_id, 0 ) )
		die();
	$title = bb_specialchars(bb_get_option('name') . ' '. __('Tag:') .' ' . get_tag_name());
} else {
	if ( !$posts = get_latest_posts( 35 ) )
		die();
	$title = bb_specialchars(bb_get_option('name')) . ': '. __('Last 35 Posts');
}
endif;

bb_do_action( 'bb_rss.php', '' );

require_once( BBPATH . 'bb-includes/feed-functions.php');

bb_send_304( $posts[0]->post_time );

bb_add_filter('post_link', 'bb_specialchars');
bb_add_filter('post_text', 'htmlspecialchars');

if (file_exists( BBPATH . 'my-templates/rss2.php'))
	require( BBPATH . 'my-templates/rss2.php' );
else	require( BBPATH . 'bb-templates/rss2.php' );
?>
