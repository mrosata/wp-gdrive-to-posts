<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://mindbetweenthelines.com
 * @since      1.0.0
 *
 * @package    Gdrive_to_posts
 * @subpackage Gdrive_to_posts/includes
 */

namespace gdrive_to_posts;
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Gdrive_to_posts
 * @subpackage Gdrive_to_posts/includes
 * @author     Michael Rosata <mrosata1984@gmail.com>
 */
class Gdrive_to_posts_remote_images {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if (!function_exists('wp_generate_attachment_metadata')) {
			// Need this to get the wp_generate_attachment_metadata function
			require (ABSPATH.'wp-admin/includes/image.php');
		}
	}


	/**
	 * Pass an image url and use the returned image
	 * as the featured image for a post.
	 *
	 * @param $url
	 * @param $parent_id
	 * @param $image_info
	 * @return bool
	 */
	static function fetch_featured_image($url, $parent_id, $image_info = array()) {
		$parent_id = (int)$parent_id;
		$matches = array();
		// Make sure that the wp_images file has been loaded before proceeding.
		if (!function_exists('wp_generate_attachment_metadata')) {
			require_once (ABSPATH.'wp-admin/includes/image.php');
		}

		if (!is_array($image_info)) {
			return false;
		}

		$image_title = isset($image_info['title']) ? esc_html($image_info['title']) : "";
		$image_content = $image_info['content'] = isset($image_info['caption']) ? esc_html($image_info['caption']) : "";

		// Local host uploads folder
		$upload_dir = wp_upload_dir();
		$target_dir = trim($upload_dir['path']."/");

		// Image path infor = dirname, basename, extension, filename
		$url_path     = pathinfo($url);
		$img_filename = $url_path['basename'];

		// Build the save path, making sure not to double up on the backslash
		$unique_name = wp_unique_filename($target_dir, $img_filename);
		$save_path   = $target_dir.$unique_name;
		//$guid        = str_replace("/var/www/html/", "http://localhost/", $save_path);

		$guid = $save_path;
		preg_match("/\.(jpg|jpeg|gif|png|png32|png64|bmp)$/", $img_filename, $matches);
		$mime_type = "image/" . $matches[1];

		// Now recieve the image file from remote host
		if ((bool)($image_data = @file_get_contents($url))) {
			// Save the image to local machine
			file_put_contents($save_path, $image_data);

			// Construct the attachment array
			$attachment = array_merge(array(
					'post_mime_type' => $mime_type,
					'guid'           => $guid,
					'post_parent'    => 0,
					'post_title'     => $image_title,
					'post_content'   => $image_content,
					'post_type'      => 'attachment',
			), array());

			// This should never be set as it would then overwrite an existing attachment.
			if (isset($attachment['ID'])) {
				unset($attachment['ID']);
			}
			// Save the data
			$img_id = wp_insert_attachment($attachment, $guid, 0);
			if (!is_wp_error($img_id)) {
				wp_update_attachment_metadata($img_id, wp_generate_attachment_metadata($img_id, $save_path));
				//set_post_thumbnail($img_id, $parent_id);
			}

			return set_post_thumbnail($parent_id, $img_id);

		}
		// Was not able to get remote url
		return false;
	}
}

