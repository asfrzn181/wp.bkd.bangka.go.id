<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BKSKM_Ajax {

    public static function init() {
        add_action( 'wp_ajax_bkpsdmd_skm_submit', array( __CLASS__, 'handle_submit' ) );
        add_action( 'wp_ajax_nopriv_bkpsdmd_skm_submit', array( __CLASS__, 'handle_submit' ) );
        add_action( 'wp_ajax_bkpsdmd_skm_delete_response', array( __CLASS__, 'handle_delete' ) );
        add_action( 'wp_ajax_bkpsdmd_skm_export_csv', array( __CLASS__, 'handle_export' ) );
    }

    public static function handle_submit() {
        // Nonce check
        if ( ! check_ajax_referer( 'bkskm_nonce_action', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => 'Sesi Anda telah kadaluarsa, silakan muat ulang halaman.' ) );
        }

        // Honeypot check
        if ( ! empty( $_POST['bkskm_website_check'] ) ) {
            wp_send_json_error( array( 'message' => 'Deteksi bot/spam.' ) );
        }

        // Field Validation
        $required_fields = array( 'tgl_layanan', 'jenis_kelamin', 'pendidikan', 'usia', 'pekerjaan', 'is_disabilitas' );
        foreach ( $required_fields as $field ) {
            if ( empty( $_POST[$field] ) ) {
                wp_send_json_error( array( 'message' => 'Mohon lengkapi seluruh data identitas responden yang bertanda bintang (*).' ) );
            }
        }

        // Validate 16 Likert questions
        for ( $i = 1; $i <= 16; $i++ ) {
            if ( ! isset( $_POST["q{$i}"] ) || ! in_array( intval( $_POST["q{$i}"] ), array( 1, 2, 3, 4 ), true ) ) {
                wp_send_json_error( array( 'message' => "Mohon isi penilaian untuk pertanyaan No. {$i}." ) );
            }
        }

        // Sanitize & insert
        $data = array(
            'tgl_layanan'       => sanitize_text_field( $_POST['tgl_layanan'] ),
            'jenis_kelamin'     => sanitize_text_field( $_POST['jenis_kelamin'] ),
            'pendidikan'        => sanitize_text_field( $_POST['pendidikan'] ),
            'usia'              => sanitize_text_field( $_POST['usia'] ),
            'pekerjaan'         => sanitize_text_field( $_POST['pekerjaan'] ),
            'pekerjaan_lainnya' => isset( $_POST['pekerjaan_lainnya'] ) ? sanitize_text_field( $_POST['pekerjaan_lainnya'] ) : '',
            'is_disabilitas'    => sanitize_text_field( $_POST['is_disabilitas'] ),
            'jenis_disabilitas' => isset( $_POST['jenis_disabilitas'] ) ? sanitize_text_field( $_POST['jenis_disabilitas'] ) : '',
            'kritik_saran'      => isset( $_POST['kritik_saran'] ) ? sanitize_textarea_field( $_POST['kritik_saran'] ) : '',
        );

        for ( $i = 1; $i <= 16; $i++ ) {
            $data["q{$i}"] = intval( $_POST["q{$i}"] );
        }

        $inserted_id = BKSKM_DB::insert_response( $data );

        if ( $inserted_id ) {
            wp_send_json_success( array(
                'message' => 'Terima kasih! Kuesioner SKM Anda berhasil disimpan.',
                'id'      => $inserted_id
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Gagal menyimpan data ke database. Silakan coba lagi.' ) );
        }
    }

    public static function handle_delete() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Akses ditolak.' ) );
        }
        if ( ! check_ajax_referer( 'bkskm_admin_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => 'Sesi tidak valid.' ) );
        }

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( $id > 0 ) {
            BKSKM_DB::delete_response( $id );
            wp_send_json_success( array( 'message' => 'Data tanggapan berhasil dihapus.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'ID tidak valid.' ) );
        }
    }

    public static function handle_export() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Akses ditolak.' );
        }

        $start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
        $end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';

        $responses = BKSKM_DB::get_responses( 10000, 0, '', $start_date, $end_date );

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=survei-skm-' . date( 'Y-m-d' ) . '.csv' );

        $output = fopen( 'php://output', 'w' );
        // Add UTF-8 BOM for Excel compatibility
        fputs( $output, "\xEF\xBB\xBF" );

        // Header Row
        fputcsv( $output, array(
            'ID', 'Tgl Dibuat', 'Tgl Layanan', 'Jenis Kelamin', 'Pendidikan', 'Usia',
            'Pekerjaan', 'Pekerjaan Lainnya', 'Disabilitas', 'Jenis Disabilitas',
            'U1 (Informasi)', 'U2 (Persyaratan)', 'U3 (Standar/Prosedur)', 'U4 (Kemudahan Alur)',
            'U5 (Keadilan Prosedur)', 'U6 (Jangka Waktu)', 'U7 (Biaya)', 'U8 (Bebas Pungli)',
            'U9 (Bebas Calo)', 'U10 (Kesesuaian Produk)', 'U11 (Kecepatan Aplikasi)', 'U12 (Kemudahan Fitur)',
            'U13 (Pelayanan Adil)', 'U14 (Bebas Gratifikasi)', 'U15 (Konsultasi/Pengaduan)', 'U16 (Kenyamanan Sistem)',
            'Kritik & Saran', 'IP Address'
        ) );

        foreach ( $responses as $row ) {
            fputcsv( $output, array(
                $row['id'],
                $row['created_at'],
                $row['tgl_layanan'],
                $row['jenis_kelamin'],
                $row['pendidikan'],
                $row['usia'],
                $row['pekerjaan'],
                $row['pekerjaan_lainnya'],
                $row['is_disabilitas'],
                $row['jenis_disabilitas'],
                $row['q1'], $row['q2'], $row['q3'], $row['q4'],
                $row['q5'], $row['q6'], $row['q7'], $row['q8'],
                $row['q9'], $row['q10'], $row['q11'], $row['q12'],
                $row['q13'], $row['q14'], $row['q15'], $row['q16'],
                $row['kritik_saran'],
                $row['ip_address']
            ) );
        }

        fclose( $output );
        exit;
    }
}
