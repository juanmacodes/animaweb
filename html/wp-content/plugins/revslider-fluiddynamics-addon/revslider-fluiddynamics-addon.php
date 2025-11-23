<?php
/*
Plugin Name: Slider Revolution The FluidDynamics AddOn
Plugin URI: http://www.themepunch.com/
Description: Add the fluiddynamics to your website that make your data look awesome.
Author: ThemePunch
Version: 6.7.6
Author URI: http://themepunch.com
*/

// If this file is called directly, abort.
if(!defined('WPINC')) die;

define('RS_FLUIDDYNAMICS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RS_FLUIDDYNAMICS_PLUGIN_URL', str_replace('index.php', '', plugins_url( 'index.php', __FILE__)));

require_once(RS_FLUIDDYNAMICS_PLUGIN_PATH . 'includes/base.class.php');

/**
* handle everyting by calling the following function *
**/
function rs_fluiddynamics_init(){

	new RsTheFluidDynamicsBase();
	
}

/**
* call all needed functions on plugins loaded *
**/
add_action('plugins_loaded', 'rs_fluiddynamics_init');
register_activation_hook( __FILE__, 'rs_fluiddynamics_init');

//build js global var for activation
add_filter( 'revslider_activate_addon', array('RsAddOnTheFluidDynamicsBase','get_data'),10,2);

// get help definitions on-demand.  merges AddOn definitions with core revslider definitions
add_filter( 'revslider_help_directory', array('RsAddOnTheFluidDynamicsBase','get_help'),10,1);

?>