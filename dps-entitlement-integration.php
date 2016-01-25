<?php
/*
Plugin Name: DPS Direct Entitlement Integration for WordPress
Plugin URI: http://github.com/sc0ttkclark/dps-entitlement-integration
Description: This plugin integrates an Adobe DPS Direct Entitlement Server into WordPress and lets people log in with their WordPress credentials
Version: 0.1
Author: Scott Kingsley Clark
Author URI: http://scottkclark.com/
*/

/**
 * @package DPS\Entitlements
 */
namespace DPS\Entitlements;

define( 'DPS_ENTITLEMENTS_URL', plugin_dir_url( __FILE__ ) );
define( 'DPS_ENTITLEMENTS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Initialize and load plugin
 */
function init() {

	require_once DPS_ENTITLEMENTS_DIR . 'classes/Singleton.php';
	require_once DPS_ENTITLEMENTS_DIR . 'classes/Plugin.php';

	Plugin::get_instance();

}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );