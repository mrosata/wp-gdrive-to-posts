<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://mindbetweenthelines.com
 * @since      1.0.0
 *
 * @package    Gdrive_to_posts
 * @subpackage Gdrive_to_posts/admin/partials
 */




namespace gdrive_to_posts;


class GDrive_To_Posts_Settings {

    private $google_drive;

    public function __construct($google_drive = false)
    {
        $this->google_drive = $google_drive;
    }

    public function google_settings_section_callback(  ) {

        echo __( 'Create a GDrive App using the google developers console and then add the credentials here to allow the'
            . ' plugin access to your Google Drive Files. It should only need a read access as the plugin does not write'
            . ' or edit any files on your Google Drive. To setup an app, head on over to the <a target="_blank"'
            . ' href="https://console.developers.google.com/apis/credentials">Google Developers Console</a>', 'gdrive_to_posts' );

    }


    public function template_settings_section_callback() {

        echo __( 'In this section you should tell the plugin how to convert the rows in your Google Sheet file into a post.'
            . ' When trying to find the ID of a Google Sheet file remember that the id can be found in the URL between a '
            . ' a set of backslashes. The URL will look like docs.google.com/spreadsheets/d/<em class="underlined">this-is-the-id_39420d-w</em>/edit#gid=0'
            . ' and so "this-is-the-id_39420d-w" would be your file\'s ID. After adding a file and label you will be '
            . ' able to edit your post template. When you select a template from the drop-down menu the plugin will search '
            . ' your Google Drive for the Sheet. The plugin needs to use your Sheet file so it can create template variables '
            . ' from the first row of the sheet. The first row columns are used as keys to place column values in your template.'
            . ' Not every row has to become a post, you can define categories and tags as well.', 'gdrive_to_posts' );

    }

    // This file should primarily consist of HTML with a little bit of PHP. -->
    public function google_api_key_field(  ) {

        $options = get_option( 'gdrive_to_posts_settings' );

        if (!isset($options['google_api_key'])) {
            $options['google_api_key'] = '';
            update_option('gdrive_to_posts_settings', $options);
        }

        ?>
        <div class="container">
        <div class="row">
        <div class="col-xs-12 col-sm-10 col-md-5 col-lg-4">
            <label for="gdrive_to_posts_settings[google_api_key]">
        <input type='text' id='gdrive_to_posts_settings[google_api_key]' name='gdrive_to_posts_settings[google_api_key]'
               value='<?php echo $options['google_api_key']; ?>'>
            </label>
        </div>
        </div>
        </div>
        <?php

    }


    public function service_account_email_address_field(  ) {

        $options = get_option( 'gdrive_to_posts_settings' );

        if (!isset($options['service_account_email_address'])) {
            $options['service_account_email_address'] = '';
            update_option('gdrive_to_posts_settings', $options);
        }

        ?>

        <div class="container">
        <div class="row">
        <div class="col-xs-12 col-sm-10 col-md-5 col-lg-4">
        <input type='text' name='gdrive_to_posts_settings[service_account_email_address]' value='<?php echo $options['service_account_email_address']; ?>'>
        </div>
        </div>
        </div>
        <?php

    }


    public function service_certificate_fingerprints_field(  ) {

        $options = get_option( 'gdrive_to_posts_settings' );

        if (!isset($options['service_certificate_fingerprints'])) {
            $options['service_certificate_fingerprints'] = '';
            update_option('gdrive_to_posts_settings', $options);
        }

        ?>
        <div class="container">
        <div class="row">
        <div class="col-xs-12 col-sm-10 col-md-5 col-lg-4">
        <input type='text' name='gdrive_to_posts_settings[service_certificate_fingerprints]' value='<?php echo $options['service_certificate_fingerprints']; ?>'>
        </div>
        </div>
        </div>
        <?php

    }



    /**
     *  The location for the p12 Google Service Key
     *   -- This used to be text input that pointed to a file, and it is for now a hidden field because now there
     *      is just 1 file with 1 name, so rather then remove this, for now I'll leave it especially because the
     *      file upload is here.
     */
    public function key_file_location_field( ) {
        $options = get_option( 'gdrive_to_posts_settings' );
        if (!isset($options['key_file_location'])) {
            $options['key_file_location'] =  '';
            update_option('gdrive_to_posts_settings', $options);
        }
        ?>

        <div class="container">
        <div class="row">
        <div class="col-xs-6 col-sm-8 col-md-3 col-lg-2">

        <input name="gdrive_to_posts_settings[key_file_location]" type="hidden" value="gdrive-file-key.p12">
        <label for="file-gdrive-to-posts-key">
        <span class="gdrive-btn gdrive-btn-file"><?php _e(' Upload File ', 'gdrive_to_posts') ?>

        <input name="file-gdrive-to-posts-key" type="file" value="">
        </span>
        </label>

        </div>
        </div>
        </div>
        <?php
    }



    public function create_new_template_fields( ) {
        ?>
        <div class="container">
        <div class="row">
        <div class="col-xs-8 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group">

            <label for="gdrive-to-posts-new-file-id">
                <?php _e(' Google Sheets file ID: ', 'gdrive_to_posts') ?></label>
            <input type="text" name="template-sheet-id" value="" class="form-control" placeholder="Google Sheets File ID">

            <br>
        </div>

        <div class="form-group">

            <label for="gdrive-to-posts-template-label">
                <?php _e('Choose a label for new template', 'gdrive_to_posts') ?></label>
            <input type="text" name="template-label" class="form-control" value="" placeholder="label for template"><br>

        </div>

        <button id="gdriveToPostsAddNewTemplateBtn" class="button button-primary" type="button" value="add_template"><?php _e('Add Template', 'gdrive_to_posts') ?>
        </button>
        </div>
        </div>
        </div>

        <?php

    }



    /**
     * Each of the different types of posts which you may want to build have to have a file id as
     * well as a template so they take up 2 fields and will be looped for how ever many types of
     * posts the user sets up.
     */
    public function select_a_template( ) {

        $options = get_option( 'gdrive_to_posts_template_body');

        if (!is_array($options)) {
            $options = array();
            update_option('gdrive_to_posts_template_body', $options);
        }

        ?>

        <div class="container">
        <div class="row">
        <div class="col-xs-6 col-sm-8 col-md-3 col-lg-2" id="gdrive-to-posts-templates">

        <label><?php _e('Select a template', 'gdrive_to_posts') ?>
            <select name='choose-editor-template'>
                <option value='' selected>
                    <?php _e('Choose a template', 'gdrive_to_posts') ?>
                </option>
                <?php
        foreach ($options as $key => $template) {
            // print out the options for this template.
            echo "\t<option value='{$key}'>{$key}</option>\n";
        }
        ?>
        </select></label>

        <button class='button button-primary' type='button' id='get-gdrive-sheet-field-names'>Fetch Field Names</button>

        </div>
        </div>
        </div>

        <?php
        }


        public function templates_fields() {

         $options = get_option( 'gdrive_to_posts_template_body');

        if (!is_array($options)) {
            $options = array();
            update_option('gdrive_to_posts_template_body', $options);
        }

        $hidden_inputs = '';
        foreach ($options as $key => $template) {
            $template = esc_textarea($template);
            $hidden_inputs .= "<input type='hidden' id='gdrive_to_posts_template_body[{$key}]' name='gdrive_to_posts_template_body[{$key}]' value='{$template}'>";
        }

        ?>
        <div class="gdrive-template-fields-explanation open">
            <p>
                The <span class="gdrive-bold">"Fetch Field Names"</span> button will show your custom variables which are available for
                use in your templates. Create variables as a-zA-Z and _ words with no spaces in the top row of you spreadsheet.
                The variables are NOT CASE SENSITIVE. For example, you could use <code>first_name</code> in your spreadsheet and
                then reference in the template below using <code>{!!LAST_NAME!!}</code>, <code>{!!last_name!!}</code> or even
                <code>{!!LasT_NaMe!!}</code>. GDrive column syntax is available also to reference columns by number.
            </p>
            <dl>
                <dt>Named columns are referenced in template using: </dt>
                <dd><code>{!!</code><em>variable_name</em><code>!!}</code></dd>
                <dt>Individual columns using: </dt>
                <dd><code>{!#</code><em>number</em><code>#!}</code></dd>
            </dl>
        </div>



        <div class='gdrive-template-fields-listings'>

        </div>

        <div class="gdrive-template-individual-settings all-templates">
            <?php $this->create_each_templates_individual_settings() ?>
        </div>

        <?php

        // These hidden inputs hold the values of each template so we can pull their value into the mce editor if the user wants
        // to edit the template body and then when the 'save changes' button is pushed they will all update.
        echo "<div id='gdrive-hidden-templates'>{$hidden_inputs}</div>";
    }


    public function delete_template_button () {
        ?>

        <div class="gdrive-template-delete">
            <button class="gdrive-btn gdrive-btn-delete" type="delete" id="gdrive-delete-template" style="display:none"><?php _e('DELETE THIS TEMPLATE') ?></button>
        </div>

        <?php
    }


    /**
     * The template wp editor
     */
    public function template_text_editor () {
        $editor_id = "gdrive_to_posts_template_body-editor";
        wp_editor('<h1>GDrive to Posts v0.1.0</h1><ul><li>Create a new template by entering a label and Sheets file ID in the boxes above</li>'
            . '<li>If you\'ve already created some templates you may switch between them using the dropdown above me!</li></ul>'
            , $editor_id, array('textarea_name'=> ' ') );
    }


    /**
     * To just get the settings for 1 template (used by ajax)
     * @param $label
     * @return string
     */
    function get_individual_settings( $label ) {
        $column_seperator = "</div><div class='col-xs-12 col-sm-6 col-md-4'>";
        // We will use this to loop through all fields needed for each template
        $template_fields = array(
            'post_status',
            'sheet_id',
            'data_field',
            'csv_last_line',
            'urls_to_links',
            'title_input',
            'category_dropdown',
            'author_select',
            'tags_input',
            'featured_image',
            'schedule'
        );
        $html_output = '';

        if (defined('DOING_AJAX') && DOING_AJAX) {
            ob_start();
            ob_flush();


            ?>
            <div class="col-xs-12 col-sm-6 col-md-4">

                <?php
                foreach($template_fields as $field) {
                    $template_field_method = "build_template_{$field}";
                    // Create the field
                    $this->$template_field_method( $label );
                    echo $column_seperator;
                }
                ?>

            </div>
            <?php


            $html_output = ob_get_contents();
            ob_end_clean();
        }
        return $html_output;
    }


    /**
     *  Goes through the individual templates and builds a <div> to hold all their individual options
     *  so that they are easier to hide/show, and so that when the user submits the options page form
     *  all their settings remain for each template.
     *
     *  This method builds all the options fields that are specific to one template
     */
    function create_each_templates_individual_settings() {

        // We can use the `csv_last_line` option to figure out each templates label, which is their options key
        $options = get_option('gdrive_to_posts_template_csv_last_line', array());

        $column_seperator = "</div><div class='col-xs-12 col-sm-6 col-md-4'>";
        // We will use this to loop through all fields needed for each template
        $template_fields = array(
            'post_status',
            'sheet_id',
            'data_field',
            'csv_last_line',
            'urls_to_links',
            'title_input',
            'category_dropdown',
            'author_select',
            'tags_input',
            'featured_image',
            'schedule'
        );

        if (is_array($options)) {
            foreach ($options as $label => $val) {

                ?>
                <div class="container-fluid template-field-container template-<?php echo $label ?>" style="display:none">
                <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-4">

                <?php
                foreach($template_fields as $field) {
                    $template_field_method = "build_template_{$field}";
                    // Create the field
                    $this->$template_field_method( $label );
                    echo $column_seperator;
                }
                ?>

                </div>
                </div>
                </div> <!--  end the .template-field-container -->
                <?php
            }
        }
    }

    function build_template_post_status( $label ) {

        $options = get_option('gdrive_to_posts_template_post_status');

        if (is_array($options)) {
            $val = isset($options[ $label ]) ? $options[ $label ] : '';
            ?>
            <div class="template-post_status gdrive-option">

                <label for="template-post_status-<?php echo $label ?>">
                    <b>
                        <?php _e("Post Status Upon Creation: ", 'gdrive_to_posts') ?>
                    </b>

                    <select name="gdrive_to_posts_template_post_status[<?php echo $label ?>]">
                        <option value="" <?php selected( '', $val ) ?>><?php _e("Select Post Status", 'gdrive_to_posts') ?></option>
                        <option value="draft" <?php selected( 'draft', $val ) ?>><?php _e("Draft", 'gdrive_to_posts') ?></option>
                        <option value="publish" <?php selected( 'publish', $val ) ?>><?php _e("Publish", 'gdrive_to_posts') ?></option>
                        <option value="private" <?php selected( 'private', $val ) ?>><?php _e("Private", 'gdrive_to_posts') ?></option>
                    </select>
                </label>
            </div>
            <?php
        }

    }


    function build_template_schedule( $label ) {

        $options = get_option('gdrive_to_posts_template_schedule', array());

        if (!is_array($options)) {
            $options = array();
            update_option('gdrive_to_posts_template_schedule', $options);
        }
        if (is_array($options)) {

            $val = isset($options[ $label ]) ? $options[ $label ] : '';
            $active_status = !!$val ? "<code class='active'>ACTIVE</code>" : "<code class='inactive'>INACTIVE</code>";
            ?>
            <div class="template-schedule gdrive-option">

                <label for="template-schedule-<?php echo $label ?>">
                    <b><?php _e("Active Schedule: ", 'gdrive_to_posts') ?>
                        <?php echo $active_status ?>
                    </b>

                    <select name="gdrive_to_posts_template_schedule[<?php echo $label ?>]">
                        <option value="" <?php selected( '', $val ) ?>>Inactive (off)</option>
                        <option value="often" <?php selected( 'often', $val ) ?>>Every Few Minutes</option>
                        <option value="hourly" <?php selected( 'hourly', $val ) ?>>Every Hour</option>
                        <option value="twicedaily" <?php selected( 'twicedaily', $val ) ?>>Noon and Midnight</option>
                        <option value="daily" <?php selected( 'daily', $val ) ?>>Daily at Midnight</option>
                    </select>
                </label>
            </div>
            <?php
        }

    }
    function build_template_sheet_id( $label ) {

        $options = get_option('gdrive_to_posts_template_sheet_id', array());

        if (is_array($options)) {
            $val = $options[ $label ];
            ?>
            <div class="template-sheet_id gdrive-option">
                <label for="template-sheet_id<?php echo $label ?>"><b><?php _e('Sheet ID: ', 'gdrive_to_posts') ?></b>
                    <input type="text" value="<?php echo $val ?>" id="gdrive_to_posts_template_sheet_id[<?php echo $label ?>]"
                                  name="gdrive_to_posts_template_sheet_id[<?php echo $label ?>]">
                </label>
            </div>
            <?php
        }

    }


    function build_template_featured_image( $label ) {
        $options = get_option('gdrive_to_posts_template_featured_image', array());

        $val = isset($options[ $label ]) ? $options[ $label ] : '';

        ?>
        <div class="template-featured_image gdrive-option">
            <label for="template-featured_image-<?php echo $label ?>"><b><?php _e('Featured Image: ', 'gdrive_to_posts') ?></b>
                <input type="text" value="<?php echo $val ?>" id="gdrive_to_posts_template_featured_image[<?php echo $label ?>]"
                              name="gdrive_to_posts_template_featured_image[<?php echo $label ?>]">
            </label>
        </div>
        <?php
    }



    function build_template_csv_last_line( $label ) {

        $options = get_option('gdrive_to_posts_template_csv_last_line', array());

        if (is_array($options)) {
            $val = $options[ $label ];
            ?>
            <div class="template-csv_last_line gdrive-option">
                <label for="template-csv_last_line-<?php echo $label ?>"><b><?php _e('Last Line Read: ', 'gdrive_to_posts') ?></b>
                    <input type="text" value="<?php echo $val ?>" id="gdrive_to_posts_template_csv_last_line[<?php echo $label ?>]"
                                  name="gdrive_to_posts_template_csv_last_line[<?php echo $label ?>]">
                </label>
            </div>
            <?php
        }

    }


    function build_template_urls_to_links( $label ) {

        $options = get_option('gdrive_to_posts_template_url_to_links', array());

        if (is_array($options)) {
            $val = $options[ $label ];
            ?>
            <div class="template-urls_as_links gdrive-option">
                <b><?php _e("Turn URL's in text to links in post: ", 'gdrive_to_posts') ?></b>
                <label for="template-data-<?php echo $label ?>">Yes
                    <input type="radio" value="1"
                           id="gdrive_to_posts_template_url_to_links[<?php echo $label ?>]"
                           name="gdrive_to_posts_template_url_to_links[<?php echo $label ?>]" <?php checked(1, intval($val), true) ?>>
                </label>
                <label for="template-data-<?php echo $label ?>">No

                    <input type="radio" value="0"
                           id="gdrive_to_posts_template_url_to_links[<?php echo $label ?>]"
                           name="gdrive_to_posts_template_url_to_links[<?php echo $label ?>]" <?php checked(0, intval($val), true) ?>>
                </label>
            </div>
            <?php
        }

    }
    /**
     * The `data` field is a checkbox which by default as 0 or '' will signal to the plugin that
     * the user wishes to use Google Credentials. By '1' it will signal that credentials are not
     * needed and to treat the Sheet_id of a resource as a URI to a .csv
     */
    function build_template_data_field( $label ) {

        $options = get_option('gdrive_to_posts_template_data', array());

        if (is_array($options)) {
            $val = $options[ $label ];

            ?>
            <div class="template-data_field gdrive-option">
                <b class="radio-input-label">Sheet ID is published URL (not recommended): </b><br>
                <label for="template-data-<?php echo $label ?>">No
                    <input type="radio" value="0"
                           id="gdrive_to_posts_template_data[<?php echo $label ?>]"
                           name="gdrive_to_posts_template_data[<?php echo $label ?>]" <?php checked(0, intval($val), true) ?>>
                </label>
                <label for="template-data-<?php echo $label ?>"><span class="label-info">Yes</span>
                    <input type="radio" value="1"
                           id="gdrive_to_posts_template_data[<?php echo $label ?>]"
                           name="gdrive_to_posts_template_data[<?php echo $label ?>]" <?php checked(1, intval($val), true) ?>>
                </label>
                <!--
                    <span class="text-info" style="display:none">
                        If you don't have a Google Server fingerprint, email, and key file yet or you are
                        accessing a file that is not your own. Check this box and the plugin will treat
                        the Sheet ID of your template as a URI (web address to your content). You may use this
                        option on your own Google Drive Sheets as well but it is not recommended as a long term
                        solution as the web address to a Sheet published to the web Sheet isn't guaranteed to
                        always be valid. It is satisfactory for testing purposes or short term work but for long
                        term you should goto Google Developers Console, create and app and fill in the
                        credentials information in the top form of this page. To get the URI for a Sheet
                        navigate to the Sheet in your browser then click the menu option "FILE" -> "Publish to
                        the web" and under "Link" choose the  options "Sheet 1" and "Comma-separated values
                        (.csv) and then click "publish". Copy the link that it gives you into the Sheet ID field
                        for your template on this plugin.
                </span>
                -->

            </div>
            <?php
        }

    }


    function build_template_title_input( $label ) {

        $options = get_option('gdrive_to_posts_template_title', array());

        if (is_array($options)) {
            $val = $options[ $label ];
            ?>
            <div class="template-title gdrive-option">
                <label for="template-title-<?php echo $label ?>"><b><?php _e('Title: ', 'gdrive_to_posts') ?></b>
                    <input type="text" value="<?php echo $val ?>" id="gdrive_to_posts_template_title[<?php echo $label ?>]"
                                  name="gdrive_to_posts_template_title[<?php echo $label ?>]">
                </label>
            </div>
            <?php
        }

    }


    function build_template_author_select( $label ) {

        $args = array(
            'show_option_none' => __( 'Select Author' ),
            'who'              => 'authors',
            'orderby'          => 'user_nicename',
            'echo'             => 1
        );

        $options = get_option('gdrive_to_posts_template_author', array());

        if (is_array($options)) {
            $val = $options[ $label ];
            $args['selected'] = intval($val);
            $args['name'] = "gdrive_to_posts_template_author[{$label}]";
            $args['id'] = "gdrive_to_posts_template_author[{$label}]";
            ?>

            <div class="template-author gdrive-option">
                <label for="template-author-<?php echo $label ?>"><b><?php _e('Author: ', 'gdrive_to_posts') ?></b>
                    <?php wp_dropdown_users( $args ); ?>
                </label>
            </div>
            <?php
        }
    }



    function build_template_tags_input( $label ) {

        $options = get_option('gdrive_to_posts_template_tags', array());

        if (is_array($options)) {
            $val = $options[ $label ];

            $args['selected'] = $val;
            ?>
            <div class="template-tags gdrive-option">
                <label for="gdrive_to_posts_template_tags[<?php echo $label ?>]"><b><?php _e('Tags: ', 'gdrive_to_posts') ?></b>
                    <input type="text" value="<?php echo $val ?>" id="gdrive_to_posts_template_tags[<?php echo $label ?>]"
                                 name="gdrive_to_posts_template_tags[<?php echo $label ?>]">
                </label>
            </div>
            <?php
        }
    }


    function build_template_category_dropdown( $label ) {
        $args = array(
            'show_option_none' => __( 'Select category' ),
            'hierarchical'     => 1,
            'hide_empty'       => 0,
            'orderby'          => 'name',
            'hide_if_empty'    => false,
            'echo'             => 1,
        );

        $options = get_option( 'gdrive_to_posts_template_category' );

        if (is_array($options)) {
            $val = $options[ $label ];
            $args['selected'] = intval($val);
            $args['name'] = "gdrive_to_posts_template_category[{$label}]";
            $args['id'] = "gdrive_to_posts_template_category[{$label}]";
            ?>
            <div class="template-category gdrive-option">
                <label for="<?php echo $args['name'] ?>"><b><?php _e('Categories: ', 'gdrive_to_posts') ?></b>
                <?php wp_dropdown_categories( $args ); ?>
                </label>
            </div>
            <?php
        }
    }


    /**
     *  Start the printing of the options.php page.
     */
    public function gdrive_to_posts_options_page( ) {

        ?>
        <section class="gdrive-to-posts-modal">
            <div>
                <h1 class="msg"><!-- There will be a message here when it is shown --></h1>
            </div>
        </section>

        <section class="wrap">

            <?php Debug_abug::console() ?>

            <div class="gdrive-to-posts-options">
                <form action='options.php' method='post' class="gdrive-to-posts-settings">
                    <h2>Google Drive to Posts</h2>
                    <table class="form-table">
                        <?php
                        settings_fields( 'gdriveAPISettings' );
                        do_settings_sections( 'gdriveAPISettings' );
                        submit_button();
                        ?>
                    </table>
                </form>
            </div>
        </section>

        <hr>

        <section class="wrap">
            <div class="gdrive-to-posts-testing">
                <h2>Google Drive Testing Area!</h2>
                <button class="button button-secondary google-client-test-btn" type="button" id="google-client-test">TEST GOOGLE CLIENT</button>
                <div class="test-gclient-results"></div>
            </div>

            <hr>

            <div class="gdrive-to-posts-preview">
                <h2>Sheet to post preview!</h2>
                <button class="button button-secondary sheet-template-tester-btn" type="button" id="sheet-template-tester">TEST RUN THIS TEMPLATE</button>
                <div class="test-preview-results"></div>
            </div>
        </section>
        <?php

    }

}

