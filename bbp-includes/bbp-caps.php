<?php

/**
 * bbp_add_roles ()
 *
 * Adds bbPress-specific user roles. This is called on plugin activation.
 *
 * @uses get_role
 *
 * @since bbPress (r2741)
 */
function bbp_add_roles () {
	// Add the Moderator role and add the default role caps. Mod caps are added by the bbp_add_caps () function
	$default =& get_role( get_option( 'default_role' ) );
	add_role( 'bbp_moderator', __( 'Forum Moderator', 'bbpress' ), $default->capabilities );

	do_action( 'bbp_add_roles' );
}
add_action( 'bbp_activation', 'bbp_add_roles', 1 );

/**
 * bbp_add_caps ()
 *
 * Adds capabilities to WordPress user roles. This is called on plugin
 * activation.
 *
 * @uses get_role
 */
function bbp_add_caps () {
	// Add caps to admin role
	if ( $admin =& get_role( 'administrator' ) ) {

		// Forum caps
		$admin->add_cap( 'publish_forums'        );
		$admin->add_cap( 'edit_forums'           );
		$admin->add_cap( 'edit_others_forums'    );
		$admin->add_cap( 'delete_forums'         );
		$admin->add_cap( 'delete_others_forums'  );
		$admin->add_cap( 'read_private_forums'   );

		// Topic caps
		$admin->add_cap( 'publish_topics'        );
		$admin->add_cap( 'edit_topics'           );
		$admin->add_cap( 'edit_others_topics'    );
		$admin->add_cap( 'delete_topics'         );
		$admin->add_cap( 'delete_others_topics'  );
		$admin->add_cap( 'read_private_topics'   );

		// Reply caps
		$admin->add_cap( 'publish_replies'       );
		$admin->add_cap( 'edit_replies'          );
		$admin->add_cap( 'edit_others_replies'   );
		$admin->add_cap( 'delete_replies'        );
		$admin->add_cap( 'delete_others_replies' );
		$admin->add_cap( 'read_private_replies'  );

		// Topic tag caps
		$admin->add_cap( 'manage_topic_tags'     );
		$admin->add_cap( 'edit_topic_tags'       );
		$admin->add_cap( 'delete_topic_tags'     );
		$admin->add_cap( 'assign_topic_tags'     );

		// Misc
		$admin->add_cap( 'throttle'              );
		$admin->add_cap( 'view_trash'            );
	}

	// Add caps to default role
	if ( $default =& get_role( get_option( 'default_role' ) ) ) {

		// Topic caps
		$default->add_cap( 'publish_topics'    );
		$default->add_cap( 'edit_topics'       );

		// Reply caps
		$default->add_cap( 'publish_replies'   );
		$default->add_cap( 'edit_replies'      );

		// Topic tag caps
		$default->add_cap( 'assign_topic_tags' );
	}

	// Add caps to moderator role
	if ( $mod =& get_role( 'bbp_moderator' ) ) {

		// Topic caps
		$mod->add_cap( 'publish_topics'        );
		$mod->add_cap( 'edit_topics'           );
		$mod->add_cap( 'edit_others_topics'    );
		$mod->add_cap( 'delete_topics'         );
		$mod->add_cap( 'delete_others_topics'  );
		$mod->add_cap( 'read_private_topics'   );

		// Reply caps
		$mod->add_cap( 'publish_replies'       );
		$mod->add_cap( 'edit_replies'          );
		$mod->add_cap( 'edit_others_replies'   );
		$mod->add_cap( 'delete_replies'        );
		$mod->add_cap( 'delete_others_replies' );
		$mod->add_cap( 'read_private_replies'  );

		// Topic tag caps
		$mod->add_cap( 'manage_topic_tags'     );
		$mod->add_cap( 'edit_topic_tags'       );
		$mod->add_cap( 'delete_topic_tags'     );
		$mod->add_cap( 'assign_topic_tags'     );

		// Users
		$mod->add_cap( 'edit_users'            );

		// Misc
		$mod->add_cap( 'moderate'              );
		$mod->add_cap( 'throttle'              );
		$mod->add_cap( 'view_trash'            );
	}

	do_action( 'bbp_add_caps' );
}
add_action( 'bbp_activation', 'bbp_add_caps', 2 );

/**
 * bbp_remove_caps ()
 *
 * Removes capabilities from WordPress user roles. This is called on plugin
 * deactivation.
 *
 * @uses get_role
 */
function bbp_remove_caps () {
	// Remove caps from admin role
	if ( $admin =& get_role( 'administrator' ) ) {

		// Forum caps
		$admin->remove_cap( 'publish_forums'        );
		$admin->remove_cap( 'edit_forums'           );
		$admin->remove_cap( 'edit_others_forums'    );
		$admin->remove_cap( 'delete_forums'         );
		$admin->remove_cap( 'delete_others_forums'  );
		$admin->remove_cap( 'read_private_forums'   );

		// Topic caps
		$admin->remove_cap( 'publish_topics'        );
		$admin->remove_cap( 'edit_topics'           );
		$admin->remove_cap( 'edit_others_topics'    );
		$admin->remove_cap( 'delete_topics'         );
		$admin->remove_cap( 'delete_others_topics'  );
		$admin->remove_cap( 'read_private_topics'   );

		// Reply caps
		$admin->remove_cap( 'publish_replies'       );
		$admin->remove_cap( 'edit_replies'          );
		$admin->remove_cap( 'edit_others_replies'   );
		$admin->remove_cap( 'delete_replies'        );
		$admin->remove_cap( 'delete_others_replies' );
		$admin->remove_cap( 'read_private_replies'  );

		// Topic tag caps
		$admin->remove_cap( 'manage_topic_tags'     );
		$admin->remove_cap( 'edit_topic_tags'       );
		$admin->remove_cap( 'delete_topic_tags'     );
		$admin->remove_cap( 'assign_topic_tags'     );

		// Misc
		$admin->remove_cap( 'throttle'              );
		$admin->remove_cap( 'view_trash'            );
	}

	// Remove caps from default role
	if ( $default =& get_role( get_option( 'default_role' ) ) ) {

		// Topic caps
		$default->remove_cap( 'publish_topics'    );
		$default->remove_cap( 'edit_topics'       );

		// Reply caps
		$default->remove_cap( 'publish_replies'   );
		$default->remove_cap( 'edit_replies'      );

		// Topic tag caps
		$default->remove_cap( 'assign_topic_tags' );
	}

	do_action( 'bbp_remove_caps' );
}
add_action( 'bbp_deactivation', 'bbp_remove_caps', 1 );

/**
 * bbp_remove_roles ()
 *
 * Removes bbPress-specific user roles. This is called on plugin deactivation.
 *
 * @uses remove_role
 *
 * @since bbPress (r2741)
 */
function bbp_remove_roles () {
	// Remove the Moderator role
	remove_role( 'bbp_moderator' );

	do_action( 'bbp_remove_roles' );
}
add_action( 'bbp_deactivation', 'bbp_remove_roles', 2 );

/**
 * bbp_map_meta_caps ()
 *
 * Maps forum/topic/reply caps to built in WordPress caps
 *
 */
function bbp_map_meta_caps ( $caps, $cap, $user_id, $args ) {

	switch ( $cap ) {
		case 'edit_forum' :
		case 'edit_topic' :
		case 'edit_reply' :

			if ( $post = get_post( $args[0] ) ) {
				$caps      = array();
				$post_type = get_post_type_object( $post->post_type );

				if ( (int)$user_id == (int)$post->post_author )
					$caps[] = $post_type->cap->edit_posts;
				else
					$caps[] = $post_type->cap->edit_others_posts;
			}

			break;

		case 'delete_forum' :

			if ( $post = get_post( $args[0] ) ) {
				$caps      = array();
				$post_type = get_post_type_object( $post->post_type );

				if ( (int)$user_id == (int) $post->post_author )
					$caps[] = $post_type->cap->delete_posts;
				else
					$caps[] = $post_type->cap->delete_others_posts;
			}

			break;

		case 'delete_topic' :
		case 'delete_reply' :

			if ( $post = get_post( $args[0] ) ) {
				$caps      = array();
				$post_type = get_post_type_object( $post->post_type );
				$caps[]    = $post_type->cap->delete_others_posts;
			}

			break;
	}

	return apply_filters( 'bbp_map_meta_caps', $caps, $cap, $user_id, $args );
}
add_filter ( 'map_meta_cap', 'bbp_map_meta_caps', 10, 4 );

/**
 * bbp_get_forum_caps ()
 *
 * Return forum caps
 *
 * @return array
 */
function bbp_get_forum_caps () {
	// Forum meta caps
	$caps = array (
		'delete_posts'        => 'delete_forums',
		'delete_others_posts' => 'delete_others_forums'
	);

	return apply_filters( 'bbp_get_forum_caps', $caps );
}

/**
 * bbp_get_topic_caps ()
 *
 * Return topic caps
 *
 * @return array
 */
function bbp_get_topic_caps () {
	// Forum meta caps
	$caps = array (
		'delete_posts'        => 'delete_topics',
		'delete_others_posts' => 'delete_others_topics'
	);

	return apply_filters( 'bbp_get_topic_caps', $caps );
}

/**
 * bbp_get_reply_caps ()
 *
 * Return reply caps
 *
 * @return array
 */
function bbp_get_reply_caps () {
	// Forum meta caps
	$caps = array (
		'edit_posts'          => 'edit_replies',
		'edit_others_posts'   => 'edit_others_replies',
		'publish_posts'       => 'publish_replies',
		'read_private_posts'  => 'read_private_replies',
		'delete_posts'        => 'delete_replies',
		'delete_others_posts' => 'delete_others_replies'
	);

	return apply_filters( 'bbp_get_reply_caps', $caps );
}

/**
 * bbp_get_topic_tag_caps ()
 *
 * Return topic tag caps
 *
 * @return array
 */
function bbp_get_topic_tag_caps () {
	// Forum meta caps
	$caps = array (
		'manage_terms' => 'manage_topic_tags',
		'edit_terms'   => 'edit_topic_tags',
		'delete_terms' => 'delete_topic_tags',
		'assign_terms' => 'assign_topic_tags'
	);

	return apply_filters( 'bbp_get_topic_tag_caps', $caps );
}

?>
