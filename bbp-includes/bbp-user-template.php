<?php

/** START User Functions ******************************************************/

/** START Favorites Functions *************************************************/

/**
 * bbp_favorites_permalink ()
 *
 * Output the link to the user's favorites page (profile page)
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2652)
 *
 * @param int $user_id optional
 * @uses bbp_get_favorites_permalink()
 */
function bbp_favorites_permalink ( $user_id = 0 ) {
	echo bbp_get_favorites_permalink( $user_id );
}
	/**
	 * bbp_get_favorites_permalink ()
	 *
	 * Return the link to the user's favorites page (profile page)
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2652)
	 *
	 * @param int $user_id optional
	 * @uses apply_filters
	 * @uses bbp_get_user_profile_url
	 * @return string Permanent link to user profile page
	 */
	function bbp_get_favorites_permalink ( $user_id = 0 ) {
		return apply_filters( 'bbp_get_favorites_permalink', bbp_get_user_profile_url( $user_id ) );
	}

/**
 * bbp_user_favorites_link ()
 *
 * Output the link to make a topic favorite/remove a topic from favorites
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2652)
 *
 * @param array $add optional
 * @param array $rem optional
 * @param int $user_id optional
 *
 * @uses bbp_get_user_favorites_link()
 */
function bbp_user_favorites_link ( $add = array(), $rem = array(), $user_id = 0 ) {
	echo bbp_get_user_favorites_link( $add, $rem, $user_id );
}
	/**
	 * bbp_get_user_favorites_link ()
	 *
	 * Return the link to make a topic favorite/remove a topic from favorites
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2652)
	 *
	 * @param array $add optional
	 * @param array $rem optional
	 * @param int $user_id optional
	 *
	 * @uses apply_filters
	 * @return string Permanent link to topic
	 */
	function bbp_get_user_favorites_link ( $add = array(), $rem = array(), $user_id = 0 ) {
		global $bbp;

		if ( empty( $user_id ) && !$user_id = $bbp->current_user->ID )
			return false;

		if ( !current_user_can( 'edit_user', (int) $user_id ) )
			return false;

		if ( !$topic_id = bbp_get_topic_id() )
			return false;

		if ( empty( $add ) || !is_array( $add ) ) {
			$add = array(
				'mid'  => __( 'Add this topic to your favorites', 'bbpress' ),
				'post' => __( ' (%?%)', 'bbpress' )
			);
		}

		if ( empty( $rem ) || !is_array( $rem ) ) {
			$rem = array(
				'pre'  => __( 'This topic is one of your %favorites% [', 'bbpress' ),
				'mid'  => __( '&times;', 'bbpress' ),
				'post' => __( ']', 'bbpress' )
			);
		}

		if ( $is_fav = bbp_is_user_favorite( $user_id, $topic_id ) ) {
			$url  = esc_url( bbp_get_favorites_permalink( $user_id ) );
			$rem  = preg_replace( '|%(.+)%|', "<a href='$url'>$1</a>", $rem );
			$favs = array( 'action' => 'bbp_favorite_remove', 'topic_id' => $topic_id );
			$pre  = ( is_array( $rem ) && isset( $rem['pre']  ) ) ? $rem['pre']  : '';
			$mid  = ( is_array( $rem ) && isset( $rem['mid']  ) ) ? $rem['mid']  : ( is_string( $rem ) ? $rem : '' );
			$post = ( is_array( $rem ) && isset( $rem['post'] ) ) ? $rem['post'] : '';
		} else {
			$url  = esc_url( bbp_get_topic_permalink( $topic_id ) );
			$add  = preg_replace( '|%(.+)%|', "<a href='$url'>$1</a>", $add );
			$favs = array( 'action' => 'bbp_favorite_add', 'topic_id' => $topic_id );
			$pre  = ( is_array( $add ) && isset( $add['pre']  ) ) ? $add['pre']  : '';
			$mid  = ( is_array( $add ) && isset( $add['mid']  ) ) ? $add['mid']  : ( is_string( $add ) ? $add : '' );
			$post = ( is_array( $add ) && isset( $add['post'] ) ) ? $add['post'] : '';
		}

		// Create the link based where the user is and if the topic is already the user's favorite
		$permalink = bbp_is_favorites() ? bbp_get_favorites_permalink( $user_id ) : bbp_get_topic_permalink( $topic_id );
		$url       = esc_url( wp_nonce_url( add_query_arg( $favs, $permalink ), 'toggle-favorite_' . $topic_id ) );
		$is_fav    = $is_fav ? 'is-favorite' : '';
		$html      = '<span id="favorite-toggle"><span id="favorite-' . $topic_id . '" class="' . $is_fav . '">' . $pre . '<a href="' . $url . '" class="dim:favorite-toggle:favorite-' . $topic_id . ':is-favorite">' . $mid . '</a>' . $post . '</span></span>';

		// Return the link
		return apply_filters( 'bbp_get_user_favorites_link', $html, $add, $rem, $user_id, $topic_id );
	}

/** END Favorites Functions ***************************************************/

/** START Subscriptions Functions *********************************************/

/**
 * bbp_subscriptions_permalink ()
 *
 * Output the link to the user's subscriptions page (profile page)
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2688)
 *
 * @param int $user_id optional
 * @uses bbp_get_subscriptions_permalink()
 */
function bbp_subscriptions_permalink ( $user_id = 0 ) {
	echo bbp_get_subscriptions_permalink( $user_id );
}
	/**
	 * bbp_get_subscriptions_permalink ()
	 *
	 * Return the link to the user's subscriptions page (profile page)
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2688)
	 *
	 * @param int $user_id optional
	 * @uses apply_filters
	 * @uses bbp_get_user_profile_url
	 * @return string Permanent link to user profile page
	 */
	function bbp_get_subscriptions_permalink ( $user_id = 0 ) {
		return apply_filters( 'bbp_get_favorites_permalink', bbp_get_user_profile_url( $user_id ) );
	}

/**
 * bbp_user_subscribe_link ()
 *
 * Output the link to subscribe/unsubscribe from a topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2668)
 *
 * @param mixed $args
 *
 * @uses bbp_get_user_subscribe_link()
 */
function bbp_user_subscribe_link ( $args = '' ) {
	echo bbp_get_user_subscribe_link( $args );
}
	/**
	 * bbp_get_user_subscribe_link ()
	 *
	 * Return the link to subscribe/unsubscribe from a topic
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2668)
	 *
	 * @param mixed $args
	 *
	 * @uses apply_filters
	 * @return string Permanent link to topic
	 */
	function bbp_get_user_subscribe_link ( $args = '', $user_id = 0 ) {
		global $bbp;

		if ( !bbp_is_subscriptions_active() )
			return;

		$defaults = array (
			'subscribe'     => __( 'Subscribe',   'bbpress' ),
			'unsubscribe'   => __( 'Unsubscribe', 'bbpress' ),
			'user_id'       => 0,
			'topic_id'      => 0,
			'before'        => '&nbsp;|&nbsp;',
			'after'         => ''
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		// Try to get a user_id from $current_user
		if ( empty( $user_id ) )
			$user_id = $bbp->current_user->ID;

		// No link if not logged in
		if ( empty( $user_id ) )
			return false;

		// No link if you can't edit yourself
		if ( !current_user_can( 'edit_user', (int) $user_id ) )
			return false;

		// No link if not viewing a topic
		if ( !$topic_id = bbp_get_topic_id( $topic_id ) )
			return false;

		// Decine which link to show
		if ( $is_subscribed = bbp_is_user_subscribed( $user_id, $topic_id ) ) {
			$text = $unsubscribe;
			$query_args  = array( 'action' => 'bbp_unsubscribe', 'topic_id' => $topic_id );
		} else {
			$text = $subscribe;
			$query_args = array( 'action' => 'bbp_subscribe', 'topic_id' => $topic_id );
		}

		// Create the link based where the user is and if the user is subscribed already
		$permalink     = bbp_is_subscriptions() ? bbp_get_subscriptions_permalink( $user_id ) : bbp_get_topic_permalink( $topic_id );
		$url           = esc_url( wp_nonce_url( add_query_arg( $query_args, $permalink ), 'toggle-subscription_' . $topic_id ) );
		$is_subscribed = $is_subscribed ? 'is-subscribed' : '';
		$html          = '<span id="subscription-toggle">' . $before . '<span id="subscribe-' . $topic_id . '" class="' . $is_subscribed . '"><a href="' . $url . '" class="dim:subscription-toggle:subscribe-' . $topic_id . ':is-subscribed">' . $text . '</a></span>' . $after . '</span>';

		// Return the link
		return apply_filters( 'bbp_get_user_subscribe_link', $html, $subscribe, $unsubscribe, $user_id, $topic_id );
	}

/** END Subscriptions Functions ***********************************************/

/**
 * bbp_current_user_id ()
 *
 * Output ID of current user
 *
 * @uses bbp_get_current_user_id()
 */
function bbp_current_user_id () {
	echo bbp_get_current_user_id();
}
	/**
	 * bbp_get_current_user_id ()
	 *
	 * Return ID of current user
	 *
	 * @global object $current_user
	 * @global string $user_identity
	 * @return int
	 */
	function bbp_get_current_user_id () {
		global $bbp;

		$retval = isset( $bbp->current_user ) ? $bbp->current_user->ID : 0;

		return apply_filters( 'bbp_get_current_user_id', $retval );
	}

/**
 * bbp_displayed_user_id ()
 *
 * Output ID of displayed user
 *
 * @uses bbp_get_displayed_user_id()
 */
function bbp_displayed_user_id () {
	echo bbp_get_displayed_user_id();
}
	/**
	 * bbp_get_displayed_user_id ()
	 *
	 * Return ID of displayed user
	 *
	 * @global object $displayed_user
	 * @global string $user_identity
	 * @return int
	 */
	function bbp_get_displayed_user_id () {
		global $bbp;

		$retval = isset( $bbp->displayed_user ) ? $bbp->displayed_user->ID : 0;

		return apply_filters( 'bbp_get_displayed_user_id', $retval );
	}

/**
 * bbp_get_displayed_user_field ()
 *
 * Return a sanitized user field value
 *
 * @global bbPress $bbp
 * @param string $field
 * @return string
 */
function bbp_get_displayed_user_field ( $field = '' ) {
	global $bbp;

	// Return field if exists
	if ( isset( $bbp->displayed_user->$field ) )
		return esc_attr( sanitize_text_field ( $bbp->displayed_user->$field ) );

	// Return empty
	return false;
}

/**
 * bbp_current_user_name ()
 *
 * Output name of current user
 *
 * @uses bbp_get_current_user_name()
 */
function bbp_current_user_name () {
	echo bbp_get_current_user_name();
}
	/**
	 * bbp_get_current_user_name ()
	 *
	 * Return name of current user
	 *
	 * @global object $current_user
	 * @global string $user_identity
	 * @return string
	 */
	function bbp_get_current_user_name () {
		global $current_user, $user_identity;

		if ( is_user_logged_in() )
			$current_user_name = $user_identity;
		else
			$current_user_name = __( 'Anonymous', 'bbpress' );

		return apply_filters( 'bbp_get_current_user_name', $current_user_name );
	}

/**
 * bbp_current_user_avatar ()
 *
 * Output avatar of current user
 *
 * @uses bbp_get_current_user_avatar()
 */
function bbp_current_user_avatar ( $size = 40 ) {
	echo bbp_get_current_user_avatar( $size );
}

	/**
	 * bbp_get_current_user_avatar ( $size = 40 )
	 *
	 * Return avatar of current user
	 *
	 * @global object $current_user
	 * @param int $size
	 * @return string
	 */
	function bbp_get_current_user_avatar ( $size = 40 ) {
		global $current_user;

		return apply_filters( 'bbp_get_current_user_avatar', get_avatar( bbp_get_current_user_id(), $size ) );
	}

/**
 * bbp_user_profile_link ()
 *
 * Output link to the profile page of a user
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2688)
 *
 * @uses bbp_get_user_profile_link ()
 *
 * @param int $user_id
 */
function bbp_user_profile_link ( $user_id = 0 ) {
	echo bbp_get_user_profile_link( $user_id );
}
	/**
	 * bbp_get_user_profile_link ()
	 *
	 * Return link to the profile page of a user
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2688)
	 *
	 * @uses bbp_get_user_profile_url ()
	 *
	 * @param int $user_id
	 * @return string
	 */
	function bbp_get_user_profile_link ( $user_id = 0 ) {
		if ( empty( $user_id ) )
			return false;

		$user      = get_userdata( $user_id );
		$name      = esc_attr( $user->display_name );
		$user_link = '<a href="' . bbp_get_user_profile_url( $user_id ) . '" title="' . $name . '">' . $name . '</a>';

		return apply_filters( 'bbp_get_user_profile_link', $user_link, $user_id );
	}

/**
 * bbp_user_profile_url ()
 *
 * Output URL to the profile page of a user
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2688)
 *
 * @uses bbp_get_user_profile_url ()
 *
 * @param int $user_id
 */
function bbp_user_profile_url ( $user_id = 0, $user_nicename = '' ) {
	echo bbp_get_user_profile_url( $user_id );
}
	/**
	 * bbp_get_user_profile_url ()
	 *
	 * Return URL to the profile page of a user
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2688)
	 *
	 * @param int $user_id
	 * @param string $user_nicename
	 *
	 * @return string
	 */
	function bbp_get_user_profile_url ( $user_id = 0, $user_nicename = '' ) {
		global $wp_rewrite, $bbp;

		// Use displayed user ID if there is one, and one isn't requested
		if ( empty( $user_id ) )
			$user_id = isset( $bbp->displayed_user ) ? $bbp->displayed_user->ID : 0;

		// No user ID so return false
		if ( empty( $user_id ) )
			return false;

		// URL for pretty permalinks
		$url = !empty( $wp_rewrite->permalink_structure ) ? $wp_rewrite->front . $bbp->user_slug . '/%bbp_user%' : '';

		// No pretty permalinks
		if ( empty( $url ) ) {
			$file = home_url( '/' );
			$url  = $file . '?bbp_user=' . $user_id;

		// Get URL safe user slug
		} else {
			if ( empty( $user_nicename ) ) {
				$user = get_userdata( $user_id );
				if ( !empty( $user->user_nicename ) )
					$user_nicename = $user->user_nicename;
			}
			$url = str_replace( '%bbp_user%', $user_nicename, $url );
			$url = home_url( user_trailingslashit( $url ) );
		}

		return apply_filters( 'bbp_get_user_profile_url', $url, $user_id, $user_nicename );

	}

/**
 * bbp_user_profile_edit_link ()
 *
 * Output link to the profile edit page of a user
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2688)
 *
 * @uses bbp_get_user_profile_edit_link ()
 *
 * @param int $user_id
 */
function bbp_user_profile_edit_link ( $user_id = 0 ) {
	echo bbp_get_user_profile_edit_link( $user_id );
}
	/**
	 * bbp_get_user_profile_edit_link ()
	 *
	 * Return link to the profile edit page of a user
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2688)
	 *
	 * @uses bbp_get_user_profile_edit_url ()
	 *
	 * @param int $user_id
	 * @return string
	 */
	function bbp_get_user_profile_edit_link ( $user_id = 0 ) {
		if ( empty( $user_id ) )
			return false;

		$user      = get_userdata( $user_id );
		$name      = $user->display_name;
		$edit_link = '<a href="' . bbp_get_user_profile_url( $user_id ) . '" title="' . esc_attr( $name ) . '">' . $name . '</a>';
		return apply_filters( 'bbp_get_user_profile_link', $edit_link, $user_id );
	}

/**
 * bbp_user_profile_edit_url ()
 *
 * Output URL to the profile edit page of a user
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2688)
 *
 * @uses bbp_get_user_edit_profile_url ()
 *
 * @param int $user_id
 */
function bbp_user_profile_edit_url ( $user_id = 0, $user_nicename = '' ) {
	echo bbp_get_user_profile_edit_url( $user_id );
}
	/**
	 * bbp_get_user_profile_edit_url ()
	 *
	 * Return URL to the profile edit page of a user
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2688)
	 *
	 * @param int $user_id
	 * @param string $user_nicename
	 *
	 * @return string
	 */
	function bbp_get_user_profile_edit_url ( $user_id = 0, $user_nicename = '' ) {
		global $wp_rewrite, $bbp;

		if ( empty( $user_id ) )
			$user_id = bbp_get_displayed_user_id();
		else
			return;

		$url = !empty( $wp_rewrite->permalink_structure ) ? $wp_rewrite->front . $bbp->user_slug . '/%bbp_user%/edit' : '';

		if ( empty( $url ) ) {
			$file = home_url( '/' );
			$url  = $file . '?bbp_user=' . $user_id . '&bbp_edit_profile=1';
		} else {
			if ( empty( $user_nicename ) ) {
				$user = get_userdata( $user_id );
				if ( !empty( $user->user_nicename ) )
					$user_nicename = $user->user_nicename;
			}
			$url = str_replace( '%bbp_user%', $user_nicename, $url );
			$url = home_url( user_trailingslashit( $url ) );
		}

		return apply_filters( 'bbp_get_user_edit_profile_url', $url, $user_id, $user_nicename );

	}

/** Edit User *****************************************************************/

/**
 * bbp_edit_user_success ()
 */
function bbp_notice_edit_user_success () {
	if ( isset( $_GET['updated'] ) ) : ?>

	<div class="bbp-template-notice updated">
		<p><?php _e( 'User updated.', 'bbpress' ) ?></p>
	</div>

<?php endif;
}
add_action( 'bbp_template_notices', 'bbp_notice_edit_user_success' );

function bbp_notice_edit_user_is_super_admin () {
	if ( is_multisite() && current_user_can( 'manage_network_options' ) && is_super_admin( bbp_get_displayed_user_id() ) ) : ?>

	<div class="bbp-template-notice important">
		<p><?php bbp_is_user_home() ? _e( 'You have super admin privileges.', 'bbpress' ) : _e( 'This user has super admin privileges.', 'bbpress' ); ?></p>
	</div>

<?php endif;
}
add_action( 'bbp_template_notices', 'bbp_notice_edit_user_is_super_admin', 2 );

/**
 * bbp_edit_user_display_name()
 *
 * Drop down for selecting the user's display name
 *
 * @global bbPress $bbp
 */
function bbp_edit_user_display_name () {
	global $bbp;

	$public_display = array();
	$public_display['display_username'] = $bbp->displayed_user->user_login;
	$public_display['display_nickname'] = $bbp->displayed_user->nickname;

	if ( !empty( $bbp->displayed_user->first_name ) )
		$public_display['display_firstname'] = $bbp->displayed_user->first_name;

	if ( !empty( $bbp->displayed_user->last_name ) )
		$public_display['display_lastname']  = $bbp->displayed_user->last_name;

	if ( !empty( $bbp->displayed_user->first_name ) && !empty( $bbp->displayed_user->last_name ) ) {
		$public_display['display_firstlast'] = $bbp->displayed_user->first_name . ' ' . $bbp->displayed_user->last_name;
		$public_display['display_lastfirst'] = $bbp->displayed_user->last_name  . ' ' . $bbp->displayed_user->first_name;
	}

	if ( !in_array( $bbp->displayed_user->display_name, $public_display ) ) // Only add this if it isn't duplicated elsewhere
		$public_display = array( 'display_displayname' => $bbp->displayed_user->display_name ) + $public_display;

	$public_display = array_map( 'trim', $public_display );
	$public_display = array_unique( $public_display ); ?>

	<select name="display_name" id="display_name">

	<?php foreach ( $public_display as $id => $item ) : ?>

		<option id="<?php echo $id; ?>" value="<?php echo esc_attr( $item ); ?>"<?php selected( $bbp->displayed_user->display_name, $item ); ?>><?php echo $item; ?></option>

	<?php endforeach; ?>

	</select>

<?php
}

/**
 * bbp_edit_user_role ()
 *
 * Output role selector (for user edit)
 *
 * @package bbPress
 * @subpackage Template
 *
 * @param string $default slug for the role that should be already selected
 */
function bbp_edit_user_role () {
	global $bbp;

	// Return if no user is displayed
	if ( !isset( $bbp->displayed_user ) )
		return;

	// Local variables
	$p = $r = '';

	// print the 'no role' option. Make it selected if the user has no role yet.
	if ( !$user_role = array_shift( $bbp->displayed_user->roles ) )
		$r .= '<option value="">' . __( '&mdash; No role for this site &mdash;', 'bbpress' ) . '</option>';

	// Loop through roles
	foreach ( get_editable_roles() as $role => $details ) {
		$name = translate_user_role( $details['name'] );

		// Make default first in list
		if ( $user_role == $role )
			$p = "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>{$name}</option>";
		else
			$r .= "\n\t<option value='" . esc_attr($role) . "'>{$name}</option>";
	}

	// Output result
	echo '<select name="role" id="role">' . $p . $r . '</select>';
}

function bbp_edit_user_contact_methods( $user_id = 0 ) {
	global $bbp;

	return _wp_get_user_contactmethods( $bbp->displayed_user );
}

/** END User Functions ********************************************************/

?>
