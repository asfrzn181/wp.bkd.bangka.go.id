<?php
/**
 * Certificate (Petikan Sertifikat)
 *
 * ALUR BARU:
 * - Generate OTOMATIS saat attendance submit (per orang, via WP Cron async)
 * - sk_id NULLABLE — sertifikat bisa terbit sebelum SK ada
 * - Admin bisa trigger batch ulang jika perlu
 * - Admin bisa revoke dengan alasan
 * - SK update: saat SK final, sistem auto-link sk_id ke semua cert webinar tersebut
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_Certificate {

    const STATUS_ACTIVE  = 'active';
    const STATUS_REVOKED = 'revoked';

    // ── Generate untuk satu attendance (dipanggil saat attendance submit) ─────
    /**
     * Generate certificate untuk satu attendance. Dipanggil via WP Cron agar
     * tidak mem-block request HTTP form absensi.
     *
     * @param int $attendance_id
     * @return string|false qr_verification_hash if successful or already exists, false otherwise
     */
    public static function generate_for_attendance( $attendance_id ) {
        global $wpdb;

        // Cek sudah ada atau belum
        $existing_hash = $wpdb->get_var( $wpdb->prepare(
            "SELECT qr_verification_hash FROM {$wpdb->prefix}webinar_certificate WHERE attendance_id = %d LIMIT 1",
            $attendance_id
        ) );
        if ( $existing_hash ) return $existing_hash;

        $att = $wpdb->get_row( $wpdb->prepare(
            "SELECT a.*,
                    r.email   AS reg_email,
                    r.submission_data AS reg_data
             FROM {$wpdb->prefix}webinar_attendance a
             LEFT JOIN {$wpdb->prefix}webinar_registrant r ON r.id = a.registrant_id
             WHERE a.id = %d",
            $attendance_id
        ) );

        if ( ! $att ) return false;

        // Ambil pola nomor dari webinar_meta
        $meta = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_meta WHERE post_id = %d", $att->webinar_id
        ) );
        $pattern = $meta->cert_number_pattern ?: 'PTKAN/{nomor}/{tahun}';
        $year    = wp_date( 'Y' );

        // Nomor urut per webinar
        $counter = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}webinar_certificate WHERE webinar_id = %d",
            $att->webinar_id
        ) ) + 1;

        $petikan_number = str_replace(
            [ '{nomor}', '{tahun}', '{counter}' ],
            [ str_pad( $counter, 3, '0', STR_PAD_LEFT ), $year, $counter ],
            $pattern
        );

        // Ambil nama dari form absensi atau registrasi
        $att_data = json_decode( $att->submission_data, true ) ?: [];
        $reg_data = json_decode( $att->reg_data ?? '{}', true ) ?: [];

        $holder_name  = self::extract_name( array_merge( $reg_data, $att_data ), $att->webinar_id );
        $holder_email = $att->reg_email ?: self::extract_email( $att_data );

        // Hash verifikasi QR unik
        $hash = bin2hex( random_bytes( 20 ) );
        while ( $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}webinar_certificate WHERE qr_verification_hash = %s LIMIT 1",
            $hash
        ) ) ) {
            $hash = bin2hex( random_bytes( 20 ) );
        }

        // Ambil sk_id jika webinar ini sudah punya SK final
        $sk_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}webinar_sk WHERE webinar_id = %d AND status = 'final' LIMIT 1",
            $att->webinar_id
        ) );

        $data = [
            'webinar_id'           => $att->webinar_id,
            'attendance_id'        => $attendance_id,
            'petikan_number'       => $petikan_number,
            'holder_name'          => $holder_name,
            'holder_email'         => $holder_email,
            'file_path_pdf'        => '',
            'qr_verification_hash' => $hash,
            'status'               => self::STATUS_ACTIVE,
            'generated_at'         => current_time( 'mysql' ),
        ];
        $formats = [ '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ];

        if ( $sk_id ) {
            $data['sk_id'] = $sk_id;
            $formats[] = '%d';
        }

        // Insert certificate
        $wpdb->insert(
            $wpdb->prefix . 'webinar_certificate',
            $data,
            $formats
        );
        $cert_id = $wpdb->insert_id;
        if ( ! $cert_id ) return false;

        // Generate PDF
        $pdf_result = WBR_Document::generate_certificate_pdf( $cert_id );
        if ( $pdf_result['success'] && ! empty( $pdf_result['filename'] ) ) {
            $wpdb->update(
                $wpdb->prefix . 'webinar_certificate',
                [ 'file_path_pdf' => $pdf_result['filename'] ],
                [ 'id' => $cert_id ],
                [ '%s' ], [ '%d' ]
            );
        }

        // Notifikasi email ke peserta
        if ( $holder_email ) {
            WBR_Email::send_certificate_ready( $cert_id );
        }

        return $hash;
    }

    /**
     * Batch generate — isi sertifikat yang belum ada untuk semua attendance
     * di sebuah webinar. Bisa dipanggil manual oleh admin.
     *
     * @param int $webinar_id
     */
    public static function process_batch( $webinar_id ) {
        global $wpdb;
        $attendances = $wpdb->get_col( $wpdb->prepare(
            "SELECT a.id FROM {$wpdb->prefix}webinar_attendance a
             LEFT JOIN {$wpdb->prefix}webinar_certificate c ON c.attendance_id = a.id
             WHERE a.webinar_id = %d AND c.id IS NULL",
            $webinar_id
        ) );
        foreach ( $attendances as $att_id ) {
            self::generate_for_attendance( (int) $att_id );
        }
    }

    /**
     * Link sk_id ke semua certificate webinar ini saat SK menjadi final.
     * Dipanggil dari WBR_SK::upload_signed().
     *
     * @param int $sk_id
     * @param int $webinar_id
     */
    public static function link_sk_to_certificates( $sk_id, $webinar_id ) {
        global $wpdb;
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}webinar_certificate
             SET sk_id = %d
             WHERE webinar_id = %d AND sk_id IS NULL",
            $sk_id, $webinar_id
        ) );
    }

    // ── Revoke ────────────────────────────────────────────────────────────────
    public static function revoke( $cert_id, $reason = '' ) {
        global $wpdb;
        WBR_Roles::require_cap( 'revoke_certificates' );

        $cert = self::get_by_id( $cert_id );
        if ( ! $cert ) return [ 'success' => false, 'message' => 'Petikan tidak ditemukan.' ];
        if ( $cert->status === self::STATUS_REVOKED ) return [ 'success' => false, 'message' => 'Sudah dicabut.' ];

        $wpdb->update(
            $wpdb->prefix . 'webinar_certificate',
            [
                'status'        => self::STATUS_REVOKED,
                'revoked_at'    => current_time( 'mysql' ),
                'revoked_by'    => get_current_user_id(),
                'revoke_reason' => sanitize_textarea_field( $reason ),
            ],
            [ 'id' => $cert_id ],
            [ '%s', '%s', '%d', '%s' ], [ '%d' ]
        );

        return [ 'success' => true, 'message' => 'Petikan berhasil dicabut.' ];
    }

    // ── Verifikasi publik via hash ─────────────────────────────────────────────
    public static function verify_by_hash( $hash ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT c.*,
                    sk.sk_number, sk.sk_date, sk.signing_official,
                    p.post_title AS webinar_title,
                    m.start_datetime, m.end_datetime
             FROM {$wpdb->prefix}webinar_certificate c
             JOIN {$wpdb->posts} p             ON p.ID  = c.webinar_id
             JOIN {$wpdb->prefix}webinar_meta m ON m.post_id = c.webinar_id
             LEFT JOIN {$wpdb->prefix}webinar_sk sk ON sk.id = c.sk_id
             WHERE c.qr_verification_hash = %s LIMIT 1",
            sanitize_text_field( $hash )
        ) );
    }

    // ── Queries ───────────────────────────────────────────────────────────────
    public static function get_by_id( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_certificate WHERE id = %d LIMIT 1", $id
        ) );
    }

    public static function get_by_webinar( $webinar_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT c.*, sk.sk_number
             FROM {$wpdb->prefix}webinar_certificate c
             LEFT JOIN {$wpdb->prefix}webinar_sk sk ON sk.id = c.sk_id
             WHERE c.webinar_id = %d
             ORDER BY c.generated_at ASC",
            $webinar_id
        ) );
    }

    public static function get_by_sk( $sk_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_certificate WHERE sk_id = %d ORDER BY petikan_number ASC",
            $sk_id
        ) );
    }

    public static function regenerate_pdf( $cert_id ) {
        return WBR_Document::generate_certificate_pdf( $cert_id );
    }

    // ── Private helpers ───────────────────────────────────────────────────────
    private static function extract_name( array $data, $webinar_id = 0 ) {
        global $wpdb;
        $key_to_label = [];
        if ( $webinar_id ) {
            $fields = $wpdb->get_results( $wpdb->prepare(
                "SELECT field_key, label FROM {$wpdb->prefix}webinar_form_field WHERE webinar_id = %d",
                $webinar_id
            ) );
            foreach ( $fields as $f ) {
                $key_to_label[ $f->field_key ] = $f->label;
            }
        }

        foreach ( $data as $key => $val ) {
            if ( ! is_string( $val ) || $val === '' ) continue;
            if ( stripos( $key, 'nama' ) !== false ) return $val;
            
            $label = $key_to_label[ $key ] ?? '';
            if ( stripos( $label, 'nama' ) !== false ) return $val;
        }
        return '';
    }

    private static function extract_email( array $data ) {
        foreach ( $data as $key => $val ) {
            if ( is_string( $val ) && stripos( $key, 'email' ) !== false ) return $val;
        }
        return '';
    }
}

// ── WP Cron hook ─────────────────────────────────────────────────────────────
add_action( 'wbr_generate_certificate_for_attendance', [ 'WBR_Certificate', 'generate_for_attendance' ] );
add_action( 'wbr_generate_certificates_batch',         function( $webinar_id ) {
    WBR_Certificate::process_batch( $webinar_id );
} );
