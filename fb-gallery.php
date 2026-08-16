<?php
/**
 * Plugin Name: FB-Gallery
 * Plugin URI:  https://github.com/faham112/fb-gallery
 * Description: Stylish image gallery with random domain redirect. Made by ❤️ coded by Faheem Badshah
 * Version:     1.0.0
 * Author:      Faheem Badshah
 * Author URI:  https://wa.me/923013250144
 * Text Domain: fb-gallery
 * License:     GPL-2.0+
 * Update URI:  https://github.com/faham112/fb-gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'FB_GALLERY_VERSION', '1.0.0' );
define( 'FB_GALLERY_PATH', plugin_dir_path( __FILE__ ) );
define( 'FB_GALLERY_URL', plugin_dir_url( __FILE__ ) );
define( 'FB_GALLERY_MAX_DOMAINS', 5 );

require_once FB_GALLERY_PATH . 'includes/functions.php';
require_once FB_GALLERY_PATH . 'includes/admin.php';
require_once FB_GALLERY_PATH . 'includes/frontend.php';
require_once FB_GALLERY_PATH . 'includes/login.php';

/**
 * GitHub Auto-Update (Plugin Update Checker)
 * Download library: https://github.com/YahnisElsts/plugin-update-checker
 * Place the "plugin-update-checker" folder inside this plugin to enable auto-updates.
 */
if ( file_exists( FB_GALLERY_PATH . 'plugin-update-checker/plugin-update-checker.php' ) ) {
    require_once FB_GALLERY_PATH . 'plugin-update-checker/plugin-update-checker.php';

    $fb_gallery_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/faham112/fb-gallery/',
        __FILE__,
        'fb-gallery'
    );

    // Prefer GitHub Releases
    $fb_gallery_update_checker->getVcsApi()->enableReleaseAssets();
}

/**
 * Activation: Create the Gallery page automatically
 */
function fb_gallery_activate() {
    $existing_id = get_option( 'fb_gallery_page_id' );

    if ( $existing_id && get_post( $existing_id ) ) {
        // Page already exists
        return;
    }

    $page_id = wp_insert_post( array(
        'post_title'   => 'FB Gallery',
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => 1,
    ) );

    if ( $page_id && ! is_wp_error( $page_id ) ) {
        update_option( 'fb_gallery_page_id', $page_id );
    }

    // Default settings
    if ( false === get_option( 'fb_gallery_domains' ) ) {
        update_option( 'fb_gallery_domains', array(
            'https://example1.com',
            'https://example2.com',
            'https://example3.com',
            'https://example4.com',
            'https://example5.com',
        ) );
    }

    if ( false === get_option( 'fb_gallery_images' ) ) {
        update_option( 'fb_gallery_images', array(
            'https://m.media-amazon.com/images/I/81WfYGIv3ML.jpg',
            'https://m.media-amazon.com/images/I/81WfYGIv3ML.jpg',
            'https://m.media-amazon.com/images/I/81WfYGIv3ML.jpg',
            'https://m.media-amazon.com/images/I/81WfYGIv3ML.jpg',
            'https://m.media-amazon.com/images/I/81WfYGIv3ML.jpg',
            'https://m.media-amazon.com/images/I/81WfYGIv3ML.jpg',
            'https://m.media-amazon.com/images/I/81WfYGIv3ML.jpg',
            'https://m.media-amazon.com/images/I/81WfYGIv3ML.jpg',
            'https://m.media-amazon.com/images/I/81WfYGIv3ML.jpg',
            'https://m.media-amazon.com/images/I/81WfYGIv3ML.jpg',
        ) );
    }
}
register_activation_hook( __FILE__, 'fb_gallery_activate' );

/**
 * Deactivation (optional cleanup - we keep the page)
 */
function fb_gallery_deactivate() {
    // Keep page and settings so user doesn't lose data
}
register_deactivation_hook( __FILE__, 'fb_gallery_deactivate' );
