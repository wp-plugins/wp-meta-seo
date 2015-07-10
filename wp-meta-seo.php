<?php

/**
 * Plugin Name: WP Meta SEO
 * Plugin URI: http://www.joomunited.com/wordpress-products/wp-meta-seo
 * Description: WP Meta SEO is a plugin for WordPress to fill meta for content, images and main SEO info in a single view.
 * Version: 1.0.4
 * Author: JoomUnited
 * Author URI: http://www.joomunited.com
 * License: GPL2
 */
/**
 * @copyright 2014  Joomunited  ( email : contact _at_ joomunited.com )
 *
 *  Original development of this plugin was kindly funded by Joomunited
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
# error_reporting(E_ALL);
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if (!defined('WPMETASEO_MINIMUM_WP_VERSION'))
    define('WPMETASEO_MINIMUM_WP_VERSION', '3.1');
if (!defined('WPMETASEO_PLUGIN_URL'))
    define('WPMETASEO_PLUGIN_URL', plugin_dir_url(__FILE__));
if (!defined('WPMETASEO_PLUGIN_DIR'))
    define('WPMETASEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
if (!defined('URL'))
    define('URL', get_site_url());

register_activation_hook(__FILE__, array('WpMetaSeo', 'plugin_activation'));
register_deactivation_hook(__FILE__, array('WpMetaSeo', 'plugin_deactivation'));


require_once( WPMETASEO_PLUGIN_DIR . 'inc/class.wp-metaseo.php' );
add_action('init', array('WpMetaSeo', 'init'));

if (is_admin()) {
	require_once( WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-content-list-table.php' );
    require_once( WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-image-list-table.php' );
    require_once( WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-dashboard.php' );
    require_once( WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-admin.php' );

    $GLOBALS['metaseo_admin'] = new MetaSeo_Admin;

    add_filter('wp_prepare_attachment_for_js', array('MetaSeo_Image_List_Table', 'add_more_attachment_sizes_js'), 10, 2);
    add_filter('image_size_names_choose', array('MetaSeo_Image_List_Table', 'add_more_attachment_sizes_choose'), 10, 1);
} else {
	/******** Check again and modify title, meta title, meta description before output ********/
	//add_filter('wp_title', array('WpMetaSeo', 'new_title'), 99);
	add_action('init', 'buffer_start');
	add_action('wp_head', 'buffer_end');
	
	function buffer_start() { ob_start("callback"); }

	function buffer_end() { ob_end_flush(); }
	
	function callback($buffer) {
	  // modify buffer here, and then return the updated code
	  global $wp_query;
	  $meta_title = get_post_meta($wp_query->post->ID, '_metaseo_metatitle', true);
          $meta_title_esc = esc_attr($meta_title_esc);
	  $meta_description = get_post_meta($wp_query->post->ID, '_metaseo_metadesc', true);
          $meta_description_esc = esc_attr($meta_description);
	  $patterns = array(
	  		'_title' => array('#<title>[^<>]+?<\/title>#i', '<title>'.$meta_title.'</title>',
							($meta_title != '' ? true : false) ),
	  		'title' => array(
	  			'#<meta name="title" [^<>]+ ?>#i',
	  			'<meta name="title" content="'. $meta_title_esc .'" />',
	  			($meta_title_esc != '' ? true : false) ),
	 'description' => array(
	 			'#<meta name="description" [^<>]+ ?>#i',
	 			'<meta name="description" content="'. $meta_description_esc .'" />',
	 			($meta_description_esc != '' ? true : false) ),
	  'og:title' => array(
	  			'#<meta property="og:title" [^<>]+ ?>#i',
	  			'<meta name="og:title" content="'. $meta_title_esc .'" />',
	  			($meta_title_esc != '' ? true : false) ),
	'og:description' => array(
				'#<meta property="og:description" [^<>]+ ?>#i',
				'<meta name="og:description" content="'. $meta_description_esc .'" />',
				($meta_description_esc != '' ? true : false) )
	  );
	  
	  //
	  foreach($patterns as $k => $pattern){
	  	 if(preg_match_all($pattern[0], $buffer, $matches)){
		  	$replacement = array();
		  	foreach($matches[0] as $key => $match){
		  		if($key < 1){
		  			$replacement[] = $pattern[2] ? $pattern[1] : $match."\n";
				} else { $replacement[] = ''; }	
		  	}
			
			$buffer = str_ireplace($matches[0], $replacement, $buffer);
		  }
		 else{
		 	$buffer = str_ireplace('</title>', "</title>\n" . $pattern[1], $buffer);
		 }
	  }
	  
	  return $buffer;
	}
	/***********************************************/
}

/******** Check and import meta data from other installed plugins for SEO ********/
/**
 * Handle import of meta data from other installed plugins for SEO
 *
 * @since 1.5.0
 */
function wpmetaseo_aio_yoast_message() {
	//update_option('_aio_import_notice_flag', 0);
	//update_option('_yoast_import_notice_flag', 0);
	$activated = 0;
	// Check if All In One Pack is active
	if(!get_option('_aio_import_notice_flag')){
		if ( is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) {
			add_action( 'admin_notices', 'wpmetaseo_import_aio_meta_notice', 2 );
			$activated++;
		}
		
		if(get_option('_aio_import_notice_flag') === false){
			update_option('_aio_import_notice_flag', 0);
		}
	}
	// Check if Yoast is active
	if(!get_option('_yoast_import_notice_flag', false)){
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			add_action( 'admin_notices', 'wpmetaseo_import_yoast_meta_notice', 3 );
			$activated++;
		}

		if(get_option('_yoast_import_notice_flag') === false){
			update_option('_yoast_import_notice_flag', 0);
		}
	}
	
	
	if($activated === 2 && !get_option('plugin_to_sync_with', false)){
		add_action('admin_notices', create_function('$notImportant', 'echo "<div class=\"error metaseo-import-wrn\"><p>". __("Be careful you installed 2 extensions doing almost the same thing, please deactivate AIOSEO or Yoast in order to work more clearly!", "wp-meta-seo") ."</p></div>";'), 1);
	}
}

add_action( 'admin_init', 'wpmetaseo_aio_yoast_message' );

function wpmetaseo_import_aio_meta_notice(){
	echo '<div class="error metaseo-import-wrn"><p>'. sprintf( __('We have found that you’re using All In One Pack Plugin, WP Meta SEO can import the meta from this plugin, %s', 'wp-meta-seo'), '<a href="#" class="button mseo-import-action" style="position:relative" onclick="importMetaData(this, event)" id="_aio_"><span class="spinner-light"></span>Import now</a> or <a href="#" class="dissmiss-import">dismiss this</a>' ) .'</p></div>';
}

function wpmetaseo_import_yoast_meta_notice(){
	echo '<div class="error metaseo-import-wrn"><p>'. sprintf( __('We have found that you’re using Yoast SEO Plugin, WP Meta SEO can import the meta from this plugin, %s', 'wp-meta-seo'), '<a href="#" class="button mseo-import-action" style="position:relative" onclick="importMetaData(this, event)" id="_yoast_">Import now<span class="spinner-light"></span></a> or <a href="#" class="dissmiss-import">dismiss this</a>' ) .'</p></div>';
}

/**
 * Encode or decode all values in string format of an array
 */
function metaseo_utf8($obj, $action = 'encode'){
	$action = strtolower(trim($action));
	$fn = "utf8_$action";
	if(is_array($obj)){
		foreach($obj as &$el){
			if(is_array($el)){
				if(is_callable($fn)){
					$el = metaseo_utf8($el, $action);
				}
			}
			elseif(is_string($el)){
				//var_dump(mb_detect_encoding($el));
				$isASCII = mb_detect_encoding($el, 'ASCII');
				if($action === 'encode' && !$isASCII){
					$el = mb_convert_encoding($el, "UTF-8", "auto");
				}
				
				$el = $fn($el);
			}
		}
	}elseif (is_object($obj)) {
        $vars = array_keys(get_object_vars($obj));
        foreach ($vars as $var) {
            metaseo_utf8($obj->$var, $action);
        }
    }
	
	return $obj;
}
/**********************************************************************************/
