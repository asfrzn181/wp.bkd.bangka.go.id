<?php
/**
 * ASN Data Dashboard Uninstall Handler.
 *
 * @package ASN_Data_Dashboard
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Require database class file.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-asndd-db.php';

// Drop the custom table.
ASNDD_DB::drop_table();

// Delete plugin options if any.
delete_option( 'asndd_version' );
