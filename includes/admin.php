<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add admin menu
 */
function fb_gallery_admin_menu() {
    add_menu_page(
        'FB Gallery',
        'FB Gallery',
        'manage_options',
        'fb-gallery',
        'fb_gallery_admin_page',
        'dashicons-images-alt2',
        58
    );
}
add_action( 'admin_menu', 'fb_gallery_admin_menu' );

/**
 * Handle form submissions
 */
function fb_gallery_handle_admin_actions() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( ! isset( $_POST['fb_gallery_nonce'] ) || ! wp_verify_nonce( $_POST['fb_gallery_nonce'], 'fb_gallery_save' ) ) {
        return;
    }

    $action = isset( $_POST['fb_action'] ) ? sanitize_text_field( $_POST['fb_action'] ) : '';

    // Add Domain
    if ( $action === 'add_domain' ) {
        $domain = isset( $_POST['domain'] ) ? sanitize_text_field( $_POST['domain'] ) : '';
        if ( $domain ) {
            $domains = fb_gallery_get_domains();
            if ( count( $domains ) < FB_GALLERY_MAX_DOMAINS ) {
                if ( ! preg_match( '~^https?://~i', $domain ) ) {
                    $domain = 'https://' . $domain;
                }
                $domains[] = esc_url_raw( $domain );
                fb_gallery_save_domains( $domains );
                add_settings_error( 'fb_gallery', 'domain_added', 'Domain added successfully.', 'success' );
            } else {
                add_settings_error( 'fb_gallery', 'domain_limit', 'Maximum 5 domains allowed.', 'error' );
            }
        }
    }

    // Delete Domain
    if ( $action === 'delete_domain' ) {
        $index = isset( $_POST['index'] ) ? intval( $_POST['index'] ) : -1;
        $domains = fb_gallery_get_domains();
        if ( isset( $domains[ $index ] ) ) {
            array_splice( $domains, $index, 1 );
            fb_gallery_save_domains( $domains );
            add_settings_error( 'fb_gallery', 'domain_deleted', 'Domain removed.', 'success' );
        }
    }

    // Add Image by URL
    if ( $action === 'add_image_url' ) {
        $url = isset( $_POST['image_url'] ) ? esc_url_raw( trim( $_POST['image_url'] ) ) : '';
        if ( $url && filter_var( $url, FILTER_VALIDATE_URL ) ) {
            $images = fb_gallery_get_images();
            $images[] = $url;
            fb_gallery_save_images( $images );
            add_settings_error( 'fb_gallery', 'image_added', 'Image URL added.', 'success' );
        } else {
            add_settings_error( 'fb_gallery', 'image_invalid', 'Invalid image URL.', 'error' );
        }
    }

    // Upload Image
    if ( $action === 'upload_image' && ! empty( $_FILES['image_file']['name'] ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $file = $_FILES['image_file'];
        $upload = wp_handle_upload( $file, array( 'test_form' => false ) );

        if ( ! isset( $upload['error'] ) && isset( $upload['url'] ) ) {
            $images = fb_gallery_get_images();
            $images[] = $upload['url'];
            fb_gallery_save_images( $images );
            add_settings_error( 'fb_gallery', 'image_uploaded', 'Image uploaded successfully.', 'success' );
        } else {
            $msg = isset( $upload['error'] ) ? $upload['error'] : 'Upload failed.';
            add_settings_error( 'fb_gallery', 'upload_error', $msg, 'error' );
        }
    }

    // Delete Image
    if ( $action === 'delete_image' ) {
        $index = isset( $_POST['index'] ) ? intval( $_POST['index'] ) : -1;
        $images = fb_gallery_get_images();
        if ( isset( $images[ $index ] ) ) {
            array_splice( $images, $index, 1 );
            fb_gallery_save_images( $images );
            add_settings_error( 'fb_gallery', 'image_deleted', 'Image removed.', 'success' );
        }
    }
}
add_action( 'admin_init', 'fb_gallery_handle_admin_actions' );

/**
 * Admin page HTML
 */
function fb_gallery_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $domains = fb_gallery_get_domains();
    $images  = fb_gallery_get_images();
    $page_id = get_option( 'fb_gallery_page_id' );
    $page    = $page_id ? get_post( $page_id ) : null;
    ?>
    <div class="wrap fb-gallery-admin">
        <h1>FB Gallery</h1>

        <?php settings_errors( 'fb_gallery' ); ?>

        <div class="fb-notice" style="background:#fff;border-left:4px solid #2271b1;padding:12px 16px;margin:16px 0;box-shadow:0 1px 1px rgba(0,0,0,.04);">
            <strong>Homepage Setup:</strong>
            <?php if ( $page ) : ?>
                Page “<strong><?php echo esc_html( $page->post_title ); ?></strong>” already created.
                Go to <a href="<?php echo esc_url( admin_url( 'options-reading.php' ) ); ?>"><strong>Settings → Reading</strong></a>
                → select “A static page” → set Homepage to <strong><?php echo esc_html( $page->post_title ); ?></strong>.
            <?php else : ?>
                Gallery page not found. Deactivate & Activate the plugin again.
            <?php endif; ?>
        </div>

        <!-- ================= DOMAINS ================= -->
        <div class="fb-card">
            <h2>Redirect Domains <span class="count"><?php echo count( $domains ); ?>/5</span></h2>
            <p class="description">Maximum 5 domains. One will be chosen randomly on redirect.</p>

            <?php if ( empty( $domains ) ) : ?>
                <p style="color:#666;">No domains added yet.</p>
            <?php else : ?>
                <ul class="fb-list">
                    <?php foreach ( $domains as $i => $domain ) : ?>
                        <li>
                            <span class="fb-url"><?php echo esc_html( $domain ); ?></span>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field( 'fb_gallery_save', 'fb_gallery_nonce' ); ?>
                                <input type="hidden" name="fb_action" value="delete_domain">
                                <input type="hidden" name="index" value="<?php echo $i; ?>">
                                <button type="submit" class="button button-link-delete" onclick="return confirm('Remove this domain?')">Remove</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ( count( $domains ) < 5 ) : ?>
                <form method="post" class="fb-form-row">
                    <?php wp_nonce_field( 'fb_gallery_save', 'fb_gallery_nonce' ); ?>
                    <input type="hidden" name="fb_action" value="add_domain">
                    <input type="text" name="domain" class="regular-text" placeholder="https://yourdomain.com" required>
                    <button type="submit" class="button button-primary">Add Domain</button>
                </form>
            <?php else : ?>
                <p class="description">Limit reached (5/5). Remove one to add new.</p>
            <?php endif; ?>
        </div>

        <!-- ================= IMAGES ================= -->
        <div class="fb-card">
            <h2>Gallery Images <span class="count"><?php echo count( $images ); ?></span></h2>
            <p class="description">Add by URL or upload from your device.</p>

            <?php if ( empty( $images ) ) : ?>
                <p style="color:#666;">No images yet.</p>
            <?php else : ?>
                <ul class="fb-list fb-images">
                    <?php foreach ( $images as $i => $img ) : ?>
                        <li>
                            <img src="<?php echo esc_url( $img ); ?>" alt="" width="48" height="48" style="object-fit:cover;border-radius:6px;vertical-align:middle;margin-right:10px;" onerror="this.style.display='none'">
                            <span class="fb-url"><?php echo esc_html( $img ); ?></span>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field( 'fb_gallery_save', 'fb_gallery_nonce' ); ?>
                                <input type="hidden" name="fb_action" value="delete_image">
                                <input type="hidden" name="index" value="<?php echo $i; ?>">
                                <button type="submit" class="button button-link-delete" onclick="return confirm('Delete this image?')">Delete</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post" class="fb-form-row" style="margin-top:16px;">
                <?php wp_nonce_field( 'fb_gallery_save', 'fb_gallery_nonce' ); ?>
                <input type="hidden" name="fb_action" value="add_image_url">
                <input type="url" name="image_url" class="regular-text" placeholder="https://example.com/image.jpg" required>
                <button type="submit" class="button button-primary">Add URL</button>
            </form>

            <form method="post" enctype="multipart/form-data" class="fb-upload-box">
                <?php wp_nonce_field( 'fb_gallery_save', 'fb_gallery_nonce' ); ?>
                <input type="hidden" name="fb_action" value="upload_image">
                <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif" required>
                <button type="submit" class="button button-primary" style="margin-top:10px;">Upload Image</button>
            </form>
        </div>

        <?php echo fb_gallery_credit_html(); ?>
    </div>

    <style>
        .fb-gallery-admin .fb-card {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 8px;
            padding: 20px 22px;
            margin: 20px 0;
            max-width: 780px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .fb-gallery-admin .fb-card h2 {
            margin-top: 0;
            font-size: 1.2em;
        }
        .fb-gallery-admin .count {
            font-size: 13px;
            color: #666;
            font-weight: 400;
        }
        .fb-gallery-admin .fb-list {
            list-style: none;
            margin: 12px 0 16px;
            padding: 0;
        }
        .fb-gallery-admin .fb-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f6f7f7;
            border: 1px solid #dcdcde;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .fb-gallery-admin .fb-url {
            flex: 1;
            min-width: 0;
            word-break: break-all;
            font-size: 13px;
        }
        .fb-gallery-admin .fb-form-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 12px;
        }
        .fb-gallery-admin .fb-form-row input[type="text"],
        .fb-gallery-admin .fb-form-row input[type="url"] {
            flex: 1;
            min-width: 220px;
        }
        .fb-gallery-admin .fb-upload-box {
            margin-top: 18px;
            padding: 16px;
            border: 1px dashed #c3c4c7;
            border-radius: 6px;
            background: #f6f7f7;
        }
        @media (max-width: 600px) {
            .fb-gallery-admin .fb-form-row {
                flex-direction: column;
                align-items: stretch;
            }
            .fb-gallery-admin .fb-form-row input {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
    <?php
}
