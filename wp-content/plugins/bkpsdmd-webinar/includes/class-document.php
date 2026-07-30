<?php
/**
 * Document — PhpWord template processor + LibreOffice PDF conversion
 *
 * Template placeholder format: ${variable_name}
 *
 * Variabel SK  : ${nama_webinar} ${tanggal_pelaksanaan} ${jam_mulai} ${jam_selesai}
 *                ${sk_number} ${sk_date} ${signing_official} ${jumlah_peserta}
 *                ${daftar_peserta} (tabel atau list)
 *
 * Variabel Petikan: ${nama_peserta} ${email_peserta} ${jabatan} ${instansi}
 *                   ${petikan_number} ${sk_number} ${sk_date} ${nama_webinar}
 *                   ${tanggal_pelaksanaan} ${signing_official} ${qr_url}
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_Document {

    // ── PhpWord loader ────────────────────────────────────────────────────────
    private static function load_phpword() {
        if ( class_exists( 'PhpOffice\\PhpWord\\TemplateProcessor' ) ) return true;

        $path = WBR_PATH . 'lib/PhpWord/src/PhpWord/autoload.php';
        if ( file_exists( $path ) ) {
            require_once $path;
            return true;
        }

        // Coba Composer autoload
        $composer = WBR_PATH . 'vendor/autoload.php';
        if ( file_exists( $composer ) ) {
            require_once $composer;
            return class_exists( 'PhpOffice\\PhpWord\\TemplateProcessor' );
        }

        return false;
    }

    // ── LibreOffice detection ─────────────────────────────────────────────────
    private static function libreoffice_path() {
        $candidates = [
            '/usr/bin/soffice',
            '/usr/local/bin/soffice',
            '/opt/libreoffice/program/soffice',
        ];
        foreach ( $candidates as $p ) {
            if ( is_executable( $p ) ) return $p;
        }
        // Fallback: which
        $which = trim( shell_exec( 'which soffice 2>/dev/null' ) );
        return $which ?: null;
    }

    // ── Generate SK draft (DOCX) ──────────────────────────────────────────────
    public static function generate_sk_draft( $sk_id ) {
        global $wpdb;

        if ( ! self::load_phpword() ) {
            return [ 'success' => false, 'message' => 'Library PhpWord tidak tersedia.' ];
        }

        $sk = WBR_SK::get_by_id( $sk_id );
        if ( ! $sk ) return [ 'success' => false, 'message' => 'SK tidak ditemukan.' ];

        // Ambil template dari webinar_meta
        $meta = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_meta WHERE post_id = %d", $sk->webinar_id
        ) );

        $template_file = WBR_UPLOAD . 'templates/' . basename( $meta->sk_template_file ?? '' );
        if ( ! file_exists( $template_file ) || is_dir( $template_file ) ) {
            $template_file = WBR_PATH . 'templates/default-sk.docx';
            self::ensure_default_docx( $template_file, 'sk' );
        }

        // Ambil daftar peserta hadir
        $attendees = WBR_Attendance::get_all( $sk->webinar_id );
        $daftar    = [];
        $no        = 1;
        foreach ( $attendees as $att ) {
            $reg = json_decode( $att->reg_data ?? $att->submission_data, true ) ?: [];
            $nama = '';
            foreach ( $reg as $k => $v ) {
                if ( is_string( $v ) && stripos( $k, 'nama' ) !== false ) { $nama = $v; break; }
            }
            $daftar[] = $no . '. ' . $nama;
            $no++;
        }

        // Build placeholders
        $start = wp_date( 'd F Y', strtotime( $sk->start_datetime ) );
        $placeholders = [
            'nama_webinar'       => $sk->webinar_title,
            'tanggal_pelaksanaan'=> $start,
            'jam_mulai'          => wp_date( 'H:i', strtotime( $sk->start_datetime ) ),
            'jam_selesai'        => wp_date( 'H:i', strtotime( $sk->end_datetime ) ),
            'sk_number'          => $sk->sk_number,
            'sk_date'            => $sk->sk_date ? wp_date( 'd F Y', strtotime( $sk->sk_date ) ) : '',
            'signing_official'   => $sk->signing_official,
            'jumlah_peserta'     => count( $attendees ),
            'daftar_peserta'     => implode( "\n", $daftar ),
            'jam_pelajaran'      => $meta->jam_pelajaran ?? 0,
        ];

        try {
            $processor = new \PhpOffice\PhpWord\TemplateProcessor( $template_file );
            foreach ( $placeholders as $key => $val ) {
                $processor->setValue( $key, htmlspecialchars( (string) $val ) );
            }

            $dest_dir  = WBR_UPLOAD . 'sk/';
            $filename  = 'sk-draft-' . $sk_id . '-' . time() . '.docx';
            $dest_path = $dest_dir . $filename;

            wp_mkdir_p( $dest_dir );
            $processor->saveAs( $dest_path );

            // Update DB
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'webinar_sk',
                [ 'sk_draft_file' => $filename ],
                [ 'id' => $sk_id ],
                [ '%s' ], [ '%d' ]
            );

            return [ 'success' => true, 'filename' => $filename, 'file' => $dest_path ];

        } catch ( Exception $e ) {
            return [ 'success' => false, 'message' => 'Error: ' . $e->getMessage() ];
        }
    }

    // ── Generate Certificate PDF ──────────────────────────────────────────────
    public static function generate_certificate_pdf( $cert_id ) {
        global $wpdb;

        if ( ! self::load_phpword() ) {
            return [ 'success' => false, 'message' => 'PhpWord tidak tersedia.' ];
        }

        $cert = WBR_Certificate::get_by_id( $cert_id );
        if ( ! $cert ) return [ 'success' => false, 'message' => 'Petikan tidak ditemukan.' ];

        $reg  = json_decode( $cert->reg_data, true ) ?: [];

        // Ambil data Webinar langsung karena SK mungkin belum ada
        $webinar_post = get_post( $cert->webinar_id );
        $meta = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_meta WHERE post_id = %d", $cert->webinar_id
        ) );

        if ( ! $webinar_post || ! $meta ) {
            return [ 'success' => false, 'message' => 'Data webinar tidak ditemukan.' ];
        }

        // Ambil SK (bisa null)
        $sk = null;
        if ( $cert->sk_id ) {
            $sk = WBR_SK::get_by_id( $cert->sk_id );
        }

        // Nama, jabatan, instansi (heuristic dari submission_data)
        $nama     = self::extract_field( $reg, 'nama' );
        $jabatan  = self::extract_field( $reg, 'jabatan' );
        $instansi = self::extract_field( $reg, 'instansi' );

        // Ambil template petikan dari meta
        $tpl_file = WBR_UPLOAD . 'templates/' . basename( $meta->petikan_template_file ?? '' );
        if ( ! file_exists( $tpl_file ) || is_dir( $tpl_file ) ) {
            $tpl_file = WBR_PATH . 'templates/default-petikan.docx';
            self::ensure_default_docx( $tpl_file, 'petikan' );
        }

        // QR code file
        $verify_url   = home_url( '/verifikasi-petikan/' . $cert->qr_verification_hash );
        $qr_dir       = WBR_UPLOAD . 'certificates/qr/';
        wp_mkdir_p( $qr_dir );
        $qr_file = $qr_dir . 'qr-' . $cert_id . '.png';
        WBR_QRCode::generate_file( $verify_url, $qr_file, 8 );

        $placeholders = [
            'nama_peserta'       => $nama,
            'email_peserta'      => $cert->email,
            'jabatan'            => $jabatan,
            'instansi'           => $instansi,
            'petikan_number'     => $cert->petikan_number,
            'sk_number'          => $sk && $sk->sk_number ? $sk->sk_number : 'Menunggu SK',
            'sk_date'            => $sk && $sk->sk_date ? wp_date( 'd F Y', strtotime( $sk->sk_date ) ) : 'Menunggu SK',
            'nama_webinar'       => $webinar_post->post_title,
            'tanggal_pelaksanaan'=> wp_date( 'd F Y', strtotime( $meta->start_datetime ) ),
            'jam_pelajaran'      => $meta->jam_pelajaran ?? 0,
            'signing_official'   => $sk && $sk->signing_official ? $sk->signing_official : '—',
            'qr_url'             => $verify_url,
        ];

        try {
            $processor = new \PhpOffice\PhpWord\TemplateProcessor( $tpl_file );
            foreach ( $placeholders as $key => $val ) {
                $processor->setValue( $key, htmlspecialchars( (string) $val ) );
            }

            // Embed QR image jika template mendukung (setImageValue)
            if ( file_exists( $qr_file ) ) {
                try {
                    $processor->setImageValue( 'qr_image', [
                        'path'  => $qr_file,
                        'width' => 80,
                        'height'=> 80,
                    ] );
                } catch ( Exception $e ) { /* template mungkin tidak ada placeholder gambar */ }
            }

            $cert_dir  = WBR_UPLOAD . 'certificates/';
            wp_mkdir_p( $cert_dir );
            $docx_name = 'petikan-' . $cert_id . '-' . time() . '.docx';
            $docx_path = $cert_dir . $docx_name;
            $processor->saveAs( $docx_path );

            // Convert ke PDF
            $pdf_result = self::convert_to_pdf( $docx_path, $cert_dir );

            if ( $pdf_result['success'] ) {
                @unlink( $docx_path ); // hapus docx temporary
                return [ 'success' => true, 'filename' => $pdf_result['filename'] ];
            }

            // Fallback: kembalikan docx
            return [ 'success' => true, 'filename' => $docx_name, 'note' => 'PDF conversion gagal, file DOCX tersimpan.' ];

        } catch ( Exception $e ) {
            return [ 'success' => false, 'message' => $e->getMessage() ];
        }
    }

    // ── LibreOffice convert DOCX → PDF ────────────────────────────────────────
    private static function convert_to_pdf( $docx_path, $output_dir ) {
        $soffice = self::libreoffice_path();
        if ( ! $soffice ) {
            return [ 'success' => false, 'message' => 'LibreOffice tidak tersedia.' ];
        }

        $docx_escaped   = escapeshellarg( $docx_path );
        $out_escaped    = escapeshellarg( rtrim( $output_dir, '/' ) );
        $cmd            = "$soffice --headless --convert-to pdf --outdir $out_escaped $docx_escaped 2>&1";
        $output         = shell_exec( $cmd );

        $pdf_name = pathinfo( $docx_path, PATHINFO_FILENAME ) . '.pdf';
        $pdf_path = rtrim( $output_dir, '/' ) . '/' . $pdf_name;

        if ( file_exists( $pdf_path ) ) {
            return [ 'success' => true, 'filename' => $pdf_name, 'path' => $pdf_path ];
        }

        return [ 'success' => false, 'message' => 'PDF tidak terbuat. Output: ' . $output ];
    }

    // ── URL download file SK atau Petikan (dengan kapabilitas check) ──────────
    public static function download_url( $type, $id ) {
        return add_query_arg( [
            'action'       => 'wbr_download_file',
            'wbr_download' => $type,
            'id'           => $id,
            'nonce'        => wp_create_nonce( 'wbr_download_' . $type . '_' . $id ),
        ], admin_url( 'admin-ajax.php' ) );
    }

    // ── Helper ────────────────────────────────────────────────────────────────
    private static function extract_field( $data, $hint ) {
        foreach ( (array) $data as $key => $val ) {
            if ( is_string( $val ) && stripos( $key, $hint ) !== false ) return $val;
        }
        return '';
    }

    /**
     * Buat file docx default minimal jika belum ada di disk
     */
    private static function ensure_default_docx( $file_path, $type ) {
        if ( file_exists( $file_path ) ) return;

        $dir = dirname( $file_path );
        wp_mkdir_p( $dir );

        if ( ! class_exists( 'ZipArchive' ) ) return;

        $sk_xml = '<w:p><w:r><w:t>SURAT KEPUTUSAN (SK MINUT)</w:t></w:r></w:p>
<w:p><w:r><w:t>Nomor: ${sk_number}</w:t></w:r></w:p>
<w:p><w:r><w:t>Tanggal: ${sk_date}</w:t></w:r></w:p>
<w:p><w:r><w:t>Tentang: Penyelenggaraan Webinar ${nama_webinar}</w:t></w:r></w:p>
<w:p><w:r><w:t>Pelaksanaan: ${tanggal_pelaksanaan} (${jam_mulai} - ${jam_selesai} WIB)</w:t></w:r></w:p>
<w:p><w:r><w:t>Jumlah Jam Pelajaran: ${jam_pelajaran} JP</w:t></w:r></w:p>
<w:p><w:r><w:t>Jumlah Peserta Hadir: ${jumlah_peserta} orang</w:t></w:r></w:p>
<w:p><w:r><w:t>Daftar Peserta:</w:t></w:r></w:p>
<w:p><w:r><w:t>${daftar_peserta}</w:t></w:r></w:p>
<w:p><w:r><w:t>Pejabat Penandatangan: ${signing_official}</w:t></w:r></w:p>';

        $petikan_xml = '<w:p><w:r><w:t>PETIKAN SERTIFIKAT KEIKUTSERTAAN</w:t></w:r></w:p>
<w:p><w:r><w:t>Nomor Petikan: ${petikan_number}</w:t></w:r></w:p>
<w:p><w:r><w:t>Diberikan kepada:</w:t></w:r></w:p>
<w:p><w:r><w:t>Nama: ${nama_peserta}</w:t></w:r></w:p>
<w:p><w:r><w:t>Email: ${email_peserta}</w:t></w:r></w:p>
<w:p><w:r><w:t>Jabatan: ${jabatan}</w:t></w:r></w:p>
<w:p><w:r><w:t>Instansi: ${instansi}</w:t></w:r></w:p>
<w:p><w:r><w:t>Atas partisipasinya dalam webinar: ${nama_webinar}</w:t></w:r></w:p>
<w:p><w:r><w:t>Tanggal Pelaksanaan: ${tanggal_pelaksanaan}</w:t></w:r></w:p>
<w:p><w:r><w:t>Jumlah Jam Pelajaran: ${jam_pelajaran} JP</w:t></w:r></w:p>
<w:p><w:r><w:t>Referensi SK Minut: ${sk_number} Tanggal ${sk_date}</w:t></w:r></w:p>
<w:p><w:r><w:t>Pejabat Penandatangan SK: ${signing_official}</w:t></w:r></w:p>
<w:p><w:r><w:t>Verifikasi Keaslian: ${qr_url}</w:t></w:r></w:p>';

        $content_xml = ( $type === 'sk' ) ? $sk_xml : $petikan_xml;

        $zip = new ZipArchive();
        if ( $zip->open( $file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) === TRUE ) {
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>');
            $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:body>' . $content_xml . '</w:body>
</w:document>');
            $zip->close();
        }
    }
}

// ── File download handler (AJAX) ──────────────────────────────────────────────
add_action( 'wp_ajax_wbr_download_file', 'wbr_handle_file_download' );
function wbr_handle_file_download() {
    $type  = sanitize_text_field( $_GET['wbr_download'] ?? '' );
    $id    = absint( $_GET['id'] ?? 0 );
    $nonce = $_GET['nonce'] ?? '';

    if ( ! wp_verify_nonce( $nonce, 'wbr_download_' . $type . '_' . $id ) ) {
        wp_die( 'Link tidak valid.', 403 );
    }

    if ( ! current_user_can( 'manage_webinars' ) ) {
        wp_die( 'Tidak diizinkan.', 403 );
    }

    switch ( $type ) {
        case 'sk_draft':
        case 'sk_signed':
            global $wpdb;
            $sk      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}webinar_sk WHERE id = %d", $id ) );
            $col     = $type === 'sk_draft' ? 'sk_draft_file' : 'sk_signed_file';
            $file    = WBR_UPLOAD . 'sk/' . basename( $sk->$col ?? '' );
            break;
        case 'certificate':
            $cert    = WBR_Certificate::get_by_id( $id );
            $file    = WBR_UPLOAD . 'certificates/' . basename( $cert->file_path_pdf ?? '' );
            break;
        default:
            wp_die( 'Tipe tidak valid.', 400 );
    }

    if ( ! file_exists( $file ) ) {
        wp_die( 'File tidak ditemukan.', 404 );
    }

    $mime = mime_content_type( $file );
    header( 'Content-Type: ' . $mime );
    header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"' );
    header( 'Content-Length: ' . filesize( $file ) );
    readfile( $file );
    exit;
}

// ── Public Certificate Download (via Hash) ────────────────────────────────────
add_action( 'wp_ajax_wbr_download_cert_public', 'wbr_handle_cert_download_public' );
add_action( 'wp_ajax_nopriv_wbr_download_cert_public', 'wbr_handle_cert_download_public' );
function wbr_handle_cert_download_public() {
    $hash = sanitize_text_field( $_GET['hash'] ?? '' );
    if ( ! $hash ) wp_die( 'Hash tidak valid.', 400 );

    $cert = WBR_Certificate::verify_by_hash( $hash );
    if ( ! $cert || ! $cert->file_path_pdf ) {
        wp_die( 'Sertifikat tidak ditemukan atau belum digenerate.', 404 );
    }

    $file = WBR_UPLOAD . 'certificates/' . basename( $cert->file_path_pdf );
    if ( ! file_exists( $file ) ) {
        wp_die( 'File fisik tidak ditemukan di server.', 404 );
    }

    $mime = mime_content_type( $file );
    header( 'Content-Type: ' . $mime );
    header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"' );
    header( 'Content-Length: ' . filesize( $file ) );
    readfile( $file );
    exit;
}
