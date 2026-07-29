<?php
/**
 * Plugin Name: BKPSDMD Short Link & QR Code
 * Plugin URI:  https://bkpsdmd.bangka.go.id
 * Description: Buat short link (5 karakter alphanum) untuk Post & Page, lengkap dengan QR Code otomatis untuk kebutuhan media sosial.
 * Version:     1.0.0
 * Author:      BKPSDMD Bangka
 * Author URI:  https://bkpsdmd.bangka.go.id
 * Text Domain: bkpsdmd-sl
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Konstanta ───────────────────────────────────────────────────────────────
define( 'BKSL_VERSION',  '1.0.0' );
define( 'BKSL_PATH',     plugin_dir_path( __FILE__ ) );
define( 'BKSL_URL',      plugin_dir_url( __FILE__ ) );
define( 'BKSL_TABLE',    'bkpsdmd_shortlinks' );
define( 'BKSL_SLUG_LEN', 5 );

// ── Include ──────────────────────────────────────────────────────────────────
require_once BKSL_PATH . 'lib/phpqrcode/qrlib.php';
require_once BKSL_PATH . 'includes/class-db.php';
require_once BKSL_PATH . 'includes/class-qrcode.php';
require_once BKSL_PATH . 'includes/class-redirector.php';
require_once BKSL_PATH . 'includes/class-ajax.php';
require_once BKSL_PATH . 'admin/admin-assets.php';
require_once BKSL_PATH . 'admin/meta-box.php';
require_once BKSL_PATH . 'admin/admin-page.php';

// ── Activation / Deactivation ────────────────────────────────────────────────
register_activation_hook( __FILE__, 'bksl_activate' );
function bksl_activate() {
    BKSL_DB::create_table();
    BKSL_Redirector::flush_rules();
}

register_deactivation_hook( __FILE__, 'bksl_deactivate' );
function bksl_deactivate() {
    flush_rewrite_rules();
}

register_uninstall_hook( __FILE__, 'bksl_uninstall' );
function bksl_uninstall() {
    BKSL_DB::drop_table();
}

// ── Init ─────────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', 'bksl_init' );
function bksl_init() {
    // DB upgrade check
    if ( get_option( 'bksl_db_version' ) !== BKSL_VERSION ) {
        BKSL_DB::create_table();
        update_option( 'bksl_db_version', BKSL_VERSION );
    }
    // Boot redirect rules
    BKSL_Redirector::init();
}

// ── Auto-create short link on publish ────────────────────────────────────────
add_action( 'publish_post', 'bksl_auto_create', 10, 2 );
add_action( 'publish_page', 'bksl_auto_create', 10, 2 );
function bksl_auto_create( $post_id, $post ) {
    // Jangan buat duplikat
    if ( BKSL_DB::get_by_post_id( $post_id ) ) {
        return;
    }
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }
    $slug = BKSL_DB::generate_unique_slug();
    BKSL_DB::insert( $post_id, $slug );
}
