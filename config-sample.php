<?php

// ** MySQL settings ** //
define('BBDB_NAME', 'bbpress');      // The name of the database
define('BBDB_USER', 'username');     // Your MySQL username
define('BBDB_PASSWORD', 'password'); // ...and password
define('BBDB_HOST', 'localhost');    // 99% chance you won't need to change this value

// Change the prefix if you want to have multiple forums in a single database.
$bb_table_prefix  = 'bb_'; // Only letters, numbers and underscores please!

// If your bbPress URL is http://bbpress.example.com/forums/ , the examples would be correct.
// Adjust the domain and path to suit your actual URL.
	// Just the domain name; no directories or path. There should be no trailing slash here.
	$bb->domain = 'http://my-cool-forums.example.com'; // Example: 'http://bbpress.example.com'
	// There should be both a leading and trailing slash here. '/' is fine if the site is in root.
	$bb->path   = '/';				   // Example: '/forums/'

// What are you going to call me?
$bb->name   = 'New bbPress Site';

// This must be set befor running the install script.
$bb->admin_email = 'you@example.com';

// Set to true if you want pretty permalinks.
$bb->mod_rewrite = false;

// The number of topics that show on each page.
$bb->page_topics = 30;

// A user can edit a post for this many minutes after submitting.
$bb->edit_lock = 60;

// Your timezone offset.  Example: -7 for Pacific Daylight Time.
$bb->gmt_offset = 0;

// Your Akismet Key.  You do not need a key to run bbPress, but if you want to take advantage
// of Akismet's powerful spam blocking, you'll need one.  You can get an Akismet key at
// http://wordpress.com/api-keys/
$bb->akismet_key = false;


// The rest is only useful if you are integrating bbPress with WordPress.
// If you're not, just leave the rest as it is.

$bb->wp_table_prefix = false;  // 'wp_'; // WordPress table prefix.
$bb->wp_home = false;  // WordPress - Options->General: Blog address (URL) // No trailing slash
$bb->wp_siteurl = false;  // WordPress - Options->General: WordPress address (URL) // No trailing slash

// Use the following line *only* if you will be loading WordPress everytime you load bbPress.
//define('WP_BB', true);

/* Stop editing */

define('BBPATH', dirname(__FILE__) . '/' );
require_once( BBPATH . 'bb-settings.php' );

?>
