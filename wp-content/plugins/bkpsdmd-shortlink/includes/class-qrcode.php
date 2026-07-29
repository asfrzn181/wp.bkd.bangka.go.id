<?php
/**
 * QR Code generator wrapper
 *
 * Primary : Pure-PHP + GD rendering (qrlib.php)
 * Fallback : Google Charts API (requires internet — only used if GD unavailable)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BKSL_QRCode {

    /**
     * Generate QR Code as base64 data URI string (PNG).
     *
     * @param  string $url    URL to encode
     * @param  int    $size   Pixel size per module (default 6)
     * @param  int    $margin Quiet zone modules (default 2)
     * @return string         data:image/png;base64,...
     */
    public static function generate_base64( $url, $size = 6, $margin = 2 ) {
        if ( function_exists( 'imagecreatetruecolor' ) ) {
            // Use local pure-PHP renderer
            ob_start();
            QRcode::png( $url, false, QR_ECLEVEL_M, $size, $margin );
            $raw = ob_get_clean();
            if ( strlen( $raw ) > 100 ) {
                return 'data:image/png;base64,' . base64_encode( $raw );
            }
        }

        // Fallback: Google Charts QR API
        return self::google_charts_base64( $url, $size );
    }

    /**
     * Fetch QR Code PNG from Google Charts API and return as base64.
     * Used only when local GD is unavailable.
     */
    private static function google_charts_base64( $url, $size = 6 ) {
        $px  = max( 100, min( 500, $size * 35 ) );
        $api = 'https://chart.googleapis.com/chart?cht=qr&chs=' . $px . 'x' . $px
             . '&chl=' . rawurlencode( $url )
             . '&choe=UTF-8&chld=M|1';

        $response = wp_remote_get( $api, [ 'timeout' => 8, 'sslverify' => false ] );
        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return self::placeholder_svg_base64( $url );
        }

        $body = wp_remote_retrieve_body( $response );
        return 'data:image/png;base64,' . base64_encode( $body );
    }

    /**
     * SVG placeholder when all else fails.
     */
    private static function placeholder_svg_base64( $url ) {
        $slug = esc_attr( wp_parse_url( $url, PHP_URL_PATH ) );
        $svg  = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">'
              . '<rect width="200" height="200" fill="#f3f4f6"/>'
              . '<rect x="20" y="20" width="60" height="60" fill="#6366f1" rx="4"/>'
              . '<rect x="30" y="30" width="40" height="40" fill="white" rx="2"/>'
              . '<rect x="40" y="40" width="20" height="20" fill="#6366f1"/>'
              . '<rect x="120" y="20" width="60" height="60" fill="#6366f1" rx="4"/>'
              . '<rect x="130" y="30" width="40" height="40" fill="white" rx="2"/>'
              . '<rect x="140" y="40" width="20" height="20" fill="#6366f1"/>'
              . '<rect x="20" y="120" width="60" height="60" fill="#6366f1" rx="4"/>'
              . '<rect x="30" y="130" width="40" height="40" fill="white" rx="2"/>'
              . '<rect x="40" y="140" width="20" height="20" fill="#6366f1"/>'
              . '<text x="100" y="105" text-anchor="middle" font-size="10" fill="#6366f1" font-family="monospace">'
              . esc_html( $slug ) . '</text>'
              . '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode( $svg );
    }

    /**
     * Generate raw PNG binary.
     */
    public static function generate_raw( $url, $size = 8, $margin = 2 ) {
        ob_start();
        QRcode::png( $url, false, QR_ECLEVEL_M, $size, $margin );
        return ob_get_clean();
    }
}
