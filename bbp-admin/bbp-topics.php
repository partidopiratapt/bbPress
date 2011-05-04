<?php

/**
 * bbPress Topics Admin Class
 *
 * @package bbPress
 * @subpackage Administration
 */

// Redirect if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'BBP_Topics_Admin' ) ) :
/**
 * Loads bbPress topics admin area
 *
 * @package bbPress
 * @subpackage Administration
 * @since bbPress (r2464)
 */
class BBP_Topics_Admin {

	/** Variables *************************************************************/

	/**
	 * @var The post type of this admin component
	 */
	var $post_type = '';

	/** Functions *************************************************************/

	/**
	 * The main bbPress topics admin loader (PHP4 compat)
	 *
	 * @since bbPress (r2515)
	 *
	 * @uses BBP_Topics_Admin::_setup_globals() Setup the globals needed
	 * @uses BBP_Topics_Admin::_setup_actions() Setup the hooks and actions
	 */
	function BBP_Topics_Admin() {
		$this->__construct();
	}

	/**
	 * The main bbPress topics admin loader
	 *
	 * @since bbPress (r2515)
	 *
	 * @uses BBP_Topics_Admin::_setup_globals() Setup the globals needed
	 * @uses BBP_Topics_Admin::_setup_actions() Setup the hooks and actions
	 */
	function __construct() {
		$this->_setup_globals();
		$this->_setup_actions();
	}

	/**
	 * Setup the admin hooks, actions and filters
	 *
	 * @since bbPress (r2646)
	 * @access private
	 *
	 * @uses add_action() To add various actions
	 * @uses add_filter() To add various filters
	 * @uses bbp_get_forum_post_type() To get the forum post type
	 * @uses bbp_get_topic_post_type() To get the topic post type
	 * @uses bbp_get_reply_post_type() To get the reply post type
	 */
	function _setup_actions() {

		// Add some general styling to the admin area
		add_action( 'admin_head', array( $this, 'admin_head' ) );

		// Topic column headers.
		add_filter( 'manage_' . $this->post_type . '_posts_columns',        array( $this, 'topics_column_headers' ) );

		// Topic columns (in post row)
		add_action( 'manage_' . $this->post_type . '_posts_custom_column',  array( $this, 'topics_column_data' ), 10, 2 );
		add_filter( 'post_row_actions',                                     array( $this, 'topics_row_actions' ), 10, 2 );

		// Topic metabox actions
		add_action( 'add_meta_boxes', array( $this, 'topic_attributes_metabox'      ) );
		add_action( 'save_post',      array( $this, 'topic_attributes_metabox_save' ) );

		// Check if there are any bbp_toggle_topic_* requests on admin_init, also have a message displayed
		add_action( 'bbp_admin_init', array( $this, 'toggle_topic'        ) );
		add_action( 'admin_notices',  array( $this, 'toggle_topic_notice' ) );

		// Anonymous metabox actions
		add_action( 'add_meta_boxes', array( $this, 'anonymous_metabox'      ) );
		add_action( 'save_post',      array( $this, 'anonymous_metabox_save' ) );

		// Add ability to filter topics and replies per forum
		add_filter( 'restrict_manage_posts', array( $this, 'filter_dropdown'  ) );
		add_filter( 'request',               array( $this, 'filter_post_rows' ) );
	}

	/**
	 * Admin globals
	 *
	 * @since bbPress (r2646)
	 * @access private
	 */
	function _setup_globals() {

		// Setup the post type for this admin component
		$this->post_type = bbp_get_topic_post_type();
	}

	/**
	 * Add the topic attributes metabox
	 *
	 * @since bbPress (r2744)
	 *
	 * @uses bbp_get_topic_post_type() To get the topic post type
	 * @uses add_meta_box() To add the metabox
	 * @uses do_action() Calls 'bbp_topic_attributes_metabox'
	 */
	function topic_attributes_metabox() {
		add_meta_box (
			'bbp_topic_attributes',
			__( 'Topic Attributes', 'bbpress' ),
			'bbp_topic_metabox',
			$this->post_type,
			'side',
			'high'
		);

		do_action( 'bbp_topic_attributes_metabox' );
	}

	/**
	 * Pass the topic attributes for processing
	 *
	 * @since bbPress (r2746)
	 *
	 * @param int $topic_id Topic id
	 * @uses current_user_can() To check if the current user is capable of
	 *                           editing the topic
	 * @uses do_action() Calls 'bbp_topic_attributes_metabox_save' with the
	 *                    topic id and parent id
	 * @return int Parent id
	 */
	function topic_attributes_metabox_save( $topic_id ) {

		// Bail if doing an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $topic_id;

		// Bail if current user cannot edit this topic
		if ( !current_user_can( 'edit_topic', $topic_id ) )
			return $topic_id;

		// Load the topic
		if ( !$topic = bbp_get_topic( $topic_id ) )
			return $topic_id;

		// OK, we're authenticated: we need to find and save the data
		$parent_id = isset( $topic->parent_id ) ? $topic->parent_id : 0;

		do_action( 'bbp_topic_attributes_metabox_save', $topic_id, $parent_id );

		return $parent_id;
	}

	/**
	 * Add the anonymous user info metabox
	 *
	 * Allows editing of information about an anonymous user
	 *
	 * @since bbPress (r2828)
	 *
	 * @uses bbp_get_topic() To get the topic
	 * @uses bbp_get_reply() To get the reply
	 * @uses bbp_is_topic_anonymous() To check if the topic is by an
	 *                                 anonymous user
	 * @uses bbp_is_reply_anonymous() To check if the reply is by an
	 *                                 anonymous user
	 * @uses bbp_get_topic_post_type() To get the topic post type
	 * @uses bbp_get_reply_post_type() To get the reply post type
	 * @uses add_meta_box() To add the metabox
	 * @uses do_action() Calls 'bbp_anonymous_metabox' with the topic/reply
	 *                    id
	 */
	function anonymous_metabox() {

		// Bail if post_type is not a topic or reply
		if ( get_post_type() != $this->post_type )
			return;

		// Bail if topic author is not anonymous
		if ( !bbp_is_topic_anonymous( get_the_ID() ) )
			return;

		// Add the metabox
		add_meta_box(
			'bbp_anonymous_metabox',
			__( 'Anonymous User Information', 'bbpress' ),
			'bbp_anonymous_metabox',
			$this->post_type,
			'side',
			'high'
		);

		do_action( 'bbp_anonymous_metabox', get_the_ID() );
	}

	/**
	 * Save the anonymous user information for the topic/reply
	 *
	 * @since bbPress (r2828)
	 *
	 * @param int $post_id Topic or reply id
	 * @uses bbp_get_topic() To get the topic
	 * @uses bbp_get_reply() To get the reply
	 * @uses current_user_can() To check if the current user can edit the
	 *                           topic or reply
	 * @uses bbp_is_topic_anonymous() To check if the topic is by an
	 *                                 anonymous user
	 * @uses bbp_is_reply_anonymous() To check if the reply is by an
	 *                                 anonymous user
	 * @uses bbp_filter_anonymous_post_data() To filter the anonymous user data
	 * @uses update_post_meta() To update the anonymous user data
	 * @uses do_action() Calls 'bbp_anonymous_metabox_save' with the topic/
	 *                    reply id and anonymous data
	 * @return int Topic or reply id
	 */
	function anonymous_metabox_save( $post_id ) {

		// Bail if no post_id
		if ( empty( $post_id ) )
			return $post_id;

		// Bail if doing an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// Bail if post_type is not a topic or reply
		if ( get_post_type( $post_id ) != $this->post_type )
			return;

		// Bail if user cannot edit replies or reply is not anonymous
		if ( ( !current_user_can( 'edit_topic', $post_id ) || !bbp_is_topic_anonymous( $post_id ) ) )
			return $post_id;

		$anonymous_data = bbp_filter_anonymous_post_data();

		update_post_meta( $post_id, '_bbp_anonymous_name',    $anonymous_data['bbp_anonymous_name']    );
		update_post_meta( $post_id, '_bbp_anonymous_email',   $anonymous_data['bbp_anonymous_email']   );
		update_post_meta( $post_id, '_bbp_anonymous_website', $anonymous_data['bbp_anonymous_website'] );

		do_action( 'bbp_anonymous_metabox_save', $post_id, $anonymous_data );

		return $post_id;
	}

	/**
	 * Add some general styling to the admin area
	 *
	 * @since bbPress (r2464)
	 *
	 * @uses bbp_get_forum_post_type() To get the forum post type
	 * @uses bbp_get_topic_post_type() To get the topic post type
	 * @uses bbp_get_reply_post_type() To get the reply post type
	 * @uses sanitize_html_class() To sanitize the classes
	 * @uses bbp_is_forum() To check if it is a forum page
	 * @uses bbp_is_topic() To check if it is a topic page
	 * @uses bbp_is_reply() To check if it is a reply page
	 * @uses do_action() Calls 'bbp_admin_head'
	 */
	function admin_head() {

		if ( bbp_is_topic() ) : ?>

			<style type="text/css" media="screen">
			/*<![CDATA[*/

				.column-bbp_forum_topic_count,
				.column-bbp_forum_reply_count,
				.column-bbp_topic_reply_count,
				.column-bbp_topic_voice_count {
					width: 8% !important;
				}

				.column-author,
				.column-bbp_reply_author,
				.column-bbp_topic_author {
					width: 10% !important;
				}

				.column-bbp_topic_forum,
				.column-bbp_reply_forum,
				.column-bbp_reply_topic {
					width: 10% !important;
				}

				.column-bbp_forum_freshness,
				.column-bbp_topic_freshness {
					width: 10% !important;
				}

				.column-bbp_forum_created,
				.column-bbp_topic_created,
				.column-bbp_reply_created {
					width: 15% !important;
				}

				.status-closed {
					background-color: #eaeaea;
				}

				.status-spam {
					background-color: #faeaea;
				}

			/*]]>*/
			</style>

		<?php endif;

	}

	/**
	 * Toggle topic
	 *
	 * Handles the admin-side opening/closing, sticking/unsticking and
	 * spamming/unspamming of topics
	 *
	 * @since bbPress (r2727)
	 *
	 * @uses bbp_get_topic() To get the topic
	 * @uses current_user_can() To check if the user is capable of editing
	 *                           the topic
	 * @uses wp_die() To die if the user isn't capable or the post wasn't
	 *                 found
	 * @uses check_admin_referer() To verify the nonce and check referer
	 * @uses bbp_is_topic_open() To check if the topic is open
	 * @uses bbp_close_topic() To close the topic
	 * @uses bbp_open_topic() To open the topic
	 * @uses bbp_is_topic_sticky() To check if the topic is a sticky or
	 *                              super sticky
	 * @uses bbp_unstick_topic() To unstick the topic
	 * @uses bbp_stick_topic() To stick the topic
	 * @uses bbp_is_topic_spam() To check if the topic is marked as spam
	 * @uses bbp_unspam_topic() To unmark the topic as spam
	 * @uses bbp_spam_topic() To mark the topic as spam
	 * @uses do_action() Calls 'bbp_toggle_topic_admin' with success, post
	 *                    data, action and message
	 * @uses add_query_arg() To add custom args to the url
	 * @uses wp_redirect() Redirect the page to custom url
	 */
	function toggle_topic() {

		// Only proceed if GET is a topic toggle action
		if ( 'GET' == $_SERVER['REQUEST_METHOD'] && !empty( $_GET['action'] ) && in_array( $_GET['action'], array( 'bbp_toggle_topic_close', 'bbp_toggle_topic_stick', 'bbp_toggle_topic_spam' ) ) && !empty( $_GET['topic_id'] ) ) {
			$action    = $_GET['action'];            // What action is taking place?
			$topic_id  = (int) $_GET['topic_id'];    // What's the topic id?
			$success   = false;                      // Flag
			$post_data = array( 'ID' => $topic_id ); // Prelim array

			if ( !$topic = bbp_get_topic( $topic_id ) ) // Which topic?
				wp_die( __( 'The topic was not found!', 'bbpress' ) );

			if ( !current_user_can( 'moderate', $topic->ID ) ) // What is the user doing here?
				wp_die( __( 'You do not have the permission to do that!', 'bbpress' ) );

			switch ( $action ) {
				case 'bbp_toggle_topic_close' :
					check_admin_referer( 'close-topic_' . $topic_id );

					$is_open = bbp_is_topic_open( $topic_id );
					$message = true == $is_open ? 'closed' : 'opened';
					$success = true == $is_open ? bbp_close_topic( $topic_id ) : bbp_open_topic( $topic_id );

					break;

				case 'bbp_toggle_topic_stick' :
					check_admin_referer( 'stick-topic_' . $topic_id );

					$is_sticky = bbp_is_topic_sticky( $topic_id );
					$is_super  = ( empty( $is_sticky ) && !empty( $_GET['super'] ) && 1 == (int) $_GET['super'] ) ? true : false;
					$message   = true == $is_sticky ? 'unsticked'     : 'sticked';
					$message   = true == $is_super  ? 'super_sticked' : $message;
					$success   = true == $is_sticky ? bbp_unstick_topic( $topic_id ) : bbp_stick_topic( $topic_id, $is_super );

					break;

				case 'bbp_toggle_topic_spam'  :
					check_admin_referer( 'spam-topic_' . $topic_id );

					$is_spam = bbp_is_topic_spam( $topic_id );
					$message = true == $is_spam ? 'unspammed' : 'spammed';
					$success = true == $is_spam ? bbp_unspam_topic( $topic_id ) : bbp_spam_topic( $topic_id );

					break;
			}

			$message = array( 'bbp_topic_toggle_notice' => $message, 'topic_id' => $topic->ID );

			if ( false == $success || is_wp_error( $success ) )
				$message['failed'] = '1';

			// Do additional topic toggle actions (admin side)
			do_action( 'bbp_toggle_topic_admin', $success, $post_data, $action, $message );

			// Redirect back to the topic
			$redirect = add_query_arg( $message, remove_query_arg( array( 'action', 'topic_id' ) ) );
			wp_redirect( $redirect );

			// For good measure
			exit();
		}
	}

	/**
	 * Toggle topic notices
	 *
	 * Display the success/error notices from
	 * {@link BBP_Admin::toggle_topic()}
	 *
	 * @since bbPress (r2727)
	 *
	 * @uses bbp_get_topic() To get the topic
	 * @uses bbp_get_topic_title() To get the topic title of the topic
	 * @uses esc_html() To sanitize the topic title
	 * @uses apply_filters() Calls 'bbp_toggle_topic_notice_admin' with
	 *                        message, topic id, notice and is it a failure
	 */
	function toggle_topic_notice() {

		// Only proceed if GET is a topic toggle action
		if ( 'GET' == $_SERVER['REQUEST_METHOD'] && !empty( $_GET['bbp_topic_toggle_notice'] ) && in_array( $_GET['bbp_topic_toggle_notice'], array( 'opened', 'closed', 'super_sticked', 'sticked', 'unsticked', 'spammed', 'unspammed' ) ) && !empty( $_GET['topic_id'] ) ) {
			$notice     = $_GET['bbp_topic_toggle_notice'];         // Which notice?
			$topic_id   = (int) $_GET['topic_id'];                  // What's the topic id?
			$is_failure = !empty( $_GET['failed'] ) ? true : false; // Was that a failure?

			// Empty? No topic?
			if ( empty( $notice ) || empty( $topic_id ) || !$topic = bbp_get_topic( $topic_id ) )
				return;

			$topic_title = esc_html( bbp_get_topic_title( $topic->ID ) );

			switch ( $notice ) {
				case 'opened'    :
					$message = $is_failure == true ? sprintf( __( 'There was a problem opening the topic "%1$s".',           'bbpress' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully opened.',           'bbpress' ), $topic_title );
					break;

				case 'closed'    :
					$message = $is_failure == true ? sprintf( __( 'There was a problem closing the topic "%1$s".',           'bbpress' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully closed.',           'bbpress' ), $topic_title );
					break;

				case 'super_sticked' :
					$message = $is_failure == true ? sprintf( __( 'There was a problem sticking the topic "%1$s" to front.', 'bbpress' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully sticked to front.', 'bbpress' ), $topic_title );
					break;

				case 'sticked'   :
					$message = $is_failure == true ? sprintf( __( 'There was a problem sticking the topic "%1$s".',          'bbpress' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully sticked.',          'bbpress' ), $topic_title );
					break;

				case 'unsticked' :
					$message = $is_failure == true ? sprintf( __( 'There was a problem unsticking the topic "%1$s".',        'bbpress' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully unsticked.',        'bbpress' ), $topic_title );
					break;

				case 'spammed'   :
					$message = $is_failure == true ? sprintf( __( 'There was a problem marking the topic "%1$s" as spam.',   'bbpress' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully marked as spam.',   'bbpress' ), $topic_title );
					break;

				case 'unspammed' :
					$message = $is_failure == true ? sprintf( __( 'There was a problem unmarking the topic "%1$s" as spam.', 'bbpress' ), $topic_title ) : sprintf( __( 'Topic "%1$s" successfully unmarked as spam.', 'bbpress' ), $topic_title );
					break;
			}

			// Do additional topic toggle notice filters (admin side)
			$message = apply_filters( 'bbp_toggle_topic_notice_admin', $message, $topic->ID, $notice, $is_failure );

			?>

			<div id="message" class="<?php echo $is_failure == true ? 'error' : 'updated'; ?> fade">
				<p style="line-height: 150%"><?php echo $message; ?></p>
			</div>

			<?php
		}
	}

	/**
	 * Manage the column headers for the topics page
	 *
	 * @since bbPress (r2485)
	 *
	 * @param array $columns The columns
	 * @uses apply_filters() Calls 'bbp_admin_topics_column_headers' with
	 *                        the columns
	 * @return array $columns bbPress topic columns
	 */
	function topics_column_headers( $columns ) {
		$columns = array(
			'cb'                    => '<input type="checkbox" />',
			'title'                 => __( 'Topics',    'bbpress' ),
			'bbp_topic_forum'       => __( 'Forum',     'bbpress' ),
			'bbp_topic_reply_count' => __( 'Replies',   'bbpress' ),
			'bbp_topic_voice_count' => __( 'Voices',    'bbpress' ),
			'bbp_topic_author'      => __( 'Author',    'bbpress' ),
			'bbp_topic_created'     => __( 'Created',   'bbpress' ),
			'bbp_topic_freshness'   => __( 'Freshness', 'bbpress' )
		);

		return apply_filters( 'bbp_admin_topics_column_headers', $columns );
	}

	/**
	 * Print extra columns for the topics page
	 *
	 * @since bbPress (r2485)
	 *
	 * @param string $column Column
	 * @param int $topic_id Topic id
	 * @uses bbp_get_topic_forum_id() To get the forum id of the topic
	 * @uses bbp_forum_title() To output the topic's forum title
	 * @uses apply_filters() Calls 'topic_forum_row_actions' with an array
	 *                        of topic forum actions
	 * @uses bbp_get_forum_permalink() To get the forum permalink
	 * @uses admin_url() To get the admin url of post.php
	 * @uses add_query_arg() To add custom args to the url
	 * @uses bbp_topic_reply_count() To output the topic reply count
	 * @uses bbp_topic_voice_count() To output the topic voice count
	 * @uses bbp_topic_author_display_name() To output the topic author name
	 * @uses get_the_date() Get the topic creation date
	 * @uses get_the_time() Get the topic creation time
	 * @uses esc_attr() To sanitize the topic creation time
	 * @uses bbp_get_topic_last_active_time() To get the time when the topic was
	 *                                    last active
	 * @uses do_action() Calls 'bbp_admin_topics_column_data' with the
	 *                    column and topic id
	 */
	function topics_column_data( $column, $topic_id ) {

		// Get topic forum ID
		$forum_id = bbp_get_topic_forum_id( $topic_id );

		// Populate column data
		switch ( $column ) {

			// Forum
			case 'bbp_topic_forum' :

				// Output forum name
				if ( !empty( $forum_id ) ) {
					bbp_forum_title( $forum_id );

					// Link information
					$actions = apply_filters( 'topic_forum_row_actions', array (
						'edit' => '<a href="' . add_query_arg( array( 'post' => $forum_id, 'action' => 'edit' ), admin_url( '/post.php' ) ) . '">' . __( 'Edit', 'bbpress' ) . '</a>',
						'view' => '<a href="' . bbp_get_forum_permalink( $forum_id ) . '">' . __( 'View', 'bbpress' ) . '</a>'
					) );

					// Output forum post row links
					foreach ( $actions as $action => $link )
						$formatted_actions[] = '<span class="' . $action . '">' . $link . '</span>';

					//echo '<div class="row-actions">' . implode( ' | ', $formatted_actions ) . '</div>';
				} else {
					_e( '(No Forum)', 'bbpress' );
				}

				break;

			// Reply Count
			case 'bbp_topic_reply_count' :
				bbp_topic_reply_count( $topic_id );
				break;

			// Reply Count
			case 'bbp_topic_voice_count' :
				bbp_topic_voice_count( $topic_id );
				break;

			// Author
			case 'bbp_topic_author' :
				bbp_topic_author_display_name( $topic_id );
				break;

			// Freshness
			case 'bbp_topic_created':
				printf( __( '%1$s <br /> %2$s', 'bbpress' ),
					get_the_date(),
					esc_attr( get_the_time() )
				);

				break;

			// Freshness
			case 'bbp_topic_freshness' :
				if ( $last_active = bbp_get_topic_last_active_time( $topic_id, false ) )
					printf( __( '%s ago', 'bbpress' ), $last_active );
				else
					_e( 'No Replies', 'bbpress' ); // This should never happen

				break;

			// Do an action for anything else
			default :
				do_action( 'bbp_admin_topics_column_data', $column, $topic_id );
				break;
		}
	}

	/**
	 * Topic Row actions
	 *
	 * Remove the quick-edit action link under the topic title and add the
	 * content and close/stick/spam links
	 *
	 * @since bbPress (r2485)
	 *
	 * @param array $actions Actions
	 * @param array $topic Topic object
	 * @uses bbp_get_topic_post_type() To get the topic post type
	 * @uses bbp_topic_content() To output topic content
	 * @uses bbp_get_topic_permalink() To get the topic link
	 * @uses bbp_get_topic_title() To get the topic title
	 * @uses current_user_can() To check if the current user can edit or
	 *                           delete the topic
	 * @uses bbp_is_topic_open() To check if the topic is open
	 * @uses bbp_is_topic_spam() To check if the topic is marked as spam
	 * @uses bbp_is_topic_sticky() To check if the topic is a sticky or a
	 *                              super sticky
	 * @uses get_post_type_object() To get the topic post type object
	 * @uses add_query_arg() To add custom args to the url
	 * @uses remove_query_arg() To remove custom args from the url
	 * @uses wp_nonce_url() To nonce the url
	 * @uses get_delete_post_link() To get the delete post link of the topic
	 * @return array $actions Actions
	 */
	function topics_row_actions( $actions, $topic ) {
		global $bbp;

		if ( $topic->post_type == $this->post_type ) {
			unset( $actions['inline hide-if-no-js'] );

			bbp_topic_content( $topic->ID );

			// Show view link if it's not set, the topic is trashed and the user can view trashed topics
			if ( empty( $actions['view'] ) && 'trash' == $topic->post_status && current_user_can( 'view_trash' ) )
				$actions['view'] = '<a href="' . bbp_get_topic_permalink( $topic->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'bbpress' ), bbp_get_topic_title( $topic->ID ) ) ) . '" rel="permalink">' . __( 'View', 'bbpress' ) . '</a>';

			// Only show the actions if the user is capable of viewing them :)
			if ( current_user_can( 'moderate', $topic->ID ) ) {

				// Close
				// Show the 'close' and 'open' link on published and closed posts only
				if ( in_array( $topic->post_status, array( 'publish', $bbp->closed_status_id ) ) ) {
					$close_uri = esc_url( wp_nonce_url( add_query_arg( array( 'topic_id' => $topic->ID, 'action' => 'bbp_toggle_topic_close' ), remove_query_arg( array( 'bbp_topic_toggle_notice', 'topic_id', 'failed', 'super' ) ) ), 'close-topic_' . $topic->ID ) );
					if ( bbp_is_topic_open( $topic->ID ) )
						$actions['closed'] = '<a href="' . $close_uri . '" title="' . esc_attr__( 'Close this topic', 'bbpress' ) . '">' . __( 'Close', 'bbpress' ) . '</a>';
					else
						$actions['closed'] = '<a href="' . $close_uri . '" title="' . esc_attr__( 'Open this topic',  'bbpress' ) . '">' . __( 'Open',  'bbpress' ) . '</a>';
				}

				// Sticky
				$stick_uri  = esc_url( wp_nonce_url( add_query_arg( array( 'topic_id' => $topic->ID, 'action' => 'bbp_toggle_topic_stick' ), remove_query_arg( array( 'bbp_topic_toggle_notice', 'topic_id', 'failed', 'super' ) ) ), 'stick-topic_'  . $topic->ID ) );
				if ( bbp_is_topic_sticky( $topic->ID ) ) {
					$actions['stick'] = '<a href="' . $stick_uri . '" title="' . esc_attr__( 'Unstick this topic', 'bbpress' ) . '">' . __( 'Unstick', 'bbpress' ) . '</a>';
				} else {
					$super_uri        = esc_url( wp_nonce_url( add_query_arg( array( 'topic_id' => $topic->ID, 'action' => 'bbp_toggle_topic_stick', 'super' => '1' ), remove_query_arg( array( 'bbp_topic_toggle_notice', 'topic_id', 'failed', 'super' ) ) ), 'stick-topic_'  . $topic->ID ) );
					$actions['stick'] = '<a href="' . $stick_uri . '" title="' . esc_attr__( 'Stick this topic to its forum', 'bbpress' ) . '">' . __( 'Stick', 'bbpress' ) . '</a> (<a href="' . $super_uri . '" title="' . esc_attr__( 'Stick this topic to front', 'bbpress' ) . '">' . __( 'to front', 'bbpress' ) . '</a>)';
				}

				// Spam
				$spam_uri  = esc_url( wp_nonce_url( add_query_arg( array( 'topic_id' => $topic->ID, 'action' => 'bbp_toggle_topic_spam' ), remove_query_arg( array( 'bbp_topic_toggle_notice', 'topic_id', 'failed', 'super' ) ) ), 'spam-topic_'  . $topic->ID ) );
				if ( bbp_is_topic_spam( $topic->ID ) )
					$actions['spam'] = '<a href="' . $spam_uri . '" title="' . esc_attr__( 'Mark the topic as not spam', 'bbpress' ) . '">' . __( 'Not spam', 'bbpress' ) . '</a>';
				else
					$actions['spam'] = '<a href="' . $spam_uri . '" title="' . esc_attr__( 'Mark this topic as spam',    'bbpress' ) . '">' . __( 'Spam',     'bbpress' ) . '</a>';

			}

			// Do not show trash links for spam topics, or spam links for trashed topics
			if ( current_user_can( 'delete_topic', $topic->ID ) ) {
				if ( $bbp->trash_status_id == $topic->post_status ) {
					$post_type_object   = get_post_type_object( bbp_get_topic_post_type() );
					$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash', 'bbpress' ) ) . "' href='" . wp_nonce_url( add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => bbp_get_topic_post_type() ), admin_url( 'edit.php' ) ) ), admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $topic->ID ) ) ), 'untrash-' . $topic->post_type . '_' . $topic->ID ) . "'>" . __( 'Restore', 'bbpress' ) . "</a>";
				} elseif ( EMPTY_TRASH_DAYS ) {
					$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash', 'bbpress' ) ) . "' href='" . add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => bbp_get_topic_post_type() ), admin_url( 'edit.php' ) ) ), get_delete_post_link( $topic->ID ) ) . "'>" . __( 'Trash', 'bbpress' ) . "</a>";
				}

				if ( $bbp->trash_status_id == $topic->post_status || !EMPTY_TRASH_DAYS ) {
					$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently', 'bbpress' ) ) . "' href='" . add_query_arg( array( '_wp_http_referer' => add_query_arg( array( 'post_type' => bbp_get_topic_post_type() ), admin_url( 'edit.php' ) ) ), get_delete_post_link( $topic->ID, '', true ) ) . "'>" . __( 'Delete Permanently', 'bbpress' ) . "</a>";
				} elseif ( $bbp->spam_status_id == $topic->post_status ) {
					unset( $actions['trash'] );
				}
			}
		}

		return $actions;
	}

	/**
	 * Add forum dropdown to topic and reply list table filters
	 *
	 * @since bbPress (r2991)
	 *
	 * @uses bbp_get_reply_post_type() To get the reply post type
	 * @uses bbp_get_topic_post_type() To get the topic post type
	 * @uses bbp_dropdown() To generate a forum dropdown
	 * @return bool False. If post type is not topic or reply
	 */
	function filter_dropdown() {

		// Bail if not viewing the topics list
		if (
				// post_type exists in _GET
				empty( $_GET['post_type'] ) ||

				// post_type is not topic type
				( $_GET['post_type'] != $this->post_type )
			)
			return;

		// Get which forum is selected
		$selected = !empty( $_GET['bbp_forum_id'] ) ? $_GET['bbp_forum_id'] : '';

		// Show the forums dropdown
		bbp_dropdown( array(
			'selected'  => $selected,
			'show_none' => __( 'In all forums', 'bbpress' )
		) );
	}

	/**
	 * Adjust the request query and include the forum id
	 *
	 * @since bbPress (r2991)
	 *
	 * @param array $query_vars Query variables from {@link WP_Query}
	 * @uses is_admin() To check if it's the admin section
	 * @uses bbp_get_topic_post_type() To get the topic post type
	 * @uses bbp_get_reply_post_type() To get the reply post type
	 * @return array Processed Query Vars
	 */
	function filter_post_rows( $query_vars ) {
		global $pagenow;

		// Avoid poisoning other requests
		if (
				// Only look in admin
				!is_admin()                 ||

				// Make sure the current page is for post rows
				( 'edit.php' != $pagenow  ) ||

				// Make sure we're looking for a post_type
				empty( $_GET['post_type'] ) ||

				// Make sure we're looking at bbPress topics
				( $_GET['post_type'] != $this->post_type )
			)

			// We're in no shape to filter anything, so return
			return $query_vars;

		// Add post_parent query_var if one is present
		if ( !empty( $_GET['bbp_forum_id'] ) ) {
			$query_vars['meta_key']   = '_bbp_forum_id';
			$query_vars['meta_value'] = $_GET['bbp_forum_id'];
		}

		// Return manipulated query_vars
		return $query_vars;
	}
}
endif; // class_exists check

/**
 * Setup bbPress Topics Admin
 *
 * @since bbPress (r2596)
 *
 * @uses BBP_Forums_Admin
 */
function bbp_topics_admin() {
	global $bbp;

	$bbp->admin->topics = new BBP_Topics_Admin();
}

?>
