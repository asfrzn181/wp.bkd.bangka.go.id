<?php
/**
 * Title: Main Banner
 * Slug: career-development/main-banner
 *
 * Menampilkan carousel banner yang dikelola dari
 * WP Admin → Banner Carousel.
 *
 * Jika plugin BKPSDMD Banner Carousel tidak aktif atau
 * tidak ada slide, fallback ke banner statis asli.
 */

/* ── Render carousel jika plugin aktif ── */
if ( shortcode_exists( 'bkpsdmd_banner_carousel' ) ) {
    echo do_shortcode( '[bkpsdmd_banner_carousel]' );
    return;
}

/* ── Fallback: banner statis asli ────────────────────────── */
?>
<!-- wp:cover {"overlayColor":"secondary","isUserOverlayColor":true,"isDark":false,"align":"full","className":"banner-section","style":{"spacing":{"padding":{"top":"10rem","bottom":"var:preset|spacing|70","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained","contentSize":"85%"}} -->
<div class="wp-block-cover alignfull is-light banner-section" style="margin-top:0;margin-bottom:0;padding-top:10rem;padding-right:0;padding-bottom:var(--wp--preset--spacing--70);padding-left:0"><span aria-hidden="true" class="wp-block-cover__background has-secondary-background-color has-background-dim-100 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|70"}}}} -->
<div class="wp-block-columns"><<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontSize":"16px","textTransform":"uppercase","letterSpacing":"1px"}},"textColor":"primary","fontFamily":"body"} -->
<p class="has-primary-color has-text-color has-link-color has-body-font-family" style="font-size:16px;letter-spacing:1px;text-transform:uppercase"><?php esc_html_e( 'SELAMAT DATANG DI BKPSDMD', 'career-development' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"elements":{"link":{"color":{"text":"var:preset|color|heading"}}},"typography":{"fontSize":"50px","fontStyle":"normal","fontWeight":"700","lineHeight":"1.3"}},"textColor":"heading","fontFamily":"body"} -->
<h2 class="wp-block-heading has-heading-color has-text-color has-link-color has-body-font-family" style="font-size:50px;font-style:normal;font-weight:700;line-height:1.3"><?php esc_html_e( 'Badan Kepegawaian dan', 'career-development' ); ?> <br><?php esc_html_e( 'Pengembangan SDM', 'career-development' ); ?> <br><?php esc_html_e( 'Kabupaten Bangka', 'career-development' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:buttons {"className":"book-btn","style":{"spacing":{"margin":{"top":"0","bottom":"0"}}}} -->
<div class="wp-block-buttons book-btn" style="margin-top:0;margin-bottom:0"><<!-- wp:button {"backgroundColor":"primary","textColor":"background","style":{"elements":{"link":{"color":{"text":"var:preset|color|background"}}},"typography":{"fontSize":"16px","letterSpacing":"0px","fontStyle":"normal","fontWeight":"600"},"spacing":{"padding":{"left":"var:preset|spacing|60","right":"var:preset|spacing|60","top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"fontFamily":"plus-jakarta-sans"} -->
<div class="wp-block-button"><a href="#" class="wp-block-button__link has-background-color has-primary-background-color has-text-color has-background has-link-color has-plus-jakarta-sans-font-family has-custom-font-size wp-element-button" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--60);font-size:16px;font-style:normal;font-weight:600;letter-spacing:0px"><?php esc_html_e( 'Pelajari Lebih Lanjut', 'career-development' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></div>
<!-- /wp:cover -->