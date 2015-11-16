<?php
/**
 *
 */

namespace gdrive_to_posts;

class GDrive_to_Posts_Workhorse
{

    private $csv;
    private $csv_text;
    private $template;
    private $options;

    function __constructor() {
        $options = get_option( 'gdrive_to_posts_settings' );
        if (!is_array($options)) {
            // If we don't have an options array yet then something is wrong.
            return false;
        }
        if (!isset($options['post_body_template'])) {
            // We will have to set a default. So there won't be much in the post body.
            $options['post_body_template'] = '';
            update_option('gdrive_to_posts_settings', $options);
        }

        $this->template = $options['post_body_template'];
        $this->options = $options;
    }


    /**
     * Make the variables how they will be in the template, spaces to underscores
     * @param $variable
     * @return array
     */
    public function normalize_field($variable) {
        $variable = preg_replace('~(\s|-|/)~', '_', $variable);
        $variable = preg_replace("~[^a-zA-Z0-9]+~", "", $variable);
        return trim(mb_strtoupper($variable));
    }


    /**
     * Gets a file from GDrive and adds it as the $csv. This is good if the caller
     * doesn't have a CSV but they have the GDrive object and sheet id.
     *
     * @param \Google_Service_Drive $gdrive
     * @param $sheet_id
     * @return bool
     */
    public function get(\Google_Service_Drive $gdrive, $sheet_id) {

        $file = $gdrive->files->get($sheet_id);
        if ($file && is_array($file->exportLinks)) {

            // Get the file as text csv using the Google Drive Export method.
            $csv = wp_remote_get($file->exportLinks['text/csv']);
            @$csv = is_array($csv) ? $csv['body'] : null;
            if (!$csv) {
                return false;
            }
            $this->add($csv);
        }
        return $csv;
    }

    public function add($csv) {
        if (!$csv) {
            return false;
        }
        $this->csv_text = $csv;
    }


    /**
     * @return array - fields that can be used as variables
     */
    public function get_fields() {
        $csv_text = $this->csv_text;

        $csv_rows = str_getcsv($csv_text, "\r", '"');
        if (!is_array($csv_rows)) {
            return false;
        }

        $keys = str_getcsv( (array_shift($csv_rows)), ',', '"');
        return array_map(function($val) {
            return $this->normalize_field($val);
        }, $keys);
    }


    public function run($show_output = false) {
        $csv_text = $this->csv_text;

        $csv_rows = str_getcsv($csv_text, "\r", '"');
        if (!is_array($csv_rows)) {
            return false;
        }

        $keys = str_getcsv( (array_shift($csv_rows)), ',', '"');
        foreach ($csv_rows as $row ) {
            $row_items = str_getcsv($row, ',', '"');
            $entry = array_combine($keys, $row_items);

            if ($show_output) {
                ?> <h3>Next raw spreadsheet entry</h3> <?php
                echo var_export($entry);
            }

        }
    }


    /**
     * To make templating easy this will uppercase all the variables in the template string
     * so they will match the keys in the normalized $keys array when parsing templates.
     *
     * @param $template_str
     * @return mixed
     */
    public function normalize_template($template_str) {
        //$template_str = "Hello my {!!Friend!!} I am going to {!!kill_tou!!} is that {!#2#!}?";
        $template_str = preg_replace_callback('/{!(!|#)([a-zA-Z0-9_]+)\1!}/mi', function($m) {
            return mb_strtoupper($m[0]);
        }, $template_str);

        return $template_str;
    }


    private $current_row;

    /**
     * Replace all the template string identifiers in the template with the appropriate
     * field from the current row's array by extracting the entire {!!variable!!} and then
     * searching the row for a row[VARIABLE] key and replacing it in the template.
     *
     * @param $template_str
     * @return string
     */
    public function parse_template($template_str) {
        $output = preg_replace_callback('/{!(!|#)([a-zA-Z0-9_]+)\1!}/mi', function($match) {

            $var_key = mb_strtoupper($match[2]);

            if (isset($var_key) && isset($this->current_row[$var_key])) {
                return $this->current_row[$var_key];
            }
            return '';
        }, $template_str);

        return $output;
    }


    public function parse_file(\Google_Service_Drive $gdrive, $sheet_id, $content_template, $title_template, $max = 10) {
        if (!$this->get($gdrive, $sheet_id)) {
            return null;
        }

        // Alright, let's parse this sheet.
        $csv_text = $this->csv_text;
        // This separates everything correctly, google sheets uses \r for lines and " as enclosure
        $csv_rows = str_getcsv($csv_text, "\r", '"');
        if (!is_array($csv_rows)) {
            return false;
        }

        //$this->normalize_template($content_template);
        $output = '';
        $keys = str_getcsv( (array_shift($csv_rows)), ',', '"');
        $keys = array_map(function($val) {
            return $this->normalize_field($val);
        }, $keys);

        $index = 1;
        // Parse through rows up to max, set at 10 by default, set it to no max the user can just pass $max=0
        foreach ($csv_rows as $row ) {
            if ($index++ > $max) {
                break;
            }
            $row_items = str_getcsv($row, ',', '"');
            $entry = array_combine($keys, $row_items);

            $this->current_row = $entry;

            $content = $this->parse_template($content_template);
            $title = $this->parse_template($title_template);

            if (!!$content) {
                $post_content = array(
                    'post_title'    => $title,
                    'post_type'     => 'post',
                    'post_content'  => $content,
                    'post_status'   => 'publish',
                    'post_author'   => 1,
                    'post_excerpt'  => '',
                    'post_category' => array(1)
                );

                // Try to create a post from the templates.
                if ($post_insert_id = $this->create_post($post_content)) {
                    $output .= "<br>Created new post at " . get_page_uri($post_insert_id);
                } else {
                    $output .= "<br>Failed to create page {$index}...";
                }
            } else {
                $output .= "<br>Failed to parse a row {$index}...";
            }

        }

        return $output;
    }


    /**
     * Builds and publish post from array
     * @param $post_args
     * @return int|\WP_Error
     */
    private function create_post($post_args) {
        $post_defaults = array(
            'post_title'    => '',
            'post_type'     => 'post',
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_excerpt'  => '',
            'post_category' => array(1)
        );
        // Create post object
        $post = array_merge($post_defaults, $post_args);

        // Insert the post into the database
        $id = wp_insert_post( $post );

        return $id;
        /** OTHER OPTIONS FOR POSTS
         * 'post_parent'    => [ <post ID> ] // Sets the parent of the new post, if any. Default 0.
         * 'menu_order'     => [ <order> ] // If new post is a page, sets the order in which it should appear in supported menus. Default 0.
         * 'to_ping'        => // Space or carriage return-separated list of URLs to ping. Default empty string.
         * 'comment_status' => [ 'closed' | 'open' ] // Default is the option 'default_comment_status', or 'closed'.
         * 'post_category'  => [ array(<category id>, ...) ] // Default empty.
         * 'tags_input'     => [ '<tag>, <tag>, ...' | array ] // Default empty.
         * 'tax_input'      => [ array( <taxonomy> => <array | string>, <taxonomy_other> => <array | string> ) ] // For custom taxonomies. Default empty.
         * 'page_template'  => [ <string> ] // Requires name of template file, eg template.php. Default empty.
         */
    }

}