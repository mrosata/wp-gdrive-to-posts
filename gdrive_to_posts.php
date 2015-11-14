<?php

/**
 *
 * @link              http://mindbetweenthelines.com
 * @since             1.0.0
 * @package           Gdrive_to_posts
 *
 * @wordpress-plugin
 * Plugin Name:       google drive to posts
 * Plugin URI:        http://mindbetweenthelines.com
 * Description:       This plugin will allow you to watch a file on your google drive and when changes are added they
 * 					  will be compared against settings in this app to check if they should become a post or not. This
 *                    means you could have 1 file with posts for multiple pages where some posts are shared on both
 *                    sites and some are not.
 * Version:           1.0.0
 * Author:            Michael Rosata
 * Author URI:        http://mindbetweenthelines.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gdrive_to_posts
 * Domain Path:       /languages
 */

namespace gdrive_to_posts;
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gdrive_to_posts-activator.php
 */
function activate_gdrive_to_posts() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gdrive_to_posts-activator.php';
	Gdrive_to_posts_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gdrive_to_posts-deactivator.php
 */
function deactivate_gdrive_to_posts() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gdrive_to_posts-deactivator.php';
	Gdrive_to_posts_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gdrive_to_posts' );
register_deactivation_hook( __FILE__, 'deactivate_gdrive_to_posts' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gdrive_to_posts.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gdrive_to_posts() {

	$plugin = new Gdrive_to_posts();
	$plugin->run();

}
run_gdrive_to_posts();
