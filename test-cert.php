<?php
require 'wp-load.php';

global $wpdb;
$wpdb->show_errors();

echo "Testing cert generation for attendance...\n";
$attendance_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}webinar_attendance ORDER BY id DESC LIMIT 1");
if (!$attendance_id) {
    echo "No attendance found to test.\n";
    exit;
}

echo "Testing attendance ID: $attendance_id\n";
require_once plugin_dir_path(__FILE__) . 'wp-content/plugins/bkpsdmd-webinar/includes/class-certificate.php';
require_once plugin_dir_path(__FILE__) . 'wp-content/plugins/bkpsdmd-webinar/includes/class-document.php';
require_once plugin_dir_path(__FILE__) . 'wp-content/plugins/bkpsdmd-webinar/includes/class-email.php'; // ensure dependencies

$hash = WBR_Certificate::generate_for_attendance($attendance_id);
if ($hash) {
    echo "Success! Hash: $hash\n";
} else {
    echo "Failed! wpdb last error: " . $wpdb->last_error . "\n";
}
