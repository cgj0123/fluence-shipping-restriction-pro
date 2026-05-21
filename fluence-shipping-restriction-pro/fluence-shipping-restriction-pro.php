<?php
/**
 * Plugin Name: Fluence Shipping Restriction Pro
 * Description: Advanced shipping restriction plugin — product & category restrictions, multi-country blocking, UI rules builder, checkout blocking, and GeoIP fallback.
 * Version: 1.2.0
 * Author: kim
 */

if ( ! defined('ABSPATH') ) exit;

define('FSRP_PATH', plugin_dir_path(__FILE__));
define('FSRP_URL', plugin_dir_url(__FILE__));

require_once FSRP_PATH.'includes/class-fsrp-rules.php';
require_once FSRP_PATH.'includes/class-fsrp-admin.php';
require_once FSRP_PATH.'includes/class-fsrp-frontend.php';

add_action('plugins_loaded', function(){
    new FSRP_Admin();
    new FSRP_Frontend();
});
