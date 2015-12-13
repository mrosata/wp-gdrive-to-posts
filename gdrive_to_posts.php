<?php

/**
 *
 * @link              http://mindbetweenthelines.com
 * @since             1.1.0
 * @package           Gdrive_to_posts
 *
 * @wordpress-plugin
 * Plugin Name:       google drive to posts
 * Plugin URI:        http://mindbetweenthelines.com
 * Description:       This plugin will allow you to watch a file on your google drive and when changes are added they
 * 					  will be compared against settings in this app to check if they should become a post or not. This
 *                    means you could have 1 file with posts for multiple pages where some posts are shared on both
 *                    sites and some are not.
 * Version:           1.1.0
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

if (!defined('GDRIVE_TO_POSTS_DEBUG')) {
	define('GDRIVE_TO_POSTS_DEBUG', true);
}
if (!defined('__GDRIVE_TO_POSTS_DIR__')) {
	$root_plugin_file = __FILE__;
	define('__GDRIVE_TO_POSTS_ROOT__', $root_plugin_file);
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


/**
 * Try to set the options if not already set.
 *
 * @param $option_namespace
 * @param $option_key
 * @param string $option_default
 * @return bool
 */
function set_option_if_not_set( $option_namespace, $option_key, $option_default = '' ) {

	if (!($options = get_option( $option_namespace ) )) {
		$options = array();
	}
	if (isset($options[$option_key]) && !empty($options[$option_key])) {

		return true;
	}
	$options[$option_key] = $option_default;

	return update_option($option_namespace, $options);
}


function all_set($array, $fields) {
	$fields = func_get_args();
	array_shift($fields);
	foreach($fields as $field) {
		if (!isset($array[$field]) || empty($array[$field])) {
			return false;
		}
	}
	return true;
}


/**
 * This is a class that just logs debug messages for my gdrive_to_posts plugin and I plan
 * to use it and extend it in upcomming projects. It's an easy way to store and view a 
 * limited set of debug messages and provides method to print out those messages in html.
 * 
 * Class Debug_abug
 * @package gdrive_to_posts
 */
class Debug_abug {

	static $max_logs = 15;

	/**
	 * Store debug messages as an option so that we can view them visually in the options
	 * page. This should only run if GDRIVE_TO_POSTS_DEBUG is set to true.
	 *
	 * @param string $message - Message to be logged into the debugger
	 * @param mixed $return_value Pass in optional value to return, you could use the log
	 *                            method in place of a value and have that same value 
	 *                            returned back to the function which logs a debug message.
	 * @return bool
	 */
	static function log($message, $return_value=true) {
		if (!defined('GDRIVE_TO_POSTS_DEBUG') || !GDRIVE_TO_POSTS_DEBUG) {
			return $return_value;
		}
		$debug_info = get_option('gdrive_to_posts-debugger');
		$debug_info = is_array($debug_info) ? $debug_info : array();
		$bug_logged_on = date('Y-m-d H:i:s');
		$debug_info[] =  "{$bug_logged_on}: {$message}";

		while (count($debug_info) > Debug_abug::$max_logs) {
			array_shift($debug_info);
		}

		// Adding new log also has the effect of trimming the fat.
		update_option('gdrive_to_posts-debugger', $debug_info);
		
		return $return_value;
	}


	/**
	 * Get the markup to display the Debug-A-Bug console.
	 *
	 * @param bool|true $echo
	 *
	 * @return string
	 */
	static function console($echo=true) {
		if (!defined('GDRIVE_TO_POSTS_DEBUG') || !GDRIVE_TO_POSTS_DEBUG) {
			// return empty because debug is off.
			return '';
		}

		$debug_info = get_option('gdrive_to_posts-debugger', array());
		// Return false because something is wrong if $debug info isn't array.
		if (!is_array($debug_info)) return false;
		
		$debug_info_log = '<code>' . implode("</code><br>\n<code>", $debug_info). '</code>';

		$debug_console = <<<DOCSTRING
		<section class="gdrive-to-posts-options_debugger">
            <div id="gdrive-to-posts_debugger-console">
                $debug_info_log
			</div>
		</section>
DOCSTRING;

		if ($echo) {
			echo $debug_console;
		}
		return $debug_console;
	}

}

run_gdrive_to_posts();
