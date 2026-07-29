<?php
/**
 * Enqueue admin & frontend assets
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Admin assets ──────────────────────────────────────────────────────────────
add_action( 'admin_enqueue_scripts', 'bkbc_enqueue_admin_assets' );
function bkbc_enqueue_admin_assets( $hook ) {
    $is_bkbc_page = strpos( $hook, 'bkbc' ) !== false
        || ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'banner_slide' )
        || ( isset( $_GET['post'] ) && get_post_type( (int) $_GET['post'] ) === 'banner_slide' );

    if ( ! $is_bkbc_page ) return;

    // jQuery UI Sortable (sudah bundled di WP)
    wp_enqueue_script( 'jquery-ui-sortable' );

    wp_enqueue_style(
        'bkbc-admin',
        BKBC_URL . 'assets/css/admin.css',
        [],
        filemtime( BKBC_PATH . 'assets/css/admin.css' )
    );

    wp_enqueue_script(
        'bkbc-admin',
        BKBC_URL . 'assets/js/admin.js',
        [ 'jquery', 'jquery-ui-sortable' ],
        filemtime( BKBC_PATH . 'assets/js/admin.js' ),
        true
    );

    wp_localize_script( 'bkbc-admin', 'bkbcAdmin', [
        'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
        'reorderNonce' => wp_create_nonce( 'bkbc_reorder' ),
        'toggleNonce'  => wp_create_nonce( 'bkbc_nonce' ),
    ] );
}

// ── Frontend assets ───────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'bkbc_enqueue_frontend_assets' );
function bkbc_enqueue_frontend_assets() {
    // Hanya load jika ada shortcode di halaman (cek post content)
    global $post;
    $has_carousel = is_front_page()
        || ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'bkpsdmd_banner_carousel' ) );

    if ( ! $has_carousel ) return;

    wp_enqueue_style(
        'bkbc-carousel',
        BKBC_URL . 'assets/css/carousel.css',
        [],
        filemtime( BKBC_PATH . 'assets/css/carousel.css' )
    );

    wp_enqueue_script(
        'bkbc-carousel',
        BKBC_URL . 'assets/js/carousel.js',
        [],
        filemtime( BKBC_PATH . 'assets/js/carousel.js' ),
        true
    );
}
