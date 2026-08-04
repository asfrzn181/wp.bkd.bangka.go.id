<?php
/**
 * Batch Generate Sertifikat (Petikan) — dengan auto-fix schema tabel.
 * Jalankan via browser: https://bkd.bangka.go.id/generate-certs.php
 * HAPUS FILE INI SETELAH SELESAI DIGUNAKAN.
 */
require 'wp-load.php';

if ( ! class_exists( 'WBR_Certificate' ) ) {
    die( '❌ Plugin Webinar tidak aktif atau class WBR_Certificate tidak ditemukan.' );
}

global $wpdb;
@set_time_limit( 300 );
@ini_set( 'memory_limit', '256M' );

$cert_table = $wpdb->prefix . 'webinar_certificate';
$att_table  = $wpdb->prefix . 'webinar_attendance';

echo "<pre style='font-family:monospace; font-size:13px;'>";
echo "🔧 BKPSDMD Webinar — Batch Generate Sertifikat\n";
echo str_repeat('=', 70) . "\n\n";

// ── STEP 1: Periksa dan perbaiki schema tabel webinar_certificate ─────────
echo "📋 STEP 1: Memeriksa schema tabel {$cert_table}...\n";

$columns = $wpdb->get_col( "SHOW COLUMNS FROM {$cert_table}" );

// Tambahkan kolom attendance_id jika belum ada
if ( ! in_array( 'attendance_id', $columns ) ) {
    echo "  ⚠️  Kolom 'attendance_id' tidak ditemukan. Menambahkan...\n";
    $wpdb->query( "ALTER TABLE {$cert_table} ADD COLUMN attendance_id BIGINT(20) UNSIGNED NULL AFTER webinar_id" );
    echo "  ✅ Kolom 'attendance_id' berhasil ditambahkan.\n";
} else {
    echo "  ✅ Kolom 'attendance_id' sudah ada.\n";
}

// Tambahkan kolom holder_name jika belum ada
if ( ! in_array( 'holder_name', $columns ) ) {
    echo "  ⚠️  Kolom 'holder_name' tidak ditemukan. Menambahkan...\n";
    $wpdb->query( "ALTER TABLE {$cert_table} ADD COLUMN holder_name VARCHAR(255) NOT NULL DEFAULT '' AFTER petikan_number" );
    echo "  ✅ Kolom 'holder_name' berhasil ditambahkan.\n";
}

// Tambahkan kolom holder_email jika belum ada
if ( ! in_array( 'holder_email', $columns ) ) {
    echo "  ⚠️  Kolom 'holder_email' tidak ditemukan. Menambahkan...\n";
    $wpdb->query( "ALTER TABLE {$cert_table} ADD COLUMN holder_email VARCHAR(191) NOT NULL DEFAULT '' AFTER holder_name" );
    echo "  ✅ Kolom 'holder_email' berhasil ditambahkan.\n";
}

// Tambahkan kolom qr_verification_hash jika belum ada
if ( ! in_array( 'qr_verification_hash', $columns ) ) {
    echo "  ⚠️  Kolom 'qr_verification_hash' tidak ditemukan. Menambahkan...\n";
    $wpdb->query( "ALTER TABLE {$cert_table} ADD COLUMN qr_verification_hash VARCHAR(64) NOT NULL DEFAULT '' AFTER file_path_pdf" );
    echo "  ✅ Kolom 'qr_verification_hash' berhasil ditambahkan.\n";
}

// Tambahkan kolom generated_at jika belum ada
if ( ! in_array( 'generated_at', $columns ) ) {
    echo "  ⚠️  Kolom 'generated_at' tidak ditemukan. Menambahkan...\n";
    $wpdb->query( "ALTER TABLE {$cert_table} ADD COLUMN generated_at DATETIME NULL" );
    echo "  ✅ Kolom 'generated_at' berhasil ditambahkan.\n";
}

// Trigger create_tables untuk update schema via dbDelta
if ( class_exists( 'WBR_DB' ) ) {
    WBR_DB::create_tables();
    echo "  ✅ Schema tabel diperbarui via WBR_DB::create_tables().\n";
}

echo "\n";

// ── STEP 2: Hapus UNIQUE KEY lama yang bermasalah ─────────────────────────
echo "📋 STEP 2: Memeriksa dan menghapus UNIQUE KEY lama yang bermasalah...\n";

$old_keys = $wpdb->get_results( "SHOW INDEX FROM {$cert_table}" );
$key_names = array_column( $old_keys, 'Key_name' );

// Hapus uk_sk_reg jika ada (schema lama yang menyebabkan Duplicate Entry)
if ( in_array( 'uk_sk_reg', $key_names ) ) {
    $wpdb->query( "ALTER TABLE {$cert_table} DROP INDEX uk_sk_reg" );
    echo "  ✅ Berhasil menghapus UNIQUE KEY 'uk_sk_reg' (schema lama).\n";
} else {
    echo "  ✅ Tidak ada 'uk_sk_reg' — aman.\n";
}

// Pastikan uk_att ada (attendance_id harus unique)
if ( ! in_array( 'uk_att', $key_names ) ) {
    $wpdb->query( "ALTER TABLE {$cert_table} ADD UNIQUE KEY uk_att (attendance_id)" );
    echo "  ✅ Berhasil menambahkan UNIQUE KEY 'uk_att' (attendance_id).\n";
} else {
    echo "  ✅ UNIQUE KEY 'uk_att' sudah ada.\n";
}
echo "\n";

// ── STEP 3: Perbaiki AUTO_INCREMENT ──────────────────────────────────────
echo "📋 STEP 3: Memperbaiki AUTO_INCREMENT semua tabel...\n";
$tables = [ 'webinar_registrant', 'webinar_attendance', 'webinar_certificate', 'webinar_sk', 'webinar_form_field' ];
foreach ( $tables as $t ) {
    $tbl = $wpdb->prefix . $t;
    $wpdb->query( "DELETE FROM {$tbl} WHERE id = 0" );
    $wpdb->query( "ALTER TABLE {$tbl} MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT" );
    echo "  ✅ {$tbl}\n";
}
echo "\n";

// ── STEP 3: Ambil semua attendance yang belum punya sertifikat ────────────
echo "📋 STEP 3: Mencari absensi tanpa sertifikat...\n";

// Query aman tanpa LEFT JOIN ke certificate (karena attendance_id baru saja ditambahkan)
$all_att = $wpdb->get_results(
    "SELECT a.id AS att_id, a.webinar_id, p.post_title AS webinar_title
     FROM {$att_table} a
     LEFT JOIN {$wpdb->posts} p ON p.ID = a.webinar_id
     ORDER BY a.webinar_id ASC, a.id ASC"
);

// Cek satu-satu mana yang belum ada certificate-nya
$missing = [];
foreach ( $all_att as $row ) {
    $has_cert = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$cert_table} WHERE attendance_id = %d LIMIT 1",
        $row->att_id
    ) );
    if ( ! $has_cert ) {
        $missing[] = $row;
    }
}

$total   = count( $missing );
$success = 0;
$failed  = 0;

echo "🔍 Ditemukan <b>{$total}</b> absensi tanpa sertifikat.\n";
echo str_repeat('-', 70) . "\n";

foreach ( $missing as $row ) {
    $label = "att_id={$row->att_id} | webinar_id={$row->webinar_id} | {$row->webinar_title}";
    $hash  = WBR_Certificate::generate_for_attendance( (int) $row->att_id );

    if ( $hash ) {
        $success++;
        echo "✅ OK     | {$label}\n";
    } else {
        $failed++;
        echo "❌ GAGAL  | {$label}\n";
    }

    if ( ob_get_level() ) ob_flush();
    flush();
}

echo str_repeat('-', 70) . "\n";
echo "✅ Berhasil : <b>{$success}</b>\n";
echo "❌ Gagal    : <b>{$failed}</b>\n";
echo "📊 Total    : <b>{$total}</b>\n\n";

flush_rewrite_rules();

if ( $total === 0 ) {
    echo "ℹ️  Tidak ada absensi yang kekurangan sertifikat.\n";
    echo "   Kemungkinan kolom <b>attendance_id</b> baru saja ditambahkan (STEP 1).\n";
    echo "   Coba lakukan absensi baru, lalu akses halaman ini lagi untuk generate sertifikatnya.\n";
} elseif ( $failed === 0 ) {
    echo "🎉 Semua sertifikat berhasil digenerate!\n";
    echo "   Silakan hapus file <b>generate-certs.php</b> dari server.\n";
} else {
    echo "⚠️  Ada beberapa yang gagal. Coba akses halaman ini sekali lagi.\n";
}

echo "</pre>";
