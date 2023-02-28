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

// Initialize the plugin
$im = new imageManager();
$im->init();

/**
 * Managing images in wordpress
 */
class imageManager {
    private $my_plugin_screen_name;

    /**
     * Initialize plugin (call to add the submenu page)
     */
    public function init() {
        add_action('admin_menu', array($this,'add_submenu_page'));
    }

    /**
     * call the wordpress function to add the subpage menu and specify function for displaying the page (display_thePage)
     */
    public function add_submenu_page() {
        $this->my_plugin_screen_name = add_submenu_page( parent_slug: "upload.php",
                        page_title: __("Manage images", "vimgr"),
                        menu_title: __("Manage images", "vimgr"),
                        capability: "manage_options", 
                        menu_slug: "manage_images",
                        callback: array($this,'display_thePage') // callback is correct!
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
            echo '<form method="post">
            <div class="attachments-wrapper">
                    <ul class="attachments">';
            foreach ($db_attachments as $post) {
                $posts_containing_image = array();
    
                // Check if we can find any posts with the image
                $posts_containing_image = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM wp_posts WHERE post_content LIKE %s",
                    "%wp:image {\"id\":".$post->ID.',%'
                    )
                );
                if(empty($posts_containing_image)) {
                    echo '<li tabindex="0" role="checkbox" aria-label="post_9478" aria-checked="false" data-id="'.$post->ID.'"
                         class="attachment save-ready">';
                    // echo '<pre>'.print_r($posts_containing_image,1).'</pre>';
                    setup_postdata($post);
                    // the_title();
                    the_attachment_link($post->ID, fullsize: false, permalink: true);
                    // the_excerpt();

                    if ( $post->post_parent ) {
                        $post_parent = get_post( $post->post_parent );
                        $info = array();
                        if ( $post_parent ) {
                            $info['uploadedToTitle'] = $post_parent->post_title ? $post_parent->post_title : __( '(no title)' );
                            $info['uploadedToLink']  = get_edit_post_link( $post->post_parent, 'raw' );
                        }
                        echo 'info:<pre>'.print_r($info,1).'</pre>';
                    }

                    echo '</li>';
                }
            }
            echo "</ul></div></form>";
        }
    }
}

?>
