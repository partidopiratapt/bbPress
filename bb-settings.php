<?php
error_reporting(E_ALL);

if ( !(phpversion() >= '4.1') )
	die( 'Your server is running PHP version ' . phpversion() . ' but bbPress requires at least 4.1' );

if ( !extension_loaded('mysql') )
	die( 'Your PHP installation appears to be missing the MySQL which is required for bbPress.' );

function bb_timer_start() {
	global $bb_timestart;
	$mtime = explode(' ', microtime() );
	$bb_timestart = $mtime[1] + $mtime[0];
	return true;
}
bb_timer_start();

require( BBPATH . '/bb-includes/db.php');
require( BBPATH . '/bb-includes/functions.php');
require( BBPATH . '/bb-includes/formatting-functions.php');
require( BBPATH . '/bb-includes/template-functions.php');
require( BBPATH . '/bb-includes/default-filters.php');

$bbdb->forums  = $table_prefix . 'forums';
$bbdb->posts   = $table_prefix . 'posts';
$bbdb->topics  = $table_prefix . 'topics';
$bbdb->users   = $table_prefix . 'users';

$static_title = '';

$_GET    = bb_global_sanitize($_GET   );
$_POST   = bb_global_sanitize($_POST  );
$_COOKIE = bb_global_sanitize($_COOKIE);
$_SERVER = bb_global_sanitize($_SERVER);

function bb_shutdown_action_hook() {
	bb_do_action('bb_shutdown', '');
}
register_shutdown_function('bb_shutdown_action_hook');

define('BBHASH', md5($table_prefix) );

$current_user = bb_current_user();

?>