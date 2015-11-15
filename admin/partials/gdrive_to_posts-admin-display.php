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

    function __construct($google_drive = false)
    {
        $this->google_drive = $google_drive;
    }

    function google_settings_section_callback(  ) {

        echo __( 'Create a GDrive App using the google developers console and then add the credentials here to allow the'
            . ' plugin access to your Google Drive Files. It should only need a read access as the plugin does not write'
            . ' or edit any files on your Google Drive. To setup an app, head on over to the <a target="_blank"'
            . ' href="https://console.developers.google.com/apis/credentials">Google Developers Console</a>', 'gdrive_to_posts' );

    }


    function template_settings_section_callback() {

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
    function google_api_key_field(  ) {

        $options = get_option( 'gdrive_to_posts_settings' );

        if (!isset($options['google_api_key'])) {
            $options['google_api_key'] = '';
            update_option('gdrive_to_posts_settings', $options);
        }

        ?>

        <input type='text' id='gdrive_to_posts_settings[google_api_key]' name='gdrive_to_posts_settings[google_api_key]' value='<?php echo $options['google_api_key']; ?>'>

        <?php

    }


    function service_account_email_address_field(  ) {

        $options = get_option( 'gdrive_to_posts_settings' );

        if (!isset($options['service_account_email_address'])) {
            $options['service_account_email_address'] = '';
            update_option('gdrive_to_posts_settings', $options);
        }

        ?>

        <input type='text' name='gdrive_to_posts_settings[service_account_email_address]' value='<?php echo $options['service_account_email_address']; ?>'>

        <?php

    }


    function service_account_certificate_fingerprints_field(  ) {

        $options = get_option( 'gdrive_to_posts_settings' );

        if (!isset($options['service_certificate_fingerprints'])) {
            $options['service_certificate_fingerprints'] = '';
            update_option('gdrive_to_posts_settings', $options);
        }

        ?>

        <input type='text' name='gdrive_to_posts_settings[service_certificate_fingerprints]' value='<?php echo $options['service_certificate_fingerprints']; ?>'>

        <?php

    }


    function gdrive_to_posts_interval_select_render(  ) {

        $options = get_option( 'gdrive_to_posts_settings' );
        if (!isset($options['gdrive_to_posts_interval'])) {
            $options['gdrive_to_posts_interval'] = '';
            update_option('gdrive_to_posts_settings', $options);
        }
        ?>
        <select name='gdrive_to_posts_settings[gdrive_to_posts_select_field_1]'>
            <option value='' <?php selected( $options['gdrive_to_posts_interval']); ?>>Constant</option>
            <option value='1' <?php selected( $options['gdrive_to_posts_interval'] ); ?>>Every 1 hour</option>
            <option value='2' <?php selected( $options['gdrive_to_posts_interval']); ?>>Every 2 hours</option>
            <option value='6' <?php selected( $options['gdrive_to_posts_interval']); ?>>Every 6 Hours</option>
            <option value='12' <?php selected( $options['gdrive_to_posts_interval'] ); ?>>Every 12 Hours</option>
        </select>
        <?php

    }



    function post_body_template_textarea( $id ) {
        $options = get_option( 'gdrive_to_posts_templates' );
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

        <input type='text' name='gdrive_to_posts_templates[<?php echo $id ?>][sheet_id]' value='<?php echo $gdrive_template['sheet_id']; ?>'>
        <td style="width:15%"></td>
        <?php
    }


    function create_new_template_fields( ) {
        ?>

        <div class="form-group">

            <label for="gdrive-to-posts-new-file-id">
                <?php _e(' Google Sheets file ID: ', 'gdrive_to_posts') ?></label>
            <input type="text" name="gdrive-to-posts-template-sheet-id" value="" class="form-control" placeholder="Google Sheets File ID">

            <br>
        </div>

        <div class="form-group">

            <label for="gdrive-to-posts-template-label">
                <?php _e('Choose a label for new template', 'gdrive_to_posts') ?></label>
            <input type="text" name="gdrive-to-posts-template-label" class="form-control" value="" placeholder="label for template"><br>

        </div>

        <button id="gdriveToPostsAddNewTemplateBtn" class="button button-primary" type="button" value="add_template"><?php _e('Add Template', 'gdrive_to_posts') ?>
        </button>
        <?php

    }


    /**
     * Each of the different types of posts which you may want to build have to have a file id as
     * well as a template so they take up 2 fields and will be looped for how ever many types of
     * posts the user sets up.
     */
    function templates_fields( ) {

        $options = get_option( 'gdrive_to_posts_templates');

        if (!is_array($options)) {
            $options = array();
            update_option('gdrive_to_posts_templates', $options);
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

            $hidden_inputs .= "<input type='hidden' id='gdrive_to_posts_templates[{$key}]' name='gdrive_to_posts_templates[{$key}]' value='{$template}'>";
        }
        echo "</select></label><button class='button button-primary' type='button' id='get-gdrive-sheet-field-names'>Fetch Field Names</button></div>";

        // These hidden inputs hold the values of each template so we can pull their value into the mce editor if the user wants
        // to edit the template body and then when the 'save changes' button is pushed they will all update.
        echo "<div id='gdrive-hidden-templates'>{$hidden_inputs}</div>";
        echo "<table>";
        $editor_id = "gdrive_to_posts_templates-editor";
        wp_editor('<h1>GDrive to Posts v0.1.0</h1><ul><li>Create a new template by entering a label and Sheets file ID in the boxes above</li>'
                  . '<li>If you\'ve already created some templates you may switch between them using the dropdown above me!</li></ul>'
                  , $editor_id, array('textarea_name'=> ' ') );
        echo "</table>";


    }


    /**
     *  Start the printing of the options.php page.
     */
    function gdrive_to_posts_options_page( ) {

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
                        ?>
                        <hr>
                        <?php
                        //settings_fields( 'gdrivePostsSettings' );
                        //do_settings_sections( 'gdrivePostsSettings' );
                        submit_button();
                        ?>
                    </table>
                </form>
            </div>
        </section>

        <section class="wrap">
            <div class="gdrive-to-posts-testing">
                <h2>If the area below shows information about your Google Drive then your API info is good!</h2>
            </div>
            <div class="gdrive-to-posts-preview">
                <pre>
                    <?php

                    $sheet_id = '123123';
                    if (is_object($this->google_drive) && !!$sheet_id) {
                        $file = $this->google_drive->files->get($sheet_id);
                        if ($file && is_array($file->exportLinks)) {

                            // Get the file as text csv using the Google Drive Export method.

                            $csv = wp_remote_get($file->exportLinks['text/csv']);
                            @$csv = is_array($csv) ? $csv['body'] : null;
                            if ($csv) {
                                // This will parse the csv and make new posts if that's what it should do.
                                $workhorse = new GDrive_To_Posts_Workhorse();
                                $workhorse->add($csv);
                                // We want to see the output here.
                                $workhorse->run(true);
                            }
                        }
                    }
                    else {?>
                        <h4>Your Google Server isn't setup correctly yet.</h4>
                        <?php
                    }
                    ?>
                </pre>
            </div>
        </section>
        <?php

    }

}

