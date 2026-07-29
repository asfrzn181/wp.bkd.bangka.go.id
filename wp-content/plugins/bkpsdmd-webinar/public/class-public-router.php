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
        add_action( 'template_redirect', [ __CLASS__, 'handle_routes' ] );
    }

    public static function handle_routes() {
        $token      = get_query_var( 'wbr_token' )   ?: ( $_GET['wbr_token']   ?? '' );
        $absensi_id = get_query_var( 'wbr_absensi' ) ?: ( $_GET['wbr_absensi'] ?? '' );
        $verify     = get_query_var( 'wbr_verify' )  ?: ( $_GET['wbr_verify']  ?? '' );

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
}
