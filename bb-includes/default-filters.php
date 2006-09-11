<?php

add_filter('forum_topics', 'number_format');
add_filter('forum_posts', 'number_format');

add_filter('topic_time', 'strtotime');
add_filter('topic_time', 'bb_since');
add_filter('topic_start_time', 'strtotime');
add_filter('topic_start_time', 'bb_since');

add_filter('pre_topic_title', 'wp_specialchars');
add_filter('get_forum_name', 'wp_specialchars');
add_filter('topic_title', 'closed_title', 30);
add_filter('topic_title', 'wp_specialchars');

add_filter('pre_post', 'trim');
add_filter('pre_post', 'encode_bad');
add_filter('pre_post', 'balanceTags');
add_filter('pre_post', 'stripslashes', 40); // KSES doesn't like escaped atributes
add_filter('pre_post', 'bb_filter_kses', 50);
add_filter('pre_post', 'addslashes', 55);
add_filter('pre_post', 'bb_autop', 60);

add_filter('post_text', 'make_clickable');

add_filter('total_posts', 'number_format');
add_filter('total_users', 'number_format');

add_filter('edit_text', 'code_trick_reverse');
add_filter('edit_text', 'htmlspecialchars');
add_filter('edit_text', 'trim', 15);

add_filter('get_user_link', 'bb_fix_link');

add_filter('bb_post_time', 'bb_offset_time');

add_filter('topic_rss_link', 'bb_make_feed');
add_filter('tag_rss_link', 'bb_make_feed');
add_filter('favorites_rss_link', 'bb_make_feed');

add_action('bb_head', 'bb_print_scripts');

if ( !$bb->mod_rewrite ) {
	add_filter('profile_tab_link', 'wp_specialchars');
	add_filter('post_link', 'wp_specialchars');
	add_filter('favorites_link', 'wp_specialchars');
}

?>
