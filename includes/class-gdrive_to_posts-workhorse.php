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

        try {
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
        catch (\Google_Service_Exception $error) {
            return false;
        }
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
        //$template_str = "This will turn {!!sTuFf!!} into {!!STUFF!!}";
        $template_str = preg_replace_callback('/{!(!|#)([a-zA-Z0-9_]+)\1!}/mi', function($m) {
            return mb_strtoupper($m[0]);
        }, $template_str);

        return $template_str;
    }


    private $current_row;
    private $hide_unknown_variables = true;

    /**
     * Replace all the template string identifiers inside $template_str
     *
     * Templates content using $this->current_row's array by first extracting the entire {!!variable!!}
     * and then searching the current row array for key [VARIABLE] and replacing it in the template.
     * This does not require that the template string be normalized.
     *
     * @param string $template_str
     * @param bool $hide_unknown_variables - no value found for variable name then replace it with empty string.
     * @return string
     */
    private function parse_template( $template_str, $hide_unknown_variables = true ) {
        // change the hide_unknown_variables on class so that it can be used inside anonymous function
        $this->hide_unknown_variables = $hide_unknown_variables;
        $output = preg_replace_callback('/{!(!|#)([a-zA-Z0-9_]+)\1!}/mi', function($match) {

            $var_key = mb_strtoupper($match[2]);

            if (isset($var_key) && isset($this->current_row[$var_key])) {
                return $this->current_row[$var_key];
            }
            if (!isset($this->current_row[$var_key]) && !$this->hide_unknown_variables) {
                return $match[2];
            }
            return '';
        }, $template_str);

        // Put hide_unknown_variables back to its default setting
        $this->hide_unknown_variables = true;
        return $output;
    }


    public function parse_file(\Google_Service_Drive $gdrive, $sheet_id, $content_template, $title_template, $author = 1, $the_tags = '', $category = 1, $stored_last_line = 1, $n_tests = 0 ) {
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

        $output = '';
        $row_number = 1;
        $post_status = $n_tests > 0 ? 'draft' : 'publish';
        // Row 1 becomes our keys for every other row, needed for templating.
        $keys = str_getcsv( (array_shift($csv_rows)), ',', '"');
        $keys = array_map(function($val) {
            return $this->normalize_field($val);
        }, $keys);

        // Parse through rows up to max, set at 10 by default, set it to no max the user can just pass $max=0
        foreach ($csv_rows as $row ) {
            // We can incremint row in begining because we shifted array before starting foreach, changing the row
            // number higher allows for less nested code then if we incremented it at the bottom of foreach
            $row_number++;
            if ($row_number <= $stored_last_line ) {
                continue;
            }
            // We are on a row which hasn't been made a post yet, but if this is a test then we do have limits as the
            // purpose of the testing is just to show the admin page what a few posts will look like.
            if ($n_tests && $row_number > ($n_tests + $stored_last_line)) {
                break;
            }


            // Begin the work knowing we are on a row that has not been read before.
            $row_items = str_getcsv($row, ',', '"');
            $entry = array_combine($keys, $row_items);

            $this->current_row = $entry;
            // Use parse_template to switch out {{!!!!}} for the data in $this->current_row which is implicitly in the
            // parse_template() method as it is part of this class GDrive_to_Posts_Workhorse
            $content = $this->parse_template($content_template);
            $title = $this->parse_template($title_template);
            $the_tags = $this->parse_template($the_tags);

            if (!!$content) {
                $post_content = array(
                    'post_title'    => $title,
                    'post_type'     => 'post',
                    'post_content'  => $content,
                    'post_status'   => $post_status,
                    'post_author'   => (int)$author,
                    'tags_input'    => $the_tags,
                    'post_excerpt'  => '',
                    'post_category' => array((int)$category)
                );

                // Try to create a post from the templates.
                if ($post_insert_id = $this->create_post($post_content)) {
                    $output .= "<br>Created new post at " . get_page_uri($post_insert_id);
                } else {
                    $output .= "<br>Failed to create page {$row_number}...";
                }
            } else {
                $output .= "<br>Failed to parse a row {$row_number}...";
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
            'post_status'   => 'draft',
            'post_author'   => 1,
            'post_excerpt'  => '',
            'post_category' => array(1)
        );
        // Create post object
        $post = array_merge( $post_defaults, $post_args );

        echo var_export( $post );
        exit;

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