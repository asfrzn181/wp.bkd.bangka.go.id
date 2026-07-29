<?php
/**
 * Roles & Capabilities
 * webinar_admin    → full access termasuk SK & petikan
 * webinar_operator → kelola webinar & absensi, tanpa SK
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_Roles {

    const CAPS_ADMIN = [
        'read'                 => true,
        'manage_webinars'      => true,
        'generate_sk'          => true,
        'generate_certificates'=> true,
        'revoke_certificates'  => true,
        'view_registrants'     => true,
        'manage_attendance'    => true,
        'export_webinar_data'  => true,
    ];

    const CAPS_OPERATOR = [
        'read'              => true,
        'manage_webinars'   => true,
        'view_registrants'  => true,
        'manage_attendance' => true,
    ];

    public static function create() {
        // Buat role jika belum ada
        if ( ! get_role( 'webinar_admin' ) ) {
            add_role( 'webinar_admin', 'Webinar Admin', self::CAPS_ADMIN );
        }
        if ( ! get_role( 'webinar_operator' ) ) {
            add_role( 'webinar_operator', 'Webinar Operator', self::CAPS_OPERATOR );
        }

        // Tambahkan caps ke administrator WP (agar tidak terkunci)
        $admin = get_role( 'administrator' );
        if ( $admin ) {
            foreach ( array_merge( self::CAPS_ADMIN, self::CAPS_OPERATOR ) as $cap => $val ) {
                $admin->add_cap( $cap );
            }
        }
    }

    public static function remove() {
        remove_role( 'webinar_admin' );
        remove_role( 'webinar_operator' );
    }

    // ── Helper: current user check ─────────────────────────────────────────
    public static function can( $cap ) {
        return current_user_can( $cap );
    }

    public static function require_cap( $cap ) {
        if ( ! self::can( $cap ) ) {
            wp_die( __( 'Anda tidak memiliki izin untuk melakukan tindakan ini.', 'bkpsdmd-wbr' ), 403 );
        }
    }
}
