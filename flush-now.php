<?php
require 'wp-load.php';

// Trigger pembuatan kolom tabel baru untuk webinar_meta
if ( class_exists( 'WBR_DB' ) ) {
    WBR_DB::create_tables();
}

global $wpdb;
$wpdb->query( "UPDATE {$wpdb->prefix}webinar_certificate SET file_path_pdf = ''" );
$wpdb->query( "DELETE FROM {$wpdb->prefix}webinar_certificate WHERE file_path_pdf != ''" ); // if any 0-bytes

flush_rewrite_rules();
echo "Rules flushed, db checked, and cache cleared.\n";
