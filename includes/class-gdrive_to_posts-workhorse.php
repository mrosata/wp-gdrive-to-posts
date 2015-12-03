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

    private $testing = false;
    /**
     * Stores an array with all the posts that have been parsed.
     * If testing is on should be the only time it happens.
     * @var array
     */
    private $work_done = array();

    function __constructor() {
        $options = get_option( 'gdrive_to_posts_settings' );
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
        return trim(strtoupper($variable));
    }


    /**
     * Gets a file from GDrive and adds it as the $csv. This is good if the caller
     * doesn't have a CSV but they have the GDrive object and sheet id.
     *
     * @param \Google_Service_Drive $gdrive
     * @param $sheet_id
     * @return bool
     */
    public function get( $gdrive, $sheet_id ) {

        try {
            $uri = '';
            if (is_a($gdrive, 'Google_Service_Drive')) {
                Debug_abug::log("About to try to fetch sheet id: $sheet_id file using Google Service Drive");
                // We will just use the $sheet_id as a file id to drive rather than plain URL.
                $file = $gdrive->files->get($sheet_id);

                if ($file && is_array($file->exportLinks)) {
                    $uri = $file->exportLinks['text/csv'];
                }
            } elseif ( $gdrive == 'treat_as_uri' ) {
                // just use sheet_id as URL. For published to web sheets or hosted .csv file's.
                $uri = $sheet_id;
                Debug_abug::log("About to try to find CSV using $sheet_id as a url.");
            }

            if (!!$uri) {
                // Get the file as text csv using the Google Drive Export method.
                //$response = wp_remote_get( $uri );
                $response = wp_remote_request( $uri );
                $csv = wp_remote_retrieve_body($response);

                //$csv = is_array($csv) ? $csv['body'] : null;
                if (!$csv) {
                    return false;
                }
                $this->add($csv);
                return $csv;
            }
        }
        catch (\Google_Service_Exception $error) {
            if (defined('GDRIVE_TO_POSTS_DEBUG') && GDRIVE_TO_POSTS_DEBUG) {
                echo var_export($error);
            }
        }
        return false;
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
            $val = preg_replace('~(\s|-|/)~', '_', $val);
            $val = preg_replace("~[^a-zA-Z0-9]+~", "", $val);
            return $val;
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
            return strtoupper($m[0]);
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
    private function parse_template( $template_str, $links_to_url = false, $hide_unknown_variables = true ) {

        // The buffer prevents strings that are only made of template variables from failing.
        $template_str = 'buffer[[-' .$template_str;
        $variables = array();
        $strings = preg_split('/{!(!|#)([a-zA-Z0-9_]+)\1!}/mi', $template_str);
        preg_match_all('/{!(!|#)([a-zA-Z0-9_]+)\1!}/mi', $template_str, $variables);

        $output = '';
        // last variable will be for cases when the $variables array gets more matches then the $strings array.
        $last_variable = '';
        $good_to_go = isset($variables[2]);
        for($i = 0, $len = count($strings); $i <  $len; $i++ ) {
            $var = ($good_to_go && isset($variables[2][$i])) ? strtoupper($variables[2][$i]) : '';
            $output .= $strings[$i] . (isset($var) && !empty($var) && isset($this->current_row[$var]) ? $this->current_row[$var] : '');
            $last_variable = ($good_to_go && isset($variables[2][($i + 1)])) ? strtoupper($variables[2][($i + 1)]) : '';
        }
        $output .= $last_variable;
        // Convert URLs into links (unless they are in html attributes
        // taken from http://stackoverflow.com/questions/12538358/convert-url-to-links-from-string-except-if-they-are-in-an-attribute-of-an-html-t
        if ($links_to_url) {
            $output = preg_replace('$(https?://)([a-z0-9_./?=&#-]+)(?![^<>]*>)$i', ' <a href="$1$2" target="_blank">$2</a> ', $output." ");
            $output = preg_replace('$(www\.[a-z0-9_./?=&#-]+)(?![^<>]*>)$i', '<a target="_blank" href="http://$1"  target="_blank">$1</a> ', $output." ");
        }

        // Remove the buffer on the templating.
        $parsed_content = preg_replace("/(^buffer\[\[-)/mi", "", $output);
        // Put hide_unknown_variables back to its default setting
        $this->hide_unknown_variables = true;
        return $parsed_content;
    }


    /**
     * This is the method that consumes an CSV and turns it into posts.
     *
     * @param \Google_Service_Drive|string $gdrive - $gdrive is either \Google_Service_Drive or string == 'treat_as_uri'
     * @param array $options
     * @param int $n_tests
     * @return bool|null|string
     * @throws \Exception
     */
    public function parse_file($gdrive, $options, $n_tests = 0 ) {
        $successes = 0;
        $failures = 0;
        // Parse Through the Options.
        $sheet_label = $options['sheet_label'];
        $sheet_id = $options['sheet_id'];
        $post_status = $options['post_status'];
        $content_template = $options['content_template'];
        $title_template = $options['title_template'];
        $author = isset($options['author']) ? $options['author'] : 1;
        $the_tags = isset($options['the_tags']) ? $options['the_tags'] : "";
        $category = isset($options['category']) ? $options['category'] : 1;
        $urls_to_links = isset($options['urls_to_links']) ? (bool)$options['urls_to_links'] : true;
        $featured_image = $options['featured_image'];
        $stored_last_line = isset($options['stored_last_line']) ? $options['stored_last_line'] : 1;

        // $gdrive is either \Google_Service_Drive or string == 'treat_as_uri'
        if (!$this->get($gdrive, $sheet_id)) {
            return Debug_abug::log("returning because could not get file for template: $sheet_label", null);
        }
        $this->testing = (boolval($n_tests));

        // Alright, let's parse this sheet.
        $csv_text = $this->csv_text;
        // This separates everything correctly, google sheets uses \r for lines and " as enclosure
        $csv_rows = str_getcsv($csv_text, "\r", '"');
        if (!is_array($csv_rows)) {
            return Debug_abug::log("Can't get array of rows from the CSV file for template: $sheet_label", false);
        }


        $all_last_lines = get_option('gdrive_to_posts_template_csv_last_line');
        if (!is_array($all_last_lines) || (!$this->testing && $all_last_lines[$sheet_label] != $stored_last_line)) {
            // Either we don't have last lines or we aren't testing and the last line passed doesn't match!
            $reason = $all_last_lines[$sheet_label] != $stored_last_line ? "Stored last line in template $sheet_label doesn't match line passed to parse_file. " : "";
            $reason .= !is_array($all_last_lines) ? "No last lines stored in database." : "";
            return Debug_abug::log($reason, false);
        }

        // Row 1 becomes our keys for every other row, needed for templating.
        $row_number = 1;
        $keys = str_getcsv( (array_shift($csv_rows)), ',', '"');
        $keys = array_map(function($variable) {
            $variable = preg_replace('~(\s|-|/)~', '_', $variable);
            $variable = preg_replace("~[^a-zA-Z0-9]+~", "", $variable);
            $variable = strtoupper($variable);
            return $variable;
        }, $keys);

        $output = '';
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
            // If the rows are different sizes then that should be fixed before combining is attempted.
            if (count($keys) !== count($row_items)) {
                if (count($keys) > count($row_items)) {
                    $row_items = array_pad($row_items, count($keys), '');
                } else {
                    $keys = array_pad($keys, count($row_items), '');
                }
            }

            // Setup the current row from CSV into Workhorse.
            $entry = array_combine($keys, $row_items);
            $this->current_row = $entry;

            // Use parse_template to switch out {{!!!!}} for the data in $this->current_row which is implicitly in the
            // parse_template() method as it is part of this class GDrive_to_Posts_Workhorse.
            $content = $this->parse_template($content_template, $urls_to_links);
            $the_featured_image = $this->parse_template($featured_image);
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
                if ($post_insert_id = $this->create_post($post_content, $the_featured_image)) {
                    // Update the last row for this file (if we are not testing).
                    if (!$this->testing) {
                        $all_last_lines[$sheet_label] = $row_number;
                        update_option( 'gdrive_to_posts_template_csv_last_line', $all_last_lines );
                        // COOL! A Post has been parsed and recorded where it was updated from updated!
                    }
                    $output .= "<br>Created new post at " . get_page_uri($post_insert_id);
                    $successes++;
                } else {
                    $failures++;
                    $output .= "<br>Failed to create page {$row_number}...";
                }
            } else {
                $output .= "<br>Failed to parse a row {$row_number}...";
                $failures++;
            }

        }

        Debug_abug::log("Template $sheet_label created $successes new posts and $failures failures.");
        $this->testing = false;
        return $output;
    }


    /**
     * An array holding posts that were created, in their array form as passed to wp_insert_post.
     * The array is only tracked when testing by users.
     *
     * @return array
     */
    public function get_work() {
        if (isset($this->work_done)) {
            return $this->work_done;
        }
    }

    /**
     * Builds and publish post from array
     * @param $post_args
     * @param $featured_image
     * @return int|\WP_Error
     */
    private function create_post($post_args, $featured_image) {
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

        if ($this->testing) {
            // When testing we store the work done to an array which can be returned from or
            // gotten from methods of this class using get_work()
            $this->work_done[] = $post;
        }

        // remove all filters is so that iframes will work
        remove_all_filters("content_save_pre");
        // Insert the post into the database
        $id = wp_insert_post( $post );

        // Now we want to get the image if there is an image with this template
        if ($featured_image != "") {
            @Gdrive_to_posts_remote_images::fetch_featured_image($featured_image, (int)$id);
        }

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