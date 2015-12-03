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

    const plugin_options = array(
        'gdrive_to_posts_settings',
        'gdrive_to_posts_template_tags',
        'gdrive_to_posts_template_category',
        'gdrive_to_posts_template_author',
        'gdrive_to_posts_template_data',
        'gdrive_to_posts_template_title',
        'gdrive_to_posts_template_body',
        'gdrive_to_posts_template_sheet_id',
        'gdrive_to_posts_template_url_to_links',
        'gdrive_to_posts_template_sheet_id',
        'gdrive_to_posts_csv_last_line',
    );
    /**
     * Deactive plugin settings
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {

        /**
         * Unregister the chron jobs setup at activation.
         */
        wp_clear_scheduled_hook('gdrive_to_posts_hourly_hook');
        wp_clear_scheduled_hook('gdrive_to_posts_twicedaily_hook');
        wp_clear_scheduled_hook('gdrive_to_posts_daily_hook');
        if ( ($timestamp = wp_next_scheduled( time(), 'gdrive_to_posts_often_hook' )) ) {
            // Unschedule an often event if we can.
            wp_unschedule_event( $timestamp, 'gdrive_to_posts_often_hook' );
        }
    }

    public static function uninstall() {
        /**
         * Remove all the options created through the plugin
         */
        foreach(Gdrive_to_posts_Deactivator::plugin_options as $option) {
            delete_option( $option );
        }

        /**
         * Make sure that the chron jobs are off
         */
        Gdrive_to_posts_Deactivator::deactivate();
    }

}
