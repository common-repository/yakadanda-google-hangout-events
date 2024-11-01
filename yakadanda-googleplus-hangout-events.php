<?php
/**
 * Plugin Name: Yakadanda Google+ Hangout Events
 * Plugin URI: http://www.yakadanda.com/plugins/yakadanda-google-hangout-events/
 * Description: A countdown function to time of the Google+ Hangout Events.
 * Version: 0.3.7
 * Author: Peter Ricci
 * Author URI: http://www.yakadanda.com/
 * Text Domain: yakadanda-google-hangout-events
 * Domain Path: /languages/
 * License: GPL2
 */

/* Put setup procedures to be run when the plugin is activated in the following function */
register_activation_hook(__FILE__, 'googleplushangoutevent_activate');
function googleplushangoutevent_activate() {
  if (!get_option('yakadanda_googleplus_hangout_event_options'))
    add_option('yakadanda_googleplus_hangout_event_options', null, false, false);
  if (!get_option('yakadanda_googleplus_hangout_event_access_token'))
    add_option('yakadanda_googleplus_hangout_event_access_token', null, false, false);
  googleplushangoutevent_event_init();
  flush_rewrite_rules();
}

// On deacativation, clean up anything your component has added.
register_deactivation_hook( __FILE__, 'googleplushangoutevent_deactivate');
function googleplushangoutevent_deactivate() {
	// You might want to delete any options or tables that your component created.

}

if(!defined('GPLUS_HANGOUT_EVENTS_VER')) define('GPLUS_HANGOUT_EVENTS_VER', '0.3.7');
if(!defined('GPLUS_HANGOUT_EVENTS_PLUGIN_DIR')) define('GPLUS_HANGOUT_EVENTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
if(!defined('GPLUS_HANGOUT_EVENTS_PLUGIN_URL')) define('GPLUS_HANGOUT_EVENTS_PLUGIN_URL', plugins_url(null, __FILE__));
if(!defined('GPLUS_HANGOUT_EVENTS_THEME_DIR')) define('GPLUS_HANGOUT_EVENTS_THEME_DIR', get_stylesheet_directory());
if(!defined('GPLUS_HANGOUT_EVENTS_THEME_URL')) define('GPLUS_HANGOUT_EVENTS_THEME_URL', get_stylesheet_directory_uri());

// Load plugin textdomain
add_action( 'plugins_loaded', 'googleplushangoutevent_load_textdomain' );
function googleplushangoutevent_load_textdomain() {
  load_plugin_textdomain('yakadanda-google-hangout-events', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
}

// Store plugin version
if (!get_option('yakadanda_googleplus_hangout_event_version')) add_option('yakadanda_googleplus_hangout_event_version', GPLUS_HANGOUT_EVENTS_VER);

// Upgrade
if (GPLUS_HANGOUT_EVENTS_VER != get_option('yakadanda_googleplus_hangout_event_version')) {
  update_option('yakadanda_googleplus_hangout_event_version', GPLUS_HANGOUT_EVENTS_VER );
  
}

add_filter('plugin_action_links', 'googleplushangoutevent_action_links', 10, 2);
function googleplushangoutevent_action_links($links, $file) {
  static $googleplus_hangout_events;
  
  if (!$googleplus_hangout_events) $googleplus_hangout_events = plugin_basename(__FILE__);
  
  if ($file == $googleplus_hangout_events) {
    $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=googleplus-hangout-events">Settings</a>';
    array_unshift($links, $settings_link);
  }
  
  return $links;
}

// Register javascripts & stylesheets
add_action('init', 'googleplushangoutevent_register');
function googleplushangoutevent_register() {
  /* Styles */
  // Backend
  // Roboto Condensed font
  wp_register_style('roboto-condensed', 'http://fonts.googleapis.com/css?family=Roboto+Condensed:400,300,700', false, GPLUS_HANGOUT_EVENTS_VER, 'all');
  // Yakadanda GooglePlus Hangout Event style
  wp_register_style('googleplushangoutevents-backend-style', GPLUS_HANGOUT_EVENTS_PLUGIN_URL . '/build/css/backend.min.css', array('roboto-condensed'), GPLUS_HANGOUT_EVENTS_VER, 'all');
  
  // Frontend
  // Google web fonts
  if ( $google_fonts = googleplushangoutevent_google_fonts() ) {
    wp_register_style('googleplushangoutevent-google-fonts', 'http://fonts.googleapis.com/css?family=' . $google_fonts, false, GPLUS_HANGOUT_EVENTS_VER, 'all');
  }

  if ( file_exists(GPLUS_HANGOUT_EVENTS_THEME_DIR . '/css/google-hangout-events.css' ) ) {
    wp_register_style('googleplushangoutevents-style', GPLUS_HANGOUT_EVENTS_THEME_URL . '/css/google-hangout-events.css', false, GPLUS_HANGOUT_EVENTS_VER, 'all');
  } else {
    wp_register_style('googleplushangoutevents-style', GPLUS_HANGOUT_EVENTS_PLUGIN_URL . '/build/css/main.min.css', false, GPLUS_HANGOUT_EVENTS_VER, 'all');
  }
  
  /* Scripts */
  // Google+ Hangout Event script
  wp_register_script('googleplushangoutevent-script', GPLUS_HANGOUT_EVENTS_PLUGIN_URL . '/build/js/main.min.js', array('jquery'), GPLUS_HANGOUT_EVENTS_VER, true );
  
  // ajax
  wp_localize_script('googleplushangoutevent-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));
}

// backend scripts
add_action('admin_enqueue_scripts', 'googleplushangoutevent_admin_enqueue_scripts');
function googleplushangoutevent_admin_enqueue_scripts() {
  if (googleplushangoutevent_is_page()) {
    // Call stylesheets
    wp_enqueue_style('googleplushangoutevents-backend-style');
    
    // Call javascripts
    wp_enqueue_script('iris');
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_script('media-upload');
    wp_enqueue_script('googleplushangoutevent-script');
  }
}

// frontend scripts
add_action('wp_enqueue_scripts', 'googleplushangoutevent_wp_enqueue_scripts');
function googleplushangoutevent_wp_enqueue_scripts() {
  // Call javascripts
  wp_enqueue_script('googleplushangoutevent-script');
}
function googleplushangoutevent_wp_enqueue_scripts_load() {
  // Call stylesheets
  $google_fonts = googleplushangoutevent_google_fonts();
  if ($google_fonts) wp_enqueue_style('googleplushangoutevent-google-fonts');
  wp_enqueue_style('googleplushangoutevents-style');
  
  // Call javascripts
  wp_enqueue_script('googleplushangoutevent-script');
}

require_once(dirname( __FILE__ ) . '/lib/includes.php');
