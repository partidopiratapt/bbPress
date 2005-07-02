<?php
require('bb-config.php');

if ( $_SERVER['HTTP_REFERER'] == bb_get_option('uri') . 'bb-login.php' && isset( $_POST['re'] ) )
	$re = $_POST['re'];
elseif ( isset( $_SERVER['HTTP_REFERER'] ) )
	$re = $_SERVER['HTTP_REFERER'];
else
	$re = bb_get_option('uri');

nocache_headers();

if ( isset( $_REQUEST['logout'] ) ) {
	bb_cookie( $bb->passcookie , $user->user_pass, time() - 31536000 );
	header('Location: ' . $re);
	bb_do_action('bb_user_logout', '');
	exit;
}

if ( $user = bb_check_login( $_POST['user_login'], $_POST['password'] ) ) {
	bb_cookie( $bb->usercookie, $user->user_login, time() + 6048000 );
	bb_cookie( $bb->passcookie, md5( $user->user_pass ) );
	bb_do_action('bb_user_login', '');
} else {
	$user_exists = bb_user_exists( $_POST['user_login'] );
	$user_login    = user_sanitize ( $_POST['user_login'] );
	$redirect_to = bb_specialchars( $re, 1 );
	include('bb-templates/login-failed.php');
	exit;
}

header('Location: ' . $re);
?>
