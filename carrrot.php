<?php
/*
Plugin Name: Carrrot
Description: Carrrot is a customer service, combining all instruments for marketing automation, sales and communications for your web app. Goal is to increase first and second sales.
Version: 1.1.0
Author: Carrrot
Author URI: https://www.carrrot.io
Text Domain: carrrot
*/

define( 'CARRROT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CARRROT_PLUGIN_BASE', plugin_basename( __FILE__ ) );
require_once (CARRROT_PLUGIN_DIR . 'includes/main.php');

//Plugin initialization
add_action( 'init', array( 'carrrot', 'init' ) );
//Localization files
load_plugin_textdomain( 'carrrot', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );