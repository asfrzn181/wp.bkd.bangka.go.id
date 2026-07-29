<?php
/**
 * AJAX handlers — generate slug, regenerate, delete, download QR
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BKSL_Ajax {

    public static function init() {
        add_action( 'wp_ajax_bksl_generate',        [ __CLASS__, 'generate' ] );
        add_action( 'wp_ajax_bksl_regenerate',      [ __CLASS__, 'regenerate' ] );
        add_action( 'wp_ajax_bksl_delete',          [ __CLASS__, 'delete_link' ] );
        add_action( 'wp_ajax_bksl_get_qr',          [ __CLASS__, 'get_qr' ] );
        add_action( 'wp_ajax_bksl_save_custom_slug',[ __CLASS__, 'save_custom_slug' ] );
    }

    // ── Generate short link untuk post (jika belum ada) ───────────────────────
    public static function generate() {
        check_ajax_referer( 'bksl_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Unauthorized', 403 );
        }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            wp_send_json_error( 'Invalid post ID' );
        }

        $existing = BKSL_DB::get_by_post_id( $post_id );
        if ( $existing ) {
            wp_send_json_success( self::build_response( $existing ) );
        }

        $slug = BKSL_DB::generate_unique_slug();
        BKSL_DB::insert( $post_id, $slug );
        $row = BKSL_DB::get_by_post_id( $post_id );
        wp_send_json_success( self::build_response( $row ) );
    }

    // ── Regenerate slug baru ──────────────────────────────────────────────────
    public static function regenerate() {
        check_ajax_referer( 'bksl_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Unauthorized', 403 );
        }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            wp_send_json_error( 'Invalid post ID' );
        }

        $new_slug = BKSL_DB::generate_unique_slug();
        $existing = BKSL_DB::get_by_post_id( $post_id );

        if ( $existing ) {
            BKSL_DB::update_slug( $post_id, $new_slug );
        } else {
            BKSL_DB::insert( $post_id, $new_slug );
        }

        $row = BKSL_DB::get_by_post_id( $post_id );
        wp_send_json_success( self::build_response( $row ) );
    }

    // ── Save custom slug ──────────────────────────────────────────────────────
    public static function save_custom_slug() {
        check_ajax_referer( 'bksl_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Unauthorized', 403 );
        }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        $slug    = sanitize_text_field( $_POST['slug'] ?? '' );

        if ( ! $post_id || ! $slug ) {
            wp_send_json_error( 'Data tidak lengkap' );
        }

        // Validasi: 3-10 char alphanum
        if ( ! preg_match( '/^[a-zA-Z0-9]{3,10}$/', $slug ) ) {
            wp_send_json_error( 'Slug hanya boleh huruf dan angka, 3-10 karakter' );
        }

        // Cek apakah slug sudah dipakai post lain
        $conflict = BKSL_DB::get_by_slug( $slug );
        if ( $conflict && (int) $conflict->post_id !== $post_id ) {
            wp_send_json_error( 'Slug sudah digunakan oleh konten lain' );
        }

        $existing = BKSL_DB::get_by_post_id( $post_id );
        if ( $existing ) {
            BKSL_DB::update_slug( $post_id, $slug );
        } else {
            BKSL_DB::insert( $post_id, $slug );
        }

        $row = BKSL_DB::get_by_post_id( $post_id );
        wp_send_json_success( self::build_response( $row ) );
    }

    // ── Delete short link ─────────────────────────────────────────────────────
    public static function delete_link() {
        check_ajax_referer( 'bksl_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized', 403 );
        }

        $id = absint( $_POST['id'] ?? 0 );
        if ( ! $id ) {
            wp_send_json_error( 'Invalid ID' );
        }

        BKSL_DB::delete_by_id( $id );
        wp_send_json_success( 'Deleted' );
    }

    // ── Get QR as base64 ──────────────────────────────────────────────────────
    public static function get_qr() {
        check_ajax_referer( 'bksl_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Unauthorized', 403 );
        }

        $url  = esc_url_raw( $_POST['url'] ?? '' );
        $size = absint( $_POST['size'] ?? 6 );
        if ( ! $url ) {
            wp_send_json_error( 'Invalid URL' );
        }

        $b64 = BKSL_QRCode::generate_base64( $url, $size );
        wp_send_json_success( [ 'qr' => $b64 ] );
    }

    // ── Build response object ─────────────────────────────────────────────────
    private static function build_response( $row ) {
        $short_url = home_url( '/' . $row->slug );
        return [
            'id'          => $row->id,
            'slug'        => $row->slug,
            'short_url'   => $short_url,
            'click_count' => $row->click_count,
            'qr'          => BKSL_QRCode::generate_base64( $short_url ),
        ];
    }
}

// Boot
BKSL_Ajax::init();
