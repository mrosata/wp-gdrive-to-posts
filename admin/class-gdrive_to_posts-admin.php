<?php


namespace gdrive_to_posts;
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Gdrive_to_posts
 * @subpackage Gdrive_to_posts/admin
 * @author     Michael Rosata <mrosata1984@gmail.com>
 */
class Gdrive_to_posts_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	private $google_drive;
	private $google_client;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Setup $this->client
		$this->gdrive_connection_init();
		if (is_object($this->google_client)) {
			// Initialize Google Drive service
			$this->google_drive = new \Google_Service_Drive($this->google_client);
		}
		$this->settings_page = new GDrive_To_Posts_Settings($this->google_drive);

	}


	function add_admin_menu() {

		add_menu_page(
				'Google Drive to Posts', 'Google Drive to Posts Menu', 'manage_options',
				'gdrive_to_posts', array( $this->settings_page, 'gdrive_to_posts_options_page')
		);
	}


	/**
	 * Create a settings page in admin menu
	 */
	public function settings_init() {
		$gdrive_api_option_group = 'gdriveAPISettings';
		$gdrive_post_option_group = 'drivePostsSetting';

		$gdrive_post_api_section = 'gdrive_to_posts_settings';
		$gdrive_post_posts_section = 'gdrive_to_posts_post_templates';

		add_settings_section(
				$gdrive_post_api_section,
				__( 'Setup GDrive App Settings', 'gdrive_to_posts' ),
				array( $this->settings_page, 'google_settings_section_callback'),
				$gdrive_api_option_group
		);

		add_settings_section(
				$gdrive_post_posts_section,
				__( 'Define rules for creating new posts', 'gdrive_to_posts' ),
				array( $this->settings_page, 'posts_settings_section_callback'),
				$gdrive_post_option_group
		);

		add_settings_field(
				'google_api_key',
				__( 'Google Developers API Key: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'google_api_key_field'),
				$gdrive_api_option_group,
				$gdrive_post_api_section
		);

		add_settings_field(
				'service_account_email_address',
				__( 'Service Accounts Email Address: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'service_account_email_address_field'),
				$gdrive_api_option_group,
				$gdrive_post_api_section
		);

		add_settings_field(
				'service_account_certificate_fingerprints',
				__( 'Service Account Certificate Fingerprints: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'service_account_certificate_fingerprints_field'),
				$gdrive_api_option_group,
				$gdrive_post_api_section
		);

		add_settings_field(
				'gdrive_to_posts_interval',
				__( 'How often to post?', 'gdrive_to_posts' ),
				array( $this->settings_page, 'gdrive_to_posts_interval_select_render'),
				$gdrive_api_option_group,
				$gdrive_post_api_section
		);


		add_settings_field(
				'create_new_template',
				__( 'Create New Template: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'create_new_template_fields'),
				$gdrive_post_option_group,
				$gdrive_post_posts_section
		);


		add_settings_field(
				'gdrive_posts_definitions',
				__( 'Post Templates: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'gdrive_posts_definitions_fields'),
				$gdrive_post_option_group,
				$gdrive_post_posts_section
		);


		register_setting( $gdrive_api_option_group, 'gdrive_to_posts_settings' );
		register_setting( $gdrive_post_option_group, 'gdrive_to_posts_settings' );


		/*
		add_settings_field(
				'gdrive_to_posts_checkbox_field_3',
				__( 'Settings field description', 'gdrive_to_posts' ),
				'gdrive_to_posts_checkbox_field_3_render',
				$gdrive_post_option_group,
				$gdrive_post_posts_section
		);

		add_settings_field(
				'gdrive_to_posts_radio_field_4',
				__( 'Settings field description', 'gdrive_to_posts' ),
				'gdrive_to_posts_radio_field_4_render',
				$gdrive_post_option_group,
				$gdrive_post_posts_section
		);
		*/


	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gdrive_to_posts_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gdrive_to_posts_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gdrive_to_posts-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gdrive_to_posts_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gdrive_to_posts_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gdrive_to_posts-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script($this->plugin_name, 'gdriveToPosts', array(
            'nonce' => wp_create_nonce('gdrive_to_posts_add-new-template'),
            'ajaxURL' => admin_url( 'admin-ajax.php' )
        ));

	}

    public function end_ajax($resp = array('success'=>0)) {
        ob_flush();
        echo json_encode($resp);
        wp_die();
    }


    public function get_template() {
        if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['template_index'])) {
            if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {
                $this->end_ajax();
            }
            $template_index = intval($_POST['template_index']);
            $options = $options = get_option('gdrive_to_posts_post_templates');
            if (is_array($options) && isset($options[$template_index])) {
                $response = array(
                    'success' => 1,
                    'template' => $options[$template_index]
                );
                $this->end_ajax($response);
            }
        }
        $this->end_ajax();
    }


	public function add_new_template() {
        // For now I think we will only allow new templates to be added using Ajax
        if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['new_file_id']) && isset($_POST['template_label'])) {
            if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {
                $this->end_ajax();
            }

            // The file id is Google Sheet file id and becomes options[n][file_id] = $file_id
            $new_file_id = filter_var($_POST['new_file_id']);
            // The file key is just a key to use as a label becomes options[n][label] = $template_label
            $template_label = filter_var($_POST['template_label']);

            if ($new_file_id) {
                $options = get_option('gdrive_to_posts_post_templates', array());

                $options[] = array(
                    'label' => $template_label,
                    'sheet_id' => $new_file_id,
                    'template' => ''
                );
                update_option('gdrive_to_posts_post_templates', $options);

                $response = array(
                    'success' => 1,
                    'html' => "<h2>{$template_label}</h2><input type='text' value='{$new_file_id}' name='gdrive_to_posts_post_templates[][sheet_id]'>"
                );
                $this->end_ajax($response);
            }
		}
        $this->end_ajax();
	}


	/** @method gdrive_connection_init
	 *
	 * Connects to the Google Client and returns the client object to be used
	 * when instantiating new Google Service API Objects.
	 *
	 * @return \Google_Client
	 */
	public function gdrive_connection_init() {

		$options = get_option( 'gdrive_to_posts_settings' );

		if (!$this->google_client) {
			$client_email = $options['service_account_email_address'];

			$private_key = file_get_contents(plugin_dir_path( dirname( __FILE__ ) ) . 'mikes map-fc28531dc547.p12');
			$scopes = array(
					'https://www.googleapis.com/auth/sqlservice.admin',
					'https://www.googleapis.com/auth/drive.readonly',
					'https://www.googleapis.com/auth/drive.photos.readonly',
					'https://www.googleapis.com/auth/drive.metadata.readonly',
					'https://www.googleapis.com/auth/drive.metadata',
					'https://www.googleapis.com/auth/drive.file'
			);

			$credentials = new \Google_Auth_AssertionCredentials(
					$client_email,
					$scopes,
					$private_key
			);
			//notasecret
			$client = new \Google_Client();
			$client->setAssertionCredentials($credentials);
			if ($client->getAuth()->isAccessTokenExpired()) {
				$client->getAuth()->refreshTokenWithAssertion();
			}


			$this->google_client = $client;
		}

		return $this->google_client;
	}
}

