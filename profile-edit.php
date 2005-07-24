<?php
require_once('bb-config.php');

if ( !$current_user ) {
	$sendto = bb_get_option('uri');
	header("Location: $sendto");
}

if ( !is_bb_profile() ) {
	$sendto = get_profile_tab_link( $current_user->ID, 'edit' );
	header("Location: $sendto");
}

require_once( BBPATH . 'bb-includes/registration-functions.php');

nocache_headers();

$profile_info_keys = get_profile_info_keys();
if ( $current_user->user_type >= 5 )
	$profile_admin_keys = get_profile_admin_keys();
$updated = false;
$user_email = true;

if ($_POST) :
	$user_url = bb_fix_link( $_POST['user_url'] );
	if ( isset($_POST['user_email']) )
		$user_email = bb_verify_email( $_POST['user_email'] );

	foreach ( $profile_info_keys as $key => $label ) :
		if ( is_string($$key) ) :
			$$key = bb_specialchars( $$key, 1 );
		elseif ( is_null($$key) ) :
			$$key = bb_specialchars( $_POST[$key], 1 );
		endif;
		if ( !$$key && $label[0] == 1 ) :
			$bad_input = true;
			$$key = false;
		endif;
	endforeach;

	if ( $current_user->user_type >=5 ):
		$user_type = bb_specialchars( $_POST['user_type'], 1 );
		foreach ( $profile_admin_keys as $key => $label ) :
			$$key = bb_specialchars( $_POST[$key], 1 );
			if ( !$$key && $label[0] == 1 ) :
				$bad_input = true;
				$$key = false;
			endif;
		endforeach;
	endif;

	$updated = true;

	if ( $user_email && !$bad_input ) :
		if ( can_admin( $user->ID ) ) :
			if ( is_string($user_email) ) 
				bb_update_user( $user->ID, $user_email, $user_url );
			foreach( $profile_info_keys as $key => $label )
				if ( strpos($key, 'user_') !== 0 )
					if ( $$key != ''  || isset($user->$key) )
						update_usermeta( $user->ID, $key, $$key );
		endif;

		if ( $current_user->user_type >= 5 ) :
			if ( $user_type != $user->user_type && $user_type < 6 )
				update_usermeta( $user->ID, 'user_type', $user_type );
			foreach( $profile_admin_keys as $key => $label )
				if ( $$key != ''  || isset($user->$key) )
					update_usermeta( $user->ID, $key, $$key );
		endif;

		if ( !empty( $_POST['pass1'] ) && $_POST['pass1'] == $_POST['pass2'] ) :
			bb_update_user_password ( $current_user->ID, $_POST['pass1'] );
			bb_cookie( $bb->passcookie, md5( md5( $_POST['pass1'] ) ) ); // One week
		endif;

		$sendto = bb_add_query_arg( 'updated', 'true', get_user_profile_link( $user->ID ) );
		header("Location: $sendto");
		exit();	
	endif;
endif;

require( BBPATH . 'bb-templates/profile-edit.php');
?>
