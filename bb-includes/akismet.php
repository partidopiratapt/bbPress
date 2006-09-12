<?php
if ( !$bb->akismet_key )
	return; // Bail if no key.

$bb_ksd_api_host = $bb->akismet_key . '.rest.akismet.com';
$bb_ksd_api_port = 80;
$bb_ksd_user_agent = 'bbPress/' . bb_get_option('version') . ' | bbAkismet/0.1';

function bb_akismet_verify_key( $key ) {
	global $bb_ksd_pre_post_status, $bb_ksd_api_host, $bb_ksd_api_port;
	$blog = urlencode( bb_get_option('uri') );
	$response = bb_ksd_http_post("key=$key&blog=$blog", 'rest.akismet.com', '/1.1/verify-key', $bb_ksd_api_port);
	if ( 'valid' == $response[1] )
		return true;
	else
		return false;
}

// Returns array with headers in $response[0] and entity in $response[1]
function bb_ksd_http_post($request, $host, $path, $port = 80) {
	global $bb_ksd_user_agent;

	$http_request  = "POST $path HTTP/1.0\r\n";
	$http_request .= "Host: $host\r\n";
	$http_request .= "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n"; // for now
	$http_request .= "Content-Length: " . strlen($request) . "\r\n";
	$http_request .= "User-Agent: $bb_ksd_user_agent\r\n";
	$http_request .= "\r\n";
	$http_request .= $request;
	$response = '';
	if( false != ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
		fwrite($fs, $http_request);

		while ( !feof($fs) )
			$response .= fgets($fs, 1160); // One TCP-IP packet
		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);
	}
	return $response;
}

function bb_ksd_submit_ham( $post_id ) {
	global $bb_ksd_api_host, $bb_ksd_api_port;

	$post = bb_get_post( $post_id );
	if ( !$post )
		return;

	$hammer = bb_get_user( $post->poster_id );
	$ham = array(
		'blog' => bb_get_option('uri'),
		'user_ip' => $post->poster_ip,
		'permalink' => get_topic_link( $post->topic_id ), // First page
		'comment_type' => 'forum',
		'comment_author' => $hammer->user_login,
		'comment_author_email' =>  $hammer->user_email,
		'comment_author_url' => $hammer->user_url,
		'comment_content' => $post->post_text,
		'comment_date_gmt' => $post->post_time
	);

	$query_string = '';
	foreach ( $ham as $key => $data )
		$query_string .= $key . '=' . urlencode( stripslashes($data) ) . '&';
	bb_ksd_http_post($query_string, $bb_ksd_api_host, "/1.1/submit-ham", $bb_ksd_api_port);
}

function bb_ksd_submit_spam( $post_id ) {
	global $bb_ksd_api_host, $bb_ksd_api_port;

	$post = bb_get_post( $post_id );
	if ( !$post )
		return;

	$spammer = bb_get_user( $post->poster_id );
	$spam = array(
		'blog' => bb_get_option('uri'),
		'user_ip' => $post->poster_ip,
		'permalink' => get_topic_link( $post->topic_id ), // First page
		'comment_type' => 'forum',
		'comment_author' => $spammer->user_login,
		'comment_author_email' =>  $spammer->user_email,
		'comment_author_url' => $spammer->user_url,
		'comment_content' => $post->post_text,
		'comment_date_gmt' => $post->post_time
	);

	$query_string = '';
	foreach ( $spam as $key => $data )
		$query_string .= $key . '=' . urlencode( stripslashes($data) ) . '&';
	bb_ksd_http_post($query_string, $bb_ksd_api_host, "/1.1/submit-spam", $bb_ksd_api_port);
}

function bb_ksd_auto_check( $post_text ) {
	global $bb_current_user, $bb_ksd_pre_post_status, $bb_ksd_api_host, $bb_ksd_api_port;

	$post = array(
		'user_ip' => preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] ),
		'user_agent' => $_SERVER['HTTP_USER_AGENT'],
		'referrer' => $_SERVER['HTTP_REFERER'],
		'blog' => bb_get_option('uri'),
		'comment_type' => 'forum',
		'comment_author' => $bb_current_user->data->user_login,
		'comment_author_email' =>  $bb_current_user->data->user_email,
		'comment_author_url' => $bb_current_user->data->user_url,
		'comment_content' => $post_text,
	);

	if ( isset($_POST['topic_id']) )
		$post['permalink'] = get_topic_link( $_POST['topic_id'] ); // First page

	$query_string = '';
	foreach ( $post as $key => $data )
		$query_string .= $key . '=' . urlencode( stripslashes($data) ) . '&';

	$response = bb_ksd_http_post($query_string, $bb_ksd_api_host, '/1.1/comment-check', $bb_ksd_api_port);
	if ( 'true' == $response[1] )
		$bb_ksd_pre_post_status = '2';
	bb_akismet_delete_old();
	return $post_text;
}

function bb_ksd_new_post( $post_id ) {
	global $bb_ksd_pre_post_status;
	if ( '2' != $bb_ksd_pre_post_status )
		return;
	$bb_post = bb_get_post( $post_id );
	$topic = get_topic( $bb_post->topic_id );
	if ( 0 == $topic->topic_posts )
		bb_delete_topic( $topic->topic_id, 2 );
}
	

function bb_akismet_delete_old() { // Delete old every 20
	$n = mt_rand(1, 20);
	if ( $n % 20 )
		return;
	global $bbdb;
	$now = bb_current_time('mysql');
	$posts = (array) $bbdb->get_col("SELECT post_id FROM $bbdb->posts WHERE DATE_SUB('$now', INTERVAL 15 DAY) > post_time AND post_status = '2'");
	foreach ( $posts as $post )
		bb_delete_post( $post, 1 );
}

function bb_ksd_pre_post_status( $post_status ) {
	global $bb_ksd_pre_post_status;
	if ( '2' == $bb_ksd_pre_post_status )
		$post_status = $bb_ksd_pre_post_status;
	return $post_status;
}

function bb_ksd_admin_menu() {
	global $bb_submenu;
	$bb_submenu['content.php'][] = array(__('Akismet Spam'), 'moderate', 'bb_ksd_admin_page');
}

function bb_ksd_delete_post( $post_id, $new_status, $old_status ) {
	if ( 2 == $new_status && 0 == $old_status )
		bb_ksd_submit_spam( $post_id );
	else if ( 0 == $new_status && 2 == $old_status )
		bb_ksd_submit_ham( $post_id );
}

function bb_ksd_admin_page() {
	global $bb, $bb_current_submenu, $bb_posts, $page;
	if ( !bb_akismet_verify_key( $bb->akismet_key ) ) : ?>
<div class="error"><p><?php printf(__('The API key you have specified is invalid.  Please double check the <code>$bb->akismet_key</code> variable in your <code>config.php file</code>.  If you don\'t have an API key yet, you can get one at <a href="%s">WordPress.com</a>.'), 'http://wordpress.com/api-keys/'); ?></p></div>
<?php	endif;

	if ( !bb_current_user_can('browse_deleted') )
		die(__("Now how'd you get here?  And what did you think you'd being doing?"));
	add_filter( 'get_topic_where', 'no_where' );
	add_filter( 'get_topic_link', 'make_link_view_all' );
	$bb_posts = get_deleted_posts( $page, false, 2, false ); ?>
<ol id="the-list">
<?php bb_admin_list_posts(); ?>
</ol>
<?php
	$total = get_deleted_posts(0, false, 2, false); echo get_page_number_links( $page, $total );
}

function bb_ksd_post_delete_link($link, $post_status) {
	if ( !bb_current_user_can('moderate') )
		return $link;
	if ( 2 == $post_status )
		$link  = "<a href='" . bb_nonce_url( bb_get_option('uri') . 'bb-admin/delete-post.php?id=' . get_post_id() . '&status=0&view=all', 'delete-post_' . get_post_id() ) . "' >" . __('Not Spam') ."</a>";
	else if ( 0 == $post_status )
		$link .= " <a href='" . bb_nonce_url( bb_get_option('uri') . 'bb-admin/delete-post.php?id=' . get_post_id() . '&status=2', 'delete-post_' . get_post_id() ) . "' >" . __('Spam') ."</a>";
	return $link;
}

add_action( 'pre_post', 'bb_ksd_auto_check', 1 );
add_filter( 'bb_new_post', 'bb_ksd_new_post' );
add_filter( 'pre_post_status', 'bb_ksd_pre_post_status' );
add_action( 'bb_admin_menu_generator', 'bb_ksd_admin_menu' );
add_action( 'bb_delete_post', 'bb_ksd_delete_post', 10, 3);
add_filter( 'post_delete_link', 'bb_ksd_post_delete_link', 10, 2 );
?>
