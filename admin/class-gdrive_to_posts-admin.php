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
	private $settings_for_templates = array(
			'category' => 0,
			'tags' => '',
			'author' => 1,
			'data' => '',
			'sheet_id' => '',
			'csv_last_line' => 1,
			'title' => '',
			'body' => ''
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
		$gdrive_post_posts_section = 'gdrive_to_posts_template_body';

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



        // The body templates
		add_settings_field(
				'templates',
				__( 'Google Sheets to Post Templates: ', 'gdrive_to_posts' ),
				array( $this->settings_page, 'templates_fields'),
				$gdrive_api_option_group,
				$gdrive_post_posts_section
		);


		//register_setting( $gdrive_api_option_group, 'gdrive_to_posts_template_sheet_id' );
		//register_setting( $gdrive_api_option_group, 'gdrive_to_posts_csv_last_line' );

		/**
		 * Even though WP allows for multi arrays in the settings, if we store options that way then
		 * we have to have them all present on the page anytime that the page is reloaded.
		 */
		foreach($this->settings_for_templates as $setting => $default) {
			register_setting( $gdrive_api_option_group, "gdrive_to_posts_template_{$setting}" );
		}

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
		if (!defined('DOING_AJAX') || !DOING_AJAX || !all_set($_POST['sheet_label'])) {
			$this->end_ajax();
		}
		if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {
			$this->end_ajax();
		}

		$options_sheet_id = get_option('gdrive_to_posts_template_sheet_id', array());
		$sheet_label = esc_attr($_POST['sheet_label']);
		if (!all_set($options_sheet_id[$sheet_label]) || !is_string(($sheet_id = $options_sheet_id[$sheet_label]))) {
			$this->end_ajax(array(
                'success' => 0,
                'error' => "Unable to locate Sheet ID for {$sheet_label}"
            ));
		}

		// Need the google drive connection.
		$gdrive = $this->gdrive_connection();
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
		if (!defined('DOING_AJAX') || !DOING_AJAX || !all_set($_POST['new_sheet_id'], $_POST['new_template_label'])) {
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

		if (!!$new_file_id && !!$template_label) {

            $response = array(
                'html' => "<option value='{$template_label}'>{$template_label}</option>",
                'hiddenHTML' => "<input value='' name='gdrive_to_posts_template_body[{$template_label}]' id='gdrive_to_posts_template_body[{$template_label}]' type='hidden'>"
            );

			//$options_template = get_option('gdrive_to_posts_template_body');
			$options_sheet_id = get_option('gdrive_to_posts_template_sheet_id', array());

			$options_sheet_id[$template_label] = $new_file_id;
			update_option('gdrive_to_posts_template_sheet_id', $options_sheet_id);
		    $additional_opts = array();
			foreach ($this->settings_for_templates as $setting => $default) {
				$additional_opts[$setting] = get_option( "gdrive_to_posts_template_{$setting}", array() );
				if ( !all_set( $additional_opts[$setting][$template_label]) ) {
					$additional_opts[$setting][ $template_label ] = $default;
					update_option( "gdrive_to_posts_template_{$setting}", $additional_opts[$setting] );
				} else {
                    $response = array();
                }
			}

            $response['success'] = 1;

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
        if (!defined('DOING_AJAX') || !DOING_AJAX || !all_set($_POST['sheet_label']) || !current_user_can('update_core')) {
            $this->end_ajax();
        }
        if (!wp_verify_nonce($_POST['nonce'], 'gdrive_to_posts_add-new-template')) {
            $this->end_ajax();
        }


        $sheet_label = esc_attr($_POST['sheet_label']);
        $options_sheet_id = get_option('gdrive_to_posts_template_sheet_id' );
        $stored_templates = get_option('gdrive_to_posts_template_body' );
        $title_templates = get_option('gdrive_to_posts_template_title' );
        $author_templates = get_option('gdrive_to_posts_template_author' );
        $tags_templates = get_option('gdrive_to_posts_template_tags' );
        $category_templates = get_option('gdrive_to_posts_template_category' );
        $stored_last_lines = get_option('gdrive_to_posts_template_csv_last_line' );


        $sheet_id = $options_sheet_id[$sheet_label];
        $template = $stored_templates[$sheet_label];
        $title_template = $title_templates[$sheet_label];
        $author = $author_templates[$sheet_label];
        $tags = $tags_templates[$sheet_label];
        $category = $category_templates[$sheet_label];
        $last_line = $stored_last_lines[$sheet_label];

        if ( !is_string($sheet_id) || !is_string($template) || !is_string($title_template) || !all_set($last_line)) {
            $resp['error'] = "Sheet ID {$sheet_id} doesn't work!";
            $this->end_ajax();
        }

        // Need the google drive connection.
        $gdrive = $this->gdrive_connection();
        // This will parse the csv and make new posts if that's what it should do.
        $workhorse = new GDrive_to_Posts_Workhorse();

        if ($output = $workhorse->parse_file($gdrive, $sheet_id, $template, $title_template, $author, $tags, $category, $last_line, 5)) {
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


    public function check_for_new_posts() {
        // Need the google drive connection.
        $gdrive = $this->gdrive_connection();
        // This will parse the csv and make new posts if that's what it should do.
        $workhorse = new GDrive_to_Posts_Workhorse();

        $options_sheet_id = get_option('gdrive_to_posts_template_sheet_id' );
        $bodies = get_option('gdrive_to_posts_template_body' );
        $titles = get_option('gdrive_to_posts_template_title' );
        $authors = get_option('gdrive_to_posts_template_author' );
        $tags = get_option('gdrive_to_posts_template_tags' );
        $categories = get_option('gdrive_to_posts_template_category' );
        $last_lines = get_option('gdrive_to_posts_template_last_line' );


        $sheet_labels = array_keys($options_sheet_id);

        foreach($sheet_labels as $sheet_label) {
            $author = intval($authors[$sheet_label]);
            $the_tags = $tags[$sheet_label];
            $category = $categories[$sheet_label];
            $last_line = intval($last_lines[$sheet_label]);


            if ($last_line > 0 && !is_string(($sheet_id = $options_sheet_id[$sheet_label])) || !is_string(($template = $bodies[$sheet_label])) || !is_string($title_template = $titles[$sheet_label])) {
                if (defined('GDRIVE_TO_POSTS_DEBUG') && GDRIVE_TO_POSTS_DEBUG) {
                    $time = data('Y-m-d H:i:S');
                    error_log("Was unable to check sheet_id $sheet_id at {$time}");
                }
                continue;
            }

            if ($output = $workhorse->parse_file($gdrive, $sheet_id, $template, $title_template, $author, $the_tags, $category, $last_line)) {
                // We want to see the output here.
                if (defined('GDRIVE_TO_POSTS_DEBUG') && GDRIVE_TO_POSTS_DEBUG) {
                    echo $output;
                }
            }
        }

    }


}

