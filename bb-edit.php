<?php
require('./bb-load.php');

nocache_headers();

if ( bb_current_user_can('edit_deleted') && 'all' == $_GET['view'] ) {
	bb_add_filter('bb_is_first_where', 'no_where');
}

$post_id = (int) $_POST['post_id'];

$bb_post  = bb_get_post( $post_id );

if ( !$bb_post ) {
	header('Location: ' . bb_get_option('uri') );
	die();
}

if ( !bb_current_user_can( 'edit_post', $post_id ) )
	die('Sorry, post is too old.');

if ( bb_is_first( $bb_post->post_id ) && bb_current_user_can( 'edit_topic', $bb_post->topic_id ) )
	bb_update_topic( $_POST['topic'], $bb_post->topic_id);

bb_update_post( $_POST['post_content'], $post_id, $bb_post->topic_id );

if ($post_id)
	header('Location: ' . get_post_link($post_id) );
else
	header('Location: ' . bb_get_option('uri') );
?>
