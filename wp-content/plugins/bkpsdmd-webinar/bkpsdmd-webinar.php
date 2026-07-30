<?php
/**
 * Plugin Name: BKPSDMD Webinar Management
 * Plugin URI:  https://bkpsdmd.bangka.go.id
 * Description: Sistem manajemen webinar instansi — pendaftaran, absensi, SK Minut, petikan sertifikat dengan QR verifikasi.
 * Version:     1.0.7
 * Author:      BKPSDMD Bangka
 * Author URI:  https://bkpsdmd.bangka.go.id
 * Text Domain: bkpsdmd-wbr
 * License:     GPL-2.0+
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Konstanta ────────────────────────────────────────────────────────────────
define( 'WBR_VERSION',  '1.0.7' );
define( 'WBR_PATH',     plugin_dir_path( __FILE__ ) );
define( 'WBR_URL',      plugin_dir_url( __FILE__ ) );
define( 'WBR_UPLOAD',   WP_CONTENT_DIR . '/uploads/bkpsdmd-webinar/' );
define( 'WBR_UPLOAD_URL', WP_CONTENT_URL . '/uploads/bkpsdmd-webinar/' );

// ── Autoload includes ────────────────────────────────────────────────────────
$includes = [
    'includes/class-db.php',
    'includes/class-roles.php',
    'includes/class-cpt.php',
    'includes/class-qrcode.php',
    'includes/class-email.php',
    'includes/class-registrant.php',
    'includes/class-attendance.php',
    'includes/class-sk.php',
    'includes/class-certificate.php',
    'includes/class-document.php',
    'includes/class-ajax.php',
    'admin/class-admin-menu.php',
    'admin/class-meta-box.php',
    'public/class-shortcode.php',
    'public/class-public-router.php',
];
foreach ( $includes as $file ) {
    require_once WBR_PATH . $file;
}

// ── Activation ────────────────────────────────────────────────────────────────
register_activation_hook( __FILE__, 'wbr_activate' );
function wbr_activate() {
    WBR_DB::create_tables();
    WBR_CPT::register();
    WBR_Roles::create();
    flush_rewrite_rules();

    // Buat direktori upload
    wp_mkdir_p( WBR_UPLOAD . 'templates/' );
    wp_mkdir_p( WBR_UPLOAD . 'sk/' );
    wp_mkdir_p( WBR_UPLOAD . 'certificates/' );
    wp_mkdir_p( WBR_UPLOAD . 'temp/' );

    // Proteksi direktori uploads
    foreach ( [ WBR_UPLOAD, WBR_UPLOAD . 'sk/', WBR_UPLOAD . 'certificates/' ] as $dir ) {
        $htaccess = $dir . '.htaccess';
        if ( ! file_exists( $htaccess ) ) {
            file_put_contents( $htaccess, "Options -Indexes\nDeny from all\n" );
        }
    }
}

// ── Deactivation ──────────────────────────────────────────────────────────────
register_deactivation_hook( __FILE__, 'wbr_deactivate' );
function wbr_deactivate() {
    flush_rewrite_rules();
    wp_clear_scheduled_hook( 'wbr_generate_certificates_batch' );
}

// ── Uninstall (via uninstall.php) ─────────────────────────────────────────────
// Lihat uninstall.php

// ── Init ──────────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', 'wbr_init' );
function wbr_init() {
    // DB upgrade jika versi berubah
    if ( get_option( 'wbr_db_version' ) !== WBR_VERSION ) {
        WBR_DB::create_tables();
        flush_rewrite_rules();
        update_option( 'wbr_db_version', WBR_VERSION );
    }

    // Boot components
    WBR_CPT::init();
    WBR_Ajax::init();
    WBR_AdminMenu::init();
    WBR_MetaBox::init();
    WBR_Shortcode::init();
    WBR_PublicRouter::init();

    // WP Cron: batch generate certificates
    add_action( 'wbr_generate_certificates_batch', [ 'WBR_Certificate', 'process_batch' ] );
}

// ── Enqueue assets ────────────────────────────────────────────────────────────
add_action( 'admin_enqueue_scripts', 'wbr_enqueue_admin_assets' );
function wbr_enqueue_admin_assets( $hook ) {
    $is_wbr = ( strpos( $hook, 'wbr' ) !== false )
        || ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'webinar' )
        || ( isset( $_GET['post'] ) && get_post_type( (int) $_GET['post'] ) === 'webinar' );
    if ( ! $is_wbr ) return;

    wp_enqueue_media();
    wp_enqueue_script( 'jquery-ui-sortable' );

    wp_enqueue_style( 'wbr-admin', WBR_URL . 'assets/css/admin.css', [], filemtime( WBR_PATH . 'assets/css/admin.css' ) );
    wp_enqueue_script( 'wbr-admin', WBR_URL . 'assets/js/admin.js', [ 'jquery', 'jquery-ui-sortable' ], filemtime( WBR_PATH . 'assets/js/admin.js' ), true );
    wp_enqueue_script( 'wbr-form-builder', WBR_URL . 'assets/js/form-builder.js', [ 'jquery', 'jquery-ui-sortable' ], filemtime( WBR_PATH . 'assets/js/form-builder.js' ), true );

    wp_localize_script( 'wbr-admin', 'wbrAdmin', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'wbr_admin_nonce' ),
        'strings' => [
            'confirm_delete'  => 'Yakin ingin menghapus?',
            'confirm_revoke'  => 'Yakin ingin mencabut petikan ini? Tindakan ini tidak dapat dibatalkan.',
            'confirm_sk'      => 'Generate draft SK Minut? Pastikan data absensi sudah lengkap.',
            'saving'          => 'Menyimpan...',
            'error'           => 'Terjadi kesalahan. Coba lagi.',
        ],
    ] );
}

add_action( 'wp_enqueue_scripts', 'wbr_enqueue_public_assets' );
function wbr_enqueue_public_assets() {
    global $post;
    $has_wbr = is_singular( 'webinar' )
        || ( is_a( $post, 'WP_Post' ) && (
            has_shortcode( $post->post_content, 'webinar_list' )
            || has_shortcode( $post->post_content, 'webinar_detail' )
        ))
        || get_query_var( 'wbr_token' ) || isset( $_GET['wbr_token'] )
        || get_query_var( 'wbr_verify' ) || isset( $_GET['wbr_verify'] )
        || get_query_var( 'wbr_absensi' ) || isset( $_GET['wbr_absensi'] );

    if ( ! $has_wbr ) return;

    wp_enqueue_style( 'wbr-public', WBR_URL . 'assets/css/public.css', [], filemtime( WBR_PATH . 'assets/css/public.css' ) );
    wp_enqueue_script( 'wbr-public', WBR_URL . 'assets/js/public.js', [ 'jquery' ], filemtime( WBR_PATH . 'assets/js/public.js' ), true );
    wp_localize_script( 'wbr-public', 'wbrPublic', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'wbr_public_nonce' ),
    ] );
}
