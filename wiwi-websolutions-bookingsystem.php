<?php
/**
 * Plugin Name: Wiwi Websolutions Bookingsystem
 * Plugin URI: https://www.wiwi.nl/
 * Description: Plugin that connects to the Wiwi Websolutions Bookingsystem.
 * Author: Wiwi Websolutions
 * Author URI: https://www.wiwi.nl
 * Text Domain: wiwi-websolutions-bookingsystem
 * Domain Path: /languages
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Version: 1.1.0
 * Requires at least: 6.0.1
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    die();
}


//======================================================================
// Load plugin textdomain
//======================================================================
add_action( 'init', 'wiwi_websolutions_bookingsystem_load_textdomain' );
function wiwi_websolutions_bookingsystem_load_textdomain() {
    load_plugin_textdomain( 'wiwi-websolutions-bookingsystem', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}



//======================================================================
// Define static data
//======================================================================
define('WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_VERSION', '1.1.0');
define('WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH', plugin_dir_url(__FILE__));

define('WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_API_KEY', get_option('_wiwi_websolutions_bookingsystem_api_key'));
define('WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_API_BASE_URL', get_option('_wiwi_websolutions_bookingsystem_api_base_url'));



//======================================================================
// Require needed files
//======================================================================
if ( is_admin() ) {
    require 'admin/settings.php';
}
require 'app/general.php';
require 'app/bookingsystem.php';
require 'app/wordpress.php';
require 'api/api.php';



//======================================================================
// Initialize and load Carbon Fields
//======================================================================
add_action( 'after_setup_theme', 'wiwi_websolutions_bookingsystem_crb_load' );
function wiwi_websolutions_bookingsystem_crb_load() {
    require_once( 'vendor/autoload.php' );
    \Carbon_Fields\Carbon_Fields::boot();
}



//======================================================================
// Add styles and scripts to the WordPress Admin area
//======================================================================
function wiwi_websolutions_bookingsystem_add_styles() {
    wp_enqueue_style( 'wiwi-websolutions-bookingsystem-style',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/css/wiwi-websolutions-bookingsystem.css' );
    wp_enqueue_script( 'wiwi-websolutions-bookingsystem-script',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/js/wiwi-websolutions-bookingsystem.js' );

    wp_enqueue_style( 'wiwi-websolutions-bookingsystem-select2',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/plugins/select2/select2.min.css' );
    wp_enqueue_script( 'wiwi-websolutions-bookingsystem-select2',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/plugins/select2/select2.min.js' );
}
add_action( 'admin_enqueue_scripts', 'wiwi_websolutions_bookingsystem_add_styles' );



//======================================================================
// Add styles and scripts to the front-end
//======================================================================
function wiwi_websolutions_bookingsystem_add_styles_front_end() {
    wp_enqueue_style( 'wiwi-websolutions-bookingsystem-fullcalendar',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/plugins/fullcalendar/fullcalendar.min.css' );
    wp_enqueue_script( 'wiwi-websolutions-bookingsystem-fullcalendar',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/plugins/fullcalendar/fullcalendar.min.js' );
    wp_enqueue_script( 'wiwi-websolutions-bookingsystem-fullcalendar-locales',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/plugins/fullcalendar/locales-all.min.js' );

    wp_enqueue_script( 'wiwi-websolutions-bookingsystem-axios',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/plugins/axios/axios.min.js' );

    wp_enqueue_script( 'wiwi-websolutions-bookingsystem-moment',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/plugins/moment/moment.min.js' );

    wp_enqueue_style( 'wiwi-websolutions-bookingsystem-sweetalert2',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/plugins/sweetalert2/sweetalert2.min.css' );
    wp_enqueue_script( 'wiwi-websolutions-bookingsystem-sweetalert2',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/plugins/sweetalert2/sweetalert2.all.min.js' );

    wp_enqueue_script( 'wiwi-websolutions-bookingsystem-front-end-script',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/js/wiwi-websolutions-bookingsystem-font-end.js' );
    wp_enqueue_style( 'wiwi-websolutions-bookingsystem-front-end-style',  WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_PLUGIN_PATH . 'assets/css/wiwi-websolutions-bookingsystem.css' );
}
add_action( 'wp_enqueue_scripts', 'wiwi_websolutions_bookingsystem_add_styles_front_end' );

