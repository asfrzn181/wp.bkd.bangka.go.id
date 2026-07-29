<?php
/**
 * Shortcodes — [webinar_list] & [webinar_detail id="123"]
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_Shortcode {

    public static function init() {
        add_shortcode( 'webinar_list',   [ __CLASS__, 'render_list' ] );
        add_shortcode( 'webinar_detail', [ __CLASS__, 'render_detail' ] );
    }

    public static function render_list( $atts ) {
        $atts = shortcode_atts( [ 'limit' => 10 ], $atts, 'webinar_list' );
        ob_start();
        require WBR_PATH . 'public/views/webinar-list.php';
        return ob_get_clean();
    }

    public static function render_detail( $atts ) {
        $atts = shortcode_atts( [ 'id' => 0 ], $atts, 'webinar_detail' );
        $webinar_id = absint( $atts['id'] );

        if ( ! $webinar_id && is_singular( 'webinar' ) ) {
            $webinar_id = get_the_ID();
        }

        if ( ! $webinar_id ) {
            return '<div class="wbr-alert error">Webinar ID tidak ditentukan.</div>';
        }

        ob_start();
        require WBR_PATH . 'public/views/webinar-detail.php';
        return ob_get_clean();
    }
}
