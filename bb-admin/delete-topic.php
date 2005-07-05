<?php
require('admin-header.php');

$topic_id = (int) $_GET['id'];
$topic    =  get_topic ( $topic_id );
if ( $current_user->user_type < 2 ) {
	header('Location: ' . bb_get_option('uri') );
	die();
}

if ( !$topic )
	die('There is a problem with that topic, pardner.');

bb_delete_topic( $topic->topic_id );

$sendto = get_forum_link( $topic->forum_id );

header( "Location: $sendto" );
exit;

?>
