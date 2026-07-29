<?php
/**
 * Attendance — absensi peserta via token link / QR
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_Attendance {

    /**
     * Proses submit form absensi
     * @param string $token  unique_token dari link/QR
     * @param array  $form_data  POST data dari form absensi
     */
    public static function submit( $token, $form_data ) {
        global $wpdb;

        // Validasi token
        $registrant = WBR_Registrant::get_by_token( $token );
        if ( ! $registrant ) {
            return [ 'success' => false, 'message' => 'Token tidak valid atau sudah kedaluwarsa.' ];
        }

        // Cek apakah sudah absen
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}webinar_attendance
             WHERE webinar_id = %d AND registrant_id = %d LIMIT 1",
            $registrant->webinar_id, $registrant->id
        ) );
        if ( $existing ) {
            return [ 'success' => false, 'message' => 'Anda sudah tercatat hadir sebelumnya.' ];
        }

        // Ambil field form absensi (hanya non-identity field untuk diisi)
        $fields = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_form_field
             WHERE webinar_id = %d AND form_type = 'attendance'
             ORDER BY sort_order ASC",
            $registrant->webinar_id
        ) );

        // Identity fields: ambil dari data registrasi
        $reg_data   = json_decode( $registrant->submission_data, true ) ?: [];
        $attendance = [];
        $errors     = [];

        foreach ( $fields as $f ) {
            if ( $f->is_identity_field ) {
                // Otomatis ambil dari data registrant
                $attendance[ $f->field_key ] = $reg_data[ $f->field_key ] ?? '';
                continue;
            }

            $raw   = $form_data[ $f->field_key ] ?? '';
            $value = self::sanitize_field( $raw, $f->field_type );

            if ( $f->is_required && $value === '' ) {
                $errors[] = $f->label . ' wajib diisi.';
                continue;
            }
            $attendance[ $f->field_key ] = $value;
        }

        if ( ! empty( $errors ) ) {
            return [ 'success' => false, 'message' => implode( '<br>', $errors ) ];
        }

        // Insert absensi
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'webinar_attendance',
            [
                'webinar_id'      => $registrant->webinar_id,
                'registrant_id'   => $registrant->id,
                'submission_data' => wp_json_encode( $attendance ),
                'attended_at'     => current_time( 'mysql' ),
            ],
            [ '%d', '%d', '%s', '%s' ]
        );

        if ( ! $inserted ) {
            return [ 'success' => false, 'message' => 'Gagal menyimpan absensi. Coba lagi.' ];
        }

        return [
            'success'      => true,
            'message'      => 'Absensi berhasil dicatat. Terima kasih telah hadir!',
            'registrant'   => $registrant,
            'webinar_id'   => $registrant->webinar_id,
        ];
    }

    /**
     * Absensi manual oleh panitia (scan QR di lokasi)
     * Tidak perlu mengisi form — langsung catat hadir
     */
    public static function record_by_operator( $token ) {
        global $wpdb;

        $registrant = WBR_Registrant::get_by_token( $token );
        if ( ! $registrant ) {
            return [ 'success' => false, 'message' => 'Token tidak valid.' ];
        }

        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}webinar_attendance
             WHERE webinar_id = %d AND registrant_id = %d LIMIT 1",
            $registrant->webinar_id, $registrant->id
        ) );

        if ( $existing ) {
            return [ 'success' => true, 'message' => 'Sudah tercatat hadir.', 'already' => true ];
        }

        // Ambil identity field dari registrant
        $reg_data   = json_decode( $registrant->submission_data, true ) ?: [];
        $attendance = $reg_data; // copy semua data registrasi sebagai identitas

        $wpdb->insert(
            $wpdb->prefix . 'webinar_attendance',
            [
                'webinar_id'      => $registrant->webinar_id,
                'registrant_id'   => $registrant->id,
                'submission_data' => wp_json_encode( $attendance ),
                'attended_at'     => current_time( 'mysql' ),
            ],
            [ '%d', '%d', '%s', '%s' ]
        );

        return [ 'success' => true, 'message' => 'Absensi berhasil dicatat oleh panitia.' ];
    }

    /**
     * Ambil semua yang hadir untuk satu webinar
     */
    public static function get_all( $webinar_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT a.*, r.email, r.submission_data AS reg_data
             FROM {$wpdb->prefix}webinar_attendance a
             JOIN {$wpdb->prefix}webinar_registrant r ON r.id = a.registrant_id
             WHERE a.webinar_id = %d
             ORDER BY a.attended_at ASC",
            $webinar_id
        ) );
    }

    private static function sanitize_field( $value, $type ) {
        switch ( $type ) {
            case 'email':    return sanitize_email( $value );
            case 'textarea': return sanitize_textarea_field( $value );
            case 'number':   return absint( $value );
            default:         return sanitize_text_field( $value );
        }
    }
}
