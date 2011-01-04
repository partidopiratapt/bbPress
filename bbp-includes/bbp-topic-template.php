<?php

/** START - Topic Loop Functions **********************************************/

/**
 * bbp_has_topics()
 *
 * The main topic loop. WordPress makes this easy for us
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2485)
 *
 * @global WP_Query $bbp->topic_query
 * @param array $args Possible arguments to change returned topics
 * @return object Multidimensional array of topic information
 */
function bbp_has_topics ( $args = '' ) {
	global $wp_rewrite, $bbp;

	$default = array (
		// Narrow query down to bbPress topics
		'post_type'      => $bbp->topic_id,

		// Forum ID
		'post_parent'    => bbp_get_forum_id(),

		// Make sure topic has some last activity time
		'meta_key'       => '_bbp_topic_last_active',

		// 'meta_value', 'author', 'date', 'title', 'modified', 'parent', rand',
		'orderby'        => 'meta_value',

		// 'ASC', 'DESC'
		'order'          => 'DESC',

		// @todo replace 15 with setting
		'posts_per_page' => 15,

		// Page Number
		'paged'          => bbp_get_paged(),

		// Topic Search
		's'              => !empty( $_REQUEST['ts'] ) ? $_REQUEST['ts'] : '',
	);

	// Don't pass post_parent if forum_id is empty or 0
	if ( empty( $default['post_parent'] ) ) {
		unset( $default['post_parent'] );
		if ( !bbp_is_user_profile_page() )
			$post_parent = get_the_ID();
	}

	// Set up topic variables
	$bbp_t = wp_parse_args( $args, $default );
	$r     = extract( $bbp_t );

	// If we're viewing a tax/term, use the existing query; if not, run our own
	if ( !is_tax() ) {
		$bbp->topic_query = new WP_Query( $bbp_t );
	} else {
		global $wp_query;
		$bbp->topic_query = $wp_query;
	}

	if ( -1 == $posts_per_page )
		$posts_per_page = $bbp->topic_query->post_count;

	// Add pagination values to query object
	$bbp->topic_query->posts_per_page = $posts_per_page;
	$bbp->topic_query->paged          = $paged;

	// Only add pagination if query returned results
	if ( ( (int)$bbp->topic_query->post_count || (int)$bbp->topic_query->found_posts ) && (int)$bbp->topic_query->posts_per_page ) {

		// If pretty permalinks are enabled, make our pagination pretty
		if ( $wp_rewrite->using_permalinks() && bbp_is_user_profile_page() )
			$base = $base = user_trailingslashit( trailingslashit( bbp_get_user_profile_url( bbp_get_displayed_user_id() ) ) . 'page/%#%/' );
		elseif ( $wp_rewrite->using_permalinks() )
			$base = user_trailingslashit( trailingslashit( get_permalink( $post_parent ) ) . 'page/%#%/' );
		else
			$base = add_query_arg( 'paged', '%#%' );

		// Pagination settings with filter
		$bbp_topic_pagination = apply_filters( 'bbp_topic_pagination', array (
			'base'      => $base,
			'format'    => '',
			'total'     => $posts_per_page == $bbp->topic_query->found_posts ? 1 : ceil( (int)$bbp->topic_query->found_posts / (int)$posts_per_page ),
			'current'   => (int)$bbp->topic_query->paged,
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
			'mid_size'  => 1
		) );

		// Add pagination to query object
		$bbp->topic_query->pagination_links = paginate_links ( $bbp_topic_pagination );

		// Remove first page from pagination
		$bbp->topic_query->pagination_links = str_replace( 'page/1/\'', '\'', $bbp->topic_query->pagination_links );
	}

	// Return object
	return apply_filters( 'bbp_has_topics', $bbp->topic_query->have_posts(), $bbp );
}

/**
 * bbp_topics()
 *
 * Whether there are more topics available in the loop
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2485)
 *
 * @global WP_Query $bbp->topic_query
 * @return object Forum information
 */
function bbp_topics () {
	global $bbp;
	return $bbp->topic_query->have_posts();
}

/**
 * bbp_the_topic()
 *
 * Loads up the current topic in the loop
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2485)
 *
 * @global WP_Query $bbp->topic_query
 * @return object Forum information
 */
function bbp_the_topic () {
	global $bbp;
	return $bbp->topic_query->the_post();
}

/**
 * bbp_topic_id()
 *
 * Output id from bbp_topic_id()
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2485)
 *
 * @uses bbp_get_topic_id()
 */
function bbp_topic_id () {
	echo bbp_get_topic_id();
}
	/**
	 * bbp_get_topic_id()
	 *
	 * Return the topic ID
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2485)
	 *
	 * @global object $topics_template
	 * @return string Forum id
	 */
	function bbp_get_topic_id ( $topic_id = 0 ) {
		global $bbp, $wp_query, $bbp;

		// Easy empty checking
		if ( !empty( $topic_id ) && is_numeric( $topic_id ) )
			$bbp_topic_id = $topic_id;

		// Currently inside a topic loop
		elseif ( !empty( $bbp->topic_query->in_the_loop ) && isset( $bbp->topic_query->post->ID ) )
			$bbp_topic_id = $bbp->topic_query->post->ID;

		// Currently viewing a topic
		elseif ( bbp_is_topic() && isset( $wp_query->post->ID ) )
			$bbp_topic_id = $wp_query->post->ID;

		// Currently viewing a singular reply
		elseif ( bbp_is_reply() )
			$bbp_topic_id = bbp_get_reply_topic_id();

		// Fallback
		else
			$bbp_topic_id = 0;

		$bbp->current_topic_id = $bbp_topic_id;

		return apply_filters( 'bbp_get_topic_id', (int)$bbp_topic_id );
	}

/**
 * bbp_topic_permalink ()
 *
 * Output the link to the topic in the topic loop
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2485)
 *
 * @uses bbp_get_topic_permalink()
 * @param int $topic_id optional
 */
function bbp_topic_permalink ( $topic_id = 0 ) {
	echo bbp_get_topic_permalink( $topic_id );
}
	/**
	 * bbp_get_topic_permalink()
	 *
	 * Return the link to the topic in the loop
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2485)
	 *
	 * @uses apply_filters
	 * @uses get_permalink
	 * @param int $topic_id optional
	 *
	 * @return string Permanent link to topic
	 */
	function bbp_get_topic_permalink ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );

		return apply_filters( 'bbp_get_topic_permalink', get_permalink( $topic_id ) );
	}

/**
 * bbp_topic_title ()
 *
 * Output the title of the topic in the loop
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2485)
 * @param int $topic_id optional
 *
 * @uses bbp_get_topic_title()
 */
function bbp_topic_title ( $topic_id = 0 ) {
	echo bbp_get_topic_title( $topic_id );
}
	/**
	 * bbp_get_topic_title ()
	 *
	 * Return the title of the topic in the loop
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2485)
	 *
	 * @uses apply_filters
	 * @uses get_the_title()
	 * @param int $topic_id optional
	 *
	 * @return string Title of topic
	 */
	function bbp_get_topic_title ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );

		return apply_filters( 'bbp_get_topic_title', get_the_title( $topic_id ) );
	}

/**
 * bbp_topic_status ()
 *
 * Output the status of the topic in the loop
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2667)
 * @param int $topic_id optional
 *
 * @uses bbp_get_topic_status()
 */
function bbp_topic_status ( $topic_id = 0 ) {
	echo bbp_get_topic_status( $topic_id );
}
	/**
	 * bbp_get_topic_status ()
	 *
	 * Return the status of the topic in the loop
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2667)
	 *
	 * @todo custom topic ststuses
	 *
	 * @uses apply_filters
	 * @uses get_the_title()
	 * @param int $topic_id optional
	 *
	 * @return string Status of topic
	 */
	function bbp_get_topic_status ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );

		return apply_filters( 'bbp_get_topic_status', get_post_status( $topic_id ) );
	}

/**
 * bbp_is_topic_open ()
 *
 * Is the topic open to new replies?
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2727)
 *
 * @uses bbp_get_topic_id()
 * @uses bbp_get_topic_status()
 *
 * @param int $topic_id optional
 * @return bool True if open, false if closed.
 */
function bbp_is_topic_open ( $topic_id = 0 ) {
	global $bbp;

	$topic_status = bbp_get_topic_status( bbp_get_topic_id( $topic_id ) );
	return $bbp->closed_status_id != $topic_status;
}

/**
 * bbp_is_topic_spam ()
 *
 * Is the topic marked as spam?
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2727)
 *
 * @uses bbp_get_topic_id()
 * @uses bbp_get_topic_status()
 *
 * @param int $topic_id optional
 * @return bool True if spam, false if not.
 */
function bbp_is_topic_spam ( $topic_id = 0 ) {
	global $bbp;

	$topic_status = bbp_get_topic_status( bbp_get_topic_id( $topic_id ) );
	return $bbp->spam_status_id == $topic_status;
}

/**
 * bbp_topic_author ()
 *
 * Output the author of the topic in the loop
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2590)
 * @param int $topic_id optional
 *
 * @uses bbp_get_topic_author()
 */
function bbp_topic_author ( $topic_id = 0 ) {
	echo bbp_get_topic_author( $topic_id );
}
	/**
	 * bbp_get_topic_author ()
	 *
	 * Return the author of the topic in the loop
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2590)
	 *
	 * @uses apply_filters
	 * @param int $topic_id optional
	 *
	 * @return string Author of topic
	 */
	function bbp_get_topic_author ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );

		if ( get_post_field( 'post_author', $topic_id ) )
			$author = get_the_author();
		else
			$author = get_post_meta( $topic_id, '_bbp_anonymous_name', true );

		return apply_filters( 'bbp_get_topic_author', $author );
	}

/**
 * bbp_topic_author_id ()
 *
 * Output the author ID of the topic in the loop
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2590)
 * @param int $topic_id optional
 *
 * @uses bbp_get_topic_author()
 */
function bbp_topic_author_id ( $topic_id = 0 ) {
	echo bbp_get_topic_author_id( $topic_id );
}
	/**
	 * bbp_get_topic_author_id ()
	 *
	 * Return the author ID of the topic in the loop
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2590)
	 *
	 * @uses apply_filters
	 * @param int $topic_id optional
	 *
	 * @return string Author of topic
	 */
	function bbp_get_topic_author_id ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );

		return apply_filters( 'bbp_get_topic_author_id', get_post_field( 'post_author', $topic_id ) );
	}

/**
 * bbp_topic_author_display_name ()
 *
 * Output the author display_name of the topic in the loop
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2590)
 * @param int $topic_id optional
 *
 * @uses bbp_get_topic_author()
 */
function bbp_topic_author_display_name ( $topic_id = 0 ) {
	echo bbp_get_topic_author_display_name( $topic_id );
}
	/**
	 * bbp_get_topic_author_display_name ()
	 *
	 * Return the author display_name of the topic in the loop
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2485)
	 *
	 * @uses apply_filters
	 * @param int $topic_id optional
	 *
	 * @return string Author of topic
	 */
	function bbp_get_topic_author_display_name ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );

		// Check for anonymous user
		if ( $author_id = get_post_field( 'post_author', $topic_id ) )
			$author_name = get_the_author_meta( 'display_name', $author_id );
		else
			$author_name = get_post_meta( $topic_id, '_bbp_anonymous_name', true );

		return apply_filters( 'bbp_get_topic_author_id', esc_attr( $author_name ) );
	}

/**
 * bbp_topic_author_avatar ()
 *
 * Output the author avatar of the topic in the loop
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2590)
 * @param int $topic_id optional
 *
 * @uses bbp_get_topic_author_avatar()
 */
function bbp_topic_author_avatar ( $topic_id = 0, $size = 40 ) {
	echo bbp_get_topic_author_avatar( $topic_id, $size );
}
	/**
	 * bbp_get_topic_author_avatar ()
	 *
	 * Return the author avatar of the topic in the loop
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2590)
	 *
	 * @uses get_avatar()
	 * @uses get_post_meta()
	 *
	 * @param int $topic_id optional
	 * @return string Avatar of the author of the topic
	 */
	function bbp_get_topic_author_avatar ( $topic_id = 0, $size = 40 ) {
		$topic_id = bbp_get_topic_id( $topic_id );

		// Check for anonymous user
		if ( $author_id = get_post_field( 'post_author', $topic_id ) )
			$author_avatar = get_avatar( $author_id );
		else
			$author_avatar = get_avatar( get_post_meta( $topic_id, '_bbp_anonymous_email', true ) );

		return apply_filters( 'bbp_get_topic_author_avatar', $author_avatar, $topic_id, $size );
	}

/**
 * bbp_topic_author_link ()
 *
 * Output the author link of the topic in the loop
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2717)
 *
 * @param mixed|int $args If it is an integer, it is used as topic_id. Optional.
 * @uses bbp_get_topic_author_link()
 */
function bbp_topic_author_link ( $args = '' ) {
	echo bbp_get_topic_author_link( $args );
}
	/**
	 * bbp_get_topic_author_link ()
	 *
	 * Return the author link of the topic in the loop
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2717)
	 *
	 * @uses bbp_get_topic_author_url()
	 * @uses bbp_get_topic_author()
	 *
	 * @param mixed|int $args If it is an integer, it is used as topic_id. Optional.
	 * @return string Author link of topic
	 */
	function bbp_get_topic_author_link ( $args = '' ) {
		// Used as topic_id
		if ( is_int( $args ) ) {
			$topic_id = bbp_get_topic_id( $args );
		} else {
			$defaults = array (
				'topic_id'   => 0,
				'link_title' => '',
				'link_text'  => ''
			);

			$r = wp_parse_args( $args, $defaults );
			extract( $r );
		}

		if ( empty( $topic_id ) )
			$topic_id = bbp_get_topic_id( $topic_id );

		if ( empty( $link_title ) && ( bbp_is_topic() || bbp_is_topic() ) )
			$link_title = sprintf( get_the_author_meta( 'ID' ) ? __( 'View %s\'s profile', 'bbpress' ) : __( 'Visit %s\'s website', 'bbpress' ), bbp_get_topic_author( $topic_id ) );

		if ( empty( $link_text ) && ( bbp_is_topic() || bbp_is_topic() ) )
			$link_text = bbp_get_topic_author_avatar( $topic_id, 80 );
		else
			$link_text = bbp_get_topic_author( $topic_id );

		$link_title = !empty( $link_title ) ? ' title="' . $link_title . '"' : '';

		// Check for anonymous user
		if ( $author_url = bbp_get_topic_author_url( $topic_id ) )
			$author_link = sprintf( '<a href="%1$s"%2$s>%3$s</a>', $author_url, $link_title, $link_text );
		else
			$author_link = $link_text;

		return apply_filters( 'bbp_get_topic_author_link', $author_link, $topic_id );
	}

		/**
		 * bbp_topic_author_url ()
		 *
		 * Output the author url of the topic in the loop
		 *
		 * @package bbPress
		 * @subpackage Template Tags
		 * @since bbPress (r2590)
		 * @param int $topic_id optional
		 *
		 * @uses bbp_get_topic_author_url()
		 */
		function bbp_topic_author_url ( $topic_id = 0 ) {
			echo bbp_get_topic_author_url( $topic_id );
		}
			/**
			 * bbp_get_topic_author_url ()
			 *
			 * Return the author url of the topic in the loop
			 *
			 * @package bbPress
			 * @subpackage Template Tags
			 * @since bbPress (r2590)
			 *
			 * @uses bbp_get_user_profile_url()
			 * @uses get_post_meta()
			 *
			 * @param int $topic_id optional
			 * @return string Author URL of topic
			 */
			function bbp_get_topic_author_url ( $topic_id = 0 ) {
				$topic_id = bbp_get_topic_id( $topic_id );

				// Check for anonymous user
				if ( $author_id = get_post_field( 'post_author', $topic_id ) )
					$author_url = bbp_get_user_profile_url( $author_id );
				else
					if ( !$author_url = get_post_meta( $topic_id, '_bbp_anonymous_website', true ) )
						$author_url = '';

				return apply_filters( 'bbp_get_topic_author_url', $author_url, $topic_id );
			}

/**
 * bbp_topic_forum_title ()
 *
 * Output the title of the forum a topic belongs to
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2485)
 *
 * @param int $topic_id optional
 *
 * @uses bbp_get_topic_forum_title()
 */
function bbp_topic_forum_title ( $topic_id = 0 ) {
	echo bbp_get_topic_forum_title( $topic_id );
}
	/**
	 * bbp_get_topic_forum_title ()
	 *
	 * Return the title of the forum a topic belongs to
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2485)
	 *
	 * @param int $topic_id optional
	 *
	 * @return string
	 */
	function bbp_get_topic_forum_title ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );
		$forum_id = bbp_get_topic_forum_id( $topic_id );

		return apply_filters( 'bbp_get_topic_forum', bbp_get_forum_title( $forum_id ) );
	}

/**
 * bbp_topic_forum_id ()
 *
 * Output the forum ID a topic belongs to
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2491)
 *
 * @param int $topic_id optional
 *
 * @uses bbp_get_topic_forum_id()
 */
function bbp_topic_forum_id ( $topic_id = 0 ) {
	echo bbp_get_topic_forum_id( $topic_id );
}
	/**
	 * bbp_get_topic_forum_id ()
	 *
	 * Return the forum ID a topic belongs to
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2491)
	 *
	 * @param int $topic_id optional
	 *
	 * @return string
	 */
	function bbp_get_topic_forum_id ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );
		$forum_id = get_post_field( 'post_parent', $topic_id );

		return apply_filters( 'bbp_get_topic_forum_id', $forum_id, $topic_id );
	}

/**
 * bbp_topic_last_active ()
 *
 * Output the topics last update date/time (aka freshness)
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2625)
 *
 *
 * @param int $topic_id optional
 *
 * @uses bbp_get_topic_last_active()
 */
function bbp_topic_last_active ( $topic_id = 0 ) {
	echo bbp_get_topic_last_active( $topic_id );
}
	/**
	 * bbp_get_topic_last_active ()
	 *
	 * Return the topics last update date/time (aka freshness)
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2625)
	 *
	 * @param int $topic_id optional
	 *
	 * @return string
	 */
	function bbp_get_topic_last_active ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );

		// Try to get the most accurate freshness time possible
		if ( !$last_active = get_post_meta( $topic_id, '_bbp_topic_last_active', true ) ) {
			if ( $reply_id = bbp_get_topic_last_reply_id( $topic_id ) ) {
				$last_active = get_post_field( 'post_date', $reply_id );
			} else {
				$last_active = get_post_field( 'post_date', $topic_id );
			}
		}

		$last_active = !empty( $last_active ) ? bbp_get_time_since( bbp_convert_date( $last_active ) ) : '';

		// Return the time since
		return apply_filters( 'bbp_get_topic_last_active', $last_active );
	}

/** TOPIC LAST REPLY **********************************************************/

/**
 * bbp_topic_last_reply_id ()
 *
 * Output the id of the topics last reply
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2625)
 *
 * @param int $topic_id optional
 *
 * @uses bbp_get_topic_last_active()
 */
function bbp_topic_last_reply_id ( $topic_id = 0 ) {
	echo bbp_get_topic_last_reply_id( $topic_id );
}
	/**
	 * bbp_get_topic_last_reply_id ()
	 *
	 * Return the topics last update date/time (aka freshness)
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2625)
	 *
	 * @param int $topic_id optional
	 *
	 * @return string
	 */
	function bbp_get_topic_last_reply_id ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );
		$reply_id = get_post_meta( $topic_id, '_bbp_topic_last_reply_id', true );

		return apply_filters( 'bbp_get_topic_last_reply_id', $reply_id );
	}

/**
 * bbp_topic_last_reply_title ()
 *
 * Output the title of the last reply inside a topic
 *
 * @param int $topic_id
 */
function bbp_topic_last_reply_title ( $topic_id = 0 ) {
	echo bbp_get_topic_last_reply_title( $topic_id );
}
	/**
	 * bbp_get_topic_last_reply_title ()
	 *
	 * Return the title of the last reply inside a topic
	 *
	 * @param int $topic_id
	 * @return string
	 */
	function bbp_get_topic_last_reply_title( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );
		return apply_filters( 'bbp_get_topic_last_topic_title', bbp_get_reply_title( bbp_get_topic_last_reply_id( $topic_id ) ) );
	}

/**
 * bbp_topic_last_reply_permalink ()
 *
 * Output the link to the last reply in a topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2464)
 *
 * @param int $topic_id optional
 * @uses bbp_get_topic_permalink()
 */
function bbp_topic_last_reply_permalink ( $topic_id = 0 ) {
	echo bbp_get_topic_last_reply_permalink( $topic_id );
}
	/**
	 * bbp_get_topic_last_reply_permalink ()
	 *
	 * Return the link to the last reply in a topic
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2464)
	 *
	 * @param int $topic_id optional
	 * @uses apply_filters
	 * @uses get_permalink
	 * @return string Permanent link to topic
	 */
	function bbp_get_topic_last_reply_permalink ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );
		return apply_filters( 'bbp_get_topic_last_reply_permalink', bbp_get_reply_permalink( bbp_get_topic_last_reply_id( $topic_id ) ) );
	}

/**
 * bbp_topic_last_reply_url ()
 *
 * Output the link to the last reply in a topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2683)
 *
 * @param int $topic_id optional
 * @uses bbp_get_topic_url()
 */
function bbp_topic_last_reply_url ( $topic_id = 0 ) {
	echo bbp_get_topic_last_reply_url( $topic_id );
}
	/**
	 * bbp_get_topic_last_reply_url ()
	 *
	 * Return the link to the last reply in a topic
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2683)
	 *
	 * @param int $topic_id optional
	 * @uses apply_filters
	 * @uses get_url
	 * @return string Permanent link to topic
	 */
	function bbp_get_topic_last_reply_url ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );
		$reply_id = bbp_get_topic_last_reply_id( $topic_id );

		if ( !empty( $reply_id ) )
			$reply_url = bbp_get_reply_url( $reply_id );
		else
			$reply_url = bbp_get_topic_permalink( $topic_id );

		return apply_filters( 'bbp_get_topic_last_reply_url', $reply_url );
	}

/**
 * bbp_topic_freshness_link ()
 *
 * Output link to the most recent activity inside a topic, complete with
 * link attributes and content.
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2625)
 *
 * @param int $topic_id
 */
function bbp_topic_freshness_link ( $topic_id = 0) {
	echo bbp_get_topic_freshness_link( $topic_id );
}
	/**
	 * bbp_get_topic_freshness_link ()
	 *
	 * Returns link to the most recent activity inside a topic, complete with
	 * link attributes and content.
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2625)
	 *
	 * @param int $topic_id
	 */
	function bbp_get_topic_freshness_link ( $topic_id = 0 ) {
		$topic_id   = bbp_get_topic_id( $topic_id );
		$link_url   = bbp_get_topic_last_reply_url( $topic_id );
		$title      = bbp_get_topic_last_reply_title( $topic_id );
		$time_since = bbp_get_topic_last_active( $topic_id );

		if ( !empty( $time_since ) )
			$anchor = '<a href="' . $link_url . '" title="' . esc_attr( $title ) . '">' . $time_since . '</a>';
		else
			$anchor = __( 'No Replies', 'bbpress' );

		return apply_filters( 'bbp_get_topic_freshness_link', $anchor );
	}

/**
 * bbp_topic_replies_link ()
 *
 * Output the replies link of the topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2740)
 *
 * @uses bbp_get_topic_replies_link()
 * @param int $topic_id
 */
function bbp_topic_replies_link ( $topic_id = 0 ) {
	echo bbp_get_topic_replies_link( $topic_id );
}

	/**
	 * bbp_get_topic_replies_link ()
	 *
	 * Return the replies link of the topic
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2740)
	 *
	 * @param int $topic_id
	 */
	function bbp_get_topic_replies_link ( $topic_id = 0 ) {
		global $bbp;

		$topic    = get_post( bbp_get_topic_id( (int) $topic_id ) );
		$topic_id = $topic->ID;
		$replies  = bbp_get_topic_reply_count( $topic_id );
		$replies  = sprintf( _n( '%s reply', '%s replies', $replies, 'bbpress' ), $replies );
		$retval   = '';

		if ( !empty( $_GET['view'] ) && 'all' == $_GET['view'] && current_user_can( 'edit_others_replies' ) )
			$retval .= "<a href='" . esc_url( remove_query_arg( array( 'view' => 'all' ),  bbp_get_topic_permalink( $topic_id ) ) ) . "'>$replies</a>";
		else
			$retval .= $replies;

		if ( current_user_can( 'edit_others_replies' ) && $deleted = bbp_get_topic_hidden_reply_count( $topic_id ) ) {
			$extra = sprintf( __( ' + %d more', 'bbpress' ), $deleted );
			if ( !empty( $_GET['view'] ) && 'all' == $_GET['view'] )
				$retval .= " $extra";
			else
				$retval .= " <a href='" . esc_url( add_query_arg( array( 'view' => 'all' ) ) ) . "'>$extra</a>";
		}

		return apply_filters( 'bbp_get_topic_replies_link', $retval );
	}

/**
 * bbp_topic_reply_count ()
 *
 * Output total reply count of a topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2485)
 *
 * @uses bbp_get_topic_reply_count()
 * @param int $topic_id
 */
function bbp_topic_reply_count ( $topic_id = 0 ) {
	echo bbp_get_topic_reply_count( $topic_id );
}
	/**
	 * bbp_get_topic_reply_count ()
	 *
	 * Return total reply count of a topic
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2485)
	 *
	 * @uses bbp_get_topic_id()
	 * @uses get_post_meta()
	 * @uses apply_filters()
	 *
	 * @param int $topic_id
	 */
	function bbp_get_topic_reply_count ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );
		$replies  = get_post_meta( $topic_id, '_bbp_topic_reply_count', true );

		if ( '' === $replies )
			$replies = bbp_update_topic_reply_count( $topic_id );

		return apply_filters( 'bbp_get_topic_reply_count', (int) $replies, $topic_id );
	}

/**
 * bbp_topic_hidden_reply_count ()
 *
 * Output total hidden reply count of a topic (hidden includes trashed and spammed replies)
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2740)
 *
 * @uses bbp_get_topic_hidden_reply_count()
 * @param int $topic_id
 */
function bbp_topic_hidden_reply_count ( $topic_id = 0 ) {
	echo bbp_get_topic_hidden_reply_count( $topic_id );
}
	/**
	 * bbp_get_topic_hidden_reply_count ()
	 *
	 * Return total hidden reply count of a topic (hidden includes trashed and spammed replies)
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2740)
	 *
	 * @uses bbp_get_topic_id()
	 * @uses get_post_meta()
	 * @uses apply_filters()
	 *
	 * @param int $topic_id
	 */
	function bbp_get_topic_hidden_reply_count ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );
		$replies  = get_post_meta( $topic_id, '_bbp_topic_hidden_reply_count', true );

		if ( '' === $replies )
			$replies = bbp_update_topic_hidden_reply_count( $topic_id );

		return apply_filters( 'bbp_get_topic_hidden_reply_count', (int) $replies, $topic_id );
	}

/**
 * bbp_topic_voice_count ()
 *
 * Output total voice count of a topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2567)
 *
 * @uses bbp_get_topic_voice_count()
 * @uses apply_filters
 *
 * @param int $topic_id
 */
function bbp_topic_voice_count ( $topic_id = 0 ) {
	echo bbp_get_topic_voice_count( $topic_id );
}
	/**
	 * bbp_get_topic_voice_count ()
	 *
	 * Return total voice count of a topic
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2567)
	 *
	 * @uses bbp_get_topic_id()
	 * @uses apply_filters
	 *
	 * @param int $topic_id
	 *
	 * @return int Voice count of the topic
	 */
	function bbp_get_topic_voice_count ( $topic_id = 0 ) {
		$topic_id = bbp_get_topic_id( $topic_id );

		// Look for existing count, and populate if does not exist
		if ( !$voices   = get_post_meta( $topic_id, '_bbp_topic_voice_count', true ) )
			$voices = bbp_update_topic_voice_count( $topic_id );

		return apply_filters( 'bbp_get_topic_voice_count', (int) $voices, $topic_id );
	}

/**
 * bbp_topic_tag_list ( $topic_id = 0, $args = '' )
 *
 * Output a the tags of a topic
 *
 * @param int $topic_id
 * @param array $args
 */
function bbp_topic_tag_list ( $topic_id = 0, $args = '' ) {
	echo bbp_get_topic_tag_list( $topic_id, $args );
}
	/**
	 * bbp_get_topic_tag_list ( $topic_id = 0, $args = '' )
	 *
	 * Return the tags of a topic
	 *
	 * @param int $topic_id
	 * @param array $args
	 * @return string
	 */
	function bbp_get_topic_tag_list ( $topic_id = 0, $args = '' ) {
		global $bbp;

		$defaults = array(
			'before' => '<p>' . __( 'Tagged:', 'bbpress' ) . '&nbsp;',
			'sep'    => ', ',
			'after'  => '</p>'
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		$topic_id = bbp_get_topic_id( $topic_id );

		return get_the_term_list( $topic_id, $bbp->topic_tag_id, $before, $sep, $after );
	}

/**
 * bbp_topic_class ()
 *
 * Output the row class of a topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2667)
 */
function bbp_topic_class ( $topic_id = 0 ) {
	echo bbp_get_topic_class( $topic_id );
}
	/**
	 * bbp_get_topic_class ()
	 *
	 * Return the row class of a topic
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2667)
	 *
	 * @global WP_Query $bbp->topic_query
	 * @param int $topic_id
	 * @return string
	 */
	function bbp_get_topic_class ( $topic_id = 0 ) {
		global $bbp;

		$alternate = $bbp->topic_query->current_post % 2 ? 'even' : 'odd';
		$status    = 'status-'  . bbp_get_topic_status();
		$post      = post_class( array( $alternate, $status ) );

		return apply_filters( 'bbp_get_topic_class', $post );
	}

/** Topic Admin Links *********************************************************/

/**
 * bbp_topic_admin_links ()
 *
 * Output admin links for topic
 *
 * @package bbPress
 * @subpackage Template Tags
 *
 * @param mixed $args
 */
function bbp_topic_admin_links ( $args = '' ) {
	echo bbp_get_topic_admin_links( $args );
}
	/**
	 * bbp_get_topic_admin_links ()
	 *
	 * Return admin links for topic
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 *
	 * @uses bbp_get_topic_edit_link ()
	 * @uses bbp_get_topic_trash_link ()
	 * @uses bbp_get_topic_close_link ()
	 * @uses bbp_get_topic_spam_link ()
	 * @uses bbp_get_topic_sticky_link ()
	 * @uses bbp_get_topic_move_dropdown ()
	 *
	 * @param mixed $args
	 * @return string
	 */
	function bbp_get_topic_admin_links ( $args = '' ) {
		global $bbp;

		if ( !bbp_is_topic() )
			return '&nbsp';

		$defaults = array (
			'id'     => bbp_get_topic_id(),
			'before' => '<span class="bbp-admin-links">',
			'after'  => '</span>',
			'sep'    => ' | ',
			'links'  => array (
				'edit'   => bbp_get_topic_edit_link    ( $args ),
				'trash'  => bbp_get_topic_trash_link   ( $args ),
				'close'  => bbp_get_topic_close_link   ( $args ),
				'spam'   => bbp_get_topic_spam_link    ( $args ),
				'sticky' => bbp_get_topic_sticky_link  ( $args ),
				'move'   => bbp_get_topic_move_dropdown( $args )
			)
		);

		$r = wp_parse_args( $args, $defaults );

		if ( !current_user_can( 'edit_topic', $r['id'] ) )
			return '&nbsp';

		// Check caps for trashing the topic
		if ( !current_user_can( 'delete_topic', $r['id'] ) )
			unset( $r['links']['trash'] );

		// Close link shouldn't be there on trashed/spammed topics, as closing/opening would change their status
		if ( in_array( bbp_get_topic_status( $r['id'] ), array( $bbp->spam_status_id, $bbp->trash_status_id ) ) )
			unset( $r['links']['close'] );

		// Process the admin links
		$links = implode( $r['sep'], $r['links'] );

		return apply_filters( 'bbp_get_topic_admin_links', $r['before'] . $links . $r['after'], $args );
	}

/**
 * bbp_topic_edit_link ()
 *
 * Output the edit link of the topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2727)
 *
 * @uses bbp_get_topic_edit_link ()
 *
 * @param mixed $args
 * @return string
 */
function bbp_topic_edit_link ( $args = '' ) {
	echo bbp_get_topic_edit_link( $args );
}

	/**
	 * bbp_get_topic_edit_link ()
	 *
	 * Return the edit link of the topic
	 *
	 * @todo Add topic edit page and correct this function.
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2727)
	 *
	 * @param mixed $args
	 * @return string
	 */
	function bbp_get_topic_edit_link ( $args = '' ) {
		return apply_filters( 'bbp_get_topic_edit_link', __( 'Edit', 'bbpress' ), $args );
	}

/**
 * bbp_topic_trash_link ()
 *
 * Output the trash link of the topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2727)
 *
 * @uses bbp_get_topic_trash_link ()
 *
 * @param mixed $args
 * @return string
 */
function bbp_topic_trash_link ( $args = '' ) {
	echo bbp_get_topic_trash_link( $args );
}

	/**
	 * bbp_get_topic_trash_link ()
	 *
	 * Return the trash link of the topic
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2727)
	 *
	 * @param mixed $args
	 * @return bool|string
	 */
	function bbp_get_topic_trash_link ( $args = '' ) {
		global $bbp;

		$defaults = array (
			'id'           => 0,
			'link_before'  => '',
			'link_after'   => '',
			'sep'          => ' | ',
			'trash_text'   => __( 'Trash',                'bbpress' ),
			'restore_text' => __( 'Restore',              'bbpress' ),
			'delete_text'  => __( 'Delete Permanentatly', 'bbpress' )
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		$actions = array();
		$topic   = get_post( bbp_get_topic_id( (int) $id ) );

		if ( empty( $topic ) || !current_user_can( 'delete_topic', $topic->ID ) )
			return;

		$topic_status = bbp_get_topic_status( $topic->ID );

		if ( $bbp->trash_status_id == $topic_status )
			$actions['untrash'] = '<a title="' . esc_attr( __( 'Restore this item from the Trash', 'bbpress' ) ) . '" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'bbp_toggle_topic_trash', 'sub_action' => 'untrash', 'topic_id' => $topic->ID ) ), 'untrash-' . $topic->post_type . '_' . $topic->ID ) ) . '" onclick="return confirm(\'' . esc_js( __( "Are you sure you want to restore that?", "bbpress" ) ) . '\');">' . esc_html( $restore_text ) . '</a>';
		elseif ( EMPTY_TRASH_DAYS )
			$actions['trash']   = '<a title="' . esc_attr( __( 'Move this item to the Trash' ) ) . '" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'bbp_toggle_topic_trash', 'sub_action' => 'trash', 'topic_id' => $topic->ID ) ), 'trash-' . $topic->post_type . '_' . $topic->ID ) ) . '" onclick="return confirm(\'' . esc_js( __( "Are you sure you want to trash that?", "bbpress" ) ) . '\' );">' . esc_html( $trash_text ) . '</a>';

		if ( $bbp->trash_status_id == $topic->post_status || !EMPTY_TRASH_DAYS )
			$actions['delete']  = '<a title="' . esc_attr( __( 'Delete this item permanently' ) ) . '" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'bbp_toggle_topic_trash', 'sub_action' => 'delete', 'topic_id' => $topic->ID ) ), 'delete-' . $topic->post_type . '_' . $topic->ID ) ) . '" onclick="return confirm(\'' . esc_js( __( "Are you sure you want to delete that permanentaly?", "bbpress" ) ) . '\' );">' . esc_html( $delete_text ) . '</a>';

		// Process the admin links
		$actions = implode( $sep, $actions );

		return apply_filters( 'bbp_get_topic_trash_link', $link_before . $actions . $link_after, $args );
	}

/**
 * bbp_topic_close_link ()
 *
 * Output the close link of the topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2727)
 *
 * @uses bbp_get_topic_close_link ()
 *
 * @param mixed $args
 * @return string
 */
function bbp_topic_close_link ( $args = '' ) {
	echo bbp_get_topic_close_link( $args );
}

	/**
	 * bbp_get_topic_close_link ()
	 *
	 * Return the close link of the topic
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2727)
	 *
	 * @param mixed $args
	 * @return string
	 */
	function bbp_get_topic_close_link ( $args = '' ) {
		$defaults = array (
			'id'          => 0,
			'link_before' => '',
			'link_after'  => '',
			'sep'         => ' | ',
			'close_text'  => __( 'Close', 'bbpress' ),
			'open_text'   => __( 'Open',  'bbpress' )
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		$topic = get_post( bbp_get_topic_id( (int) $id ) );

		if ( empty( $topic ) || !current_user_can( 'edit_topic', $topic->ID ) )
			return;

		$display  = bbp_is_topic_open( $topic->ID ) ? $close_text : $open_text;

		$uri = add_query_arg( array( 'action' => 'bbp_toggle_topic_close', 'topic_id' => $topic->ID ) );
		$uri = esc_url( wp_nonce_url( $uri, 'close-topic_' . $topic->ID ) );

		return apply_filters( 'bbp_get_topic_close_link', $link_before . '<a href="' . $uri . '">' . $display . '</a>' . $link_after, $args );
	}

/**
 * bbp_topic_spam_link ()
 *
 * Output the spam link of the topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2727)
 *
 * @uses bbp_get_topic_spam_link ()
 *
 * @param mixed $args
 * @return string
 */
function bbp_topic_spam_link ( $args = '' ) {
	echo bbp_get_topic_spam_link( $args );
}

	/**
	 * bbp_get_topic_spam_link ()
	 *
	 * Return the spam link of the topic
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2727)
	 *
	 * @param mixed $args
	 * @return string
	 */
	function bbp_get_topic_spam_link ( $args = '' ) {
		$defaults = array (
			'id'           => 0,
			'link_before'  => '',
			'link_after'   => '',
			'sep'          => ' | ',
			'spam_text'    => __( 'Spam',   'bbpress' ),
			'unspam_text'  => __( 'Unspam', 'bbpress' )
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		$topic = get_post( bbp_get_topic_id( (int) $id ) );

		if ( empty( $topic ) || !current_user_can( 'edit_topic', $topic->ID ) )
			return;

		$display  = bbp_is_topic_spam( $topic->ID ) ? $unspam_text : $spam_text;

		$uri = add_query_arg( array( 'action' => 'bbp_toggle_topic_spam', 'topic_id' => $topic->ID ) );
		$uri = esc_url( wp_nonce_url( $uri, 'spam-topic_' . $topic->ID ) );

		return apply_filters( 'bbp_get_topic_spam_link', $link_before . '<a href="' . $uri . '">' . $display . '</a>' . $link_after, $args );
	}

/**
 * bbp_topic_sticky_link ()
 *
 * Output the sticky link of the topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2727)
 *
 * @uses bbp_get_topic_sticky_link ()
 *
 * @param mixed $args
 * @return string
 */
function bbp_topic_sticky_link ( $args = '' ) {
	echo bbp_get_topic_sticky_link( $args );
}

	/**
	 * bbp_get_topic_sticky_link ()
	 *
	 * Return the sticky link of the topic
	 *
	 * @todo Add topic sticky functionality.
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2727)
	 *
	 * @param mixed $args
	 * @return string
	 */
	function bbp_get_topic_sticky_link ( $args = '' ) {
		return apply_filters( 'bbp_get_topic_sticky_link', __( 'Sticky', 'bbpress' ), $args );
	}

/**
 * bbp_topic_move_dropdown ()
 *
 * Output the move dropdown HTML of the topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2727)
 *
 * @uses bbp_get_topic_move_dropdown ()
 *
 * @param mixed $args
 * @return string
 */
function bbp_topic_move_dropdown ( $args = '' ) {
	echo bbp_get_topic_move_dropdown( $args );
}

	/**
	 * bbp_get_topic_move_dropdown ()
	 *
	 * Return the move dropdown HTML of the topic
	 *
	 * @todo Add the move dropdown functionality.
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2727)
	 *
	 * @param mixed $args
	 * @return string
	 */
	function bbp_get_topic_move_dropdown ( $args = '' ) {
		return apply_filters( 'bbp_get_topic_move_dropdown', __( 'Move', 'bbpress' ), $args );
	}

/** Topic Updaters ************************************************************/

/**
 * bbp_update_topic_reply_count ()
 *
 * Adjust the total reply count of a topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2467)
 *
 * @uses bbp_get_topic_id()
 * @uses apply_filters
 *
 * @param int $topic_id optional Forum ID to update
 *
 * @return int
 */
function bbp_update_topic_reply_count ( $topic_id = 0 ) {
	global $wpdb, $bbp;

	$topic_id = bbp_get_topic_id( $topic_id );

	// If it's a reply, then get the parent (topic id)
	if ( $bbp->reply_id == get_post_field( 'post_type', $topic_id ) )
		$topic_id = bbp_get_reply_topic_id( $topic_id );

	// Get replies of topic
	$replies = count( $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_status = 'publish' AND post_type = '" . $bbp->reply_id . "';", $topic_id ) ) );

	// Update the count
	update_post_meta( $topic_id, '_bbp_topic_reply_count', (int)$replies );

	return apply_filters( 'bbp_update_topic_reply_count', (int) $replies, $topic_id );
}

/**
 * bbp_update_topic_hidden_reply_count ()
 *
 * Adjust the total hidden reply count of a topic (hidden includes trashed and spammed replies)
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2740)
 *
 * @uses bbp_get_topic_id()
 * @uses apply_filters
 *
 * @param int $topic_id optional Forum ID to update
 *
 * @return int
 */
function bbp_update_topic_hidden_reply_count ( $topic_id = 0 ) {
	global $wpdb, $bbp;

	$topic_id = bbp_get_topic_id( $topic_id );

	// If it's a reply, then get the parent (topic id)
	if ( $bbp->reply_id == get_post_field( 'post_type', $topic_id ) )
		$topic_id = bbp_get_reply_topic_id( $topic_id );

	// Get replies of topic
	$replies = count( $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_status IN ( '" . join( '\',\'', array( $bbp->trash_status_id, $bbp->spam_status_id ) ) . "') AND post_type = '" . $bbp->reply_id . "';", $topic_id ) ) );

	// Update the count
	update_post_meta( $topic_id, '_bbp_topic_hidden_reply_count', (int) $replies );

	return apply_filters( 'bbp_update_topic_hidden_reply_count', (int) $replies, $topic_id );
}

/**
 * bbp_update_topic_last_active ()
 *
 * Update the topics last active date/time (aka freshness)
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2680)
 *
 * @param int $topic_id optional
 *
 * @return string
 */
function bbp_update_topic_last_active ( $topic_id = 0, $new_time = '' ) {
	$topic_id = bbp_get_topic_id( $topic_id );

	// Check time and use current if empty
	if ( empty( $new_time ) )
		$new_time = current_time( 'mysql' );

	// Update the last reply ID
	if ( !empty( $topic_id ) ) {
		update_post_meta( $topic_id, '_bbp_topic_last_active', $new_time );
		return true;
	}

	return false;
}

/**
 * bbp_update_topic_last_reply_id ()
 *
 * Update the topic with the most recent reply ID
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2625)
 *
 * @todo everything
 * @param int $topic_id
 */
function bbp_update_topic_last_reply_id ( $topic_id = 0, $reply_id = 0 ) {
	$topic_id = bbp_get_topic_id( $topic_id );
	$reply_id = bbp_get_reply_id( $reply_id );

	// Update the last reply ID
	if ( !empty( $topic_id ) ) {
		update_post_meta( $topic_id, '_bbp_topic_last_reply_id', $reply_id );
		return true;
	}

	return false;
}

/**
 * bbp_update_topic_voice_count ()
 *
 * Adjust the total voice count of a topic
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2567)
 *
 * @uses bbp_get_topic_id()
 * @uses apply_filters
 *
 * @todo cache
 *
 * @param int $topic_id optional Topic ID to update
 * @return bool false on failure, voice count on success
 */
function bbp_update_topic_voice_count ( $topic_id = 0 ) {
	global $wpdb, $bbp;

	$topic_id = bbp_get_topic_id( $topic_id );

	// If it is not a topic or reply, then we don't need it
	if ( !in_array( get_post_field( 'post_type', $topic_id ), array( $bbp->topic_id, $bbp->reply_id ) ) )
		return false;

	// If it's a reply, then get the parent (topic id)
	if ( $bbp->reply_id == get_post_field( 'post_type', $topic_id ) )
		$topic_id = bbp_get_reply_topic_id( $topic_id );

	// There should always be at least 1 voice
	if ( !$voices = count( $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_author FROM $wpdb->posts WHERE ( post_parent = %d AND post_status = 'publish' AND post_type = '" . $bbp->reply_id . "' ) OR ( ID = %d AND post_type = '" . $bbp->topic_id . "' );", $topic_id, $topic_id ) ) ) )
		$voices = 1;

	// Update the count
	update_post_meta( $topic_id, '_bbp_topic_voice_count', (int) $voices );

	return apply_filters( 'bbp_update_topic_voice_count', (int) $voices, $topic_id );
}

/** Topic Pagination **********************************************************/

/**
 * bbp_forum_pagination_count ()
 *
 * Output the pagination count
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2519)
 *
 * @global WP_Query $bbp->topic_query
 */
function bbp_forum_pagination_count () {
	echo bbp_get_forum_pagination_count();
}
	/**
	 * bbp_get_forum_pagination_count ()
	 *
	 * Return the pagination count
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2519)
	 *
	 * @global WP_Query $bbp->topic_query
	 * @return string
	 */
	function bbp_get_forum_pagination_count () {
		global $bbp;

		if ( !isset( $bbp->topic_query ) )
			return false;

		// Set pagination values
		$start_num = intval( ( $bbp->topic_query->paged - 1 ) * $bbp->topic_query->posts_per_page ) + 1;
		$from_num  = bbp_number_format( $start_num );
		$to_num    = bbp_number_format( ( $start_num + ( $bbp->topic_query->posts_per_page - 1 ) > $bbp->topic_query->found_posts ) ? $bbp->topic_query->found_posts : $start_num + ( $bbp->topic_query->posts_per_page - 1 ) );
		$total     = bbp_number_format( !empty( $bbp->topic_query->found_posts ) ? $bbp->topic_query->found_posts : $bbp->topic_query->post_count );

		// Set return string
		if ( $total > 1 && (int)$from_num == (int)$to_num )
			$retstr = sprintf( __( 'Viewing topic %1$s (of %2$s total)', 'bbpress' ), $from_num, $total );
		elseif ( $total > 1 && empty( $to_num ) )
			$retstr = sprintf( __( 'Viewing %1$s topics', 'bbpress' ), $total );
		elseif ( $total > 1 && (int)$from_num != (int)$to_num )
			$retstr = sprintf( __( 'Viewing %1$s topics - %2$s through %3$s (of %4$s total)', 'bbpress' ), $bbp->topic_query->post_count, $from_num, $to_num, $total );
		else
			$retstr = sprintf( __( 'Viewing %1$s topic', 'bbpress' ), $total );

		// Filter and return
		return apply_filters( 'bbp_get_topic_pagination_count', $retstr );
	}

/**
 * bbp_forum_pagination_links ()
 *
 * Output pagination links
 *
 * @package bbPress
 * @subpackage Template Tags
 * @since bbPress (r2519)
 */
function bbp_forum_pagination_links () {
	echo bbp_get_forum_pagination_links();
}
	/**
	 * bbp_get_forum_pagination_links ()
	 *
	 * Return pagination links
	 *
	 * @package bbPress
	 * @subpackage Template Tags
	 * @since bbPress (r2519)
	 *
	 * @global WP_Query $bbp->topic_query
	 * @return string
	 */
	function bbp_get_forum_pagination_links () {
		global $bbp;

		if ( !isset( $bbp->topic_query ) )
			return false;

		return apply_filters( 'bbp_get_forum_pagination_links', $bbp->topic_query->pagination_links );
	}

/** END - Topic Loop Functions ************************************************/

/** Topic Actions *************************************************************/

/**
 * bbp_close_topic ()
 *
 * Closes a topic
 *
 * @since bbPress (r2740)
 *
 * @param int $topic_id Topic ID.
 * @return mixed False on failure
 */
function bbp_close_topic ( $topic_id = 0 ) {
	global $bbp;

	if ( !$topic = wp_get_single_post( $topic_id, ARRAY_A ) )
		return $topic;

	if ( $topic['post_status'] == $bbp->closed_status_id )
		return false;

	do_action( 'bbp_close_topic', $topic_id );

	add_post_meta( $topic_id, '_bbp_close_meta_status', $topic['post_status'] );

	$topic['post_status'] = $bbp->closed_status_id;
	wp_insert_post( $topic );

	do_action( 'bbp_closed_topic', $topic_id );

	return $topic;
}

/**
 * bbp_open_topic ()
 *
 * Opens a topic
 *
 * @since bbPress (r2740)
 *
 * @param int $topic_id Topic ID.
 * @return mixed False on failure
 */
function bbp_open_topic ( $topic_id = 0 ) {
	global $bbp;

	if ( !$topic = wp_get_single_post( $topic_id, ARRAY_A ) )
		return $topic;

	if ( $topic['post_status'] != $bbp->closed_status_id )
		return false;

	do_action( 'bbp_open_topic', $topic_id );

	$topic_status         = get_post_meta( $topic_id, '_bbp_close_meta_status', true );
	$topic['post_status'] = $topic_status;

	delete_post_meta( $topic_id, '_bbp_close_meta_status' );

	wp_insert_post( $topic );

	do_action( 'bbp_opend_topic', $topic_id );

	return $topic;
}

/**
 * bbp_spam_topic ()
 *
 * Marks a topic as spam
 *
 * @since bbPress (r2740)
 *
 * @param int $topic_id Topic ID.
 * @return mixed False on failure
 */
function bbp_spam_topic ( $topic_id = 0 ) {
	global $bbp;

	if ( !$topic = wp_get_single_post( $topic_id, ARRAY_A ) )
		return $topic;

	if ( $topic['post_status'] == $bbp->spam_status_id )
		return false;

	do_action( 'bbp_spam_topic', $topic_id );

	add_post_meta( $topic_id, '_bbp_spam_meta_status', $topic['post_status'] );

	$topic['post_status'] = $bbp->spam_status_id;
	wp_insert_post( $topic );

	do_action( 'bbp_spammed_topic', $topic_id );

	return $topic;
}

/**
 * bbp_unspam_topic ()
 *
 * unspams a topic
 *
 * @since bbPress (r2740)
 *
 * @param int $topic_id Topic ID.
 * @return mixed False on failure
 */
function bbp_unspam_topic ( $topic_id = 0 ) {
	global $bbp;

	if ( !$topic = wp_get_single_post( $topic_id, ARRAY_A ) )
		return $topic;

	if ( $topic['post_status'] != $bbp->spam_status_id )
		return false;

	do_action( 'bbp_unspam_topic', $topic_id );

	$topic_status         = get_post_meta( $topic_id, '_bbp_spam_meta_status', true );
	$topic['post_status'] = $topic_status;

	delete_post_meta( $topic_id, '_bbp_spam_meta_status' );

	wp_insert_post( $topic );

	do_action( 'bbp_unspammed_topic', $topic_id );

	return $topic;
}

/**
 * bbp_edit_user_success ()
 */
function bbp_topic_notices () {
	global $bbp;

	if ( bbp_is_topic() ) {

		$topic_status = bbp_get_topic_status();

		if ( in_array( $topic_status, array( $bbp->spam_status_id, $bbp->trash_status_id ) ) ) {

			$notice_text = ( $bbp->spam_status_id == $topic_status ) ? __( 'This topic is marked as spam.', 'bbpress' ) : __( 'This topic is currently trashed.', 'bbpress' ); ?>

	<div class="bbp-template-notice error">
		<p><?php echo $notice_text; ?></p>
	</div>

<?php

		}
	}
}
add_action( 'bbp_template_notices', 'bbp_topic_notices' );

?>
