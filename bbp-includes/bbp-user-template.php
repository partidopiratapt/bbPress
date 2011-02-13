<?php

/**
 * bbPress User Template Tags
 *
 * @package bbPress
 * @subpackage TemplateTags
 */

/** Users *********************************************************************/

/**
 * Output a validated user id
 *
 * @since bbPress (r2729)
 *
 * @param int $user_id Optional. User id
 * @param bool $displayed_user_fallback Fallback on displayed user?
 * @param bool $current_user_fallback Fallback on current user?
 * @uses bbp_get_user_id() To get the user id
 */
function bbp_user_id( $user_id = 0, $displayed_user_fallback = true, $current_user_fallback = false ) {
	echo bbp_get_user_id( $user_id, $displayed_user_fallback, $current_user_fallback );
}
	/**
	 * Return a validated user id
	 *
	 * @since bbPress (r2729)
	 *
	 * @param int $user_id Optional. User id
	 * @param bool $displayed_user_fallback Fallback on displayed user?
	 * @param bool $current_user_fallback Fallback on current user?
	 * @uses get_query_var() To get the 'bbp_user_id' query var
	 * @uses apply_filters() Calls 'bbp_get_user_id' with the user id
	 * @return int Validated user id
	 */
	function bbp_get_user_id( $user_id = 0, $displayed_user_fallback = true, $current_user_fallback = false ) {
		global $bbp;

		// Easy empty checking
		if ( !empty( $user_id ) && is_numeric( $user_id ) )
			$bbp_user_id = $user_id;

		// Currently viewing or editing a user
		elseif ( ( true == $displayed_user_fallback ) && !empty( $bbp->displayed_user->ID ) )
			$bbp_user_id = $bbp->displayed_user->ID;

		// Maybe fallback on the current_user ID
		elseif ( ( true == $current_user_fallback ) && !empty( $bbp->current_user->ID ) )
			$bbp_user_id = $bbp->current_user->ID;

		// Failsafe
		else
			$bbp_user_id = get_query_var( 'bbp_user_id' );

		return apply_filters( 'bbp_get_user_id', (int) $bbp_user_id );
	}

/**
 * Output ID of current user
 *
 * @since bbPress (r2574)
 *
 * @uses bbp_get_current_user_id() To get the current user id
 */
function bbp_current_user_id() {
	echo bbp_get_current_user_id();
}
	/**
	 * Return ID of current user
	 *
	 * @since bbPress (r2574)
	 *
	 * @uses bbp_get_user_id() To get the current user id
	 * @uses apply_filters() Calls 'bbp_get_current_user_id' with the id
	 * @return int Current user id
	 */
	function bbp_get_current_user_id() {
		return apply_filters( 'bbp_get_current_user_id', bbp_get_user_id( 0, false, true ) );
	}

/**
 * Output ID of displayed user
 *
 * @since bbPress (r2688)
 *
 * @uses bbp_get_displayed_user_id() To get the displayed user id
 */
function bbp_displayed_user_id() {
	echo bbp_get_displayed_user_id();
}
	/**
	 * Return ID of displayed user
	 *
	 * @since bbPress (r2688)
	 *
	 * @uses bbp_get_user_id() To get the displayed user id
	 * @uses apply_filters() Calls 'bbp_get_displayed_user_id' with the id
	 * @return int Displayed user id
	 */
	function bbp_get_displayed_user_id() {
		return apply_filters( 'bbp_get_displayed_user_id', bbp_get_user_id( 0, true, false ) );
	}

/**
 * Return a sanitized user field value
 *
 * @since bbPress (r2688)
 *
 * @param string $field Field to get
 * @uses sanitize_text_field() To sanitize the field
 * @uses esc_attr() To sanitize the field
 * @return string|bool Value of the field if it exists, else false
 */
function bbp_get_displayed_user_field( $field = '' ) {
	global $bbp;

	// Return field if exists
	if ( isset( $bbp->displayed_user->$field ) )
		return esc_attr( sanitize_text_field( $bbp->displayed_user->$field ) );

	// Return empty
	return false;
}

/**
 * Output name of current user
 *
 * @since bbPress (r2574)
 *
 * @uses bbp_get_current_user_name() To get the current user name
 */
function bbp_current_user_name() {
	echo bbp_get_current_user_name();
}
	/**
	 * Return name of current user
	 *
	 * @since bbPress (r2574)
	 *
	 * @uses apply_filters() Calls 'bbp_get_current_user_name' with the
	 *                        current user name
	 * @return string
	 */
	function bbp_get_current_user_name() {
		global $user_identity;

		if ( is_user_logged_in() )
			$current_user_name = $user_identity;
		else
			$current_user_name = __( 'Anonymous', 'bbpress' );

		return apply_filters( 'bbp_get_current_user_name', $current_user_name );
	}

/**
 * Output avatar of current user
 *
 * @since bbPress (r2574)
 *
 * @param int $size Size of the avatar. Defaults to 40
 * @uses bbp_get_current_user_avatar() To get the current user avatar
 */
function bbp_current_user_avatar( $size = 40 ) {
	echo bbp_get_current_user_avatar( $size );
}

	/**
	 * Return avatar of current user
	 *
	 * @since bbPress (r2574)
	 *
	 * @param int $size Size of the avatar. Defaults to 40
	 * @uses bbp_get_current_user_id() To get the current user id
	 * @uses bbp_get_current_anonymous_user_data() To get the current
	 *                                              anonymous user's email
	 * @uses get_avatar() To get the avatar
	 * @uses apply_filters() Calls 'bbp_get_current_user_avatar' with the
	 *                        avatar and size
	 * @return string Current user avatar
	 */
	function bbp_get_current_user_avatar( $size = 40 ) {

		if ( !$user = bbp_get_current_user_id() )
			$user = bbp_get_current_anonymous_user_data( 'email' );

		return apply_filters( 'bbp_get_current_user_avatar', get_avatar( $user, $size ), $size );
	}

/**
 * Output link to the profile page of a user
 *
 * @since bbPress (r2688)
 *
 * @param int $user_id Optional. User id
 * @uses bbp_get_user_profile_link() To get user profile link
 */
function bbp_user_profile_link( $user_id = 0 ) {
	echo bbp_get_user_profile_link( $user_id );
}
	/**
	 * Return link to the profile page of a user
	 *
	 * @since bbPress (r2688)
	 *
	 * @param int $user_id Optional. User id
	 * @uses bbp_get_user_id() To get user id
	 * @uses get_userdata() To get user data
	 * @uses bbp_get_user_profile_url() To get user profile url
	 * @uses apply_filters() Calls 'bbp_get_user_profile_link' with the user
	 *                        profile link and user id
	 * @return string User profile link
	 */
	function bbp_get_user_profile_link( $user_id = 0 ) {
		if ( !$user_id = bbp_get_user_id( $user_id ) )
			return false;

		$user      = get_userdata( $user_id );
		$name      = esc_attr( $user->display_name );
		$user_link = '<a href="' . bbp_get_user_profile_url( $user_id ) . '" title="' . $name . '">' . $name . '</a>';

		return apply_filters( 'bbp_get_user_profile_link', $user_link, $user_id );
	}

/**
 * Output URL to the profile page of a user
 *
 * @since bbPress (r2688)
 *
 * @param int $user_id Optional. User id
 * @param string $user_nicename Optional. User nicename
 * @uses bbp_get_user_profile_url() To get user profile url
 */
function bbp_user_profile_url( $user_id = 0, $user_nicename = '' ) {
	echo bbp_get_user_profile_url( $user_id );
}
	/**
	 * Return URL to the profile page of a user
	 *
	 * @since bbPress (r2688)
	 *
	 * @param int $user_id Optional. User id
	 * @param string $user_nicename Optional. User nicename
	 * @uses bbp_get_user_id() To get user id
	 * @uses add_query_arg() To add custom args to the url
	 * @uses home_url() To get blog home url
	 * @uses apply_filters() Calls 'bbp_get_user_profile_url' with the user
	 *                        profile url, user id and user nicename
	 * @return string User profile url
	 */
	function bbp_get_user_profile_url( $user_id = 0, $user_nicename = '' ) {
		global $wp_rewrite, $bbp;

		// Use displayed user ID if there is one, and one isn't requested
		if ( !$user_id = bbp_get_user_id( $user_id ) )
			return false;

		// No pretty permalinks
		if ( empty( $wp_rewrite->permalink_structure ) ) {
			$url = add_query_arg( array( 'bbp_user' => $user_id ), home_url( '/' ) );

		// Get URL safe user slug
		} else {
			$url = $wp_rewrite->front . $bbp->user_slug . '/%bbp_user%';

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
 * Output link to the profile edit page of a user
 *
 * @since bbPress (r2688)
 *
 * @param int $user_id Optional. User id
 * @uses bbp_get_user_profile_edit_link() To get user profile edit link
 */
function bbp_user_profile_edit_link( $user_id = 0 ) {
	echo bbp_get_user_profile_edit_link( $user_id );
}
	/**
	 * Return link to the profile edit page of a user
	 *
	 * @since bbPress (r2688)
	 *
	 * @param int $user_id Optional. User id
	 * @uses bbp_get_user_id() To get user id
	 * @uses get_userdata() To get user data
	 * @uses bbp_get_user_profile_edit_url() To get user profile edit url
	 * @uses apply_filters() Calls 'bbp_get_user_profile_link' with the edit
	 *                        link and user id
	 * @return string User profile edit link
	 */
	function bbp_get_user_profile_edit_link( $user_id = 0 ) {
		if ( !$user_id = bbp_get_user_id( $user_id ) )
			return false;

		$user      = get_userdata( $user_id );
		$name      = $user->display_name;
		$edit_link = '<a href="' . bbp_get_user_profile_url( $user_id ) . '" title="' . esc_attr( $name ) . '">' . $name . '</a>';
		return apply_filters( 'bbp_get_user_profile_link', $edit_link, $user_id );
	}

/**
 * Output URL to the profile edit page of a user
 *
 * @since bbPress (r2688)
 *
 * @param int $user_id Optional. User id
 * @param string $user_nicename Optional. User nicename
 * @uses bbp_get_user_profile_edit_url() To get user profile edit url
 */
function bbp_user_profile_edit_url( $user_id = 0, $user_nicename = '' ) {
	echo bbp_get_user_profile_edit_url( $user_id );
}
	/**
	 * Return URL to the profile edit page of a user
	 *
	 * @since bbPress (r2688)
	 *
	 * @param int $user_id Optional. User id
	 * @param string $user_nicename Optional. User nicename
	 * @uses bbp_get_user_id() To get user id
	 * @uses add_query_arg() To add custom args to the url
	 * @uses home_url() To get blog home url
	 * @uses apply_filters() Calls 'bbp_get_user_edit_profile_url' with the
	 *                        edit profile url, user id and user nicename
	 * @return string
	 */
	function bbp_get_user_profile_edit_url( $user_id = 0, $user_nicename = '' ) {
		global $wp_rewrite, $bbp;

		if ( !$user_id = bbp_get_user_id( $user_id ) )
			return;

		if ( empty( $wp_rewrite->permalink_structure ) ) {
			$url = add_query_arg( array( 'bbp_user' => $user_id, 'edit' => '1' ), home_url( '/' ) );
		} else {
			$url = $wp_rewrite->front . $bbp->user_slug . '/%bbp_user%/edit';

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

/**
 * Output the link to the admin section
 *
 * @since bbPress (r2827)
 *
 * @param mixed $args Optional. See {@link bbp_get_admin_link()}
 * @uses bbp_get_admin_link() To get the admin link
 */
function bbp_admin_link( $args = '' ) {
	echo bbp_get_admin_link( $args );
}
	/**
	 * Return the link to the admin section
	 *
	 * @since bbPress (r2827)
	 *
	 * @param mixed $args Optional. This function supports these arguments:
	 *  - text: The text
	 *  - before: Before the lnk
	 *  - after: After the link
	 * @uses current_user_can() To check if the current user can moderate
	 * @uses admin_url() To get the admin url
	 * @uses apply_filters() Calls 'bbp_get_admin_link' with the link & args
	 * @return The link
	 */
	function bbp_get_admin_link( $args = '' ) {
		if ( !current_user_can( 'moderate' ) )
			return;

		if ( $args && is_string( $args ) && false === strpos( $args, '=' ) )
			$args = array( 'text' => $args );

		$defaults = array( 'text' => __( 'Admin', 'bbpress' ), 'before' => '', 'after' => '' );
		$args     = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		$uri = admin_url();

		return apply_filters( 'bbp_get_admin_link', $before . '<a href="' . $uri . '">' . $text . '</a>' . $after, $args );
	}

/** Favorites *****************************************************************/

/**
 * Output the link to the user's favorites page (profile page)
 *
 * @since bbPress (r2652)
 *
 * @param int $user_id Optional. User id
 * @uses bbp_get_favorites_permalink() To get the favorites permalink
 */
function bbp_favorites_permalink( $user_id = 0 ) {
	echo bbp_get_favorites_permalink( $user_id );
}
	/**
	 * Return the link to the user's favorites page (profile page)
	 *
	 * @since bbPress (r2652)
	 *
	 * @param int $user_id Optional. User id
	 * @uses bbp_get_user_profile_url() To get the user profile url
	 * @uses apply_filters() Calls 'bbp_get_favorites_permalink' with the
	 *                        user profile url and user id
	 * @return string Permanent link to user profile page
	 */
	function bbp_get_favorites_permalink( $user_id = 0 ) {
		return apply_filters( 'bbp_get_favorites_permalink', bbp_get_user_profile_url( $user_id ), $user_id );
	}

/**
 * Output the link to make a topic favorite/remove a topic from favorites
 *
 * @since bbPress (r2652)
 *
 * @param array $add Optional. Add to favorites args
 * @param array $rem Optional. Remove from favorites args
 * @param int $user_id Optional. User id
 * @uses bbp_get_user_favorites_link() To get the user favorites link
 */
function bbp_user_favorites_link( $add = array(), $rem = array(), $user_id = 0 ) {
	echo bbp_get_user_favorites_link( $add, $rem, $user_id );
}
	/**
	 * User favorites link
	 *
	 * Return the link to make a topic favorite/remove a topic from
	 * favorites
	 *
	 * @since bbPress (r2652)
	 *
	 * @param array $add Optional. Add to favorites args
	 * @param array $rem Optional. Remove from favorites args
	 * @param int $user_id Optional. User id
	 * @uses bbp_get_user_id() To get the user id
	 * @uses current_user_can() If the current user can edit the user
	 * @uses bbp_get_topic_id() To get the topic id
	 * @uses bbp_is_user_favorite() To check if the topic is user's favorite
	 * @uses bbp_get_favorites_permalink() To get the favorites permalink
	 * @uses bbp_get_topic_permalink() To get the topic permalink
	 * @uses bbp_is_favorites() Is it the favorites page?
	 * @uses apply_filters() Calls 'bbp_get_user_favorites_link' with the
	 *                        html, add args, remove args, user & topic id
	 * @return string User favorites link
	 */
	function bbp_get_user_favorites_link( $add = array(), $rem = array(), $user_id = 0 ) {
		if ( !$user_id = bbp_get_user_id( $user_id, true, true ) )
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

/** Subscriptions *************************************************************/

/**
 * Output the link to the user's subscriptions page (profile page)
 *
 * @since bbPress (r2688)
 *
 * @param int $user_id Optional. User id
 * @uses bbp_get_subscriptions_permalink() To get the subscriptions link
 */
function bbp_subscriptions_permalink( $user_id = 0 ) {
	echo bbp_get_subscriptions_permalink( $user_id );
}
	/**
	 * Return the link to the user's subscriptions page (profile page)
	 *
	 * @since bbPress (r2688)
	 *
	 * @param int $user_id Optional. User id
	 * @uses bbp_get_user_profile_url() To get the user profile url
	 * @uses apply_filters() Calls 'bbp_get_favorites_permalink' with the
	 *                        user profile url and user id
	 * @return string Permanent link to user subscriptions page
	 */
	function bbp_get_subscriptions_permalink( $user_id = 0 ) {
		return apply_filters( 'bbp_get_subscriptions_permalink', bbp_get_user_profile_url( $user_id ), $user_id );
	}

/**
 * Output the link to subscribe/unsubscribe from a topic
 *
 * @since bbPress (r2668)
 *
 * @param mixed $args See {@link bbp_get_user_subscribe_link()}
 * @uses bbp_get_user_subscribe_link() To get the subscribe link
 */
function bbp_user_subscribe_link( $args = '' ) {
	echo bbp_get_user_subscribe_link( $args );
}
	/**
	 * Return the link to subscribe/unsubscribe from a topic
	 *
	 * @since bbPress (r2668)
	 *
	 * @param mixed $args This function supports these arguments:
	 *  - subscribe: Subscribe text
	 *  - unsubscribe: Unsubscribe text
	 *  - user_id: User id
	 *  - topic_id: Topic id
	 *  - before: Before the link
	 *  - after: After the link
	 * @param int $user_id Optional. User id
	 * @uses bbp_get_user_id() To get the user id
	 * @uses current_user_can() To check if the current user can edit user
	 * @uses bbp_get_topic_id() To get the topic id
	 * @uses bbp_is_user_subscribed() To check if the user is subscribed
	 * @uses bbp_is_subscriptions() To check if it's the subscriptions page
	 * @uses bbp_get_subscriptions_permalink() To get subscriptions link
	 * @uses bbp_get_topic_permalink() To get topic link
	 * @uses apply_filters() Calls 'bbp_get_user_subscribe_link' with the
	 *                        link, args, user id & topic id
	 * @return string Permanent link to topic
	 */
	function bbp_get_user_subscribe_link( $args = '', $user_id = 0 ) {
		if ( !bbp_is_subscriptions_active() )
			return;

		$defaults = array (
			'subscribe'   => __( 'Subscribe',   'bbpress' ),
			'unsubscribe' => __( 'Unsubscribe', 'bbpress' ),
			'user_id'     => 0,
			'topic_id'    => 0,
			'before'      => '&nbsp;|&nbsp;',
			'after'       => ''
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		// Try to get a user_id
		if ( !$user_id = bbp_get_user_id( $user_id, true, true ) )
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
		return apply_filters( 'bbp_get_user_subscribe_link', $html, $args, $user_id, $topic_id );
	}


/** Edit User *****************************************************************/

/**
 * Edit profile success message
 *
 * @since bbPress (r2688)
 *
 * @uses bbp_is_user_profile_page() To check if it's the profile page
 * @uses bbp_is_user_profile_edit() To check if it's the profile edit page
 */
function bbp_notice_edit_user_success() {
	if ( isset( $_GET['updated'] ) && ( bbp_is_user_profile_page() || bbp_is_user_profile_edit() ) ) : ?>

	<div class="bbp-template-notice updated">
		<p><?php _e( 'User updated.', 'bbpress' ); ?></p>
	</div>

	<?php endif;
}

/**
 * Super admin privileges notice
 *
 * @since bbPress (r2688)
 *
 * @uses is_multisite() To check if the blog is multisite
 * @uses bbp_is_user_profile_page() To check if it's the profile page
 * @uses bbp_is_user_profile_edit() To check if it's the profile edit page
 * @uses current_user_can() To check if the current user can manage network
 *                           options
 * @uses bbp_get_displayed_user_id() To get the displayed user id
 * @uses is_super_admin() To check if the user is super admin
 * @uses bbp_is_user_home() To check if it's the user home
 */
function bbp_notice_edit_user_is_super_admin() {
	if ( is_multisite() && ( bbp_is_user_profile_page() || bbp_is_user_profile_edit() ) && current_user_can( 'manage_network_options' ) && is_super_admin( bbp_get_displayed_user_id() ) ) : ?>

	<div class="bbp-template-notice important">
		<p><?php bbp_is_user_home() ? _e( 'You have super admin privileges.', 'bbpress' ) : _e( 'This user has super admin privileges.', 'bbpress' ); ?></p>
	</div>

<?php endif;
}

/**
 * Drop down for selecting the user's display name
 *
 * @since bbPress (r2688)
 */
function bbp_edit_user_display_name() {
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
 * Output role selector (for user edit)
 *
 * @since bbPress (r2688)
 */
function bbp_edit_user_role() {
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
			$r .= "\n\t<option value='" . esc_attr( $role ) . "'>{$name}</option>";
	}

	// Output result
	echo '<select name="role" id="role">' . $p . $r . '</select>';
}

/**
 * Return user contact methods Selectbox
 *
 * @since bbPress (r2688)
 *
 * @return string user contact methods
 */
function bbp_edit_user_contact_methods() {
	global $bbp;

	return _wp_get_user_contactmethods( $bbp->displayed_user );
}

/** Login *********************************************************************/

/**
 * Redirect a user back to their profile if they are already logged in.
 *
 * This should be used before {@link get_header()} is called in template files
 * where the user should never have access to the contents of that file.
 *
 * @since bbPress (r2815)
 *
 * @param string $url The URL to redirect to
 * @uses is_user_logged_in() Check if user is logged in
 * @uses wp_safe_redirect() To safely redirect
 * @uses bbp_get_user_profile_url() To get the profile url of the user
 * @uses bbp_get_current_user_id() To get the current user id
 */
function bbp_logged_in_redirect( $url = '' ) {
	if ( !is_user_logged_in() )
		return;

	$redirect_to = !empty( $url ) ? $url : bbp_get_user_profile_url( bbp_get_current_user_id() );
	wp_safe_redirect( $redirect_to );
	exit;
}

/**
 * Output the required hidden fields when logging in
 *
 * @since bbPress (r2815)
 *
 * @uses bbp_redirect_to_field() To output the hidden request url field
 * @uses wp_nonce_field() To generate hidden nonce fields
 */
function bbp_user_login_fields() {
?>

		<input type="hidden" name="action"      value="bbp-user-login" id="bbp_user_login" />
		<input type="hidden" name="user-cookie" value="1" />

		<?php bbp_redirect_to_field(); ?>

		<?php wp_nonce_field( 'bbp-user-login' );
}

/** Register ******************************************************************/

/**
 * Output the required hidden fields when registering
 *
 * @since bbPress (r2815)
 *
 * @uses wp_nonce_field() To generate hidden nonce fields
 */
function bbp_user_register_fields() {
?>

		<input type="hidden" name="action"      value="bbp-user-register" id="bbp_user_register" />
		<input type="hidden" name="user-cookie" value="1" />

		<?php wp_nonce_field( 'bbp-user-register' );
}

/** Lost Password *************************************************************/

/**
 * Output the required hidden fields when user lost password
 *
 * @since bbPress (r2815)
 *
 * @uses wp_referer_field() Set referer
 * @uses wp_nonce_field() To generate hidden nonce fields
 */
function bbp_user_lost_pass_fields() {
?>

		<input type="hidden" name="action"      id="bbp_user_lost_pass" value="bbp-user-lost-pass" />
		<input type="hidden" name="user-cookie" value="1" />

		<?php wp_nonce_field( 'bbp-user-lost-pass' );
}

/** Author Avatar *************************************************************/

/**
 * Output the author link of a post
 *
 * @since bbPress (r2875)
 *
 * @param mixed $args Optional. If it is an integer, it is used as post id.
 * @uses bbp_get_author_link() To get the post author link
 */
function bbp_author_link( $args = '' ) {
	echo bbp_get_author_link( $args );
}
	/**
	 * Return the author link of the post
	 *
	 * @since bbPress (r2875)
	 *
	 * @param mixed $args Optional. If an integer, it is used as reply id.
	 * @uses bbp_get_reply_id() To get the reply id
	 * @uses bbp_is_topic() To check if it's a topic page
	 * @uses bbp_is_reply() To check if it's a reply page
	 * @uses bbp_is_reply_anonymous() To check if the reply is by an
	 *                                 anonymous user
	 * @uses bbp_get_reply_author() To get the reply author name
	 * @uses bbp_get_reply_author_url() To get the reply author url
	 * @uses bbp_get_reply_author_avatar() To get the reply author avatar
	 * @uses apply_filters() Calls 'bbp_get_reply_author_link' with the
	 *                        author link and args
	 * @return string Author link of reply
	 */
	function bbp_get_author_link( $args = '' ) {
		$defaults = array (
			'post_id'    => 0,
			'link_title' => '',
			'type'       => 'both',
			'size'       => 80
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		// Used as reply_id
		if ( is_numeric( $args ) )
			$post_id = $args;

		if ( bbp_get_topic_id( $post_id ) )
			return bbp_get_topic_author_link( $args );
		elseif ( bbp_get_reply_id( $post_id ) )
			return bbp_get_reply_author_link( $args );
		else
			$user_id = get_post_field( 'post_author', $post_id );

		// Neither a reply nor a topic, so could be a revision
		if ( !empty( $post_id ) ) {
			if ( empty( $link_title ) )
				$link_title = sprintf( !bbp_is_reply_anonymous( $post_id ) ? __( 'View %s\'s profile', 'bbpress' ) : __( 'Visit %s\'s website', 'bbpress' ), get_the_author_meta( 'display_name', $user_id ) );

			$link_title = !empty( $link_title ) ? ' title="' . $link_title . '"' : '';
			$author_url = bbp_get_user_profile_url( $user_id );
			$anonymous  = bbp_is_reply_anonymous( $post_id );

			// Get avatar
			if ( 'avatar' == $type || 'both' == $type )
				$author_links[] = get_avatar( $user_id, $size );

			// Get display name
			if ( 'name' == $type   || 'both' == $type )
				$author_links[] = get_the_author_meta( 'display_name', $user_id );

			// Add links if not anonymous
			if ( empty( $anonymous ) ) {
				foreach ( $author_links as $link_text ) {
					$author_link[] = sprintf( '<a href="%1$s"%2$s>%3$s</a>', $author_url, $link_title, $link_text );
				}
				$author_link = join( '&nbsp;', $author_link );

			// No links if anonymous
			} else {
				$author_link = join( '&nbsp;', $author_links );
			}

		// No post so link is empty
		} else {
			$author_link = '';
		}

		return apply_filters( 'bbp_get_author_link', $author_link, $args );
	}
?>
