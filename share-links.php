<?php
/*
 * Plugin Name: TS Share Links Plugin
 * Plugin URI: http://tecsmith.com.au
 * Description: Add share buttons to the bottom of your content
 * Author: Vino Rodrigues
 * Version: 1.0.3
 * Author URI: http://vinorodrigues.com
 *
 * @author Vino Rodrigues
 * @package TS-Share-Links
 * @since TS-Share-Links 0.9
**/


if (!defined('SHARE_LINKS_URL'))
	define( 'SHARE_LINKS_URL', str_replace( ' ', '%20', plugins_url( '', __FILE__ ) ) );


global $_ts_share_links_done;
$_ts_share_links_done = false;


/* ---------- CLASSES ----------------------------------------------------- */

/**
 * Base definition of TS_Share_Link class
 */
class TS_Share_Link {

	function get_name() { return ''; }

	function get_code() { return ''; }

	static function inject_js($name, $src) {
		if (function_exists('ts_enqueue_script')) {
			ts_enqueue_script( $name, $src );
			return '';
		} else {
			return '<script type="text/javascript">' .
				$src . '</script>';
		}
	}

	static function get_url() {
		global $wp;
		return home_url(add_query_arg(array(),$wp->request));
	}
}


/* ..... Print ............................................................ */

/**
 * Print this page
 */
class TS_Share_Print extends TS_Share_Link {

	function get_name() {
		return 'share-print';
	}

	function get_code() {
		$out = '<a class="btn btn-print" onclick="window.print();"><img src="' . SHARE_LINKS_URL . '/img/print.png" alt="' . __('Print') . '" /> ' . __('Print') . '</a>';
		return $out;
	}

}


/* ..... Email ............................................................ */

/**
 * Email this page
 */
class TS_Share_Email extends TS_Share_Link {

	function get_name() {
		return 'share-email';
	}

	function get_code() {
		$subject = str_replace(' ', '%20', get_bloginfo('name'));
		$body = str_replace('+', '%20', urlencode( $this->get_url() ) );
		$out = '<a class="btn btn-email" href="mailto:?to=&subject=' . $subject . '&body=' . $body . '"><img src="' . SHARE_LINKS_URL . '/img/email.png" alt="' . __('Email') . '" /> ' . __('Email') . '</a>';
		return $out;
	}
}


/* ..... Facebook ......................................................... */

/**
 * Like on Facebook
 *
 * @see: https://developers.facebook.com/docs/plugins/like-button
 */
class TS_Share_Facebook_Like extends TS_Share_Link {

	function get_name() {
		return 'share-facebook';
	}

	function get_code() {
		ob_start();
?>
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.6";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
<?php
		$src = ob_get_clean();

		ob_start();
?>

<!-- Load Facebook SDK for JavaScript -->
<div id="fb-root"></div>
<?= $this->inject_js( $this->get_name(), $src ) ?>

<!-- Your like button code -->
<div class="fb-like"
  data-href="<?= $url ?>"
  data-layout="button_count"
  data-action="like"+
  data-show-faces="false"
  data-share="true"></div>
<?php
		$out = ob_get_clean();
		return $out;
	}

}  // TS_Share_Facebook_Like


/* ..... Google + ......................................................... */

/**
 * +1 on Google Plus
 *
 * @see: https://developers.google.com/+/web/+1button/
 */
class TS_Share_Google_PlusOne extends TS_Share_Link {

	function get_name() {
		return 'share-googleplus';
	}

	function get_code() {
		ob_start();
?>
(function() {
  var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
  po.src = 'https://apis.google.com/js/platform.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
<?php
	$src = ob_get_clean();

	ob_start();
?>
<!-- Google +1 button render. -->
<div class="g-plusone" data-size="medium" data-href="http://www.www.com"></div>

<!-- Load Google +1 JavaScript -->
<?= $this->inject_js( $this->get_name(), $src ); ?>
<?php
		$out = ob_get_clean();
		return $out;
	}

}  // TS_Share_Google_PlusOne


/* ..... Twitter .......................................................... */

/**
 * Mention on Twitter
 *
 * @see: https://about.twitter.com/resources/buttons#tweet
 */
class TS_Share_Twitter_Mention extends TS_Share_Link {

	function get_name() {
		return 'share-twitter';
	}

	function get_code() {
		$out = '<a href="https://twitter.com/share"' .
			' class="twitter-share-button" data-url="' .
			$this->get_url() . '">Tweet</a>';

		$src = '!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],' .
			'p=/^http:/.test(d.location)?"http":"https";' .
			'if(!d.getElementById(id)){js=d.createElement(s);' .
			'js.id=id;js.src=p+"://platform.twitter.com/widgets.js";' .
			'fjs.parentNode.insertBefore(js,fjs);}}' .
			'(document, "script", "twitter-wjs");';

		return $out . $this->inject_js( $this->get_name(), $src );
	}

}  // TS_Share_Twitter_Mention


/* ..... LinkedIn ......................................................... */

/**
 * Share on LinkedIn
 *
 * @see: https://developer.linkedin.com/plugins/share
 */
class TS_Share_Linkedin extends TS_Share_Link {

	function get_name() {
		return 'share-linkedin';
	}

	function get_code() {
		ob_start();
?>
(function() {
  var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
  po.src = 'https://platform.linkedin.com/in.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
<?php
		$src = ob_get_clean();

		$out = '<script type="IN/Share" data-url="' .
			$this->get_url() . '" data-counter="right"></script>';

		return $out . $this->inject_js( $this->get_name(), $src );
	}

}  // TS_Share_Linkedin


/* ---------- PLUGIN CODE ------------------------------------------------- */

/**
 * Generate the 'share' content
 *
 * @global boolean $_ts_share_links_done
 * @return string
 */
function _ts_share_links_generate_content() {
	global $_ts_share_links_done;
	if ($_ts_share_links_done) return '';  // already shared once

	$_ts_share_links_done = true;

	$_tmp = apply_filters( 'ts_share_link_objects', array(
		'TS_Share_Email',
		'TS_Share_Print',
		'TS_Share_Twitter_Mention',
		'TS_Share_Facebook_Like',
		'TS_Share_Linkedin',
		'TS_Share_Google_PlusOne',
		));

	$objs = array();
	foreach ($_tmp as $name) $objs[$name] = false;

	foreach ($objs as $name => $_x) {
		eval( '$objs[\'' . $name . '\'] = new ' . $name . '();');
	}

	$output = '<div class="share-links">';

	$output .= '<div class="share-lbl">' . __('Share:') . '</div>';

	foreach ($objs as $name => $obj) {
		$class = apply_filters( 'ts_share_links_content_class', $obj->get_name() );
		$output .= '<div class="' . $class . '">';
		$output .= $obj->get_code();
		$output .= '</div>';
	}

	$output .= '</div>';

	return $output;
}


/* ..... zamoose/themehookalliance ........................................ */

/**
 * Application of Theme Hook Aliance
 * @see: https://github.com/zamoose/themehookalliance
 */
if (function_exists('tha_content_bottom')) :

function ts_share_links_content_bottom() {
	if (!is_404()) echo _ts_share_links_generate_content();
}

add_action( 'tha_content_bottom', 'ts_share_links_content_bottom' );

endif;  // function_exists('tha_content_bottom')


/* ..... Content Hooks .................................................... */

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

/**
 * Trigger a the end of eche loop content
 */
add_filter( 'the_content', 'ts_share_links_content', 77 );
add_filter( 'the_excerpt', 'ts_share_links_content', 77 );


/* ..... Enqueue plugin CSS ............................................... */

/**
 *
 */
function ts_share_links_wp_head() {
	wp_enqueue_style( 'ts-share-links', SHARE_LINKS_URL .
		'/css/share-links.css' );
}

add_action( 'wp_enqueue_scripts', 'ts_share_links_wp_head' );


/* ..... eof .............................................................. */
