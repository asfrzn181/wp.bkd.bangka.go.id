<?php
/**
 * SK Minut — manajemen Surat Keputusan Menit
 * Workflow: draft → menunggu_ttd → final
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_SK {

    const STATUS_DRAFT       = 'draft';
    const STATUS_MENUNGGU    = 'menunggu_ttd';
    const STATUS_FINAL       = 'final';

    const METHOD_WET         = 'wet_signature';
    const METHOD_TTE         = 'tte_srikandi';

    /**
     * Ambil SK untuk satu webinar
     */
    public static function get_by_webinar( $webinar_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_sk WHERE webinar_id = %d LIMIT 1",
            $webinar_id
        ) );
    }

    /**
     * Buat record SK baru (hanya data meta, belum generate file)
     */
    public static function create( $webinar_id, $data ) {
        global $wpdb;

        $existing = self::get_by_webinar( $webinar_id );
        if ( $existing ) {
            return [ 'success' => false, 'message' => 'SK untuk webinar ini sudah ada.' ];
        }

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'webinar_sk',
            [
                'webinar_id'      => $webinar_id,
                'sk_number'       => sanitize_text_field( $data['sk_number']       ?? '' ),
                'sk_date'         => sanitize_text_field( $data['sk_date']         ?? '' ),
                'signing_official'=> sanitize_text_field( $data['signing_official']?? '' ),
                'signing_method'  => in_array( $data['signing_method'] ?? '', [ self::METHOD_WET, self::METHOD_TTE ] )
                                     ? $data['signing_method'] : self::METHOD_WET,
                'status'          => self::STATUS_DRAFT,
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%s' ]
        );

        if ( ! $inserted ) {
            return [ 'success' => false, 'message' => 'Gagal membuat SK.' ];
        }

        $sk_id = $wpdb->insert_id;

        // Generate draft docx
        $result = WBR_Document::generate_sk_draft( $sk_id );

        return [
            'success'    => true,
            'sk_id'      => $sk_id,
            'draft_file' => $result['file'] ?? '',
            'message'    => 'SK draft berhasil dibuat.',
        ];
    }

    /**
     * Update data SK (nomor, tanggal, pejabat, metode TTD)
     */
    public static function update( $sk_id, $data ) {
        global $wpdb;
        $fields = [];
        $formats = [];

        $allowed = [ 'sk_number', 'sk_date', 'signing_official', 'signing_method' ];
        foreach ( $allowed as $f ) {
            if ( isset( $data[ $f ] ) ) {
                $fields[ $f ] = sanitize_text_field( $data[ $f ] );
                $formats[]    = '%s';
            }
        }

        if ( empty( $fields ) ) return false;

        return $wpdb->update(
            $wpdb->prefix . 'webinar_sk',
            $fields,
            [ 'id' => $sk_id ],
            $formats,
            [ '%d' ]
        );
    }

    /**
     * Ajukan ke TTD — ubah status ke menunggu_ttd
     */
    public static function submit_for_signing( $sk_id ) {
        global $wpdb;
        $sk = self::get_by_id( $sk_id );
        if ( ! $sk || $sk->status !== self::STATUS_DRAFT ) {
            return [ 'success' => false, 'message' => 'SK harus berstatus draft untuk diajukan.' ];
        }
        if ( ! $sk->sk_draft_file ) {
            return [ 'success' => false, 'message' => 'Draft SK belum digenerate.' ];
        }

        $wpdb->update(
            $wpdb->prefix . 'webinar_sk',
            [ 'status' => self::STATUS_MENUNGGU ],
            [ 'id' => $sk_id ],
            [ '%s' ], [ '%d' ]
        );

        return [ 'success' => true, 'message' => 'SK berhasil diajukan untuk ditandatangani.' ];
    }

    /**
     * Upload file SK yang sudah ditandatangani → status final
     *
     * ALUR BARU: Sertifikat sudah ada (dibuat saat absensi), 
     * yang perlu dilakukan hanya mengisi sk_id di semua petikan webinar ini.
     */
    public static function upload_signed( $sk_id, $file ) {
        global $wpdb;

        $sk = self::get_by_id( $sk_id );
        if ( ! $sk ) return [ 'success' => false, 'message' => 'SK tidak ditemukan.' ];
        if ( $sk->status === self::STATUS_FINAL ) {
            return [ 'success' => false, 'message' => 'SK sudah final.' ];
        }

        // Validasi file: hanya PDF atau DOCX
        $allowed_types = [ 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ];
        $finfo = new finfo( FILEINFO_MIME_TYPE );
        $mime  = $finfo->file( $file['tmp_name'] );

        if ( ! in_array( $mime, $allowed_types ) ) {
            return [ 'success' => false, 'message' => 'Format file tidak valid. Upload PDF atau DOCX.' ];
        }

        // Pindahkan ke direktori SK
        $ext       = pathinfo( $file['name'], PATHINFO_EXTENSION );
        $filename  = 'sk-signed-' . $sk_id . '-' . time() . '.' . strtolower( $ext );
        $dest_dir  = WBR_UPLOAD . 'sk/';
        wp_mkdir_p( $dest_dir );
        $dest_path = $dest_dir . $filename;

        if ( ! move_uploaded_file( $file['tmp_name'], $dest_path ) ) {
            return [ 'success' => false, 'message' => 'Gagal menyimpan file.' ];
        }

        // Update SK status menjadi final
        $wpdb->update(
            $wpdb->prefix . 'webinar_sk',
            [
                'sk_signed_file' => $filename,
                'status'         => self::STATUS_FINAL,
            ],
            [ 'id' => $sk_id ],
            [ '%s', '%s' ], [ '%d' ]
        );

        // Link sk_id ke semua petikan webinar ini yang belum terhubung ke SK
        WBR_Certificate::link_sk_to_certificates( $sk_id, $sk->webinar_id );

        // Generate petikan yang mungkin belum ada (jika ada attendance sebelum SK tapi after SK final)
        wp_schedule_single_event( time() + 5, 'wbr_generate_certificates_batch', [ $sk->webinar_id ] );

        return [
            'success'  => true,
            'message'  => 'SK berhasil difinalisasi. Semua petikan sertifikat telah dihubungkan dengan SK ini.',
            'filename' => $filename,
        ];
    }


    /**
     * Regenerate draft SK (jika data berubah)
     */
    public static function regenerate_draft( $sk_id ) {
        return WBR_Document::generate_sk_draft( $sk_id );
    }

    public static function get_by_id( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT sk.*, p.post_title AS webinar_title, m.start_datetime, m.end_datetime
             FROM {$wpdb->prefix}webinar_sk sk
             JOIN {$wpdb->posts} p ON p.ID = sk.webinar_id
             JOIN {$wpdb->prefix}webinar_meta m ON m.post_id = sk.webinar_id
             WHERE sk.id = %d LIMIT 1",
            $id
        ) );
    }

    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT sk.*, p.post_title AS webinar_title
             FROM {$wpdb->prefix}webinar_sk sk
             JOIN {$wpdb->posts} p ON p.ID = sk.webinar_id
             ORDER BY sk.created_at DESC"
        );
    }

    public static function status_label( $status ) {
        return [
            self::STATUS_DRAFT    => '📝 Draft',
            self::STATUS_MENUNGGU => '⏳ Menunggu TTD',
            self::STATUS_FINAL    => '✅ Final',
        ][ $status ] ?? $status;
    }
}
