<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get domains array
 */
function fb_gallery_get_domains() {
    $domains = get_option( 'fb_gallery_domains', array() );
    if ( ! is_array( $domains ) ) {
        $domains = array();
    }
    return array_values( array_filter( array_map( 'trim', $domains ) ) );
}

/**
 * Get images array
 */
function fb_gallery_get_images() {
    $images = get_option( 'fb_gallery_images', array() );
    if ( ! is_array( $images ) ) {
        $images = array();
    }
    return array_values( array_filter( array_map( 'trim', $images ) ) );
}

/**
 * Save domains (max 5)
 */
function fb_gallery_save_domains( $domains ) {
    if ( ! is_array( $domains ) ) {
        $domains = array();
    }
    $clean = array();
    foreach ( $domains as $d ) {
        $d = trim( $d );
        if ( $d === '' ) continue;
        if ( ! preg_match( '~^https?://~i', $d ) ) {
            $d = 'https://' . $d;
        }
        $clean[] = esc_url_raw( $d );
    }
    $clean = array_slice( array_unique( $clean ), 0, FB_GALLERY_MAX_DOMAINS );
    update_option( 'fb_gallery_domains', $clean );
    return $clean;
}

/**
 * Save images
 */
function fb_gallery_save_images( $images ) {
    if ( ! is_array( $images ) ) {
        $images = array();
    }
    $clean = array();
    foreach ( $images as $img ) {
        $img = trim( $img );
        if ( $img !== '' ) {
            $clean[] = esc_url_raw( $img );
        }
    }
    update_option( 'fb_gallery_images', array_values( $clean ) );
    return $clean;
}

/**
 * Credit HTML
 */
function fb_gallery_credit_html() {
    return '<div class="fb-gallery-credit" style="text-align:center;margin-top:28px;padding-top:16px;font-size:13px;color:#666;line-height:1.5;">
        made by ❤️ coded by<br>
        <a href="https://wa.me/923013250144" target="_blank" rel="noopener" style="color:#25D366;text-decoration:none;">Faheem Badshah</a>
    </div>';
}
