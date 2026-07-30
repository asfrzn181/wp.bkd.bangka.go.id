<?php
require 'wp-load.php';
global $wpdb;

echo "Checking tables...\n";
$tables = ['webinar_meta', 'webinar_form_field', 'webinar_registrant', 'webinar_attendance', 'webinar_sk', 'webinar_certificate'];
foreach ($tables as $t) {
    $table_name = $wpdb->prefix . $t;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
    echo $table_name . ": " . ($exists ? "Exists" : "MISSING") . "\n";
}

echo "\nTrying to run WBR_DB::create_tables()...\n";
$wpdb->show_errors();
WBR_DB::create_tables();
echo "Done.\n";
