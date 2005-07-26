<?php

require('../bb-config.php');
header('Content-type: text/plain');

if( $current_user->user_type >= 5 ) :

if ( $topics = $bbdb->get_results("SELECT topic_id, COUNT(post_id) AS t_count FROM $bbdb->posts WHERE post_status = '0' GROUP BY topic_id") ) :
	foreach ($topics as $topic) :
		$bbdb->query("UPDATE $bbdb->topics SET topic_posts = $topic->t_count WHERE topic_id = $topic->topic_id");
	endforeach;
	unset($topics);
endif;


if ( $all_forums = $bbdb->get_col("SELECT forum_id FROM $bbdb->forums") ) :
	$all_forums = array_flip( $all_forums );
	$forums = $bbdb->get_results("SELECT forum_id, COUNT(topic_id) AS topic_count, SUM(topic_posts) AS post_count FROM $bbdb->topics
		WHERE topic_status = 0 GROUP BY forum_id");
	foreach ($forums as $forum) :
		$bbdb->query("UPDATE $bbdb->forums SET topics = $forum->topic_count, posts = $forum->post_count WHERE forum_id = $forum->forum_id");
		unset($all_forums[$forum->forum_id]);
	endforeach;
	if ( $all_forums ) :
		$all_forums = implode(',', array_flip( $all_forums ) );
		$bbdb->query("UPDATE $bbdb->forums SET topics = 0, posts = 0 WHERE forum_id IN ($all_forums)");
	endif;
	unset($all_forums);
	unset($forums);
endif;

if ( $users = $bbdb->get_col("SELECT ID FROM $bbdb->users") ) :
	foreach ( $users as $user ) :
		$topics_replied = $bbdb->get_var("SELECT COUNT(DISTINCT topic_id) FROM $bbdb->posts WHERE post_status = '0' AND poster_id = $user");
		update_usermeta( $user, $table_prefix. 'topics_replied', $topics_replied );
	endforeach;
	unset($users, $topics_started, $topics_replied);
endif;

if ( $tags = $bbdb->get_results("SELECT tag_id, COUNT(topic_id) AS tag_count FROM $bbdb->tagged GROUP BY tag_id") ) :
	foreach ( $tags as $tag ) :
		$bbdb->query("UPDATE $bbdb->tags SET tag_count = $tag->tag_count WHERE tag_id = $tag->tag_id");
	endforeach;
	unset($tags);
else :
	$bbdb->query("UPDATE $bbdb->tags SET tag_count = 0");
endif;

endif;

echo "$bbdb->num_queries queries. " . bb_timer_stop(0) . ' seconds';
?>
