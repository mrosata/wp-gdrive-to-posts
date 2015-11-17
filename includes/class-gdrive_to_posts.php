<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://mindbetweenthelines.com
 * @since      1.0.0
 *
 * @package    Gdrive_to_posts
 * @subpackage Gdrive_to_posts/includes
 */

namespace gdrive_to_posts;
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Gdrive_to_posts
 * @subpackage Gdrive_to_posts/includes
 * @author     Michael Rosata <mrosata1984@gmail.com>
 */
class Gdrive_to_posts {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Gdrive_to_posts_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The admin hooks and functionality object
	 * @var Gdrive_to_posts_Admin
	 */
	protected $plugin_admin;

	/**
	 * The public hooks and functionality object
	 * @var Gdrive_to_posts_Admin
	 */
	protected $plugin_public;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'gdrive_to_posts';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();

		$this->define_admin_hooks();
		$this->define_public_hooks();

        $this->define_chron_jobs();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - \Google_Client which connects to and authentics users Google API
	 * - \Google_Service_***** pass these a Google_Client to connect to service like Drive
	 * - GDrive_to_Posts_Workhorse. Actual CSV parser, template and creation of posts
	 * - Google_Client_Handler. Custom class to adapt Google API to plugin options
	 * - Gdrive_to_posts_Loader. Orchestrates the hooks of the plugin.
	 * - Gdrive_to_posts_i18n. Defines internationalization functionality.
	 * - Gdrive_to_posts_Admin. Defines all hooks for the admin area.
	 * - Gdrive_to_posts_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Need to add the Google API Library to the include path.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/google-api-php-client/src/Google/autoload.php';
		/**
		 * The class used to parse the csv files returned from Google Drive
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gdrive_to_posts-google-client-handler.php';
		/**
		 * The class used to parse the csv files returned from Google Drive
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gdrive_to_posts-workhorse.php';
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gdrive_to_posts-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gdrive_to_posts-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/gdrive_to_posts-admin-display.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gdrive_to_posts-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gdrive_to_posts-public.php';

		$this->loader = new Gdrive_to_posts_Loader();

		$this->plugin_admin = new Gdrive_to_posts_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->plugin_public = new Gdrive_to_posts_Public( $this->get_plugin_name(), $this->get_version() );

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Gdrive_to_posts_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Gdrive_to_posts_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = $this->plugin_admin;

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// These are the menus in admin
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_init' );

		$this->loader->add_action( 'wp_ajax_gdrive_to_posts_add_new_template', $plugin_admin, 'add_new_template' );
		$this->loader->add_action( 'wp_ajax_gdrive_to_posts_fetch_sheet_fields', $plugin_admin, 'fetch_sheet_fields' );
		$this->loader->add_action( 'wp_ajax_gdrive_to_posts_test_gclient', $plugin_admin, 'test_gclient' );
		$this->loader->add_action( 'wp_ajax_gdrive_to_posts_parse_through_template', $plugin_admin, 'parse_through_template' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
    {

        $plugin_public = $this->plugin_public;

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

    /**
     * Sets up the hooks to handle the chron jobs
     */
    private function define_chron_jobs() {

		// TODO: Need to move the GDrive functionality out of admin object so that public plugin can share an object with admin and we won't have to be using the admin object here in the define public hooks section.
		// Setup the timing functions.
		$timing_options = get_option('gdrive_to_posts_settings');
		if (is_array($timing_options) && isset($timing_options['fetch_interval'])) {
			// TODO: Maybe in the future the update interval should be template based not account based?
			switch($timing_options['fetch_interval']) {
				case 'hourly':
					$this->loader->add_action( 'gdrive_to_posts_hourly_hook', $this->plugin_admin, 'check_for_new_posts', 20 );
					break;
				case 'twicedaily':
					$this->loader->add_action( 'gdrive_to_posts_twicedaily_hook', $this->plugin_admin, 'check_for_new_posts', 20 );
                    echo "<h2>-------------------------------------------------------------added an 2 time daily hook!</h2>";
                    break;
				case 'daily':
					$this->loader->add_action( 'gdrive_to_posts_daily_hook', $this->plugin_admin, 'check_for_new_posts', 20 );
                    break;
				case 'often':
					// Check if it is already schedualed
					if (!(wp_next_scheduled( 'gdrive_to_posts_often_hook'))) {
						wp_schedule_single_event(strtotime('+ 1 minute'), 'gdrive_to_posts_often_hook');
					}
					$this->loader->add_action( 'gdrive_to_posts_often_hook', $this->plugin_admin, 'check_for_new_posts', 20 );
                    break;
				default:
					break;
			}
		}

	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Gdrive_to_posts_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
