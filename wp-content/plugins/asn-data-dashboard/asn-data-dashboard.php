<?php
/**
 * Plugin Name:       ASN Data Dashboard
 * Plugin URI:        https://bkpsdmd.bangka.go.id
 * Description:       Dashboard untuk mengelola dan menampilkan data rekapitulasi ASN (Aparatur Sipil Negara) bulanan dengan tabel editable dan grafik otomatis.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            BKPSDMD Bangka
 * Author URI:        https://bkpsdmd.bangka.go.id
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       asn-data-dashboard
 * Domain Path:       /languages
 *
 * @package ASN_Data_Dashboard
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'ASNDD_VERSION',    '1.0.0' );
define( 'ASNDD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ASNDD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ASNDD_TABLE_NAME', 'asn_data' ); // Without prefix; use $wpdb->prefix . ASNDD_TABLE_NAME.

// Include all classes.
require_once ASNDD_PLUGIN_DIR . 'includes/class-asndd-schema.php';
require_once ASNDD_PLUGIN_DIR . 'includes/class-asndd-db.php';
require_once ASNDD_PLUGIN_DIR . 'includes/class-asndd-ajax.php';
require_once ASNDD_PLUGIN_DIR . 'includes/class-asndd-rest.php';
require_once ASNDD_PLUGIN_DIR . 'includes/class-asndd-shortcode.php';
require_once ASNDD_PLUGIN_DIR . 'includes/class-asndd-admin.php';

/**
 * Plugin activation: create custom table.
 */
function asndd_activate() {
	ASNDD_DB::create_table();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'asndd_activate' );

/**
 * Plugin deactivation.
 */
function asndd_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'asndd_deactivate' );

/**
 * Bootstrap plugin after all plugins are loaded.
 */
function asndd_init() {
	// Load text domain.
	load_plugin_textdomain(
		'asn-data-dashboard',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);

	// Initialize components.
	new ASNDD_Admin();
	new ASNDD_Ajax();
	new ASNDD_Shortcode();
	new ASNDD_REST();
}
add_action( 'plugins_loaded', 'asndd_init' );
