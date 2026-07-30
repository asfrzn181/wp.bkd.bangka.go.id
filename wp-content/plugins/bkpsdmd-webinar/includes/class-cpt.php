<?php
/**
 * Custom Post Type: webinar
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_CPT {

    public static function register() {
        register_post_type( 'webinar', self::args() );
    }

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register' ] );
        add_action( 'init', [ __CLASS__, 'add_rewrite_rules' ] );
        add_filter( 'query_vars', [ __CLASS__, 'query_vars' ] );
    }

    private static function args() {
        return [
            'labels' => [
                'name'               => 'Webinar',
                'singular_name'      => 'Webinar',
                'add_new'            => 'Tambah Webinar',
                'add_new_item'       => 'Tambah Webinar Baru',
                'edit_item'          => 'Edit Webinar',
                'view_item'          => 'Lihat Webinar',
                'search_items'       => 'Cari Webinar',
                'not_found'          => 'Tidak ada webinar',
                'menu_name'          => 'Webinar',
            ],
            'public'              => true,
            'show_in_rest'        => true,
            'show_ui'             => true,
            'show_in_menu'        => false,  // kita kelola via custom menu
            'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            'rewrite'             => [ 'slug' => 'webinar', 'with_front' => false ],
            'has_archive'         => false,
            'menu_icon'           => 'dashicons-video-alt2',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
        ];
    }

    public static function add_rewrite_rules() {
        // /verifikasi-petikan/{hash}
        add_rewrite_rule(
            '^verifikasi-petikan/([a-zA-Z0-9_-]+)/?$',
            'index.php?wbr_verify=$matches[1]',
            'top'
        );
        // /absensi/{token}
        add_rewrite_rule(
            '^absensi/([a-zA-Z0-9_-]+)/?$',
            'index.php?wbr_token=$matches[1]',
            'top'
        );
        // /absensi-walkin/{webinar_id}
        add_rewrite_rule(
            '^absensi-walkin/([0-9]+)/?$',
            'index.php?wbr_absensi=$matches[1]',
            'top'
        );
    }

    public static function query_vars( $vars ) {
        $vars[] = 'wbr_verify';
        $vars[] = 'wbr_token';
        $vars[] = 'wbr_absensi';
        return $vars;
    }
}
