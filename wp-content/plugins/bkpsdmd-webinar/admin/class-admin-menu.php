<?php
/**
 * Admin Menu — daftarkan semua menu & submenu
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_AdminMenu {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register' ] );
    }

    public static function register() {
        // Menu utama
        add_menu_page(
            'Manajemen Webinar',
            'Webinar',
            'manage_webinars',
            'wbr-dashboard',
            [ __CLASS__, 'page_dashboard' ],
            'dashicons-video-alt2',
            28
        );

        // Submenu: Dashboard
        add_submenu_page( 'wbr-dashboard', 'Dashboard Webinar', 'Dashboard', 'manage_webinars', 'wbr-dashboard', [ __CLASS__, 'page_dashboard' ] );

        // Submenu: Daftar Webinar
        add_submenu_page( 'wbr-dashboard', 'Daftar Webinar', 'Daftar Webinar', 'manage_webinars', 'edit.php?post_type=webinar' );

        // Submenu: + Webinar Baru
        add_submenu_page( 'wbr-dashboard', 'Tambah Webinar', '+ Webinar Baru', 'manage_webinars', 'post-new.php?post_type=webinar' );

        // Submenu: Peserta & Absensi
        add_submenu_page( 'wbr-dashboard', 'Peserta & Absensi', 'Peserta & Absensi', 'view_registrants', 'wbr-registrants', [ __CLASS__, 'page_registrants' ] );

        // Submenu: Manajemen SK
        if ( current_user_can( 'generate_sk' ) ) {
            add_submenu_page( 'wbr-dashboard', 'Manajemen SK Minut', 'SK Minut', 'generate_sk', 'wbr-sk', [ __CLASS__, 'page_sk' ] );
        }

        // Submenu: Petikan Sertifikat
        if ( current_user_can( 'generate_certificates' ) ) {
            add_submenu_page( 'wbr-dashboard', 'Petikan Sertifikat', 'Petikan Sertifikat', 'generate_certificates', 'wbr-certificates', [ __CLASS__, 'page_certificates' ] );
        }
    }

    // ── Page renderers ────────────────────────────────────────────────────────
    public static function page_dashboard()    { require WBR_PATH . 'admin/views/dashboard.php'; }
    public static function page_registrants()  { require WBR_PATH . 'admin/views/registrants.php'; }
    public static function page_sk()           { require WBR_PATH . 'admin/views/sk-management.php'; }
    public static function page_certificates() { require WBR_PATH . 'admin/views/certificates.php'; }
}
