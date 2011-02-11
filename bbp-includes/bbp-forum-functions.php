<?php

/**
 * bbPress Forum Functions
 *
 * @package bbPress
 * @subpackage Functions
 */

/** Walk **********************************************************************/

/**
 * Walk the forum tree
 *
 * @param object $forums Forums
 * @param int $depth Depth
 * @param int $current Current forum
 * @param array $r Parsed arguments, supported by the walker. If you want to
 *                  use your own walker, pass the 'walker' arg with the walker.
 *                  The walker defaults to {@link BBP_Walker_Forum}
 * @return object Walked forum tree
 */
function bbp_walk_forum( $forums, $depth, $current, $r ) {
	$walker = empty( $r['walker'] ) ? new BBP_Walker_Forum : $r['walker'];
	$args   = array( $forums, $depth, $r, $current );
	return call_user_func_array( array( &$walker, 'walk' ), $args );
}

/** Forum Actions *************************************************************/

/**
 * Closes a forum
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id forum id
 * @uses wp_get_single_post() To get the forum
 * @uses do_action() Calls 'bbp_close_forum' with the forum id
 * @uses add_post_meta() To add the previous status to a meta
 * @uses wp_insert_post() To update the forum with the new status
 * @uses do_action() Calls 'bbp_opened_forum' with the forum id
 * @return mixed False or {@link WP_Error} on failure, forum id on success
 */
function bbp_close_forum( $forum_id = 0 ) {
	global $bbp;

	if ( !$forum = wp_get_single_post( $forum_id, ARRAY_A ) )
		return $forum;

	do_action( 'bbp_close_forum', $forum_id );

	update_post_meta( $forum_id, '_bbp_forum_status', 'closed' );

	do_action( 'bbp_closed_forum', $forum_id );

	return $forum_id;
}

/**
 * Opens a forum
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id forum id
 * @uses wp_get_single_post() To get the forum
 * @uses do_action() Calls 'bbp_open_forum' with the forum id
 * @uses get_post_meta() To get the previous status
 * @uses delete_post_meta() To delete the previous status meta
 * @uses wp_insert_post() To update the forum with the new status
 * @uses do_action() Calls 'bbp_opened_forum' with the forum id
 * @return mixed False or {@link WP_Error} on failure, forum id on success
 */
function bbp_open_forum( $forum_id = 0 ) {
	global $bbp;

	if ( !$forum = wp_get_single_post( $forum_id, ARRAY_A ) )
		return $forum;

	do_action( 'bbp_open_forum', $forum_id );

	update_post_meta( $forum_id, '_bbp_forum_status', 'open' );

	do_action( 'bbp_opened_forum', $forum_id );

	return $forum_id;
}

/**
 * Make the forum a category
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id Optional. Forum id
 * @uses update_post_meta() To update the forum category meta
 * @return bool False on failure, true on success
 */
function bbp_categorize_forum( $forum_id = 0 ) {
	return update_post_meta( $forum_id, '_bbp_forum_type', 'category' );
}

/**
 * Remove the category status from a forum
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id Optional. Forum id
 * @uses delete_post_meta() To delete the forum category meta
 * @return bool False on failure, true on success
 */
function bbp_normalize_forum( $forum_id = 0 ) {
	return update_post_meta( $forum_id, '_bbp_forum_type', 'forum' );
}

/**
 * Mark the forum as private
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id Optional. Forum id
 * @uses update_post_meta() To update the forum private meta
 * @return bool False on failure, true on success
 */
function bbp_privatize_forum( $forum_id = 0 ) {
	return update_post_meta( $forum_id, '_bbp_forum_visibility', 'private' );
}

/**
 * Unmark the forum as private
 *
 * @since bbPress (r2746)
 *
 * @param int $forum_id Optional. Forum id
 * @uses delete_post_meta() To delete the forum private meta
 * @return bool False on failure, true on success
 */
function bbp_publicize_forum( $forum_id = 0 ) {
	return update_post_meta( $forum_id, '_bbp_forum_visibility', 'public' );
}

/** Forum Updaters ************************************************************/

/**
 * Update the forum last topic id
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @param int $topic_id Optional. Topic id
 * @uses bbp_get_forum_id() To get the forum id
 * @uses bbp_get_topic_id() To get the topic id
 * @uses update_post_meta() To update the forum's last topic id meta
 * @return bool True on success, false on failure
 */
function bbp_update_forum_last_topic_id( $forum_id = 0, $topic_id = 0 ) {
	$forum_id = bbp_get_forum_id( $forum_id );
	$topic_id = bbp_get_topic_id( $topic_id );

	// Update the last topic ID
	return update_post_meta( $forum_id, '_bbp_forum_last_topic_id', (int) $topic_id );
}

/**
 * Update the forum last reply id
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @param int $reply_id Optional. Reply id
 * @uses bbp_get_forum_id() To get the forum id
 * @uses bbp_get_reply_id() To get the reply id
 * @uses update_post_meta() To update the forum's last reply id meta
 * @return bool True on success, false on failure
 */
function bbp_update_forum_last_reply_id( $forum_id = 0, $reply_id = 0 ) {
	$forum_id = bbp_get_forum_id( $forum_id );
	$reply_id = bbp_get_reply_id( $reply_id );

	// Update the last reply ID with what was passed
	return update_post_meta( $forum_id, '_bbp_forum_last_reply_id', (int) $reply_id );
}

/**
 * Update the forum last active post ID
 *
 * @since bbPress (r2860)
 *
 * @param int $forum_id Optional. Forum id
 * @param int $active_id Optional. active id
 * @uses bbp_get_forum_id() To get the forum id
 * @uses bbp_get_active_id() To get the active id
 * @uses update_post_meta() To update the forum's last active id meta
 * @return bool True on success, false on failure
 */
function bbp_update_forum_last_active_id( $forum_id = 0, $active_id = 0 ) {
	$forum_id  = bbp_get_forum_id( $forum_id );

	return update_post_meta( $forum_id, '_bbp_forum_last_active_id', (int) $active_id );
}

/**
 * Update the forums last active date/time (aka freshness)
 *
 * @since bbPress (r2680)
 *
 * @param int $forum_id Optional. Topic id
 * @param string $new_time Optional. New time in mysql format
 * @uses bbp_get_forum_id() To get the forum id
 * @uses bbp_get_reply_forum_id() To get the reply forum id
 * @uses current_time() To get the current time
 * @uses update_post_meta() To update the forum last active meta
 * @return bool True on success, false on failure
 */
function bbp_update_forum_last_active_time( $forum_id = 0, $new_time = '' ) {
	$forum_id = bbp_get_forum_id( $forum_id );

	// Check time and use current if empty
	if ( empty( $new_time ) )
		$new_time = current_time( 'mysql' );

	// Update the last reply ID
	if ( !empty( $forum_id ) )
		update_post_meta( $forum_id, '_bbp_forum_last_active', $new_time );

	return apply_filters( 'bbp_update_forum_last_active', $new_time, $forum_id );
}

/**
 * Update the forum sub-forum count
 *
 * @todo Make this work.
 *
 * @since bbPress (r2625)
 *
 * @param int $forum_id Optional. Forum id
 * @uses bbp_get_forum_id() To get the forum id
 * @return bool True on success, false on failure
 */
function bbp_update_forum_subforum_count( $forum_id = 0, $subforums = 0 ) {
	$forum_id = bbp_get_forum_id( $forum_id );

	return update_post_meta( $forum_id, '_bbp_forum_subforum_count', (int) $subforums );;
}

/**
 * Adjust the total topic count of a forum
 *
 * @since bbPress (r2464)
 *
 * @param int $forum_id Optional. Forum id or topic id. It is checked whether it
 *                       is a topic or a forum. If it's a topic, its parent,
 *                       i.e. the forum is automatically retrieved.
 * @param bool $total_count Optional. To return the total count or normal
 *                           count?
 * @uses get_post_field() To check whether the supplied id is a topic
 * @uses bbp_get_topic_forum_id() To get the topic's forum id
 * @uses wpdb::prepare() To prepare the sql statement
 * @uses wpdb::get_col() To execute the query and get the column back
 * @uses bbp_get_topic_status() To get the topic status
 * @uses update_post_meta() To update the forum's topic count meta
 * @uses apply_filters() Calls 'bbp_update_forum_topic_count' with the topic
 *                        count, forum id and total count bool
 */
function bbp_update_forum_topic_count( $forum_id = 0 ) {
	global $wpdb, $bbp;

	$forum_id = bbp_get_forum_id( $forum_id );
	$children_topic_count = 0;

	// Loop through subforums and add together forum topic counts
	if ( $children = get_posts( array( 'post_parent' => $forum_id, 'post_type' => bbp_get_forum_post_type(), 'numberposts' => -1 ) ) )
		foreach ( (array) $children as $child )
			$children_topic_count += bbp_update_forum_topic_count ( $child->ID );

	// Get total topics for this forum
	$topics = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_parent = %d AND post_status IN ( '" . join( "', '", array( 'publish', $bbp->closed_status_id ) ) . "' ) AND post_type = '%s';", $forum_id, bbp_get_topic_post_type() ) );

	// Calculate total topics in this forum
	$total_topics = $topics + $children_topic_count;

	// Update the count
	update_post_meta( $forum_id, '_bbp_forum_topic_count',       $topics       );
	update_post_meta( $forum_id, '_bbp_forum_total_topic_count', $total_topics );

	return apply_filters( 'bbp_update_forum_topic_count', $total_topics );
}

/**
 * Adjust the total topic count of a forum
 *
 * @since bbPress (r2464)
 *
 * @param int $forum_id Optional. Forum id or topic id. It is checked whether it
 *                       is a topic or a forum. If it's a topic, its parent,
 *                       i.e. the forum is automatically retrieved.
 * @param bool $total_count Optional. To return the total count or normal
 *                           count?
 * @uses get_post_field() To check whether the supplied id is a topic
 * @uses bbp_get_topic_forum_id() To get the topic's forum id
 * @uses wpdb::prepare() To prepare the sql statement
 * @uses wpdb::get_col() To execute the query and get the column back
 * @uses bbp_get_topic_status() To get the topic status
 * @uses update_post_meta() To update the forum's topic count meta
 * @uses apply_filters() Calls 'bbp_update_forum_topic_count' with the topic
 *                        count, forum id and total count bool
 */
function bbp_update_forum_reply_count( $forum_id = 0 ) {
	global $wpdb, $bbp;

	$forum_id = bbp_get_forum_id( $forum_id );
	$children_reply_count = 0;

	// Loop through children and add together forum reply counts
	if ( $children = get_posts( array( 'post_parent' => $forum_id, 'post_type' => bbp_get_forum_post_type(), 'numberposts' => -1 ) ) )
		foreach ( (array) $children as $child )
			$children_reply_count += bbp_update_forum_reply_count ( $child->ID );

	// Don't count replies if the forum is a category
	if ( $topics = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_status = 'publish' AND post_type = '%s';", $forum_id, bbp_get_topic_post_type() ) ) )
		$reply_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_parent IN ( " . join( ',', $topics ) . " ) AND post_status = 'publish' AND post_type = '%s';", bbp_get_reply_post_type() ) );
	else
		$reply_count = 0;

	// Calculate total replies in this forum
	$total_replies = (int) $reply_count + $children_reply_count;

	// Update the count
	update_post_meta( $forum_id, '_bbp_forum_reply_count',       $reply_count   );
	update_post_meta( $forum_id, '_bbp_forum_total_reply_count', $total_replies );

	return apply_filters( 'bbp_update_forum_reply_count', $total_replies );
}

?>
