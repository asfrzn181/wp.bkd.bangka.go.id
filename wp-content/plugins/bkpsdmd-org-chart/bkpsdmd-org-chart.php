<?php
/**
 * Plugin Name: BKPSDMD Struktur Organisasi
 * Plugin URI:  https://bkpsdmd.bangka.go.id
 * Description: Plugin struktur organisasi interaktif untuk website dinas. Mendukung minimize/maximize, hover foto pegawai, dan mudah dikelola lewat admin.
 * Version:     1.0.0
 * Author:      BKPSDMD Bangka
 * Author URI:  https://bkpsdmd.bangka.go.id
 * Text Domain: bkpsdmd-org
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---- Konstanta ----
define( 'BKPSDMD_ORG_VERSION',  '1.0.0' );
define( 'BKPSDMD_ORG_PLUGIN',   plugin_dir_path( __FILE__ ) );
define( 'BKPSDMD_ORG_URL',      plugin_dir_url( __FILE__ ) );
define( 'BKPSDMD_ORG_TABLE',    'bkpsdmd_org' );

// ---- Include files ----
require_once BKPSDMD_ORG_PLUGIN . 'includes/class-db.php';
require_once BKPSDMD_ORG_PLUGIN . 'includes/class-shortcode.php';
require_once BKPSDMD_ORG_PLUGIN . 'includes/seeder.php';
require_once BKPSDMD_ORG_PLUGIN . 'admin/admin-page.php';

// ---- Aktivasi: buat tabel DB ----
register_activation_hook( __FILE__, array( 'BKPSDMD_Org_DB', 'create_table' ) );

// ---- Deaktivasi ----
register_deactivation_hook( __FILE__, '__return_false' );

// ---- Uninstall: hapus tabel ----
register_uninstall_hook( __FILE__, array( 'BKPSDMD_Org_DB', 'drop_table' ) );

// ---- Init ----
add_action( 'plugins_loaded', 'bkpsdmd_org_init' );
function bkpsdmd_org_init() {
    // Pastikan tabel ada (upgrade safe)
    if ( get_option( 'bkpsdmd_org_db_version' ) !== BKPSDMD_ORG_VERSION ) {
        BKPSDMD_Org_DB::create_table();
        update_option( 'bkpsdmd_org_db_version', BKPSDMD_ORG_VERSION );
    }
}
