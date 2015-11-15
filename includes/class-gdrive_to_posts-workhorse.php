<?php
/**
 *
 */

namespace gdrive_to_posts;

class GDrive_To_Posts_Workhorse
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
        return $keys;
    }


    public function run($show_output = false) {
        $csv_text = $this->csv_text;

        $csv_rows = str_getcsv($csv_text, "\r", '"');
        if (!is_array($csv_rows)) {
            return false;
        }

        $keys = str_getcsv( (array_shift($csv_rows)), ',', '"');
        foreach ($csv_rows as $row ) {
            ?> <h3>Ready for the next row???</h3> <?php

            $row_items = str_getcsv($row, ',', '"');

            $entry = array_combine($keys, $row_items);

            echo var_dump($entry);

        }
    }
}