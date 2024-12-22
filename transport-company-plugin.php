<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Transport_Company
 *
 * @wordpress-plugin
 * Plugin Name:       Transportation company
 * Description:       Plugin responsible for handling transportation companies
 * Version:           1.0.0
 * Text Domain: transportation-company-textdomain
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

use Dotenv\Dotenv;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PLUGIN_NAME_VERSION', '1.0.0');
define('MY_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/transport-company-activator.php
 */
function activate_transport_company()
{
	require_once plugin_dir_path(__FILE__) . 'includes/transport-company-activator.php';
	Transport_Company_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/transport-company-deactivator.php
 */
function deactivate_transport_company()
{
	require_once plugin_dir_path(__FILE__) . 'includes/transport-company-deactivator.php';
	Transport_Company_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_transport_company');
register_deactivation_hook(__FILE__, 'deactivate_transport_company');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/transport-company.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin()
{
	require_once(plugin_dir_path(__FILE__) . '/vendor/autoload.php');


	// Load the .env file
	$dotenv = Dotenv::createImmutable(__DIR__);
	$dotenv->load();

	$plugin = new Transport_Company_Plugin();
	$plugin->run();
}
run_plugin();
