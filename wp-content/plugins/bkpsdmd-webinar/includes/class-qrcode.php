<?php
/**
 * QR Code wrapper — reuse phpqrcode dari bkpsdmd-shortlink atau bundled copy
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_QRCode {

    private static function ensure_lib() {
        // Coba pakai dari bkpsdmd-shortlink dulu
        $shared = WP_PLUGIN_DIR . '/bkpsdmd-shortlink/lib/phpqrcode/qrlib.php';
        $local  = WBR_PATH . 'lib/phpqrcode/qrlib.php';

        if ( file_exists( $shared ) && ! class_exists( 'QRcode' ) ) {
            require_once $shared;
        } elseif ( file_exists( $local ) && ! class_exists( 'QRcode' ) ) {
            require_once $local;
        }

        return class_exists( 'QRcode' );
    }

    /**
     * Generate QR code sebagai base64 data URI (untuk embed di email/HTML)
     */
    public static function generate_base64( $url, $size = 6, $margin = 2 ) {
        if ( ! self::ensure_lib() ) return '';

        ob_start();
        QRcode::png( $url, false, QR_ECLEVEL_M, $size, $margin );
        $png = ob_get_clean();

        return 'data:image/png;base64,' . base64_encode( $png );
    }

    /**
     * Generate QR code dan simpan ke file path
     */
    public static function generate_file( $url, $file_path, $size = 8, $margin = 2 ) {
        if ( ! self::ensure_lib() ) return false;

        $dir = dirname( $file_path );
        if ( ! is_dir( $dir ) ) wp_mkdir_p( $dir );

        QRcode::png( $url, $file_path, QR_ECLEVEL_M, $size, $margin );
        return file_exists( $file_path );
    }
}
