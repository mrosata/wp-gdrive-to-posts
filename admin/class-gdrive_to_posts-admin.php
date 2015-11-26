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


	public $data_map = array(
		0 => 'drive',
		1 => 'uri'
	);


	private $settings_for_templates = array(
			'data' => 0,
            'schedule' => '',
			'url_to_links' => 1,
			'category' => 0,
			'tags' => '',
			'author' => 1,
			'sheet_id' => '',
			'csv_last_line' => 1,
			'title' => '',
		    'post_status' => '',
			'body' => '',
			'featured_image' => ''
	);


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

		$hook = add_menu_page(
            'Google Drive to Posts', 'Google Drive to Posts Menu', 'manage_options',
            'gdrive_to_posts', array( $this->settings_page, 'gdrive_to_posts_options_page')
		);

	}


	function handle_upload_key_file ( ) {

        // You should also check filesize here.
        if ($_FILES['file-gdrive-to-posts-key']['size'] > 10000) {
            throw new \RuntimeException('Exceeded filesize limit.');
        }

        // On this example, obtain safe unique name from its binary data.
        if (!move_uploaded_file(
            $_FILES['file-gdrive-to-posts-key']['tmp_name'],
            plugin_dir_path(__FILE__) . 'key/gdrive-file-key.p12'
        )) {
            echo json_encode(array('success'=>0));
            exit;
        }
        echo json_encode(array('success'=>1));
        exit;
	}

	/**
	 * Build the settings page.
	 */
	public function settings_init() {
		$gdrive_api_option_group = 'gdriveAPISettings';
		$gdrive_post_api_section = 'gdrive_to_posts_settings';
		$gdrive_post_posts_section = 'gdrive_to_posts_template_body';


		add_settings_section(
				$gdrive_post_api_section,
				__( 'Setup GDrive App Settings', 'gdrive_to_posts' ),
				array( $this->settings_page, 'google_settings_section_callback'),
				$gdrive_api_option_group
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
				__( 'Key File P12  Location: (file extension ".p12")', 'gdrive_to_posts' ),
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



        // The body templates
		add_settings_field(
				'make-templates',
				__( 'Select Template: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'select_a_template'),
				$gdrive_api_option_group,
				$gdrive_post_posts_section
		);


        // The body templates
		add_settings_field(
				'made-templates',
				__( 'Current Google Sheets Templates: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'templates_fields'),
				$gdrive_api_option_group,
				$gdrive_post_posts_section
		);


        // The body templates
        add_settings_field(
            'delete_template',
            '',
            array( $this->settings_page, 'delete_template_button'),
            $gdrive_api_option_group,
            $gdrive_post_posts_section
        );


        // The body templates
		add_settings_field(
				'text-editor',
				__( 'Current Google Sheets Templates: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'template_text_editor'),
				$gdrive_api_option_group,
				$gdrive_post_posts_section
		);


		/**
		 * Even though WP allows for multi arrays in the settings, if we store options that way then
		 * we have to have them all present on the page anytime that the page is reloaded.
		 */
		foreach($this->settings_for_templates as $setting => $default) {
			register_setting( $gdrive_api_option_group, "gdrive_to_posts_template_{$setting}" );
		}
		// This is the setting to change the file for key.
		register_setting( $gdrive_api_option_group, 'gdrive_update_key_file' );
		register_setting( $gdrive_api_option_group, 'gdrive_to_posts_settings' );

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

        $prefix = 'gdrive' . time();
        wp_enqueue_script( $prefix . 'files1', plugin_dir_url( __FILE__ ) . 'js/jQuery-File-Upload/js/vendor/jquery.ui.widget.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $prefix . 'files2', plugin_dir_url( __FILE__ ) . 'js/jQuery-File-Upload/js/jquery.iframe-transport.js', array( $prefix . 'files1' ), $this->version, false );
        wp_enqueue_script( $prefix . 'files3', plugin_dir_url( __FILE__ ) . 'js/jQuery-File-Upload/js/jquery.fileupload.js', array( $prefix . 'files1' ), $this->version, false );

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gdrive_to_posts-admin.js', array( 'jquery', $prefix. 'files3' ), $this->version, false );
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

        $options = get_option( 'gdrive_to_posts_settings', array() );

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
		if (!defined('DOING_AJAX') || !DOING_AJAX || !all_set($_POST, 'sheet_label')) {
			$this->end_ajax();
		}
		if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {
			$this->end_ajax();
		}

		$options_sheet_id = get_option('gdrive_to_posts_template_sheet_id', array());
		$sheet_label = esc_attr($_POST['sheet_label']);
		if (!all_set($options_sheet_id, $sheet_label) || !is_string(($sheet_id = $options_sheet_id[$sheet_label]))) {
			$this->end_ajax(array(
                'success' => 0,
                'error' => "Unable to locate Sheet ID for {$sheet_label}"
            ));
		}


		$stored_treat_as_uri_data = get_option('gdrive_to_posts_template_data', array() );
		$treat_as_uri = isset($stored_treat_as_uri_data[$sheet_label]) ? boolval($stored_treat_as_uri_data[$sheet_label]) : false;
		if (!$treat_as_uri) {
			// Need the google drive connection.
			$gdrive = $this->gdrive_connection();
		} else {
			$gdrive = 'treat_as_uri';
		}

        // This will parse the csv and make new posts if that's what it should do.
        $workhorse = new GDrive_to_Posts_Workhorse();

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
		if (!defined('DOING_AJAX') || !DOING_AJAX || !all_set($_POST, 'new_sheet_id', 'new_template_label')) {
			$this->end_ajax();
		}
		if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {
			$this->end_ajax();
		}

		// The file id is Google Sheet file id and becomes options[n][file_id] = $file_id
		$new_file_id = filter_var($_POST['new_sheet_id']);
		// The file key is just a key to use as a label becomes options[n][label] = $template_label
		$template_label = esc_attr($_POST['new_template_label']);
		$template_label = str_replace(' ', '-', $template_label);

		if (!!($new_file_id) && !!($template_label)) {

			foreach ($this->settings_for_templates as $setting => $default) {
                switch ($setting) {
                    case 'sheet_id':
                        // We need to handle the sheet id without overwriting it.
                        $options_sheet_id = get_option('gdrive_to_posts_template_sheet_id', array());
                        $options_sheet_id[$template_label] = $new_file_id;
                        update_option('gdrive_to_posts_template_sheet_id', $options_sheet_id);
                        break;
                    default:
                        set_option_if_not_set("gdrive_to_posts_template_{$setting}", $template_label, $default);
                        break;
                }
			}

			// Return the settings for Ajax to add into the page.
            $response['success'] = 1;
			$response['html'] = "<option value='{$template_label}'>{$template_label}</option>";
			$response['hiddenHTML'] = $this->settings_page->get_individual_settings( $template_label );

			$this->end_ajax($response);
		}

		$this->end_ajax();
	}


    /**
     * Delete a template and all its data.. permanent!
     */
    function delete_template() {

        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            wp_die("This function should only be called using Ajax.");
        }

        // Get the label used as a key to identify all the data for this template to delete
        $sheet_label = esc_attr($_POST['sheet_label']);
        if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template') || !$sheet_label) {
            $this->end_ajax();
        }

        foreach($this->settings_for_templates as $setting_type => $default_value) {
            if (!($settings = get_option("gdrive_to_posts_template_{$setting_type}"))) {
                continue;
            }
            // If there are settings for this template, even if empty we should delete.
            if (isset($settings[$sheet_label])) {
                unset($settings[$sheet_label]);
                update_option("gdrive_to_posts_template_{$setting_type}", $settings);
            }
        }

        $this->end_ajax(array('success'=>1, 'message' => __("Removed Sheet labeled") . " \"{$sheet_label}\" " . __(" from the system")));
    }




    /**
     * Test the connection to the google drive service
     *  -- Ajax 'wp_ajax_gdrive_to_posts_test_gclient'
     *
     * @return \Google_Client
     */
    public function test_gclient() {

        if (!defined('DOING_AJAX') || !DOING_AJAX || !current_user_can('update_core')) {
            wp_die("This function should only be called using Ajax.");
        }
        if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {

            $this->end_ajax();
        }
        $resp = array('success' => 0, 'gclient' => 0, 'gdrive' => 0);

        $options = get_option( 'gdrive_to_posts_settings', array() );


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
        if (!defined('DOING_AJAX') || !DOING_AJAX || !all_set($_POST, 'sheet_label') || !current_user_can('update_core')) {
            $this->end_ajax();
        }
        if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {
            $this->end_ajax();
        }


		// TODO: This is done 2 times, create a function to pull out these options. Or have the function that consumes them do it
        $sheet_label = esc_attr($_POST['sheet_label']);

        $options = $this->get_options_for_template($sheet_label);
        if (!$options['post_status']) {
            // If we're not going to publish/draft/private then no point
            $this->end_ajax();
        }

        if ( !is_string($options['sheet_id']) || !is_string($options['content_template']) || !$options['stored_last_line']) {
            if (defined('GDRIVE_TO_POSTS_DEBUG') && GDRIVE_TO_POSTS_DEBUG) {
                $resp['error'] = "Sheet ID {$options['sheet_id']} doesn't work!";
            }
            $this->end_ajax();
        }

		// Get the connection or the explicit option to not use GDrive connection.
		if (!$options['treat_as_uri']) {
			// Need the google drive connection.
			$gdrive = $this->gdrive_connection();
		} else {
			$gdrive = 'treat_as_uri';
		}

        // This will parse the csv and make new posts if that's what it should do.
        $workhorse = new GDrive_to_Posts_Workhorse();
        if ($output = $workhorse->parse_file($gdrive, $options, 5)) {
            // We want to see the output here.
            $this->end_ajax(array('success'=>1, 'output'=>$output));
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
     * The function which runs during chron jobs to update any new posts
     * that appear in setup templates by cheking their Google Sheets
     *
     * @param null $specific_templates
     * @throws \Exception
     */
    public function check_for_new_posts($specific_templates=null) {
        // This will parse the csv and make new posts if that's what it should do.
        $workhorse = new GDrive_to_Posts_Workhorse();

        $options_sheet_id = get_option('gdrive_to_posts_template_sheet_id' );
        $sheet_labels = array_keys($options_sheet_id);

        // If passed in list of labels then only parse those, making sure they exist by doing an intersect.
        if (is_array($specific_templates)) {
            $sheet_labels = array_intersect($specific_templates, $sheet_labels);
        }

        foreach($sheet_labels as $sheet_label) {
            $options = $this->get_options_for_template($sheet_label);

            if ($options['post_status'] == '' || $options['active_schedule'] == '') {
                // If we're not going to publish/draft/private then no point
                continue;
            }

			$treat_as_uri = isset($stored_treat_as_uri_data[$sheet_label]) ? boolval($stored_treat_as_uri_data[$sheet_label]) : false;

			// We have to get connection on sheet to sheet basis.
			if (!$treat_as_uri) {
				// Need the google drive connection.
				$gdrive = $this->gdrive_connection();
			} else {
				$gdrive = 'treat_as_uri';
			}


            if (!$options['stored_last_line'] || !$options['sheet_id'] || !is_string($options['content_template'])) {

                if (defined('GDRIVE_TO_POSTS_DEBUG') && GDRIVE_TO_POSTS_DEBUG) {
                    $time = date('Y-m-d H:i:S');
                    error_log("Was unable to check sheet_id {$options['sheet_id']} at {$time}");
                }
                continue;
            }


            $workhorse->parse_file($gdrive, $options);
        }

    }


    /**
     * Gets and prepares all the options for a single template.
     *
     * @param $sheet_label
     * @return array
     */
    private function get_options_for_template($sheet_label){

        $options_sheet_id = get_option('gdrive_to_posts_template_sheet_id' );
        $post_status_opts = get_option( 'gdrive_to_posts_template_post_status' );
        $bodies = get_option('gdrive_to_posts_template_body' );
        $titles = get_option('gdrive_to_posts_template_title' );
        $authors = get_option('gdrive_to_posts_template_author' );
        $tags = get_option('gdrive_to_posts_template_tags' );
        $categories = get_option('gdrive_to_posts_template_category' );
        $last_lines = get_option('gdrive_to_posts_template_csv_last_line' );
        $templates_as_uris = get_option('gdrive_to_posts_template_data' );
        $urls_to_links = get_option('gdrive_to_posts_template_url_to_links' );
        $template_images = get_option('gdrive_to_posts_template_featured_image');
        $template_schedules = get_option('gdrive_to_posts_template_schedule');


        $options = array();
        $options['sheet_label'] = $sheet_label;
        $options['active_schedule'] = (string)$template_schedules[$sheet_label];
        $options['sheet_id'] = (string)$options_sheet_id[$sheet_label];
        $options['post_status'] = (string)$post_status_opts[$sheet_label];
        $options['content_template'] = (string)$bodies[$sheet_label];
        $options['title_template'] = (string)$titles[$sheet_label];
        $options['author'] = intval($authors[$sheet_label]);
        $options['the_tags'] = $tags[$sheet_label];
        $options['category'] = $categories[$sheet_label];
        $options['urls_to_links'] = isset($urls_to_links[$sheet_label]) ? boolval($urls_to_links[$sheet_label]) : true;
        $options['featured_image'] = isset($template_images[$sheet_label]) ? $template_images[$sheet_label] : '';
        $options['stored_last_line'] = intval($last_lines[$sheet_label]);
        $options['treat_as_uri'] = isset($templates_as_uris[$sheet_label]) ? $templates_as_uris[$sheet_label] : '';

        return $options;
    }


}

