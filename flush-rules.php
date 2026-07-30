<?php
require 'wp-load.php';

echo "Flushing rewrite rules...\n";
flush_rewrite_rules();

$rules = get_option('rewrite_rules');
echo "Checking if absensi-walkin is in rules:\n";
$found = false;
foreach ($rules as $key => $val) {
    if (strpos($key, 'absensi-walkin') !== false || strpos($key, 'absensi') !== false) {
        echo "$key => $val\n";
        $found = true;
    }
}
if (!$found) {
    echo "Rules NOT FOUND!\n";
}
echo "Done.\n";
