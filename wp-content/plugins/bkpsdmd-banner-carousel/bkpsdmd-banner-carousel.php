<?php
/**
 * Plugin Name: BKPSDMD Banner Carousel
 * Plugin URI:  https://bkpsdmd.bangka.go.id
 * Description: Kelola carousel banner halaman utama dengan admin panel — gambar, judul, deskripsi, CTA, animasi, autoplay, dan lebih banyak lagi.
 * Version:     1.0.0
 * Author:      BKPSDMD Bangka
 * Author URI:  https://bkpsdmd.bangka.go.id
 * Text Domain: bkpsdmd-bc
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Konstanta ────────────────────────────────────────────────────────────────
define( 'BKBC_VERSION', '1.0.0' );
define( 'BKBC_PATH',    plugin_dir_path( __FILE__ ) );
define( 'BKBC_URL',     plugin_dir_url( __FILE__ ) );

// ── Includes ─────────────────────────────────────────────────────────────────
require_once BKBC_PATH . 'includes/class-cpt.php';
require_once BKBC_PATH . 'includes/class-meta-box.php';
require_once BKBC_PATH . 'includes/class-settings.php';
require_once BKBC_PATH . 'includes/class-shortcode.php';
require_once BKBC_PATH . 'admin/admin-assets.php';
require_once BKBC_PATH . 'admin/admin-page.php';

// ── Init ─────────────────────────────────────────────────────────────────────
add_action( 'init', [ 'BKBC_CPT', 'register' ] );
add_action( 'add_meta_boxes', [ 'BKBC_MetaBox', 'register' ] );
add_action( 'save_post_banner_slide', [ 'BKBC_MetaBox', 'save' ], 10, 2 );
add_action( 'admin_menu', [ 'BKBC_Settings', 'register_menu' ] );
add_action( 'admin_init', [ 'BKBC_Settings', 'register_settings' ] );
add_shortcode( 'bkpsdmd_banner_carousel', [ 'BKBC_Shortcode', 'render' ] );

// ── Reorder via AJAX ─────────────────────────────────────────────────────────
add_action( 'wp_ajax_bkbc_reorder', 'bkbc_ajax_reorder' );
function bkbc_ajax_reorder() {
    check_ajax_referer( 'bkbc_reorder', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }
    $order = json_decode( stripslashes( $_POST['order'] ?? '[]' ), true );
    if ( ! is_array( $order ) ) {
        wp_send_json_error( 'Invalid data' );
    }
    foreach ( $order as $i => $post_id ) {
        wp_update_post( [ 'ID' => absint( $post_id ), 'menu_order' => (int) $i ] );
    }
    wp_send_json_success( 'Reordered' );
}

// ── Toggle aktif/nonaktif via AJAX ────────────────────────────────────────────
add_action( 'wp_ajax_bkbc_toggle', 'bkbc_ajax_toggle' );
function bkbc_ajax_toggle() {
    check_ajax_referer( 'bkbc_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }
    $post_id = absint( $_POST['post_id'] ?? 0 );
    $current = get_post_meta( $post_id, '_bkbc_active', true );
    $new_val = $current === '1' ? '0' : '1';
    update_post_meta( $post_id, '_bkbc_active', $new_val );
    wp_send_json_success( [ 'active' => $new_val ] );
}
