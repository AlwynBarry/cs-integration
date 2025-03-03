<?php

namespace amb_dev\CSI;


/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin admin area.
 * This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions,
 * and defines a function that starts the plugin.
 *
 * @link              https://github.com/AlwynBarry
 * @since             1.0.0
 * @package           Cs_Integration
 *
 * @wordpress-plugin
 * Plugin Name:       Integration for ChurchSuite
 * Plugin URI:        https://github.com/AlwynBarry/cs-integration
 * Description:       CS Integration provides shortcodes to request and display JSON data from the public JSON ChurchSuite feeds.
 * Version:           1.0.2
 * Author:            Alwyn Barry
 * Author URI:        https://github.com/AlwynBarry/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cs-integration
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The current plugin version.
 * Started at version 1.0.0 and uses SemVer - https://semver.org
 * This will be updated it as we release new versions.
 */
define( 'CS_INTEGRATION_VERSION', '1.0.2' );

/**
 * Run the plugin activation. For this plugin this does nothing ... no activation required
 */
function activate_cs_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cs-integration-activator.php';
	\amb_dev\CSI\CS_Integration_Activator::activate();
}

/**
 * Run the plugin deactivation. For this plugin this does nothing ... no deactivation required
 */
function deactivate_cs_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cs-integration-deactivator.php';
	\amb_dev\CSI\Cs_Integration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate_cs_integration' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivate_cs_integration' );


/**
 * Import the core class that runs the plugin
 */
 
require_once plugin_dir_path( __FILE__ ) . 'includes/class-cs-integration.php';
use \amb_dev\CSI\Cs_Integration as Cs_Integration;


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cs_integration() {

	$plugin = new Cs_Integration();
	$plugin->run();

}
run_cs_integration();
