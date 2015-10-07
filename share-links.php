<?php
/*
 * Plugin Name: TS Share Links Plugin
 * Plugin URI: http://tecsmith.com.au
 * Description: Add share buttons to the bottom of your content
 * Author: Vino Rodrigues
 * Version: 1.0.2
 * Author URI: http://vinorodrigues.com
 *
 * @author Vino Rodrigues
 * @package TS-Share-Links
 * @since TS-Share-Links 0.9
**/


global $_ts_share_links_done;
$_ts_share_links_done = false;


// Small fix to work arround windows and virtual paths while in dev env.
if ( defined('WP_DEBUG') && WP_DEBUG )
	define( 'SHARE_LINKS_URL', plugins_url() . '/ts-share-links' );
if (!defined('SHARE_LINKS_URL'))
	define( 'SHARE_LINKS_URL', plugins_url( '', __FILE__ ) );


require_once('inc/raw-scripts.php');


/* ---------- CLASSES ----------------------------------------------------- */


/**
 * Base definition of TS_Share_Link class
 */
class TS_Share_Link {

	function get_classes() { return ''; }

	function get_code() { return ''; }

}


/**
 * Print this page
 */
class TS_Share_Print extends TS_Share_Link {

	function get_classes() {
		return 'share-print';
	}

	function get_code() {
		$out = '<a class="print-button" href="#print"><img src="' . SHARE_LINKS_URL . '/img/print.png" alt="' . __('Print') . '" /> ' . __('Print') . '</a>';
		$src = '(function($) { $(document).ready(function() { $(\'.print-button\').click(function(jQuery) { window.print(); return false; }); }); })(jQuery);';

		wp_enqueue_script('jquery');
		ts_enqueue_script($this->get_classes(), $src);
		return $out;
	}

}


/**
 * Email this page
 */
class TS_Share_Email extends TS_Share_Link {

	function get_classes() {
		return 'share-email';
	}

	function get_code() {
		$ssl = empty($_SERVER['HTTPS']) ? '' : ($_SERVER['HTTPS'] == 'on') ? true : false;
		$prt = $ssl ? '443' : '80';
		$prt = ($_SERVER['SERVER_PORT'] == $prt) ? '' : (':'.$_SERVER['SERVER_PORT']);
		$url = 'http' . ($ssl ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . $prt . $_SERVER['REQUEST_URI'];

		$subject = str_replace(' ', '%20', get_bloginfo('name'));
		$body = str_replace('+', '%20', urlencode( $url) );
		$out = '<a class="email-button" href="mailto:?to=&subject=' . $subject . '&body=' . $body . '"><img src="' . SHARE_LINKS_URL . '/img/email.png" alt="' . __('Email') . '" /> ' . __('Email') . '</a>';
		return $out;
	}

}


/**
 * Like on Facebook
 */
class TS_Share_Facebook_Like extends TS_Share_Link {

	function get_classes() {
		return 'share-facebook';
	}

	function get_code() {
		$out = '<div id="fb-root"></div>';
		$src = '(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, \'script\', \'facebook-jssdk\'));';
		$out .= '<div class="fb-like" data-send="true" data-layout="button_count" data-width="200" data-show-faces="false"></div>';

		ts_enqueue_script($this->get_classes(), $src);
		return $out;
	}

}  // TS_Share_Facebook_Like


/**
 * +1 on Google Plus
 */
class TS_Share_Google_PlusOne extends TS_Share_Link {

	function get_classes() {
		return 'share-googleplus';
	}

	function get_code() {
		$out = '<div class="g-plusone" data-size="medium"></div>';

		wp_enqueue_script(
			$this->get_classes(),
			'https://apis.google.com/js/plusone.js',
			array(),
			false,
			true );

		return $out;
	}

}  // TS_Share_Google_PlusOne


/**
 * Mention on Twitter
 */
class TS_Share_Twitter_Mention extends TS_Share_Link {

	function get_classes() {
		return 'share-twitter';
	}

	function get_code() {
		$out = '<a href="https://twitter.com/share" class="twitter-share-button" data-dnt="true">Tweet</a>';
		$src = '!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");';

		ts_enqueue_script($this->get_classes(), $src);
		return $out;
	}

}  // TS_Share_Twitter_Mention


/**
 * Share on LinkedIn
 */
class TS_Share_Linkedin extends TS_Share_Link {

	function get_classes() {
		return 'share-linkedin';
	}

	function get_code() {
		$out = '<script type="IN/Share" data-counter="right"></script>';

		wp_enqueue_script(
			$this->get_classes(),
			'https://platform.linkedin.com/in.js',
			array(),
			false,
			true );

		return $out;
	}

}  // TS_Share_Linkedin

/**
 * Digg Widget
 * @since 1.0.2
 */
class TS_Share_Digg extends TS_Share_Link {

	function get_classes() {
		return 'share-digg';
	}

	function get_code() {
		$out = '<a class="DiggThisButton DiggCompact"></a>';
		$src = '(function() {
	var s = document.createElement(\'SCRIPT\'), s1 = document.getElementsByTagName(\'SCRIPT\')[0];
	s.type = \'text/javascript\';
	s.async = true;
	s.src = \'http://widgets.digg.com/buttons.js\';
	s1.parentNode.insertBefore(s, s1);
	})();';
		ts_enqueue_script($this->get_classes(), $src);
		return $out;
	}

}  // TS_Share_Digg


/* ---------- PLUGIN CODE ------------------------------------------------- */


/**
 * Generate the 'share' content
 *
 * @global boolean $_ts_share_links_done
 * @return string
 */
function _ts_share_links_generate_content() {
	global $_ts_share_links_done;
	if ($_ts_share_links_done) return '';

	$_tmp = apply_filters( 'ts_share_link_objects', array(
	    'TS_Share_Print',
	    'TS_Share_Email',
	    'TS_Share_Facebook_Like',
	    'TS_Share_Google_PlusOne',
	    'TS_Share_Twitter_Mention',
	    'TS_Share_Linkedin',
	    'TS_Share_Digg',
	));

	$objs = array();
	foreach ($_tmp as $name) $objs[$name] = false;

	foreach ($objs as $name => $_x) {
		eval( '$objs[\'' . $name . '\'] = new ' . $name . '();');
	}

	$output = '<div class="share-links">';

	$output .= '<span class="share-label">' . __('Share :') . '</span>';

	foreach ($objs as $name => $obj) {
		$class = apply_filters( 'ts_share_links_content_class', $obj->get_classes() );
		$output .= '<div class="' . $class . '">';
		$output .= $obj->get_code();
		$output .= '</div>';
	}

	$output .= '</div>';

	$_ts_share_links_done = true;
	return $output;
}


function ts_share_links_remove_plugin_filters() {
	remove_filter( 'the_content', 'ts_share_links_content', 77 );
	remove_filter( 'the_excerpt', 'ts_share_links_content', 77 );
}


function ts_share_links_content( $content ) {
	global $wp_query;
	
	if (!is_singular() || !$wp_query->post) {
		ts_share_links_remove_plugin_filters();  // don't bother calling again
		return $content;
	}

	return $content . _ts_share_links_generate_content();
}


function ts_share_links_content_bottom() {
	if (!is_404()) echo _ts_share_links_generate_content();
}


/**
 * Application of Theme Hook Aliance
 * See: https://github.com/zamoose/themehookalliance
 */
add_action( 'tha_content_bottom', 'ts_share_links_content_bottom' );

/**
 * Trigger a the end of eche loop content
 */
add_filter( 'the_content', 'ts_share_links_content', 77 );
add_filter( 'the_excerpt', 'ts_share_links_content', 77 );


/**
 *
 */
if ( ! function_exists('ts_share_links_wp_head') ) :
function ts_share_links_wp_head() {
	wp_enqueue_style( 'ts-share-links', SHARE_LINKS_URL . '/css/share-links' .
		((defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min') .
		'.css' );
}
endif;
add_action( 'wp_enqueue_scripts', 'ts_share_links_wp_head' );


/* eof */
