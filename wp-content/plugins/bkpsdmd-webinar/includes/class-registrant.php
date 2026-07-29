<?php
/**
 * Registrant — pendaftaran peserta webinar
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_Registrant {

    /**
     * Proses submit form pendaftaran publik
     * @return array { success, message, registrant_id? }
     */
    public static function submit( $webinar_id, $form_data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'webinar_registrant';

        // Ambil field form registrasi
        $fields = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_form_field
             WHERE webinar_id = %d AND form_type = 'registration'
             ORDER BY sort_order ASC",
            $webinar_id
        ) );

        if ( empty( $fields ) ) {
            return [ 'success' => false, 'message' => 'Form pendaftaran belum dikonfigurasi.' ];
        }

        // Validasi & sanitasi
        $email         = '';
        $sanitized     = [];
        $errors        = [];

        foreach ( $fields as $f ) {
            $raw   = $form_data[ $f->field_key ] ?? '';
            $value = self::sanitize_field( $raw, $f->field_type );

            if ( $f->is_required && $value === '' ) {
                $errors[] = $f->label . ' wajib diisi.';
                continue;
            }

            // Deteksi field email
            if ( $f->field_type === 'email' ) {
                $email = sanitize_email( $raw );
            }

            $sanitized[ $f->field_key ] = $value;
        }

        if ( ! empty( $errors ) ) {
            return [ 'success' => false, 'message' => implode( '<br>', $errors ) ];
        }

        if ( empty( $email ) ) {
            return [ 'success' => false, 'message' => 'Field email wajib ada di form pendaftaran.' ];
        }

        // Cek apakah email sudah terdaftar di webinar ini
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE webinar_id = %d AND email = %s LIMIT 1",
            $webinar_id, $email
        ) );
        if ( $existing ) {
            return [ 'success' => false, 'message' => 'Email ini sudah terdaftar untuk webinar ini.' ];
        }

        // Generate unique token
        $token = self::generate_token();

        // Insert
        $inserted = $wpdb->insert( $table, [
            'webinar_id'      => $webinar_id,
            'unique_token'    => $token,
            'email'           => $email,
            'submission_data' => wp_json_encode( $sanitized ),
            'registered_at'   => current_time( 'mysql' ),
        ], [ '%d', '%s', '%s', '%s', '%s' ] );

        if ( ! $inserted ) {
            return [ 'success' => false, 'message' => 'Gagal menyimpan data. Coba lagi.' ];
        }

        $registrant_id = $wpdb->insert_id;

        // Kirim email
        WBR_Email::send_registration_confirmation( $registrant_id );

        return [
            'success'       => true,
            'message'       => 'Pendaftaran berhasil! Cek email Anda untuk link absensi.',
            'registrant_id' => $registrant_id,
        ];
    }

    /**
     * Ambil data registrant berdasarkan token
     */
    public static function get_by_token( $token ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_registrant WHERE unique_token = %s LIMIT 1",
            sanitize_text_field( $token )
        ) );
    }

    /**
     * Ambil semua registrant untuk satu webinar (untuk dashboard admin)
     */
    public static function get_all( $webinar_id, $with_attendance = false ) {
        global $wpdb;
        $join = $with_attendance
            ? "LEFT JOIN {$wpdb->prefix}webinar_attendance a ON a.registrant_id = r.id AND a.webinar_id = r.webinar_id"
            : '';
        $select = $with_attendance
            ? "r.*, a.attended_at, CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END AS has_attended"
            : 'r.*';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT {$select} FROM {$wpdb->prefix}webinar_registrant r
             {$join}
             WHERE r.webinar_id = %d
             ORDER BY r.registered_at DESC",
            $webinar_id
        ) );
    }

    /**
     * Buat registrant minimal untuk peserta walk-in (tidak melalui form pendaftaran)
     * Data diambil dari form absensi.
     *
     * @param int    $webinar_id
     * @param string $email       Dari field email di form absensi
     * @param array  $form_data   Semua data dari form absensi
     * @return int|null  registrant_id
     */
    public static function create_walkin( $webinar_id, $email, array $form_data ) {
        global $wpdb;
        $token = self::generate_token();
        $wpdb->insert(
            $wpdb->prefix . 'webinar_registrant',
            [
                'webinar_id'      => $webinar_id,
                'unique_token'    => $token,
                'email'           => sanitize_email( $email ),
                'submission_data' => wp_json_encode( [] ), // no pre-registration data
                'registered_at'   => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s', '%s', '%s' ]
        );
        return $wpdb->insert_id ?: null;
    }

    /**
     * Hapus registrant (admin action)
     */
    public static function delete( $id ) {
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'webinar_registrant', [ 'id' => $id ], [ '%d' ] );
        $wpdb->delete( $wpdb->prefix . 'webinar_attendance', [ 'registrant_id' => $id ], [ '%d' ] );
    }


    // ── Private helpers ───────────────────────────────────────────────────────

    private static function generate_token() {
        global $wpdb;
        do {
            $token = bin2hex( random_bytes( 24 ) ); // 48 char hex
        } while ( $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}webinar_registrant WHERE unique_token = %s LIMIT 1",
            $token
        ) ) );
        return $token;
    }

    private static function sanitize_field( $value, $type ) {
        switch ( $type ) {
            case 'email':    return sanitize_email( $value );
            case 'textarea': return sanitize_textarea_field( $value );
            case 'url':      return esc_url_raw( $value );
            case 'number':   return absint( $value );
            case 'checkbox': return is_array( $value )
                ? array_map( 'sanitize_text_field', $value )
                : sanitize_text_field( $value );
            default:         return sanitize_text_field( $value );
        }
    }
}
