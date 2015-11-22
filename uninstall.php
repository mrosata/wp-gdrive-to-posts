<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://mindbetweenthelines.com
 * @since      1.0.0
 *
 * @package    Gdrive_to_posts
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	if (!class_exists(\gdrive_to_posts\Gdrive_to_posts_Deactivator::class)) {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gdrive_to_posts-deactivator.php';
	}
	\gdrive_to_posts\Gdrive_to_posts_Deactivator::uninstall();
	exit;
}
