<?php
/**
 *
 * Plugin Name:       Simple Admin Branding
 * Description:       Lightweight plugin to brand the WordPress login page using the built-in site icon assets.
 * Version:           1.0.2
 * Author:            PorterAI
 * Author URI: 		  https://porterai.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simple-admin-branding
 * Domain Path: 	  /languages
 */

namespace ES_Simple_Admin_Branding;

define('SIMPLE_ADMIN_BRANDING_PLUGIN_PATH', dirname( plugin_basename( __FILE__ ) ) );

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require plugin_dir_path( __FILE__ ) . 'includes/class-simple-admin-branding.php';
function simple_admin_branding() {
	Branding::getInstance();
}
simple_admin_branding();