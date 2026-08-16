<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Change WordPress login URL to /login.php
 */
function fb_gallery_login_url( $login_url, $redirect = '', $force_reauth = false ) {
    $login_url = home_url( '/login.php' );

    if ( ! empty( $redirect ) ) {
        $login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
    }

    if ( $force_reauth ) {
        $login_url = add_query_arg( 'reauth', '1', $login_url );
    }

    return $login_url;
}
add_filter( 'login_url', 'fb_gallery_login_url', 10, 3 );

/**
 * Also change site_url for wp-login.php requests
 */
function fb_gallery_site_url( $url, $path, $scheme, $blog_id ) {
    if ( $path === 'wp-login.php' || strpos( $path, 'wp-login.php' ) === 0 ) {
        $url = home_url( '/login.php' );

        // Preserve query string if any
        if ( strpos( $path, '?' ) !== false ) {
            $query = substr( $path, strpos( $path, '?' ) );
            $url  .= $query;
        }
    }
    return $url;
}
add_filter( 'site_url', 'fb_gallery_site_url', 10, 4 );

/**
 * Handle /login.php request → load real WordPress login
 */
function fb_gallery_handle_custom_login() {
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';

    // Match /login.php (with or without query string)
    if ( preg_match( '#/login\.php(?:\?|$)#', $request_uri ) ) {
        // Prevent redirect loops
        if ( defined( 'FB_GALLERY_LOADING_LOGIN' ) ) {
            return;
        }
        define( 'FB_GALLERY_LOADING_LOGIN', true );

        // Load the real WordPress login form
        require ABSPATH . 'wp-login.php';
        exit;
    }
}
add_action( 'init', 'fb_gallery_handle_custom_login', 1 );

/**
 * Redirect wp-login.php and wp-admin (when logged out) to /login.php
 */
function fb_gallery_redirect_to_custom_login() {
    if ( is_user_logged_in() ) {
        return;
    }

    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';

    // Already on our custom login → do nothing
    if ( preg_match( '#/login\.php(?:\?|$)#', $request_uri ) ) {
        return;
    }

    // Redirect direct wp-login.php visits
    if ( strpos( $request_uri, 'wp-login.php' ) !== false ) {
        $redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : admin_url();
        wp_safe_redirect( home_url( '/login.php?redirect_to=' . urlencode( $redirect_to ) ) );
        exit;
    }

    // Redirect wp-admin when not logged in
    if ( is_admin() && ! wp_doing_ajax() && ! wp_doing_cron() ) {
        wp_safe_redirect( home_url( '/login.php?redirect_to=' . urlencode( admin_url() ) ) );
        exit;
    }
}
add_action( 'init', 'fb_gallery_redirect_to_custom_login', 2 );

/**
 * After successful login, go to admin dashboard
 * (default WP behavior, just making sure)
 */
function fb_gallery_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
    if ( ! is_wp_error( $user ) && isset( $user->roles ) && is_array( $user->roles ) ) {
        if ( in_array( 'administrator', $user->roles, true ) ) {
            return admin_url();
        }
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'fb_gallery_login_redirect', 10, 3 );
