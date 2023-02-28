<?php
/*
Plugin Name: Image Management
Plugin URI: http://githublink.com
Description: Handle your images
Author: Johanna Julén
Version: 0.0.1
Author URI: http://vidde.org
*/

namespace ViddeImageManager;

$im = new imageManager();
$im->init();

/**
 * Managing images in wordpress
 */
class imageManager {
    private $my_plugin_screen_name;


    public function init() {
        add_action('admin_menu', array($this,'add_submenu_page'));
    }


    public function add_submenu_page() {
        $this->my_plugin_screen_name = add_submenu_page( parent_slug: "upload.php",
                        page_title: __("Manage images", "vimgr"),
                        menu_title: __("Manage images", "vimgr"),
                        capability: "manage_options", 
                        menu_slug: "manage_images",
                        callback: array($this,'display_thePage')
        );
    }

    /**
     * prints the whole admin page where this plugin shows functionality to the user
     */
    public static function display_thePage() {
        global $wpdb; // get wpdb from wordpress
    
        // Get all "attachments" from the database
        $db_attachments = get_posts( array(
            "post_type" => "attachment",
            "numberposts" => -1,
            "post_status" => null,
            "post_parent" => null
        ));
    
        ////////////////////////////////////////
        // display all the pictures not in posts
        ////////////////////////////////////////
        echo '<h1>'.__("Images not attached to any post", "vimgr").'</h1>';
        if($db_attachments) {
            foreach ($db_attachments as $post) {
                $posts_containing_image = array();
    
                // Check if we can find any posts with the image
                $posts_containing_image = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM wp_posts WHERE post_content LIKE %s",
                    "%wp:image {\"id\":".$post->ID.',%'
                    )
                );
                if(empty($posts_containing_image)) {
                    // echo '<pre>'.print_r($posts_containing_image,1).'</pre>';
                    setup_postdata($post);
                    // the_title();
                    the_attachment_link($post->ID, fullsize: false, permalink: true);
                    // the_excerpt();
                }
            }
        }
    }
}

?>
