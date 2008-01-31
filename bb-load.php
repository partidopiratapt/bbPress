<?php

// Define BBPATH as this files directory
define( 'BBPATH', dirname(__FILE__) . '/' );

// Initialise $bb object
$bb = new StdClass();

if ( file_exists( BBPATH . 'bb-config.php') ) {
	
	// The config file resides in BBPATH
	require_once( BBPATH . 'bb-config.php');
	
} elseif ( file_exists( dirname(BBPATH) . '/bb-config.php') ) {
	
	// The config file resides one level below BBPATH
	require_once( dirname(BBPATH) . '/bb-config.php' );
	
} elseif ( !defined('BB_INSTALLING') || !BB_INSTALLING ) {
	
	// The config file doesn't exist and we aren't on the installation page
	
	// Cut to the chase, go to the installer and use it to deal with errors
	$install_uri = preg_replace('|(/bb-admin)?/[^/]+?$|', '/', $_SERVER['PHP_SELF']) . 'bb-admin/install.php';
	header('Location: ' . $install_uri);
	
}
?>
