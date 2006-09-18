<?php
require('admin-action.php');

$post_id = (int) $_GET['id'];

if ( bb_current_user_can('edit_deleted') && 'all' == $_GET['view'] ) {
	add_filter('get_topic_where', 'no_where');
	add_filter('bb_delete_post', 'topics_replied_on_undelete_post');
}

if ( !bb_current_user_can('manage_posts') ) {
	header('Location: ' . bb_get_option('uri') );
	exit();
}

bb_check_admin_referer( 'delete-post_' . $post_id );

$status  = (int) $_GET['status'];
$bb_post = bb_get_post ( $post_id );

if ( !$bb_post )
	bb_die(__('There is a problem with that post, pardner.'));

bb_delete_post( $post_id, $status );

$topic = get_topic( $bb_post->topic_id );

if ( $topic->topic_posts == 0 )
	$sendto = get_forum_link( $topic->forum_id );
else
	$sendto = $_SERVER['HTTP_REFERER'];

header( "Location: $sendto" );
exit;

?>
