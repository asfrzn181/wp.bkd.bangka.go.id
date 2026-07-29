<?php
/**
 * Public Router — intercept query vars:
 * ?wbr_token= (Form Absensi)
 * ?wbr_verify= (Halaman Verifikasi Petikan)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_PublicRouter {

    public static function init() {
        add_action( 'template_redirect', [ __CLASS__, 'handle_routes' ] );
    }

    public static function handle_routes() {
        $token  = get_query_var( 'wbr_token' )  ?: ( $_GET['wbr_token']  ?? '' );
        $verify = get_query_var( 'wbr_verify' ) ?: ( $_GET['wbr_verify'] ?? '' );

        if ( $token ) {
            self::render_attendance_form( sanitize_text_field( $token ) );
            exit;
        }

        if ( $verify ) {
            self::render_certificate_verify( sanitize_text_field( $verify ) );
            exit;
        }
    }

    private static function render_attendance_form( $token ) {
        $registrant = WBR_Registrant::get_by_token( $token );
        require_once WBR_PATH . 'public/views/attendance-form.php';
    }

    private static function render_certificate_verify( $hash ) {
        $cert = WBR_Certificate::verify_by_hash( $hash );
        require_once WBR_PATH . 'public/views/certificate-verify.php';
    }
}
