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
/*
function gdrive_to_posts_checkbox_field_3_render(  ) {

    $options = get_option( 'gdrive_to_posts_settings' );
    ?>
    <input type='checkbox' name='gdrive_to_posts_settings[gdrive_to_posts_checkbox_field_3]' <?php checked( $options['gdrive_to_posts_checkbox_field_3'], 1 ); ?> value='1'>
    <?php

}


function gdrive_to_posts_radio_field_4_render(  ) {

    $options = get_option( 'gdrive_to_posts_settings' );
    ?>
    <input type='radio' name='gdrive_to_posts_settings[gdrive_to_posts_radio_field_4]' <?php checked( $options['gdrive_to_posts_radio_field_4'], 1 ); ?> value='1'>
    <?php

}
*/
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

        <input type='text' id='gdrive_to_posts_settings[google_api_key]' name='gdrive_to_posts_settings[google_api_key]' value='<?php echo $options['google_api_key']; ?>'>

        <?php

    }


    public function service_account_email_address_field(  ) {

        $options = get_option( 'gdrive_to_posts_settings' );

        if (!isset($options['service_account_email_address'])) {
            $options['service_account_email_address'] = '';
            update_option('gdrive_to_posts_settings', $options);
        }

        ?>

        <input type='text' name='gdrive_to_posts_settings[service_account_email_address]' value='<?php echo $options['service_account_email_address']; ?>'>

        <?php

    }


    public function service_certificate_fingerprints_field(  ) {

        $options = get_option( 'gdrive_to_posts_settings' );

        if (!isset($options['service_certificate_fingerprints'])) {
            $options['service_certificate_fingerprints'] = '';
            update_option('gdrive_to_posts_settings', $options);
        }

        ?>

        <input type='text' name='gdrive_to_posts_settings[service_certificate_fingerprints]' value='<?php echo $options['service_certificate_fingerprints']; ?>'>

        <?php

    }


    public function fetch_interval_field(  ) {

        $options = get_option( 'gdrive_to_posts_settings' );
        if (!isset($options['fetch_interval'])) {
            $options['fetch_interval'] = '';
            update_option('gdrive_to_posts_settings', $options);
        }
        ?>
        <select name='gdrive_to_posts_settings[fetch_interval]'>
            <option value='' <?php selected( $options['fetch_interval']); ?>><?php _e('Choose Approximate Interval', 'gdrive_to_posts') ?></option>
            <option value='often' <?php selected( $options['fetch_interval'], 'often' ); ?>><?php _e('About 10 Minutes', 'gdrive_to_posts') ?></option>
            <option value='hourly' <?php selected( $options['fetch_interval'], 'hourly'); ?>><?php _e('Hourly', 'gdrive_to_posts') ?></option>
            <option value='twicedaily' <?php selected( $options['fetch_interval'], 'twicedaily'); ?>><?php _e('Noon and midnight', 'gdrive_to_posts') ?></option>
            <option value='daily' <?php selected( $options['fetch_interval'], 'daily'); ?>><?php _e('Daily at midnight', 'gdrive_to_posts') ?></option>
        </select>
        <?php

    }


    /**
    *  The location for the p12 Google Service Key
     * //todo: this should become a type=file
     */
    public function key_file_location_field( ) {
        $options = get_option( 'gdrive_to_posts_settings' );
        if (!isset($options['key_file_location'])) {
            $options['key_file_location'] =  '';
            update_option('gdrive_to_posts_settings', $options);
        }
        ?><input name="gdrive_to_posts_settings[key_file_location]" type="text" value="<?php echo $options['key_file_location'] ?>"><?php
    }



    public function post_body_template_textarea( $id ) {
        $options = get_option( 'gdrive_to_posts_template_body' );
        if (!is_array($options)) {
            // There is no way this should be called if the base level settings haven't even been created!
            return false;
        }
        $gdrive_template = $options[ $id ];
        if (!is_array($gdrive_template)) {
            ?>
            <h1>There is a problem with your Google Drive to Posts plugin.</h1>
            <p>The developer would like to hear about this, you can chew his ear off
                at <a href="mailto:mrosata1984@gmail.com">mrosata1984@gmail.com</a>.
                Basically the plugin is trying to build a template form for a template
                that does not exist! That's crackers bro, straight jazz crackers!
            </p>
            <?php
            return false;
        }

        echo "<h2 id='gdrive-to-posts-template-label'>{$gdrive_template['label']}</h2>";
        ?>

        <input type='text' name='gdrive_to_posts_template_body[<?php echo $id ?>][sheet_id]' value='<?php echo $gdrive_template['sheet_id']; ?>'>
        <td style="width:15%"></td>
        <?php
    }


    public function create_new_template_fields( ) {
        ?>

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
        <hr>
        <?php

    }


    /**
     * Title Templates for the posts
     */
    public function template_titles_fields( ) {
        $options = get_option( 'gdrive_to_posts_template_title' );

        if (!is_array($options)) {
            $options = array();
            update_option('gdrive_to_posts_template_title', $options);
        }

        $hidden_titles = '';
        foreach ($options as $key => $title_template) {
            if (!is_string($title_template)) {
                continue;
            }
            $title_template = esc_textarea($title_template);
            $hidden_titles .= "<input type='hidden' id='gdrive_to_posts_template_title[{$key}]' name='gdrive_to_posts_template_title[{$key}]' value='{$title_template}'>";
        }

        // The hidden inputs with titles in them for JavaScript to pull out
        echo "<div id='hidden-title-templates'>{$hidden_titles}</div>";
    }



    /**
     * Each of the different types of posts which you may want to build have to have a file id as
     * well as a template so they take up 2 fields and will be looped for how ever many types of
     * posts the user sets up.
     */
    public function templates_fields( ) {

        $options = get_option( 'gdrive_to_posts_template_body');

        if (!is_array($options)) {
            $options = array();
            update_option('gdrive_to_posts_template_body', $options);
        }

        ?><div id="gdrive-to-posts-templates"><?php

        $hidden_inputs = '';
        $chose_template = __('Choose a template', 'gdrive_to_posts');
        echo "<label>Select a template<select name='choose-editor-template'>\n\t<option value='' selected>{$chose_template}</option>\n";
        foreach ($options as $key => $template) {
            if (!is_string($template)) {
                continue;
            }
            // print out the options for this template.
            echo "\t<option value='{$key}'>{$key}</option>\n";

            $template = esc_textarea($template);
            $hidden_inputs .= "<input type='hidden' id='gdrive_to_posts_template_body[{$key}]' name='gdrive_to_posts_template_body[{$key}]' value='{$template}'>";
        }
        ?>
        </select></label>

        <button class='button button-primary' type='button' id='get-gdrive-sheet-field-names'>Fetch Field Names</button>

        </div>


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
        <div class="gdrive-template-tags-author-title">
            <div><?php $this->build_each_template_category_dropdown() ?></div>
            <div><?php $this->build_each_template_author_select() ?></div>
            <div><?php $this->build_each_template_tags_input() ?></div>
            <div><?php $this->build_each_template_title_input() ?></div>
        </div>
        <hr>

        <?php

        // These hidden inputs hold the values of each template so we can pull their value into the mce editor if the user wants
        // to edit the template body and then when the 'save changes' button is pushed they will all update.
        echo "<div id='gdrive-hidden-templates'>{$hidden_inputs}</div>";
        echo "<table>";

        $editor_id = "gdrive_to_posts_template_body-editor";
        wp_editor('<h1>GDrive to Posts v0.1.0</h1><ul><li>Create a new template by entering a label and Sheets file ID in the boxes above</li>'
                  . '<li>If you\'ve already created some templates you may switch between them using the dropdown above me!</li></ul>'
                  , $editor_id, array('textarea_name'=> ' ') );
        echo "</table>";


    }


    function build_each_template_title_input() {

        $options = get_option('gdrive_to_posts_template_title', array());

        foreach ($options as $label => $val) {
            ?>
            <div class="template-title template-<?php echo $label ?>" style="display:none;">
                <label for="template-title-<?php echo $label ?>">
                    Title: <input type="text" value="<?php echo $val ?>" id="gdrive_to_posts_template_title[<?php echo $label ?>]"
                                 name="gdrive_to_posts_template_title[<?php echo $label ?>]">
                </label>
            </div>
            <?php
        }
    }


    function build_each_template_author_select( ) {

        $args = array(
            'show_option_none' => __( 'Select Author' ),
            'who'              => 'authors',
            'orderby'          => 'user_nicename',
            'echo'             => 1
        );

        $options = get_option('gdrive_to_posts_template_author', array());

        foreach($options as $label => $val) {
            $args['selected'] = intval($val);
            $args['name'] = "gdrive_to_posts_template_author[{$label}]";
            $args['id'] = "gdrive_to_posts_template_author[{$label}]";
            ?>

            <div class="template-author template-<?php echo $label ?>" style="display:none;">
                <label for="template-author-<?php echo $label ?>">
                    Author: <?php wp_dropdown_users( $args ); ?>
                </label>
            </div>
            <?php
        }
    }



    function build_each_template_tags_input( ) {

        $options = get_option('gdrive_to_posts_template_tags', array());

        foreach ($options as $label => $val) {
            $args['selected'] = $val;
            ?>
            <div class="template-tags template-<?php echo $label ?>" style="display:none;">
                <label for="template-tags-<?php echo $label ?>">
                    Tags: <input type="text" value="<?php echo $val ?>" id="gdrive_to_posts_template_tags[<?php echo $label ?>]"
                                 name="gdrive_to_posts_template_tags[<?php echo $label ?>]">
                </label>
            </div>
            <?php
        }
    }


    function build_each_template_category_dropdown( ) {
        $args = array(
            'show_option_none' => __( 'Select category' ),
            'hierarchical'     => 1,
            'hide_empty'         => 0,
            'orderby'          => 'name',
            'hide_if_empty'      => false,
            'echo'             => 1,
        );
        $options = get_option( 'gdrive_to_posts_template_category', array());

        foreach($options as $label => $val) {
            $args['selected'] = intval($val);
            $args['name'] = "gdrive_to_posts_template_category[{$label}]";
            $args['id'] = "gdrive_to_posts_template_category[{$label}]";
            ?>
            <div class="template-category template-category-<?php echo $label ?>" style="display:none;">
                <h3><?php _e('Categories:'); ?></h3>
                <?php wp_dropdown_categories( $args ); ?>
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
            <div class="gdrive-to-posts-options">
                <form action='options.php' method='post'>
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

