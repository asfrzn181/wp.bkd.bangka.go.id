<?php
/**
 * Certificate (Petikan) — generate batch & revoke
 * Dipanggil via WP Cron setelah SK final
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_Certificate {

    const STATUS_ACTIVE  = 'active';
    const STATUS_REVOKED = 'revoked';

    /**
     * WP Cron callback — generate semua petikan untuk SK yang sudah final
     * @param int $sk_id
     */
    public static function process_batch( $sk_id ) {
        global $wpdb;

        $sk = WBR_SK::get_by_id( $sk_id );
        if ( ! $sk || $sk->status !== WBR_SK::STATUS_FINAL ) return;

        // Ambil semua yang hadir di webinar ini
        $attendees = WBR_Attendance::get_all( $sk->webinar_id );

        // Ambil pola nomor petikan dari webinar_meta
        $meta = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_meta WHERE post_id = %d",
            $sk->webinar_id
        ) );
        $pattern = $meta->cert_number_pattern ?? 'PTKAN/{nomor}/{tahun}';

        $year    = wp_date( 'Y' );
        $counter = 1;

        foreach ( $attendees as $att ) {
            // Skip jika sudah ada petikan untuk kombinasi sk+registrant ini
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}webinar_certificate
                 WHERE sk_id = %d AND registrant_id = %d LIMIT 1",
                $sk_id, $att->registrant_id
            ) );
            if ( $exists ) { $counter++; continue; }

            // Nomor petikan
            $petikan_number = str_replace(
                [ '{nomor}', '{tahun}', '{counter}' ],
                [ str_pad( $counter, 3, '0', STR_PAD_LEFT ), $year, $counter ],
                $pattern
            );

            // Hash unik untuk QR verifikasi
            $hash = bin2hex( random_bytes( 20 ) );
            while ( $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}webinar_certificate WHERE qr_verification_hash = %s LIMIT 1", $hash
            ) ) ) {
                $hash = bin2hex( random_bytes( 20 ) );
            }

            // Insert terlebih dahulu (agar ada ID)
            $wpdb->insert(
                $wpdb->prefix . 'webinar_certificate',
                [
                    'sk_id'                => $sk_id,
                    'registrant_id'        => $att->registrant_id,
                    'petikan_number'       => $petikan_number,
                    'file_path_pdf'        => '',
                    'qr_verification_hash' => $hash,
                    'status'               => self::STATUS_ACTIVE,
                    'generated_at'         => current_time( 'mysql' ),
                ],
                [ '%d', '%d', '%s', '%s', '%s', '%s', '%s' ]
            );
            $cert_id = $wpdb->insert_id;

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

            // Kirim notifikasi email
            WBR_Email::send_certificate_ready( $cert_id );

            $counter++;
        }
    }

    /**
     * Revoke satu petikan
     */
    public static function revoke( $cert_id, $reason = '' ) {
        global $wpdb;
        WBR_Roles::require_cap( 'revoke_certificates' );

        $cert = self::get_by_id( $cert_id );
        if ( ! $cert ) {
            return [ 'success' => false, 'message' => 'Petikan tidak ditemukan.' ];
        }
        if ( $cert->status === self::STATUS_REVOKED ) {
            return [ 'success' => false, 'message' => 'Petikan sudah dicabut sebelumnya.' ];
        }

        $wpdb->update(
            $wpdb->prefix . 'webinar_certificate',
            [
                'status'       => self::STATUS_REVOKED,
                'revoked_at'   => current_time( 'mysql' ),
                'revoked_by'   => get_current_user_id(),
                'revoke_reason'=> sanitize_textarea_field( $reason ),
            ],
            [ 'id' => $cert_id ],
            [ '%s', '%s', '%d', '%s' ], [ '%d' ]
        );

        return [ 'success' => true, 'message' => 'Petikan berhasil dicabut.' ];
    }

    /**
     * Verifikasi via hash (publik)
     */
    public static function verify_by_hash( $hash ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT c.*,
                    r.email, r.submission_data AS reg_data,
                    sk.sk_number, sk.sk_date, sk.signing_official,
                    p.post_title AS webinar_title,
                    m.start_datetime, m.end_datetime
             FROM {$wpdb->prefix}webinar_certificate c
             JOIN {$wpdb->prefix}webinar_registrant r  ON r.id  = c.registrant_id
             JOIN {$wpdb->prefix}webinar_sk sk         ON sk.id = c.sk_id
             JOIN {$wpdb->posts} p                     ON p.ID  = r.webinar_id
             JOIN {$wpdb->prefix}webinar_meta m        ON m.post_id = r.webinar_id
             WHERE c.qr_verification_hash = %s LIMIT 1",
            sanitize_text_field( $hash )
        ) );
    }

    public static function get_by_id( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT c.*, r.email, r.submission_data AS reg_data
             FROM {$wpdb->prefix}webinar_certificate c
             JOIN {$wpdb->prefix}webinar_registrant r ON r.id = c.registrant_id
             WHERE c.id = %d LIMIT 1",
            $id
        ) );
    }

    /**
     * Daftar petikan per SK (untuk admin)
     */
    public static function get_by_sk( $sk_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT c.*, r.email, r.submission_data AS reg_data
             FROM {$wpdb->prefix}webinar_certificate c
             JOIN {$wpdb->prefix}webinar_registrant r ON r.id = c.registrant_id
             WHERE c.sk_id = %d
             ORDER BY c.generated_at ASC",
            $sk_id
        ) );
    }

    /**
     * Regenerate satu PDF petikan (admin manual)
     */
    public static function regenerate_pdf( $cert_id ) {
        return WBR_Document::generate_certificate_pdf( $cert_id );
    }
}
