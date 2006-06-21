<?php
require('./bb-load.php');

bb_auth();

if ( !$bb_current_user )
	die(__('You need to be logged in to add a tag.'));

$topic_id = (int) @$_POST['id' ];
$tag      =       @$_POST['tag'];

$topic = get_topic ( $topic_id );
if ( !$topic )
	die(__('Topic not found.'));

if ( add_topic_tag( $topic_id, $tag ) )
	header('Location: ' . get_topic_link( $topic_id ) );
else
	die(__('The tag was not added.  Either the tag name was invalid or the topic is closed.'));
?>
