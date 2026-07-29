<?php
/**
 * AJAX Handlers — semua action admin & publik
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_Ajax {

    public static function init() {
        // ── Admin AJAX ──────────────────────────────────────────────────────
        $admin_actions = [
            'wbr_save_meta'          => [ 'save_webinar_meta',        'manage_webinars' ],
            'wbr_save_form_fields'   => [ 'save_form_fields',          'manage_webinars' ],
            'wbr_delete_registrant'  => [ 'delete_registrant',         'manage_webinars' ],
            'wbr_record_attendance'  => [ 'record_attendance_operator','manage_attendance' ],
            'wbr_create_sk'          => [ 'create_sk',                 'generate_sk' ],
            'wbr_update_sk'          => [ 'update_sk',                 'generate_sk' ],
            'wbr_regenerate_sk'      => [ 'regenerate_sk',             'generate_sk' ],
            'wbr_submit_signing'     => [ 'submit_for_signing',        'generate_sk' ],
            'wbr_revoke_cert'        => [ 'revoke_certificate',        'revoke_certificates' ],
            'wbr_regenerate_cert'    => [ 'regenerate_certificate',    'generate_certificates' ],
            'wbr_export_registrants' => [ 'export_registrants',        'export_webinar_data' ],
        ];

        foreach ( $admin_actions as $action => [ $method, $cap ] ) {
            add_action( 'wp_ajax_' . $action, function () use ( $method, $cap ) {
                if ( ! current_user_can( $cap ) ) wp_send_json_error( 'Unauthorized', 403 );
                check_ajax_referer( 'wbr_admin_nonce', 'nonce' );
                WBR_Ajax::$method();
            } );
        }

        // ── Public AJAX ─────────────────────────────────────────────────────
        add_action( 'wp_ajax_nopriv_wbr_register',      [ __CLASS__, 'public_register' ] );
        add_action( 'wp_ajax_nopriv_wbr_attend',        [ __CLASS__, 'public_attend' ] );
        add_action( 'wp_ajax_wbr_register',             [ __CLASS__, 'public_register' ] );
        add_action( 'wp_ajax_wbr_attend',               [ __CLASS__, 'public_attend' ] );

        // ── File upload (SK signed) ─────────────────────────────────────────
        add_action( 'wp_ajax_wbr_upload_sk_signed', [ __CLASS__, 'upload_sk_signed' ] );
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  ADMIN HANDLERS
    // ──────────────────────────────────────────────────────────────────────────

    public static function save_webinar_meta() {
        global $wpdb;
        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id || get_post_type( $post_id ) !== 'webinar' ) {
            wp_send_json_error( 'Invalid post' );
        }
        $data = [
            'post_id'              => $post_id,
            'start_datetime'       => sanitize_text_field( $_POST['start_datetime']        ?? '' ),
            'end_datetime'         => sanitize_text_field( $_POST['end_datetime']          ?? '' ),
            'zoom_link'            => esc_url_raw( $_POST['zoom_link']                     ?? '' ),
            'youtube_link'         => esc_url_raw( $_POST['youtube_link']                  ?? '' ),
            'cert_number_pattern'  => sanitize_text_field( $_POST['cert_number_pattern']   ?? '' ),
        ];

        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}webinar_meta WHERE post_id = %d", $post_id
        ) );

        if ( $existing ) {
            $wpdb->update( $wpdb->prefix . 'webinar_meta', $data, [ 'post_id' => $post_id ] );
        } else {
            $wpdb->insert( $wpdb->prefix . 'webinar_meta', $data );
        }

        // Handle template uploads
        foreach ( [ 'sk_template_file', 'petikan_template_file' ] as $field ) {
            if ( ! empty( $_FILES[ $field ]['tmp_name'] ) ) {
                $file = $_FILES[ $field ];
                $ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
                if ( $ext !== 'docx' ) continue;
                $dest = WBR_UPLOAD . 'templates/tpl-' . $field . '-' . $post_id . '.docx';
                move_uploaded_file( $file['tmp_name'], $dest );
                $wpdb->update(
                    $wpdb->prefix . 'webinar_meta',
                    [ $field => basename( $dest ) ],
                    [ 'post_id' => $post_id ]
                );
            }
        }

        wp_send_json_success( 'Data webinar berhasil disimpan.' );
    }

    public static function save_form_fields() {
        global $wpdb;
        $webinar_id = absint( $_POST['webinar_id'] ?? 0 );
        $form_type  = in_array( $_POST['form_type'] ?? '', [ 'registration', 'attendance' ] )
                      ? $_POST['form_type'] : 'registration';
        $fields     = json_decode( stripslashes( $_POST['fields'] ?? '[]' ), true );

        if ( ! is_array( $fields ) ) wp_send_json_error( 'Data tidak valid' );

        // Hapus field lama untuk webinar+form_type ini
        $wpdb->delete(
            $wpdb->prefix . 'webinar_form_field',
            [ 'webinar_id' => $webinar_id, 'form_type' => $form_type ],
            [ '%d', '%s' ]
        );

        // Insert field baru
        foreach ( $fields as $i => $f ) {
            $options = isset( $f['options'] ) && is_array( $f['options'] )
                ? wp_json_encode( array_map( 'sanitize_text_field', $f['options'] ) )
                : null;

            $wpdb->insert( $wpdb->prefix . 'webinar_form_field', [
                'webinar_id'       => $webinar_id,
                'form_type'        => $form_type,
                'field_key'        => sanitize_key( $f['field_key'] ?? 'field_' . $i ),
                'label'            => sanitize_text_field( $f['label'] ?? '' ),
                'field_type'       => sanitize_text_field( $f['field_type'] ?? 'text' ),
                'options'          => $options,
                'is_required'      => ! empty( $f['is_required'] ) ? 1 : 0,
                'is_identity_field'=> ! empty( $f['is_identity_field'] ) ? 1 : 0,
                'sort_order'       => $i,
            ] );
        }

        wp_send_json_success( count( $fields ) . ' field berhasil disimpan.' );
    }

    public static function delete_registrant() {
        $id = absint( $_POST['id'] ?? 0 );
        WBR_Registrant::delete( $id );
        wp_send_json_success( 'Peserta dihapus.' );
    }

    public static function record_attendance_operator() {
        $token  = sanitize_text_field( $_POST['token'] ?? '' );
        $result = WBR_Attendance::record_by_operator( $token );
        if ( $result['success'] ) wp_send_json_success( $result['message'] );
        else wp_send_json_error( $result['message'] );
    }

    public static function create_sk() {
        $webinar_id = absint( $_POST['webinar_id'] ?? 0 );
        $result     = WBR_SK::create( $webinar_id, $_POST );
        if ( $result['success'] ) wp_send_json_success( $result );
        else wp_send_json_error( $result['message'] );
    }

    public static function update_sk() {
        $sk_id  = absint( $_POST['sk_id'] ?? 0 );
        $result = WBR_SK::update( $sk_id, $_POST );
        wp_send_json_success( 'SK diperbarui.' );
    }

    public static function regenerate_sk() {
        $sk_id  = absint( $_POST['sk_id'] ?? 0 );
        $result = WBR_SK::regenerate_draft( $sk_id );
        if ( $result['success'] ) wp_send_json_success( $result );
        else wp_send_json_error( $result['message'] );
    }

    public static function submit_for_signing() {
        $sk_id  = absint( $_POST['sk_id'] ?? 0 );
        $result = WBR_SK::submit_for_signing( $sk_id );
        if ( $result['success'] ) wp_send_json_success( $result['message'] );
        else wp_send_json_error( $result['message'] );
    }

    public static function upload_sk_signed() {
        check_ajax_referer( 'wbr_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'generate_sk' ) ) wp_send_json_error( 'Unauthorized', 403 );

        $sk_id  = absint( $_POST['sk_id'] ?? 0 );
        $file   = $_FILES['sk_signed_file'] ?? null;

        if ( ! $file ) wp_send_json_error( 'Tidak ada file.' );

        $result = WBR_SK::upload_signed( $sk_id, $file );
        if ( $result['success'] ) wp_send_json_success( $result['message'] );
        else wp_send_json_error( $result['message'] );
    }

    public static function revoke_certificate() {
        $cert_id = absint( $_POST['cert_id'] ?? 0 );
        $reason  = sanitize_textarea_field( $_POST['reason'] ?? '' );
        $result  = WBR_Certificate::revoke( $cert_id, $reason );
        if ( $result['success'] ) wp_send_json_success( $result['message'] );
        else wp_send_json_error( $result['message'] );
    }

    public static function regenerate_certificate() {
        $cert_id = absint( $_POST['cert_id'] ?? 0 );
        $result  = WBR_Certificate::regenerate_pdf( $cert_id );
        if ( $result['success'] ) wp_send_json_success( $result );
        else wp_send_json_error( $result['message'] );
    }

    public static function export_registrants() {
        $webinar_id = absint( $_POST['webinar_id'] ?? 0 );
        $registrants = WBR_Registrant::get_all( $webinar_id, true );

        $rows = [ [ 'No', 'Email', 'Terdaftar', 'Status Hadir', 'Waktu Hadir' ] ];
        $i    = 1;
        foreach ( $registrants as $r ) {
            $data = json_decode( $r->submission_data, true ) ?: [];
            $rows[] = [
                $i++,
                $r->email,
                $r->registered_at,
                $r->has_attended ? 'Hadir' : 'Belum Hadir',
                $r->attended_at ?? '-',
            ];
        }

        // Return sebagai CSV string
        $csv = '';
        foreach ( $rows as $row ) {
            $csv .= implode( ',', array_map( fn($v) => '"' . str_replace( '"', '""', $v ) . '"', $row ) ) . "\n";
        }

        wp_send_json_success( [ 'csv' => $csv, 'count' => count( $registrants ) ] );
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  PUBLIC HANDLERS
    // ──────────────────────────────────────────────────────────────────────────

    public static function public_register() {
        check_ajax_referer( 'wbr_public_nonce', 'nonce' );

        $webinar_id = absint( $_POST['webinar_id'] ?? 0 );
        $form_data  = array_map( 'wp_unslash', (array) ( $_POST['form_data'] ?? [] ) );

        $result = WBR_Registrant::submit( $webinar_id, $form_data );

        if ( $result['success'] ) wp_send_json_success( $result['message'] );
        else wp_send_json_error( $result['message'] );
    }

    public static function public_attend() {
        check_ajax_referer( 'wbr_public_nonce', 'nonce' );

        $token     = sanitize_text_field( $_POST['token'] ?? '' );
        $form_data = array_map( 'wp_unslash', (array) ( $_POST['form_data'] ?? [] ) );

        $result = WBR_Attendance::submit( $token, $form_data );

        if ( $result['success'] ) wp_send_json_success( $result['message'] );
        else wp_send_json_error( $result['message'] );
    }
}
