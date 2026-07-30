<?php
/**
 * Public Router — intercept query vars:
 * ?wbr_token= (Form Absensi via Token Peserta Terdaftar)
 * ?wbr_absensi={webinar_id} (Form Absensi Walk-in / Langsung Hadir)
 * ?wbr_verify= (Halaman Verifikasi Petikan Sertifikat)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_PublicRouter {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'add_rewrite_rules' ] );
        add_filter( 'query_vars', [ __CLASS__, 'add_query_vars' ] );
        add_action( 'template_redirect', [ __CLASS__, 'handle_routes' ] );
    }

    public static function add_rewrite_rules() {
        add_rewrite_rule( '^absensi-walkin/([^/]+)/?', 'index.php?wbr_absensi=$matches[1]', 'top' );
        add_rewrite_rule( '^verifikasi-petikan/([^/]+)/?', 'index.php?wbr_verify=$matches[1]', 'top' );
        add_rewrite_rule( '^absensi-peserta/([^/]+)/?', 'index.php?wbr_token=$matches[1]', 'top' );
        add_rewrite_rule( '^daftar-peserta/([^/]+)/?', 'index.php?wbr_peserta=$matches[1]', 'top' );
    }

    public static function add_query_vars( $vars ) {
        $vars[] = 'wbr_absensi';
        $vars[] = 'wbr_verify';
        $vars[] = 'wbr_token';
        $vars[] = 'wbr_peserta';
        return $vars;
    }

    public static function handle_routes() {
        $token      = get_query_var( 'wbr_token' )   ?: ( $_GET['wbr_token']   ?? '' );
        $absensi_id = get_query_var( 'wbr_absensi' ) ?: ( $_GET['wbr_absensi'] ?? '' );
        $verify     = get_query_var( 'wbr_verify' )  ?: ( $_GET['wbr_verify']  ?? '' );
        $peserta    = get_query_var( 'wbr_peserta' ) ?: ( $_GET['wbr_peserta'] ?? '' );

        if ( $token ) {
            self::render_token_attendance( sanitize_text_field( $token ) );
            exit;
        }

        if ( $absensi_id ) {
            self::render_walkin_attendance( absint( $absensi_id ) );
            exit;
        }

        if ( $verify ) {
            self::render_certificate_verify( sanitize_text_field( $verify ) );
            exit;
        }

        if ( $peserta ) {
            self::render_participant_list( absint( $peserta ) );
            exit;
        }
    }

    private static function render_token_attendance( $token ) {
        $registrant = WBR_Registrant::get_by_token( $token );
        $is_walkin  = false;
        require_once WBR_PATH . 'public/views/attendance-form.php';
    }

    private static function render_walkin_attendance( $webinar_id ) {
        $registrant = null;
        $is_walkin  = true;
        require_once WBR_PATH . 'public/views/attendance-form.php';
    }

    private static function render_certificate_verify( $hash ) {
        $cert = WBR_Certificate::verify_by_hash( $hash );
        require_once WBR_PATH . 'public/views/certificate-verify.php';
    }

    private static function render_participant_list( $webinar_id ) {
        $webinar = get_post( $webinar_id );
        if ( ! $webinar || $webinar->post_type !== 'webinar' ) {
            wp_die( 'Webinar tidak ditemukan.', 'Tidak Ditemukan', [ 'response' => 404 ] );
        }
        $certificates = WBR_Certificate::get_by_webinar( $webinar_id );
        require_once WBR_PATH . 'public/views/participant-list.php';
    }
}
