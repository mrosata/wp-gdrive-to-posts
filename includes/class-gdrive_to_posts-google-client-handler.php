<?php
/**
 * Created by Stayshine Web Development.
 * Author: Michael Rosata
 * Email: mike@stayshine.com
 * Date: 11/15/15
 * Time: 9:32 AM
 *
 * Project: wp-dev
 */

namespace gdrive_to_posts;


class Google_Client_Handler
{

    private $google_client;
    private $key_file;
    private $api_email;
    private $fingerprint;
    private $interval;

    private $config_opts = array(
        'google_api_key',
        'service_account_email_address',
        'service_certificate_fingerprints',
        'key_file_location'
    );

    public $OK;

    function __construct($config)
    {
        //notasecret
        // Make sure that all the config options are set.
        foreach($this->config_opts as $key => $val) {
            if (!isset($config[$val])) {
                return null;
            }
        }

        // Parse through config options
        $this->key_file = $config['key_file_location'];
        $this->api_email = $config['service_account_email_address'];
        $this->fingerprint = $config['service_certificate_fingerprints'];
        $this->interval = isset($config['fetch_interval']) ? $config['fetch_interval'] : 2;

        // Connect to Google Client
        $this->init_google_client();
        $this->OK = (is_a($this->google_client, 'Google_Client'));
    }


    private function init_google_client() {
        try {
            if (!$this->google_client) {
                $client_email = $this->api_email;
                $key_file = $this->key_file;

                $private_key = file_get_contents(plugin_dir_path( dirname( __FILE__ ) ) . $key_file);
                $scopes = array(
                    'https://www.googleapis.com/auth/drive',
                    'https://www.googleapis.com/auth/sqlservice.admin',
                    'https://www.googleapis.com/auth/drive.readonly',
                    'https://www.googleapis.com/auth/drive.photos.readonly',
                    'https://www.googleapis.com/auth/drive.metadata.readonly',
                    'https://www.googleapis.com/auth/drive.metadata',
                    'https://www.googleapis.com/auth/drive.file'
                );

                $credentials = new \Google_Auth_AssertionCredentials(
                    $client_email,
                    $scopes,
                    $private_key
                );

                $client = new \Google_Client();
                $client->setAssertionCredentials($credentials);
                if ($client->getAuth()->isAccessTokenExpired()) {
                    $client->getAuth()->refreshTokenWithAssertion();
                }

                $this->google_client = $client;
            }

            return $this->google_client;
        } catch (\Google_Auth_Exception $e) {
            // The Google Auth didn't go through
            $this->google_client = null;
        }
    }


    /**
     * Return a Service wrapper.
     *
     * @param string $what - which service? 'drive', 'sheets'
     * @return bool
     */
    public function connect($what = 'drive') {
        $method_name = "connect_to_{$what}";

        if (!$this->google_client) {
            return false;
        }
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }
        return false;
    }


    private function connect_to_drive() {
        // Make sure that the Google Client has connected.
        if (!is_a($this->google_client, 'Google_Client')) {
            if (defined('GDRIVE_TO_POSTS_DEBUG') && GDRIVE_TO_POSTS_DEBUG) {
                echo "The Google Client Object isn't preset!";
                exit;
            }
            return false;
        }

        // Initialize Google Drive service
        try {
            $result = new \Google_Service_Drive($this->google_client);
            return $result;

        }
        catch(\Google_Service_Exception $error) {
            // We were not able to connect to the service
            if (defined('GDRIVE_TO_POSTS_DEBUG') && GDRIVE_TO_POSTS_DEBUG) {
                print_r($error);
            }
        }

        return false;
    }



    function get_sheet($sheet_id) {
        $file = $this->google_drive->files->get($sheet_id);
        if ($file && is_array($file->exportLinks)) {

            // Get the file as text csv using the Google Drive Export method.

            $csv = wp_remote_get($file->exportLinks['text/csv']);
            @$csv = is_array($csv) ? $csv['body'] : null;
            if ($csv) {
                // This will parse the csv and make new posts if that's what it should do.
                $workhorse = new GDrive_to_Posts_Workhorse();
                $workhorse->add($csv);
                // We want to see the output here.
                $workhorse->run(true);
            }
        }
    }

}