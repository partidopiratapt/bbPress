<?php

function bb_default_scripts( &$scripts ) {
	$base = bb_get_option( 'uri' );
	$scripts->add( 'fat', $base . BB_INC . 'js/fat.js', array('add-load-event'), '1.0-RC1_3660' );
	$scripts->add( 'prototype', $base . BB_INC . 'js/prototype.js', false, '1.5.0' );
	$scripts->add( 'wp-ajax', $base . BB_INC . 'js/wp-ajax-js.php', array('prototype'), '2.1-beta2' );
	$scripts->add( 'listman', $base . BB_INC . 'js/list-manipulation-js.php', array('add-load-event', 'wp-ajax', 'fat'), '440' );
	$scripts->add( 'topic', $base . BB_INC . 'js/topic.js', array('add-load-event', 'listman', 'jquery'), '20080422' );
	$scripts->add( 'jquery', $base . BB_INC . 'js/jquery/jquery.js', false, '1.1.3.1');
	$scripts->add( 'interface', $base . BB_INC . 'js/jquery/interface.js', array('jquery'), '1.2.3');
	$scripts->add( 'jquery-color', $base . BB_INC . 'js/jquery/jquery.color.js', array('jquery'), '1.0' );
	$scripts->add( 'add-load-event', $base . BB_INC . 'js/add-load-event.js' );
	$scripts->add( 'content-forums', $base . '/bb-admin/js/content-forums.js', array('listman', 'interface'), '20080309' );
	$scripts->localize( 'content-forums', 'bbSortForumsL10n', array(
		'handleText' => __('drag'),
		'saveText' => __('Save Forum Order &#187;'),
		'editText' => __('Edit Forum Order &#187;')
	));
}

function bb_prototype_before_jquery( $js_array ) {
	if ( false === $jquery = array_search( 'jquery', $js_array ) )
		return $js_array;

	if ( false === $prototype = array_search( 'prototype', $js_array ) )
		return $js_array;

	if ( $prototype < $jquery )
		return $js_array;

	unset($js_array[$prototype]);

	array_splice( $js_array, $jquery, 0, 'prototype' );

	return $js_array;
}

function bb_just_in_time_script_localization() {
	wp_localize_script( 'topic', 'bbTopicJS', array(
		'currentUserId' => bb_get_current_user_info( 'id' ),
		'topicId' => get_topic_id(),
		'favoritesLink' => get_favorites_link(),
		'isFav' => (int) is_user_favorite( bb_get_current_user_info( 'id' ) ),
		'confirmPostDelete' => __('Are you sure you wanna delete this post by "%author%"?'),
		'confirmTagDelete' => __('Are you sure you want to remove the "%tag%" tag?'),
		'favLinkYes' => __( 'favorites' ),
		'favLinkNo' => __( '?' ),
		'favYes' => __( 'This topic is one of your %favLinkYes% [%favDel%]' ),
		'favNo' => __( '%favAdd% (%favLinkNo%)' ),
		'favDel' => __( 'x' ),
		'favAdd' => __( 'Add this topic to your favorites' )
	));
}

add_action( 'wp_default_scripts', 'bb_default_scripts' );
add_filter( 'wp_print_scripts', 'bb_just_in_time_script_localization' );
add_filter( 'print_scripts_array', 'bb_prototype_before_jquery' );

