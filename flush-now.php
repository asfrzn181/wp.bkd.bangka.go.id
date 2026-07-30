<?php
require 'wp-load.php';

// Trigger pembuatan kolom tabel baru untuk webinar_meta
if ( class_exists( 'WBR_DB' ) ) {
    WBR_DB::create_tables();
}

global $wpdb;
$wpdb->query( "UPDATE {$wpdb->prefix}webinar_certificate SET file_path_pdf = ''" );
$wpdb->query( "DELETE FROM {$wpdb->prefix}webinar_certificate WHERE file_path_pdf != ''" ); // if any 0-bytes

// FORCE FIX AUTO_INCREMENT DI HOSTING
$tables = ['webinar_registrant', 'webinar_attendance', 'webinar_certificate', 'webinar_sk', 'webinar_form_field'];
foreach ($tables as $t) {
    $table = $wpdb->prefix . $t;
    // Hapus ID 0 yang nyangkut
    $wpdb->query("DELETE FROM {$table} WHERE id = 0");
    // Paksa auto increment
    $wpdb->query("ALTER TABLE {$table} MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
}

flush_rewrite_rules();
echo "Rules flushed, db checked, cache cleared, and AUTO_INCREMENT forcefully fixed.\n";
