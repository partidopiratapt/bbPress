<?php

/* Formatting */

if ( !function_exists('clean_pre') ) : // [WP6102] - current at [WP8525]
// Accepts matches array from preg_replace_callback in wpautop()
// or a string
function clean_pre($matches) {
	if ( is_array($matches) )
		$text = $matches[1] . $matches[2] . "</pre>";
	else
		$text = $matches;

	$text = str_replace('<br />', '', $text);
	$text = str_replace('<p>', "\n", $text);
	$text = str_replace('</p>', '', $text);

	return $text;
}
endif;

if ( !function_exists('js_escape') ) : // [WP5734] - current at [WP8525]
// Escape single quotes, specialchar double quotes, and fix line endings.
function js_escape($text) {
	$safe_text = wp_specialchars($text, 'double');
	$safe_text = preg_replace('/&#(x)?0*(?(1)27|39);?/i', "'", stripslashes($safe_text));
	$safe_text = preg_replace("/\r?\n/", "\\n", addslashes($safe_text));
	return apply_filters('js_escape', $safe_text, $text);
}
endif;

if ( !function_exists('attribute_escape') ) : // [WP4660] - current at [WP8525]
// Escaping for HTML attributes
function attribute_escape($text) {
	$safe_text = wp_specialchars($text, true);
	return apply_filters('attribute_escape', $safe_text, $text);
}
endif;

if ( !function_exists( 'force_balance_tags' ) ) : // [WP5805] - current at [WP8525]
/*
 force_balance_tags

 Balances Tags of string using a modified stack.

 @param text      Text to be balanced
 @param force     Forces balancing, ignoring the value of the option
 @return          Returns balanced text
 @author          Leonard Lin (leonard@acm.org)
 @version         v1.1
 @date            November 4, 2001
 @license         GPL v2.0
 @notes
 @changelog
 ---  Modified by Scott Reilly (coffee2code) 02 Aug 2004
	1.2  ***TODO*** Make better - change loop condition to $text
	1.1  Fixed handling of append/stack pop order of end text
	     Added Cleaning Hooks
	1.0  First Version
*/
function force_balance_tags( $text ) {
	$tagstack = array(); $stacksize = 0; $tagqueue = ''; $newtext = '';
	$single_tags = array('br', 'hr', 'img', 'input'); //Known single-entity/self-closing tags
	$nestable_tags = array('blockquote', 'div', 'span'); //Tags that can be immediately nested within themselves

	# WP bug fix for comments - in case you REALLY meant to type '< !--'
	$text = str_replace('< !--', '<    !--', $text);
	# WP bug fix for LOVE <3 (and other situations with '<' before a number)
	$text = preg_replace('#<([0-9]{1})#', '&lt;$1', $text);

	while (preg_match("/<(\/?\w*)\s*([^>]*)>/",$text,$regex)) {
		$newtext .= $tagqueue;

		$i = strpos($text,$regex[0]);
		$l = strlen($regex[0]);

		// clear the shifter
		$tagqueue = '';
		// Pop or Push
		if ($regex[1][0] == "/") { // End Tag
			$tag = strtolower(substr($regex[1],1));
			// if too many closing tags
			if($stacksize <= 0) {
				$tag = '';
				//or close to be safe $tag = '/' . $tag;
			}
			// if stacktop value = tag close value then pop
			else if ($tagstack[$stacksize - 1] == $tag) { // found closing tag
				$tag = '</' . $tag . '>'; // Close Tag
				// Pop
				array_pop ($tagstack);
				$stacksize--;
			} else { // closing tag not at top, search for it
				for ($j=$stacksize-1;$j>=0;$j--) {
					if ($tagstack[$j] == $tag) {
					// add tag to tagqueue
						for ($k=$stacksize-1;$k>=$j;$k--){
							$tagqueue .= '</' . array_pop ($tagstack) . '>';
							$stacksize--;
						}
						break;
					}
				}
				$tag = '';
			}
		} else { // Begin Tag
			$tag = strtolower($regex[1]);

			// Tag Cleaning

			// If self-closing or '', don't do anything.
			if((substr($regex[2],-1) == '/') || ($tag == '')) {
			}
			// ElseIf it's a known single-entity tag but it doesn't close itself, do so
			elseif ( in_array($tag, $single_tags) ) {
				$regex[2] .= '/';
			} else {	// Push the tag onto the stack
				// If the top of the stack is the same as the tag we want to push, close previous tag
				if (($stacksize > 0) && !in_array($tag, $nestable_tags) && ($tagstack[$stacksize - 1] == $tag)) {
					$tagqueue = '</' . array_pop ($tagstack) . '>';
					$stacksize--;
				}
				$stacksize = array_push ($tagstack, $tag);
			}

			// Attributes
			$attributes = $regex[2];
			if($attributes) {
				$attributes = ' '.$attributes;
			}
			$tag = '<'.$tag.$attributes.'>';
			//If already queuing a close tag, then put this tag on, too
			if ($tagqueue) {
				$tagqueue .= $tag;
				$tag = '';
			}
		}
		$newtext .= substr($text,0,$i) . $tag;
		$text = substr($text,$i+$l);
	}

	// Clear Tag Queue
	$newtext .= $tagqueue;

	// Add Remaining text
	$newtext .= $text;

	// Empty Stack
	while($x = array_pop($tagstack)) {
		$newtext .= '</' . $x . '>'; // Add remaining tags to close
	}

	// WP fix for the bug with HTML comments
	$newtext = str_replace("< !--","<!--",$newtext);
	$newtext = str_replace("<    !--","< !--",$newtext);

	return $newtext;
}
endif;

if ( !function_exists('_make_url_clickable_cb') ) : // current at [WP8525]
function _make_url_clickable_cb($matches) {
	$ret = '';
	$url = $matches[2];
	$url = clean_url($url);
	if ( empty($url) )
		return $matches[0];
	// removed trailing [.,;:] from URL
	if ( in_array(substr($url, -1), array('.', ',', ';', ':')) === true ) {
		$ret = substr($url, -1);
		$url = substr($url, 0, strlen($url)-1);
	}
	return $matches[1] . "<a href=\"$url\" rel=\"nofollow\">$url</a>" . $ret;
}
endif;

if ( !function_exists('_make_web_ftp_clickable_cb') ) : // current at [WP8525]
function _make_web_ftp_clickable_cb($matches) {
	$ret = '';
	$dest = $matches[2];
	$dest = 'http://' . $dest;
	$dest = clean_url($dest);
	if ( empty($dest) )
		return $matches[0];
	// removed trailing [,;:] from URL
	if ( in_array(substr($dest, -1), array('.', ',', ';', ':')) === true ) {
		$ret = substr($dest, -1);
		$dest = substr($dest, 0, strlen($dest)-1);
	}
	return $matches[1] . "<a href=\"$dest\" rel=\"nofollow\">$dest</a>" . $ret;
}
endif;

if ( !function_exists('_make_email_clickable_cb') ) : // current at [WP8525]
function _make_email_clickable_cb($matches) {
	$email = $matches[2] . '@' . $matches[3];
	return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
}
endif;

if ( !function_exists('make_clickable') ) : // [WP8525] - current at [WP8525]
function make_clickable($ret) {
	$ret = ' ' . $ret;
	// in testing, using arrays here was found to be faster
	$ret = preg_replace_callback('#([\s>])([\w]+?://[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_url_clickable_cb', $ret);
	$ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_web_ftp_clickable_cb', $ret);
	$ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret);
	// this one is not in an array because we need it to run last, for cleanup of accidental links within links
	$ret = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $ret);
	$ret = trim($ret);
	return $ret;
}
endif;

/* Forms */

if ( !function_exists('wp_referer_field') ) : // current at [WP8525]
function wp_referer_field( $echo = true) {
	$ref = attribute_escape( $_SERVER['REQUEST_URI'] );
	$referer_field = '<input type="hidden" name="_wp_http_referer" value="'. $ref . '" />';

	if ( $echo )
		echo $referer_field;
	return $referer_field;
}
endif;

if ( !function_exists('wp_original_referer_field') ) : // current at [WP8525]
function wp_original_referer_field( $echo = true, $jump_back_to = 'current' ) {
	$jump_back_to = ( 'previous' == $jump_back_to ) ? wp_get_referer() : $_SERVER['REQUEST_URI'];
	$ref = ( wp_get_original_referer() ) ? wp_get_original_referer() : $jump_back_to;
	$orig_referer_field = '<input type="hidden" name="_wp_original_http_referer" value="' . attribute_escape( stripslashes( $ref ) ) . '" />';
	if ( $echo )
		echo $orig_referer_field;
	return $orig_referer_field;
}
endif;

if ( !function_exists('wp_get_referer') ) : // current at [WP8525]
function wp_get_referer() {
	if ( ! empty( $_REQUEST['_wp_http_referer'] ) )
		$ref = $_REQUEST['_wp_http_referer'];
	else if ( ! empty( $_SERVER['HTTP_REFERER'] ) )
		$ref = $_SERVER['HTTP_REFERER'];

	if ( $ref !== $_SERVER['REQUEST_URI'] )
		return $ref;
	return false;
}
endif;

if ( !function_exists('wp_get_original_referer') ) :  // [WP3908] - current at [WP8525]
function wp_get_original_referer() {
	if ( !empty( $_REQUEST['_wp_original_http_referer'] ) )
		return $_REQUEST['_wp_original_http_referer'];
	return false;
}
endif;

if ( !function_exists('build_query') ) : // current at [WP8525]
/**
 * Build URL query based on an associative and, or indexed array.
 *
 * This is a convenient function for easily building url queries. It sets the
 * separator to '&' and uses _http_build_query() function.
 *
 * @see _http_build_query() Used to build the query
 * @link http://us2.php.net/manual/en/function.http-build-query.php more on what
 *		http_build_query() does.
 *
 * @since unknown
 *
 * @param array $data URL-encode key/value pairs.
 * @return string URL encoded string
 */
function build_query( $data ) {
	return _http_build_query( $data, NULL, '&', '', false );
}
endif;

if ( !function_exists('add_query_arg') ) : // current at [WP8525]
/**
 * Retrieve a modified URL query string.
 *
 * You can rebuild the URL and append a new query variable to the URL query by
 * using this function. You can also retrieve the full URL with query data.
 *
 * Adding a single key & value or an associative array. Setting a key value to
 * emptystring removes the key. Omitting oldquery_or_uri uses the $_SERVER
 * value.
 *
 * @since 1.5.0
 *
 * @param mixed $param1 Either newkey or an associative_array
 * @param mixed $param2 Either newvalue or oldquery or uri
 * @param mixed $param3 Optional. Old query or uri
 * @return unknown
 */
function add_query_arg() {
	$ret = '';
	if ( is_array( func_get_arg(0) ) ) {
		if ( @func_num_args() < 2 || false === @func_get_arg( 1 ) )
			$uri = $_SERVER['REQUEST_URI'];
		else
			$uri = @func_get_arg( 1 );
	} else {
		if ( @func_num_args() < 3 || false === @func_get_arg( 2 ) )
			$uri = $_SERVER['REQUEST_URI'];
		else
			$uri = @func_get_arg( 2 );
	}

	if ( $frag = strstr( $uri, '#' ) )
		$uri = substr( $uri, 0, -strlen( $frag ) );
	else
		$frag = '';

	if ( preg_match( '|^https?://|i', $uri, $matches ) ) {
		$protocol = $matches[0];
		$uri = substr( $uri, strlen( $protocol ) );
	} else {
		$protocol = '';
	}

	if ( strpos( $uri, '?' ) !== false ) {
		$parts = explode( '?', $uri, 2 );
		if ( 1 == count( $parts ) ) {
			$base = '?';
			$query = $parts[0];
		} else {
			$base = $parts[0] . '?';
			$query = $parts[1];
		}
	} elseif ( !empty( $protocol ) || strpos( $uri, '=' ) === false ) {
		$base = $uri . '?';
		$query = '';
	} else {
		$base = '';
		$query = $uri;
	}

	wp_parse_str( $query, $qs );
	$qs = urlencode_deep( $qs ); // this re-URL-encodes things that were already in the query string
	if ( is_array( func_get_arg( 0 ) ) ) {
		$kayvees = func_get_arg( 0 );
		$qs = array_merge( $qs, $kayvees );
	} else {
		$qs[func_get_arg( 0 )] = func_get_arg( 1 );
	}

	foreach ( $qs as $k => $v ) {
		if ( $v === false )
			unset( $qs[$k] );
	}

	$ret = build_query( $qs );
	$ret = trim( $ret, '?' );
	$ret = preg_replace( '#=(&|$)#', '$1', $ret );
	$ret = $protocol . $base . $ret . $frag;
	$ret = rtrim( $ret, '?' );
	return $ret;
}
endif;

if ( !function_exists('remove_query_arg') ) : // current at [WP8525]
/**
 * Removes an item or list from the query string.
 *
 * @since 1.5.0
 *
 * @param string|array $key Query key or keys to remove.
 * @param bool $query When false uses the $_SERVER value.
 * @return unknown
 */
function remove_query_arg( $key, $query=false ) {
	if ( is_array( $key ) ) { // removing multiple keys
		foreach ( (array) $key as $k )
			$query = add_query_arg( $k, false, $query );
		return $query;
	}
	return add_query_arg( $key, false, $query );
}
endif;

if ( !function_exists('get_status_header_desc') ) : // current at [WP8525]
/**
 * Retrieve the description for the HTTP status.
 *
 * @since 2.3.0
 *
 * @param int $code HTTP status code.
 * @return string Empty string if not found, or description if found.
 */
function get_status_header_desc( $code ) {
	global $wp_header_to_desc;

	$code = absint( $code );

	if ( !isset( $wp_header_to_desc ) ) {
		$wp_header_to_desc = array(
			100 => 'Continue',
			101 => 'Switching Protocols',

			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',

			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',

			400 => 'Bad Request',
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',

			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
		);
	}

	if ( isset( $wp_header_to_desc[$code] ) )
		return $wp_header_to_desc[$code];
	else
		return '';
}
endif;

if ( !function_exists('status_header') ) : // current at [WP8525]
/**
 * Set HTTP status header.
 *
 * @since unknown
 * @uses apply_filters() Calls 'status_header' on status header string, HTTP
 *		HTTP code, HTTP code description, and protocol string as separate
 *		parameters.
 *
 * @param int $header HTTP status code
 * @return null Does not return anything.
 */
function status_header( $header ) {
	$text = get_status_header_desc( $header );

	if ( empty( $text ) )
		return false;

	$protocol = $_SERVER["SERVER_PROTOCOL"];
	if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
		$protocol = 'HTTP/1.0';
	$status_header = "$protocol $header $text";
	if ( function_exists( 'apply_filters' ) )
		$status_header = apply_filters( 'status_header', $status_header, $header, $text, $protocol );

	if ( version_compare( phpversion(), '4.3.0', '>=' ) )
		return @header( $status_header, true, $header );
	else
		return @header( $status_header );
}
endif;

if ( !function_exists('nocache_headers') ) : // current at [WP8525]
/**
 * Sets the headers to prevent caching for the different browsers.
 *
 * Different browsers support different nocache headers, so several headers must
 * be sent so that all of them get the point that no caching should occur.
 *
 * @since 2.0.0
 */
function nocache_headers() {
	// why are these @-silenced when other header calls aren't?
	@header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
	@header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	@header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
	@header( 'Pragma: no-cache' );
}
endif;

if ( !function_exists('cache_javascript_headers') ) : // current at [WP8525] - Not verbatim WP. Charset hardcoded.
/**
 * Set the headers for caching for 10 days with JavaScript content type.
 *
 * @since 2.1.0
 */
function cache_javascript_headers() {
	$expiresOffset = 864000; // 10 days
	header( "Content-Type: text/javascript; charset=utf-8" );
	header( "Vary: Accept-Encoding" ); // Handle proxies
	header( "Expires: " . gmdate( "D, d M Y H:i:s", time() + $expiresOffset ) . " GMT" );
}
endif;

/* Templates */

if ( !function_exists('paginate_links') ) : // [WP6026] - current at [WP8525]
function paginate_links( $args = '' ) {
	$defaults = array(
		'base' => '%_%', // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
		'format' => '?page=%#%', // ?page=%#% : %#% is replaced by the page number
		'total' => 1,
		'current' => 0,
		'show_all' => false,
		'prev_next' => true,
		'prev_text' => __('&laquo; Previous'),
		'next_text' => __('Next &raquo;'),
		'end_size' => 1, // How many numbers on either end including the end
		'mid_size' => 2, // How many numbers to either side of current not including current
		'type' => 'plain',
		'add_args' => false // array of query args to aadd
	);

	$args = wp_parse_args( $args, $defaults );
	extract($args, EXTR_SKIP);

	// Who knows what else people pass in $args
	$total    = (int) $total;
	if ( $total < 2 )
		return;
	$current  = (int) $current;
	$end_size = 0  < (int) $end_size ? (int) $end_size : 1; // Out of bounds?  Make it the default.
	$mid_size = 0 <= (int) $mid_size ? (int) $mid_size : 2;
	$add_args = is_array($add_args) ? $add_args : false;
	$r = '';
	$page_links = array();
	$n = 0;
	$dots = false;

	if ( $prev_next && $current && 1 < $current ) :
		$link = str_replace('%_%', 2 == $current ? '' : $format, $base);
		$link = str_replace('%#%', $current - 1, $link);
		if ( $add_args )
			$link = add_query_arg( $add_args, $link );
		$page_links[] = "<a class='prev page-numbers' href='" . clean_url($link) . "'>$prev_text</a>";
	endif;
	for ( $n = 1; $n <= $total; $n++ ) :
		if ( $n == $current ) :
			$page_links[] = "<span class='page-numbers current'>$n</span>";
			$dots = true;
		else :
			if ( $show_all || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
				$link = str_replace('%_%', 1 == $n ? '' : $format, $base);
				$link = str_replace('%#%', $n, $link);
				if ( $add_args )
					$link = add_query_arg( $add_args, $link );
				$page_links[] = "<a class='page-numbers' href='" . clean_url($link) . "'>$n</a>";
				$dots = true;
			elseif ( $dots && !$show_all ) :
				$page_links[] = "<span class='page-numbers dots'>...</span>";
				$dots = false;
			endif;
		endif;
	endfor;
	if ( $prev_next && $current && ( $current < $total || -1 == $total ) ) :
		$link = str_replace('%_%', $format, $base);
		$link = str_replace('%#%', $current + 1, $link);
		if ( $add_args )
			$link = add_query_arg( $add_args, $link );
		$page_links[] = "<a class='next page-numbers' href='" . clean_url($link) . "'>$next_text</a>";
	endif;
	switch ( $type ) :
		case 'array' :
			return $page_links;
			break;
		case 'list' :
			$r .= "<ul class='page-numbers'>\n\t<li>";
			$r .= join("</li>\n\t<li>", $page_links);
			$r .= "</li>\n</ul>\n";
			break;
		default :
			$r = join("\n", $page_links);
			break;
	endswitch;
	return $r;
}
endif;

if ( !function_exists('ent2ncr') ) : // [WP3641] - current at [WP8525]
function ent2ncr($text) {
	$to_ncr = array(
		'&quot;' => '&#34;',
		'&amp;' => '&#38;',
		'&frasl;' => '&#47;',
		'&lt;' => '&#60;',
		'&gt;' => '&#62;',
		'|' => '&#124;',
		'&nbsp;' => '&#160;',
		'&iexcl;' => '&#161;',
		'&cent;' => '&#162;',
		'&pound;' => '&#163;',
		'&curren;' => '&#164;',
		'&yen;' => '&#165;',
		'&brvbar;' => '&#166;',
		'&brkbar;' => '&#166;',
		'&sect;' => '&#167;',
		'&uml;' => '&#168;',
		'&die;' => '&#168;',
		'&copy;' => '&#169;',
		'&ordf;' => '&#170;',
		'&laquo;' => '&#171;',
		'&not;' => '&#172;',
		'&shy;' => '&#173;',
		'&reg;' => '&#174;',
		'&macr;' => '&#175;',
		'&hibar;' => '&#175;',
		'&deg;' => '&#176;',
		'&plusmn;' => '&#177;',
		'&sup2;' => '&#178;',
		'&sup3;' => '&#179;',
		'&acute;' => '&#180;',
		'&micro;' => '&#181;',
		'&para;' => '&#182;',
		'&middot;' => '&#183;',
		'&cedil;' => '&#184;',
		'&sup1;' => '&#185;',
		'&ordm;' => '&#186;',
		'&raquo;' => '&#187;',
		'&frac14;' => '&#188;',
		'&frac12;' => '&#189;',
		'&frac34;' => '&#190;',
		'&iquest;' => '&#191;',
		'&Agrave;' => '&#192;',
		'&Aacute;' => '&#193;',
		'&Acirc;' => '&#194;',
		'&Atilde;' => '&#195;',
		'&Auml;' => '&#196;',
		'&Aring;' => '&#197;',
		'&AElig;' => '&#198;',
		'&Ccedil;' => '&#199;',
		'&Egrave;' => '&#200;',
		'&Eacute;' => '&#201;',
		'&Ecirc;' => '&#202;',
		'&Euml;' => '&#203;',
		'&Igrave;' => '&#204;',
		'&Iacute;' => '&#205;',
		'&Icirc;' => '&#206;',
		'&Iuml;' => '&#207;',
		'&ETH;' => '&#208;',
		'&Ntilde;' => '&#209;',
		'&Ograve;' => '&#210;',
		'&Oacute;' => '&#211;',
		'&Ocirc;' => '&#212;',
		'&Otilde;' => '&#213;',
		'&Ouml;' => '&#214;',
		'&times;' => '&#215;',
		'&Oslash;' => '&#216;',
		'&Ugrave;' => '&#217;',
		'&Uacute;' => '&#218;',
		'&Ucirc;' => '&#219;',
		'&Uuml;' => '&#220;',
		'&Yacute;' => '&#221;',
		'&THORN;' => '&#222;',
		'&szlig;' => '&#223;',
		'&agrave;' => '&#224;',
		'&aacute;' => '&#225;',
		'&acirc;' => '&#226;',
		'&atilde;' => '&#227;',
		'&auml;' => '&#228;',
		'&aring;' => '&#229;',
		'&aelig;' => '&#230;',
		'&ccedil;' => '&#231;',
		'&egrave;' => '&#232;',
		'&eacute;' => '&#233;',
		'&ecirc;' => '&#234;',
		'&euml;' => '&#235;',
		'&igrave;' => '&#236;',
		'&iacute;' => '&#237;',
		'&icirc;' => '&#238;',
		'&iuml;' => '&#239;',
		'&eth;' => '&#240;',
		'&ntilde;' => '&#241;',
		'&ograve;' => '&#242;',
		'&oacute;' => '&#243;',
		'&ocirc;' => '&#244;',
		'&otilde;' => '&#245;',
		'&ouml;' => '&#246;',
		'&divide;' => '&#247;',
		'&oslash;' => '&#248;',
		'&ugrave;' => '&#249;',
		'&uacute;' => '&#250;',
		'&ucirc;' => '&#251;',
		'&uuml;' => '&#252;',
		'&yacute;' => '&#253;',
		'&thorn;' => '&#254;',
		'&yuml;' => '&#255;',
		'&OElig;' => '&#338;',
		'&oelig;' => '&#339;',
		'&Scaron;' => '&#352;',
		'&scaron;' => '&#353;',
		'&Yuml;' => '&#376;',
		'&fnof;' => '&#402;',
		'&circ;' => '&#710;',
		'&tilde;' => '&#732;',
		'&Alpha;' => '&#913;',
		'&Beta;' => '&#914;',
		'&Gamma;' => '&#915;',
		'&Delta;' => '&#916;',
		'&Epsilon;' => '&#917;',
		'&Zeta;' => '&#918;',
		'&Eta;' => '&#919;',
		'&Theta;' => '&#920;',
		'&Iota;' => '&#921;',
		'&Kappa;' => '&#922;',
		'&Lambda;' => '&#923;',
		'&Mu;' => '&#924;',
		'&Nu;' => '&#925;',
		'&Xi;' => '&#926;',
		'&Omicron;' => '&#927;',
		'&Pi;' => '&#928;',
		'&Rho;' => '&#929;',
		'&Sigma;' => '&#931;',
		'&Tau;' => '&#932;',
		'&Upsilon;' => '&#933;',
		'&Phi;' => '&#934;',
		'&Chi;' => '&#935;',
		'&Psi;' => '&#936;',
		'&Omega;' => '&#937;',
		'&alpha;' => '&#945;',
		'&beta;' => '&#946;',
		'&gamma;' => '&#947;',
		'&delta;' => '&#948;',
		'&epsilon;' => '&#949;',
		'&zeta;' => '&#950;',
		'&eta;' => '&#951;',
		'&theta;' => '&#952;',
		'&iota;' => '&#953;',
		'&kappa;' => '&#954;',
		'&lambda;' => '&#955;',
		'&mu;' => '&#956;',
		'&nu;' => '&#957;',
		'&xi;' => '&#958;',
		'&omicron;' => '&#959;',
		'&pi;' => '&#960;',
		'&rho;' => '&#961;',
		'&sigmaf;' => '&#962;',
		'&sigma;' => '&#963;',
		'&tau;' => '&#964;',
		'&upsilon;' => '&#965;',
		'&phi;' => '&#966;',
		'&chi;' => '&#967;',
		'&psi;' => '&#968;',
		'&omega;' => '&#969;',
		'&thetasym;' => '&#977;',
		'&upsih;' => '&#978;',
		'&piv;' => '&#982;',
		'&ensp;' => '&#8194;',
		'&emsp;' => '&#8195;',
		'&thinsp;' => '&#8201;',
		'&zwnj;' => '&#8204;',
		'&zwj;' => '&#8205;',
		'&lrm;' => '&#8206;',
		'&rlm;' => '&#8207;',
		'&ndash;' => '&#8211;',
		'&mdash;' => '&#8212;',
		'&lsquo;' => '&#8216;',
		'&rsquo;' => '&#8217;',
		'&sbquo;' => '&#8218;',
		'&ldquo;' => '&#8220;',
		'&rdquo;' => '&#8221;',
		'&bdquo;' => '&#8222;',
		'&dagger;' => '&#8224;',
		'&Dagger;' => '&#8225;',
		'&bull;' => '&#8226;',
		'&hellip;' => '&#8230;',
		'&permil;' => '&#8240;',
		'&prime;' => '&#8242;',
		'&Prime;' => '&#8243;',
		'&lsaquo;' => '&#8249;',
		'&rsaquo;' => '&#8250;',
		'&oline;' => '&#8254;',
		'&frasl;' => '&#8260;',
		'&euro;' => '&#8364;',
		'&image;' => '&#8465;',
		'&weierp;' => '&#8472;',
		'&real;' => '&#8476;',
		'&trade;' => '&#8482;',
		'&alefsym;' => '&#8501;',
		'&crarr;' => '&#8629;',
		'&lArr;' => '&#8656;',
		'&uArr;' => '&#8657;',
		'&rArr;' => '&#8658;',
		'&dArr;' => '&#8659;',
		'&hArr;' => '&#8660;',
		'&forall;' => '&#8704;',
		'&part;' => '&#8706;',
		'&exist;' => '&#8707;',
		'&empty;' => '&#8709;',
		'&nabla;' => '&#8711;',
		'&isin;' => '&#8712;',
		'&notin;' => '&#8713;',
		'&ni;' => '&#8715;',
		'&prod;' => '&#8719;',
		'&sum;' => '&#8721;',
		'&minus;' => '&#8722;',
		'&lowast;' => '&#8727;',
		'&radic;' => '&#8730;',
		'&prop;' => '&#8733;',
		'&infin;' => '&#8734;',
		'&ang;' => '&#8736;',
		'&and;' => '&#8743;',
		'&or;' => '&#8744;',
		'&cap;' => '&#8745;',
		'&cup;' => '&#8746;',
		'&int;' => '&#8747;',
		'&there4;' => '&#8756;',
		'&sim;' => '&#8764;',
		'&cong;' => '&#8773;',
		'&asymp;' => '&#8776;',
		'&ne;' => '&#8800;',
		'&equiv;' => '&#8801;',
		'&le;' => '&#8804;',
		'&ge;' => '&#8805;',
		'&sub;' => '&#8834;',
		'&sup;' => '&#8835;',
		'&nsub;' => '&#8836;',
		'&sube;' => '&#8838;',
		'&supe;' => '&#8839;',
		'&oplus;' => '&#8853;',
		'&otimes;' => '&#8855;',
		'&perp;' => '&#8869;',
		'&sdot;' => '&#8901;',
		'&lceil;' => '&#8968;',
		'&rceil;' => '&#8969;',
		'&lfloor;' => '&#8970;',
		'&rfloor;' => '&#8971;',
		'&lang;' => '&#9001;',
		'&rang;' => '&#9002;',
		'&larr;' => '&#8592;',
		'&uarr;' => '&#8593;',
		'&rarr;' => '&#8594;',
		'&darr;' => '&#8595;',
		'&harr;' => '&#8596;',
		'&loz;' => '&#9674;',
		'&spades;' => '&#9824;',
		'&clubs;' => '&#9827;',
		'&hearts;' => '&#9829;',
		'&diams;' => '&#9830;'
	);

	return str_replace( array_keys($to_ncr), array_values($to_ncr), $text );
}
endif;

if ( !function_exists('urlencode_deep') ) : // [WP5261] - current at [WP8525]
function urlencode_deep($value) {
	 $value = is_array($value) ?
		 array_map('urlencode_deep', $value) :
		 urlencode($value);

	 return $value;
}
endif;

if ( !function_exists( 'zeroise' ) ) : // [WP3855] - current at [WP8525]
function zeroise($number,$threshold) { // function to add leading zeros when necessary
	return sprintf('%0'.$threshold.'s', $number);
}
endif;

if ( !function_exists( 'backslashit' ) ) : // [WP3855] - current at [WP8525]
function backslashit($string) {
	$string = preg_replace('/^([0-9])/', '\\\\\\\\\1', $string);
	$string = preg_replace('/([a-z])/i', '\\\\\1', $string);
	return $string;
}
endif;

if ( !function_exists( 'wp_remote_fopen' ) ) : // current at [WP8525]
/**
 * HTTP request for URI to retrieve content.
 *
 * Tries to retrieve the HTTP content with fopen first and then using cURL, if
 * fopen can't be used.
 *
 * @since unknown
 *
 * @param string $uri URI/URL of web page to retrieve.
 * @return string HTTP content.
 */
function wp_remote_fopen( $uri ) {
	$timeout = 10;
	$parsed_url = @parse_url( $uri );

	if ( !$parsed_url || !is_array( $parsed_url ) )
		return false;

	if ( !isset( $parsed_url['scheme'] ) || !in_array( $parsed_url['scheme'], array( 'http','https' ) ) )
		$uri = 'http://' . $uri;

	if ( ini_get( 'allow_url_fopen' ) ) {
		$fp = @fopen( $uri, 'r' );
		if ( !$fp )
			return false;

		//stream_set_timeout($fp, $timeout); // Requires php 4.3
		$linea = '';
		while ( $remote_read = fread( $fp, 4096 ) )
			$linea .= $remote_read;
		fclose( $fp );
		return $linea;
	} elseif ( function_exists( 'curl_init' ) ) {
		$handle = curl_init();
		curl_setopt( $handle, CURLOPT_URL, $uri);
		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 1 );
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $handle, CURLOPT_TIMEOUT, $timeout );
		$buffer = curl_exec( $handle );
		curl_close( $handle );
		return $buffer;
	} else {
		return false;
	}
}
endif;

if ( !function_exists( 'validate_file' ) ) : // [WP8372] - current at [WP8525]
/**
 * File validates against allowed set of defined rules.
 *
 * A return value of '1' means that the $file contains either '..' or './'. A
 * return value of '2' means that the $file contains ':' after the first
 * character. A return value of '3' means that the file is not in the allowed
 * files list.
 *
 * @since 2.6
 *
 * @param string $file File path.
 * @param array $allowed_files List of allowed files.
 * @return int 0 means nothing is wrong, greater than 0 means something was wrong.
 */
function validate_file( $file, $allowed_files = '' ) {
	if ( false !== strpos( $file, '..' ))
		return 1;

	if ( false !== strpos( $file, './' ))
		return 1;

	if (':' == substr( $file, 1, 1 ))
		return 2;

	if (!empty ( $allowed_files ) && (!in_array( $file, $allowed_files ) ) )
		return 3;

	return 0;
}
endif;

?>
