<?php

function bb_verify_email( $email ) {
	if (ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'.'@'.
		'[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
		'[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email)) {
		if ( $check_domain && function_exists('checkdnsrr') ) {
			list (, $domain)  = explode('@', $email);
			if ( checkdnsrr($domain . '.', 'MX') || checkdnsrr($domain . '.', 'A') ) {
				return $email;
			}
			return false;
		}
		return $email;
	}
	return false;
}

function bb_new_user( $username, $email, $website, $location, $interests ) {
	global $bbdb;
	$now       = bb_current_time('mysql');
	$password  = bb_random_pass();
	$passcrypt = md5( $password );

	$bbdb->query("INSERT INTO $bbdb->users
	(username,    user_regdate, user_password, user_email, user_website, user_from,  user_interest)
	VALUES
	('$username', '$now',       '$passcrypt',  '$email',   '$website',  '$location', '$interests')");
	
	$user_id = $bbdb->insert_id;
	bb_send_pass( $user_id, $password );
	bb_do_action('bb_new_user', $user_id);
	return $user_id;
}

function bb_update_user( $user_id, $website, $location, $interests ) {
	global $bbdb;

	$bbdb->query("UPDATE $bbdb->users SET
	user_website  = '$website',
	user_from     = '$location',
	user_interest = '$interests'
	WHERE user_id = '$user_id'
	");

	bb_do_action('bb_update_user', $user_id);
	return $user_id;
}

function bb_update_user_password( $user_id, $password ) {
	global $bbdb;
	$passhash = md5( $password );

	$bbdb->query("UPDATE $bbdb->users SET
	user_password = '$passhash'
	WHERE user_id = '$user_id'
	");

	bb_do_action('bb_update_user_password', $user_id);
	return $user_id;
}

function bb_random_pass( $length = 6) {
	$number = mt_rand(1, 15);
	$string = md5( uniqid( microtime() ) );
 	$password = substr( $string, $number, $length );
	return $password;
}

function bb_send_pass( $user, $pass ) {
	global $bbdb;
	$user = (int) $user;
	$user = $bbdb->get_row("SELECT * FROM $bbdb->users WHERE user_id = $user");

	if ( $user ) :
		mail( $user->user_email, bb_get_option('name') . ': Password', "Your password is: $pass
You can now login: " . bb_get_option('uri') . "

Enjoy!", 'From: ' . bb_get_option('admin_email') );

	endif;
}
?>