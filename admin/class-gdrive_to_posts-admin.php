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


	private $gdrive;
	private $gclient;

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
        $this->settings_page = new GDrive_To_Posts_Settings();
	}


    /**
     * Create Options menu in dashboard.
     */
	function add_admin_menu() {

		add_menu_page(
            'Google Drive to Posts', 'Google Drive to Posts Menu', 'manage_options',
            'gdrive_to_posts', array( $this->settings_page, 'gdrive_to_posts_options_page')
		);
	}


	/**
	 * Build the settings page.
	 */
	public function settings_init() {
		$gdrive_api_option_group = 'gdriveAPISettings';
		$gdrive_post_api_section = 'gdrive_to_posts_settings';
		$gdrive_post_posts_section = 'gdrive_to_posts_templates';

		add_settings_section(
				$gdrive_post_api_section,
				__( 'Setup GDrive App Settings', 'gdrive_to_posts' ),
				array( $this->settings_page, 'google_settings_section_callback'),
				$gdrive_api_option_group
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
				'service_certificate_fingerprints',
				__( 'Service Account Certificate Fingerprints: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'service_certificate_fingerprints_field'),
				$gdrive_api_option_group,
				$gdrive_post_api_section
		);

		add_settings_field(
				'fetch_interval',
				__( 'Check for new posts every: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'fetch_interval_field'),
				$gdrive_api_option_group,
				$gdrive_post_api_section
		);

		add_settings_field(
				'key_file_location',
				__( 'Key File P12  Location: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'key_file_location_field'),
				$gdrive_api_option_group,
				$gdrive_post_api_section
		);


        /** Posts Templates Settings
         */

		add_settings_section(
				$gdrive_post_posts_section,
				__( 'Define rules for creating new posts', 'gdrive_to_posts' ),
				array( $this->settings_page, 'template_settings_section_callback'),
				$gdrive_api_option_group
		);


		add_settings_field(
				'create_new_template',
				__( 'Create New Template: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'create_new_template_fields'),
				$gdrive_api_option_group,
				$gdrive_post_posts_section
		);


		add_settings_field(
				'templates',
				__( 'Google Sheets to Post Templates: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'templates_fields'),
				$gdrive_api_option_group,
				$gdrive_post_posts_section
		);


		//register_setting( $gdrive_template_option_group, 'gdrive_to_posts_sheet_id' );
		register_setting( $gdrive_api_option_group, 'gdrive_to_posts_templates' );
		register_setting( $gdrive_api_option_group, 'gdrive_to_posts_settings' );


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


    /**
     * Connects to the Google Client and returns the client object to be used
     * when instantiating new Google Service API Objects.
     *
     * @return \Google_Client
     */
    public function gdrive_connection() {

        $options = get_option( 'gdrive_to_posts_settings' );

        if (!is_array($options) || !($gclient = new Google_Client_Handler($options)) ) {
            if (defined('GDRIVE_TO_POSTS_DEBUG') && GDRIVE_TO_POSTS_DEBUG) {
                echo "Missing Google Drive Connection Settings";
            }
            return false;
        }

        $gdrive = $gclient->connect('drive');
        if (!is_a($gdrive, 'Google_Service_Drive')) {
            if (defined('GDRIVE_TO_POSTS_DEBUG') && GDRIVE_TO_POSTS_DEBUG) {
                echo "Couln't connect to Drive";
            }
            return false;
        }

        return $gdrive;
    }



	/**
	 *  Get the fields from the top row of a Google Sheet and return it to the front-end
     *  - this is an ajax action. 'wp_ajax_gdrive_to_posts_fetch_sheet_fields'
	 */
	public function fetch_sheet_fields() {
		// For now I think we will only allow new templates to be added using Ajax
		if (!defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['sheet_label'])) {
			$this->end_ajax();
		}
		if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {
			$this->end_ajax();
		}

		$options_sheet_id = get_option('gdrive_to_posts_sheet_id');
		$sheet_label = esc_attr($_POST['sheet_label']);
		if (!is_string(($sheet_id = $options_sheet_id[$sheet_label]))) {
			$this->end_ajax();
		}

		// Need the google drive connection.
		$gdrive = $this->gdrive_connection();
        // This will parse the csv and make new posts if that's what it should do.
        $workhorse = new GDrive_To_Posts_Workhorse();

        if ($workhorse->get($gdrive, $sheet_id)) {
            // We want to see the output here.
            if ($fields = $workhorse->get_fields()) {
                $this->end_ajax(array('success'=>1, 'fields'=>$fields));
            } else {
                $this->end_ajax(array('success'=>0, 'error'=>'Connected OK, Found the Sheet, No Fields.'));
            }
        }
        else {
            if (!$gdrive) {
                $this->end_ajax(array('success'=>0, 'error'=>"Couldn't connect to GDrive with your credentials."));
            }
            $this->end_ajax(array('success'=>0, 'error'=>"Couldn't get file from GDrive."));
        }

		$this->end_ajax();
	}


    /**
     * Adds new template and sheet file id.
     *  -- Ajax 'wp_ajax_gdrive_to_posts_add_new_template'
     *  -- IF the template already exists then it just changes the sheet file id.
     */
	public function add_new_template() {
		// For now I think we will only allow new templates to be added using Ajax
		if (!defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['new_sheet_id']) || !isset($_POST['new_template_label'])) {
			$this->end_ajax();
		}
		if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {
			$this->end_ajax();
		}

		// The file id is Google Sheet file id and becomes options[n][file_id] = $file_id
		$new_file_id = esc_url($_POST['new_sheet_id'], FILTER_SANITIZE_SPECIAL_CHARS);
		// The file key is just a key to use as a label becomes options[n][label] = $template_label
		$template_label = esc_attr($_POST['new_template_label']);
		$template_label = str_replace(' ', '-', $template_label);

		if (!!$new_file_id && !!$template_label) {
			$options_template = get_option('gdrive_to_posts_templates');
			$options_sheet_id = get_option('gdrive_to_posts_sheet_id', array());


            // Only set the template text on labels that are not yet created.
			if (!isset($options_template[$template_label])) {
                $options_template[$template_label] = 'Use this area to create a new template';
                $response['message'] = 'Created new template!';
			}
            else {$response['message'] = "Updated File ID on label {$template_label}";}

			$options_sheet_id[$template_label] = $new_file_id;
			update_option('gdrive_to_posts_templates', $options_template);
			update_option('gdrive_to_posts_sheet_id', $options_sheet_id);

			$response = array(
					'success' => 1,
					'html' => "<option value='{$template_label}'>{$template_label}</option>",
					'hiddenHTML' => "<input value='' name='gdrive_to_posts_templates[{$template_label}]' id='gdrive_to_posts_templates[{$template_label}]' type='hidden'>"
			);

			$this->end_ajax($response);
		}

		$this->end_ajax();
	}



    /**
     * Test the connection to the google drive service
     *  -- Ajax 'wp_ajax_gdrive_to_posts_test_gclient'
     *
     * @return \Google_Client
     */
    public function test_gclient() {

        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            wp_die("This function should only be called using Ajax.");
        }
        if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {
            $this->end_ajax();
        }

        $resp = array('success' => 0, 'gclient' => 0, 'gdrive' => 0);

        $options = get_option( 'gdrive_to_posts_settings' );
        if (!is_array($options) || !($gclient = new Google_Client_Handler($options)) ) {
            $resp['error'] = "Missing Google Drive Connection Settings";
            $this->end_ajax($resp);
            exit;
        }

        $resp['gclient'] = intval($gclient->OK);
        $gdrive = $gclient->connect('drive');

        if (is_a($gdrive, 'Google_Service_Drive')) {
            $resp['success'] = 1;
            $resp['gdrive'] = 1;
        }

        $this->end_ajax($resp);
        exit;
    }


    /**
     * Test the connection to the google drive service
     *  -- Ajax 'wp_ajax_gdrive_to_posts_test_template'
     *
     * @return \Google_Client
     */
    public function test_template() {

        $resp = array('success'=>0);
        if (!defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['sheet_label'])) {
            $this->end_ajax();
        }
        if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {
            $this->end_ajax();
        }

        $options_sheet_id = get_option('gdrive_to_posts_sheet_id');
        $sheet_label = esc_attr($_POST['sheet_label']);
        if (!is_string(($sheet_id = $options_sheet_id[$sheet_label]))) {
            $resp['error'] = "Sheet ID {$sheet_id} doesn't work!";
            $this->end_ajax();
        }

        // Need the google drive connection.
        $gdrive = $this->gdrive_connection();
        // This will parse the csv and make new posts if that's what it should do.
        $workhorse = new GDrive_To_Posts_Workhorse();

        if ($output = $workhorse->parse_file($gdrive, $sheet_id)) {
            // We want to see the output here.
            $this->end_ajax(array('success'=>0, 'output'=>$output));
        }
        else {
            if (!$gdrive) {
                $this->end_ajax(array('success'=>0, 'error'=>"Couldn't connect to GDrive with your credentials."));
            }
            $this->end_ajax(array('success'=>0, 'error'=>"Couldn't get file from GDrive."));
        }

        $this->end_ajax();
    }
}

