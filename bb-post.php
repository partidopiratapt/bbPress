<?php
require('./bb-load.php');

bb_auth();

nocache_headers();

if ( !bb_current_user_can('write_posts') )
	bb_die(__('You are not allowed to post.  Are you logged in?'));

if ( isset($bb_current_user->data->last_posted) && time() < $bb_current_user->data->last_posted + 30 && !bb_current_user_can('throttle') )
	bb_die(__('Slow down; you move too fast.'));

if ( !$post_content = trim($_POST['post_content']) )
	bb_die(__('You need to actually submit some content!'));

if ( isset($_POST['topic']) && $forum = (int) $_POST['forum_id'] ) {
	if ( !bb_current_user_can('write_topics') )
		bb_die(__('You are not allowed to write new topics.'));

	bb_check_admin_referer( 'create-topic' );

	$topic = trim( $_POST['topic'] );
	$tags  = trim( $_POST['tags']  );
	$support = (int) $_POST['support'];

	if ('' == $topic)
		bb_die(__('Please enter a topic title'));

	$topic_id = bb_new_topic( $topic, $forum, $tags );
	if ( 1 != $support )
		bb_resolve_topic( $topic_id, 'mu' );
} elseif ( isset($_POST['topic_id'] ) ) {
	$topic_id = (int) $_POST['topic_id'];
	bb_check_admin_referer( 'create-post_' . $topic_id );
}

if ( !topic_is_open( $topic_id ) )
	bb_die(__('This topic has been closed'));

$post_id = bb_new_post( $topic_id, $_POST['post_content'] );

$link = get_post_link($post_id);

$topic = get_topic( $topic_id, false );

if ( $topic->topic_posts )
	$link = add_query_arg( 'replies', $topic->topic_posts, $link );

if ($post_id)
	wp_redirect( $link );
else
	wp_redirect( bb_get_option( 'uri' ) );
exit;

?>
