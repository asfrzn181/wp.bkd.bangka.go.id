<?php
/**
 * Seeder - Import Data Struktur Organisasi BKPSDMD Bangka
 * Jalankan sekali dari halaman admin plugin.
 *
 * @package bkpsdmd-org-chart
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// Catatan: cek current_user_can() dilakukan di handle_import() pada admin-page.php


class BKPSDMD_Org_Seeder {

    private static $inserted = array(); // slug => id

    /**
     * Cek apakah data sudah ada (hindari duplikasi)
     */
    public static function already_imported() {
        global $wpdb;
        $table = $wpdb->prefix . BKPSDMD_ORG_TABLE;
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
        return intval( $count ) > 0;
    }

    /**
     * Hapus semua data sebelum import ulang
     */
    public static function truncate() {
        global $wpdb;
        $table = $wpdb->prefix . BKPSDMD_ORG_TABLE;
        $wpdb->query( "TRUNCATE TABLE {$table}" ); // phpcs:ignore
        self::$inserted = array();
    }

    /**
     * Helper insert + simpan ID
     * @param string $slug         Kunci unik internal
     * @param string $parent_slug  Slug parent
     * @param string $jabatan      Nama jabatan
     * @param int    $urutan       Urutan tampil
     * @param string $keterangan   Keterangan tambahan
     * @param string $nama         Nama pegawai (opsional)
     * @param string $nip          NIP (opsional)
     */
    private static function ins( $slug, $parent_slug, $jabatan, $urutan = 0, $keterangan = '', $nama = '', $nip = '' ) {
        $parent_id = isset( self::$inserted[ $parent_slug ] ) ? self::$inserted[ $parent_slug ] : 0;
        $id = BKPSDMD_Org_DB::insert( array(
            'parent_id'  => $parent_id,
            'jabatan'    => $jabatan,
            'nama'       => $nama,
            'nip'        => $nip,
            'foto_url'   => '',
            'keterangan' => $keterangan,
            'urutan'     => $urutan,
            'aktif'      => 1,
        ) );
        if ( $id ) {
            self::$inserted[ $slug ] = $id;
        }
        return $id;

    }

    /**
     * Import seluruh struktur jabatan BKPSDMD Bangka
     * Mengembalikan jumlah record yang berhasil diinsert
     */
    public static function run() {
        self::$inserted = array();
        $count = 0;

        // ============================================================
        // LEVEL 0: KEPALA BADAN
        // ============================================================
        if ( self::ins( 'kepala_badan', '', 'KEPALA BADAN KEPEGAWAIAN DAN PENGEMBANGAN SUMBER DAYA MANUSIA DAERAH', 1 ) ) $count++;

        // ============================================================
        // LEVEL 1: SEKRETARIS + KEPALA BIDANG (langsung bawah Kepala Badan)
        // ============================================================
        if ( self::ins( 'sekretaris', 'kepala_badan', 'SEKRETARIS', 1 ) ) $count++;
        if ( self::ins( 'kabid_mutasi', 'kepala_badan', 'KEPALA BIDANG MUTASI KEPEGAWAIAN', 2, '', 'ACHMAD RIYADI, S.Sos, MM', '198703182011011002' ) ) $count++;
        if ( self::ins( 'kabid_sisinfokas', 'kepala_badan', 'KEPALA BIDANG SISTEM INFORMASI KEPEGAWAIAN', 3 ) ) $count++;
        if ( self::ins( 'kabid_pengembangan', 'kepala_badan', 'KEPALA BIDANG PENGEMBANGAN DAN PEMBINAAN SUMBER DAYA MANUSIA', 4 ) ) $count++;

        // Kelompok Jabatan Fungsional langsung bawah Kepala Badan
        if ( self::ins( 'fjf_kb_1', 'kepala_badan', 'Analis SDM Aparatur Ahli Madya', 10, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_kb_2', 'kepala_badan', 'Analis Pengembangan Kompetensi ASN Ahli Madya', 11, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_kb_3', 'kepala_badan', 'Pranata Komputer Ahli Madya', 12, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_kb_4', 'kepala_badan', 'Analis Kebijakan Ahli Madya', 13, 'Kelompok Jabatan Fungsional' ) ) $count++;

        // ============================================================
        // LEVEL 2: BAWAH SEKRETARIS
        // ============================================================
        if ( self::ins( 'kasubbag_ppk', 'sekretaris', 'KEPALA SUB BAGIAN PERENCANAAN, PELAPORAN DAN KEUANGAN', 1 ) ) $count++;
        if ( self::ins( 'kasubbag_uk',  'sekretaris', 'KEPALA SUB BAGIAN UMUM DAN KEPEGAWAIAN', 2 ) ) $count++;

        // Kelompok Jabatan Fungsional bawah Sekretaris
        if ( self::ins( 'fjf_sek_1', 'sekretaris', 'Pranata Komputer Mahir', 10, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sek_2', 'sekretaris', 'Pranata Komputer Penyelia', 11, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sek_3', 'sekretaris', 'Pranata Komputer Terampil', 12, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sek_4', 'sekretaris', 'Analis SDM Aparatur Ahli Muda', 13, 'Kelompok Jabatan Fungsional' ) ) $count++;

        // ============================================================
        // LEVEL 3: STAF BAWAH KASUBBAG PERENCANAAN, PELAPORAN DAN KEUANGAN
        // ============================================================
        if ( self::ins( 'ppk_s1', 'kasubbag_ppk', 'Penelaah Teknis Kebijakan', 1 ) ) $count++;
        if ( self::ins( 'ppk_s2', 'kasubbag_ppk', 'Pengolah Data dan Informasi', 2 ) ) $count++;
        if ( self::ins( 'ppk_s3', 'kasubbag_ppk', 'Pengadministrasi Perkantoran', 3 ) ) $count++;
        if ( self::ins( 'ppk_s4', 'kasubbag_ppk', 'Penata Layanan Operasional', 4 ) ) $count++;

        // ============================================================
        // LEVEL 3: STAF BAWAH KASUBBAG UMUM DAN KEPEGAWAIAN
        // ============================================================
        if ( self::ins( 'uk_s1', 'kasubbag_uk', 'Pengadministrasi Perkantoran', 1, '', 'SALIUS SAWAL, S.Akun' ) ) $count++;
        if ( self::ins( 'uk_s2', 'kasubbag_uk', 'Pengolah Data dan Informasi', 2, '', 'ALFAD NUR HUDA, S.T' ) ) $count++;
        if ( self::ins( 'uk_s3', 'kasubbag_uk', 'Operator Layanan Operasional (Pengemudi)', 3, '', 'ENGGARA RAMDANSYAH, S.Sos' ) ) $count++;
        if ( self::ins( 'uk_s4', 'kasubbag_uk', 'Pengelola Umum Operasional (Petugas Keamanan)', 4 ) ) $count++;
        if ( self::ins( 'uk_s5', 'kasubbag_uk', 'Operator Layanan Operasional (Pramu Kebersihan)', 5, '', 'RIPAN SETIYADI' ) ) $count++;
        // Catatan: MUHAMMAD ZIDNI IMANI (Tenaga Kebersihan) belum dapat dipastikan posisinya
        // Kemungkinan di posisi Pramu Kebersihan ke-2 — perlu konfirmasi

        // ============================================================
        // LEVEL 2: STAF + FUNGSIONAL BAWAH KEPALA BIDANG MUTASI KEPEGAWAIAN
        // ============================================================
        if ( self::ins( 'mut_s1', 'kabid_mutasi', 'Penelaah Teknis Kebijakan', 1 ) ) $count++;
        if ( self::ins( 'mut_s2', 'kabid_mutasi', 'Penata Kelola Sistem dan Teknologi Informasi', 2 ) ) $count++;
        if ( self::ins( 'mut_s3', 'kabid_mutasi', 'Pengelola Layanan Operasional', 3 ) ) $count++;
        if ( self::ins( 'mut_s4', 'kabid_mutasi', 'Pengolah Data dan Informasi', 4 ) ) $count++;

        // Fungsional bawah Bidang Mutasi
        if ( self::ins( 'fjf_mut_1', 'kabid_mutasi', 'Analis SDM Aparatur Ahli Muda', 10, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_mut_2', 'kabid_mutasi', 'Analis SDM Aparatur Ahli Pertama', 11, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_mut_3', 'kabid_mutasi', 'Pranata SDM Aparatur Penyelia', 12, 'Kelompok Jabatan Fungsional' ) ) $count++;
        // LESTARI, S.Kom (NI PPPK. 199402162023212044) → Pranata Komputer di Bidang Mutasi
        if ( self::ins( 'fjf_mut_4', 'kabid_mutasi', 'Pranata Komputer Ahli Pertama', 13, 'Kelompok Jabatan Fungsional', 'LESTARI, S.Kom', '199402162023212044' ) ) $count++;
        if ( self::ins( 'fjf_mut_5', 'kabid_mutasi', 'Pranata SDM Aparatur Mahir', 14, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_mut_6', 'kabid_mutasi', 'Pranata SDM Aparatur Terampil', 15, 'Kelompok Jabatan Fungsional' ) ) $count++;

        // ============================================================
        // LEVEL 2: STAF + FUNGSIONAL BAWAH KEPALA BIDANG SISTEM INFORMASI KEPEGAWAIAN
        // ============================================================
        if ( self::ins( 'sis_s1', 'kabid_sisinfokas', 'Pengolah Data dan Informasi', 1 ) ) $count++;
        if ( self::ins( 'sis_s2', 'kabid_sisinfokas', 'Operator Layanan Operasional', 2 ) ) $count++;

        // Fungsional bawah Bidang Sisinfokas
        if ( self::ins( 'fjf_sis_1',  'kabid_sisinfokas', 'Analis SDM Aparatur Ahli Muda', 10, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sis_2',  'kabid_sisinfokas', 'Analis SDM Aparatur Ahli Pertama', 11, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sis_3',  'kabid_sisinfokas', 'Analis Kebijakan Ahli Pertama', 12, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sis_4',  'kabid_sisinfokas', 'Analis Kebijakan Ahli Muda', 13, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sis_5',  'kabid_sisinfokas', 'Pranata Komputer Mahir', 14, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sis_6',  'kabid_sisinfokas', 'Pranata Komputer Penyelia', 15, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sis_7',  'kabid_sisinfokas', 'Pranata Komputer Ahli Pertama', 16, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sis_8',  'kabid_sisinfokas', 'Arsiparis Mahir', 17, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sis_9',  'kabid_sisinfokas', 'Arsiparis Penyelia', 18, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sis_10', 'kabid_sisinfokas', 'Arsiparis Ahli Pertama', 19, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_sis_11', 'kabid_sisinfokas', 'Arsiparis Ahli Muda', 20, 'Kelompok Jabatan Fungsional' ) ) $count++;

        // ============================================================
        // LEVEL 2: STAF + FUNGSIONAL BAWAH KEPALA BIDANG PENGEMBANGAN DAN PEMBINAAN SDM
        // ============================================================
        if ( self::ins( 'peng_s1', 'kabid_pengembangan', 'Penelaah Teknis Kebijakan', 1 ) ) $count++;
        if ( self::ins( 'peng_s2', 'kabid_pengembangan', 'Penata Layanan Operasional', 2 ) ) $count++;
        if ( self::ins( 'peng_s3', 'kabid_pengembangan', 'Pengadministrasi Perkantoran', 3 ) ) $count++;
        if ( self::ins( 'peng_s4', 'kabid_pengembangan', 'Pengelola Layanan Operasional', 4 ) ) $count++;

        // Fungsional bawah Bidang Pengembangan
        if ( self::ins( 'fjf_peng_1', 'kabid_pengembangan', 'Analis Pengembangan Kompetensi ASN Ahli Muda', 10, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_peng_2', 'kabid_pengembangan', 'Analis Pengembangan Kompetensi ASN Ahli Pertama', 11, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_peng_3', 'kabid_pengembangan', 'Analis SDM Aparatur Ahli Muda', 12, 'Kelompok Jabatan Fungsional' ) ) $count++;
        if ( self::ins( 'fjf_peng_4', 'kabid_pengembangan', 'Analis SDM Aparatur Ahli Pertama', 13, 'Kelompok Jabatan Fungsional' ) ) $count++;

        return $count;
    }
}
