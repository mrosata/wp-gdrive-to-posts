<?php

namespace gdrive_to_posts;
/**
 * Fired during plugin deactivation
 *
 * @link       http://mindbetweenthelines.com
 * @since      1.0.0
 *
 * @package    Gdrive_to_posts
 * @subpackage Gdrive_to_posts/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Gdrive_to_posts
 * @subpackage Gdrive_to_posts/includes
 * @author     Michael Rosata <mrosata1984@gmail.com>
 */
class Gdrive_to_posts_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		/**
		 * Remove all the options created through the plugin
		 */
		delete_option( 'gdrive_to_posts_template_tags' );
		delete_option( 'gdrive_to_posts_template_category' );
		delete_option( 'gdrive_to_posts_template_author' );
		delete_option( 'gdrive_to_posts_template_data' );
		delete_option( 'gdrive_to_posts_template_title' );
		delete_option( 'gdrive_to_posts_template_body' );
		delete_option( 'gdrive_to_posts_template_sheet_id' );
		delete_option( 'gdrive_to_posts_settings' );

		/**
		 * Unregister the chron jobs setup at activation.
		 */
		wp_clear_scheduled_hook('gdrive_to_posts_hourly_hook');
		wp_clear_scheduled_hook('gdrive_to_posts_twicedaily_hook');
		wp_clear_scheduled_hook('gdrive_to_posts_daily_hook');
		if ( ($timestamp = wp_next_scheduled( time(), 'gdrive_to_posts_daily_hook' )) ) {
			// Unschedule an often event if we can.
			wp_unschedule_event( $timestamp, 'gdrive_to_posts_daily_hook' );
		}
	}

}
