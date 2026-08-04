<?php
/**
 * Plugin Name: BKPSDMD Survei Kepuasan Masyarakat (SKM)
 * Plugin URI:  https://bkpsdmd.bangka.go.id
 * Description: Plugin Kuesioner Survei Kepuasan Masyarakat (SKM) sesuai PermenPANRB No. 14 Tahun 2017 lengkap dengan penyimpanan database, analitik IKM, dan fitur ekspor data.
 * Version:     1.0.0
 * Author:      BKPSDMD Kabupaten Bangka
 * Author URI:  https://bkpsdmd.bangka.go.id
 * Text Domain: bkpsdmd-skm
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Definisi Konstanta ───────────────────────────────────────────────────────
define( 'BKSKM_VERSION', '1.0.0' );
define( 'BKSKM_PATH', plugin_dir_path( __FILE__ ) );
define( 'BKSKM_URL', plugin_dir_url( __FILE__ ) );
define( 'BKSKM_TABLE_RESPONSES', 'bkpsdmd_skm_responses' );

// ── Include Class Files ──────────────────────────────────────────────────────
require_once BKSKM_PATH . 'includes/class-db.php';
require_once BKSKM_PATH . 'includes/class-shortcode.php';
require_once BKSKM_PATH . 'includes/class-endpoint.php';
require_once BKSKM_PATH . 'includes/class-ajax.php';
require_once BKSKM_PATH . 'admin/class-admin.php';

// ── Hook Aktivasi & Deaktivasi ───────────────────────────────────────────────
register_activation_hook( __FILE__, 'bkskm_activate' );
function bkskm_activate() {
    BKSKM_DB::create_tables();
    BKSKM_Endpoint::auto_create_page();
    update_option( 'bkskm_version', BKSKM_VERSION );
}

register_deactivation_hook( __FILE__, 'bkskm_deactivate' );
function bkskm_deactivate() {
    flush_rewrite_rules();
}

// ── Inisialisasi Plugin ──────────────────────────────────────────────────────
add_action( 'plugins_loaded', 'bkskm_init' );
function bkskm_init() {
    // Database upgrade check
    if ( get_option( 'bkskm_version' ) !== BKSKM_VERSION ) {
        BKSKM_DB::create_tables();
        BKSKM_Endpoint::flush_rules();
        update_option( 'bkskm_version', BKSKM_VERSION );
    }

    // Inisialisasi komponen
    BKSKM_Shortcode::init();
    BKSKM_Endpoint::init();
    BKSKM_Ajax::init();
    if ( is_admin() ) {
        BKSKM_Admin::init();
    }
}
