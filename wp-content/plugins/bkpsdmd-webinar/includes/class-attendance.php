<?php
/**
 * Attendance — Dua skenario:
 *
 * A) Self-service via token (peserta sudah daftar)
 *    → Buka /absensi/{token} → form identity pre-filled → submit
 *
 * B) Walk-in (peserta datang langsung tanpa daftar)
 *    → Buka /absensi/webinar/{id} → isi form absensi lengkap → submit
 *
 * C) Operator/panitia scan QR/input token di panel admin
 *
 * Setiap attendance yang tercatat → langsung schedule WP Cron untuk generate sertifikat.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_Attendance {

    // ── Submit dari form self-service (via token registrasi) ─────────────────
    public static function submit_via_token( $token, array $form_data ) {
        global $wpdb;

        $registrant = WBR_Registrant::get_by_token( $token );
        if ( ! $registrant ) {
            return [ 'success' => false, 'message' => 'Token tidak valid.' ];
        }

        $webinar_id = (int) $registrant->webinar_id;

        // Cek duplikat
        if ( self::has_attended( $webinar_id, $registrant->id ) ) {
            return [ 'success' => false, 'message' => 'Absensi sudah tercatat sebelumnya.' ];
        }

        $att_id = self::insert( $webinar_id, $registrant->id, $form_data );
        if ( ! $att_id ) return [ 'success' => false, 'message' => 'Gagal menyimpan absensi.' ];

        $hash = WBR_Certificate::generate_for_attendance( $att_id );

        return [
            'success'      => true,
            'message'      => '✅ Kehadiran Anda berhasil tercatat. Membuka sertifikat...',
            'redirect_url' => $hash ? home_url( '/verifikasi-petikan/' . $hash ) : '',
        ];
    }

    // ── Submit walk-in (tanpa token, langsung isi form) ──────────────────────
    public static function submit_walkin( $webinar_id, array $form_data ) {
        global $wpdb;

        $webinar_id = absint( $webinar_id );
        if ( ! $webinar_id || get_post_type( $webinar_id ) !== 'webinar' ) {
            return [ 'success' => false, 'message' => 'Webinar tidak ditemukan.' ];
        }

        // Buat registrant minimal dengan data dari form absensi
        $email_key = self::find_email_key( $webinar_id );
        $email     = sanitize_email( $form_data[ $email_key ] ?? '' );

        // Cegah duplikat walk-in berdasarkan email
        if ( $email ) {
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT a.id FROM {$wpdb->prefix}webinar_attendance a
                 JOIN {$wpdb->prefix}webinar_registrant r ON r.id = a.registrant_id
                 WHERE a.webinar_id = %d AND r.email = %s LIMIT 1",
                $webinar_id, $email
            ) );
            if ( $exists ) return [ 'success' => false, 'message' => 'Email ini sudah tercatat hadir.' ];
        }

        // Buat registrant minimal (walk-in, tidak perlu form registrasi)
        $reg_id = WBR_Registrant::create_walkin( $webinar_id, $email, $form_data );
        if ( ! $reg_id ) return [ 'success' => false, 'message' => 'Gagal mencatat data peserta.' ];

        $att_id = self::insert( $webinar_id, $reg_id, $form_data );
        if ( ! $att_id ) return [ 'success' => false, 'message' => 'Gagal menyimpan absensi.' ];

        $hash = WBR_Certificate::generate_for_attendance( $att_id );

        return [
            'success'      => true,
            'message'      => '✅ Kehadiran Anda berhasil tercatat. Membuka sertifikat...',
            'redirect_url' => $hash ? home_url( '/verifikasi-petikan/' . $hash ) : '',
        ];
    }

    // ── Operator catat hadir via token/scan QR (dari panel admin) ────────────
    public static function operator_record( $token ) {
        $registrant = WBR_Registrant::get_by_token( $token );
        if ( ! $registrant ) return [ 'success' => false, 'message' => 'Token tidak valid.' ];

        $webinar_id = (int) $registrant->webinar_id;
        if ( self::has_attended( $webinar_id, $registrant->id ) ) {
            return [ 'success' => false, 'message' => 'Sudah hadir.' ];
        }

        // Operator mencatat tanpa isi form tambahan
        $att_id = self::insert( $webinar_id, $registrant->id, [] );
        if ( ! $att_id ) return [ 'success' => false, 'message' => 'Gagal mencatat.' ];

        WBR_Certificate::generate_for_attendance( $att_id );

        return [ 'success' => true, 'message' => 'Kehadiran berhasil dicatat dan sertifikat dibuat.' ];
    }

    // ── Core insert ───────────────────────────────────────────────────────────
    private static function insert( $webinar_id, $registrant_id, array $form_data ) {
        global $wpdb;

        // Filter hanya field non-identity untuk submission_data
        $att_fields = self::get_attendance_fields( $webinar_id );
        $filtered   = [];
        foreach ( $att_fields as $f ) {
            if ( ! $f->is_identity_field && isset( $form_data[ $f->field_key ] ) ) {
                $filtered[ $f->field_key ] = sanitize_text_field( (string) $form_data[ $f->field_key ] );
            }
        }

        $wpdb->insert(
            $wpdb->prefix . 'webinar_attendance',
            [
                'webinar_id'      => $webinar_id,
                'registrant_id'   => $registrant_id,
                'submission_data' => wp_json_encode( $filtered ),
                'attended_at'     => current_time( 'mysql' ),
            ],
            [ '%d', '%d', '%s', '%s' ]
        );
        return $wpdb->insert_id ?: null;
    }

    // (Fungsi schedule_certificate dihapus karena sekarang sinkron)

    // ── Helper: cek sudah hadir ───────────────────────────────────────────────
    public static function has_attended( $webinar_id, $registrant_id ) {
        global $wpdb;
        return (bool) $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}webinar_attendance WHERE webinar_id = %d AND registrant_id = %d LIMIT 1",
            $webinar_id, $registrant_id
        ) );
    }

    // ── Get all attendances for a webinar ─────────────────────────────────────
    public static function get_all( $webinar_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT a.*,
                    r.email, r.submission_data AS reg_data, r.unique_token,
                    c.id AS cert_id, c.status AS cert_status, c.petikan_number,
                    c.qr_verification_hash
             FROM {$wpdb->prefix}webinar_attendance a
             JOIN {$wpdb->prefix}webinar_registrant r ON r.id = a.registrant_id
             LEFT JOIN {$wpdb->prefix}webinar_certificate c ON c.attendance_id = a.id
             WHERE a.webinar_id = %d
             ORDER BY a.attended_at ASC",
            $webinar_id
        ) );
    }

    public static function get_by_id( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_attendance WHERE id = %d LIMIT 1", $id
        ) );
    }

    // ── Helper: ambil attendance form fields ──────────────────────────────────
    private static function get_attendance_fields( $webinar_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_form_field
             WHERE webinar_id = %d AND form_type = 'attendance'
             ORDER BY sort_order ASC",
            $webinar_id
        ) );
    }

    // ── Helper: cari field key email di form absensi ──────────────────────────
    private static function find_email_key( $webinar_id ) {
        global $wpdb;
        $field = $wpdb->get_row( $wpdb->prepare(
            "SELECT field_key FROM {$wpdb->prefix}webinar_form_field
             WHERE webinar_id = %d AND form_type = 'attendance' AND field_type = 'email' LIMIT 1",
            $webinar_id
        ) );
        return $field ? $field->field_key : 'email';
    }
}
