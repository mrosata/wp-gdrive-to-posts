<?php

/**
 * Fired during plugin activation
 *
 * @link       http://mindbetweenthelines.com
 * @since      1.0.0
 *
 * @package    Gdrive_to_posts
 * @subpackage Gdrive_to_posts/includes
 */

namespace gdrive_to_posts;
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Gdrive_to_posts
 * @subpackage Gdrive_to_posts/includes
 * @author     Michael Rosata <mrosata1984@gmail.com>
 */
class Gdrive_to_posts_Activator {

	/**
	 * Setup chron jobs and certain options setup.
	 *
	 * Chron jobs will all be setup to start at 12:00 midnight the morning of the day that user installs this plugin
	 * and then depending on user settings their sheets will be fetched and parsed during one of these chron jobs. They
	 * will also have the choice to use constant which will actually just be every 10 minutes and that will setup to
	 * fire as a one time event only when the previous job finishes.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$today_12_am = strtotime(date('Y-m-d'));
		wp_schedule_event($today_12_am, 'hourly', 'gdrive_to_posts_hourly_hook', array('hourly'));
		wp_schedule_event($today_12_am, 'twicedaily', 'gdrive_to_posts_twicedaily_hook', array('twicedaily'));
		wp_schedule_event($today_12_am, 'daily', 'gdrive_to_posts_daily_hook', array('daily'));
	}

}
