<?php
/**
 * Custom Post Type: banner_slide
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BKBC_CPT {

    public static function register() {
        register_post_type( 'banner_slide', [
            'labels' => [
                'name'               => 'Banner Slides',
                'singular_name'      => 'Banner Slide',
                'add_new'            => 'Tambah Slide',
                'add_new_item'       => 'Tambah Slide Baru',
                'edit_item'          => 'Edit Slide',
                'new_item'           => 'Slide Baru',
                'view_item'          => 'Lihat Slide',
                'search_items'       => 'Cari Slide',
                'not_found'          => 'Tidak ada slide ditemukan',
                'not_found_in_trash' => 'Tidak ada slide di Trash',
                'menu_name'          => 'Banner Carousel',
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => false,   // Kita buat menu sendiri
            'show_in_rest'        => false,
            'supports'            => [ 'title', 'thumbnail', 'page-attributes' ],
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-images-alt2',
            'rewrite'             => false,
            'has_archive'         => false,
            'capability_type'     => 'post',
        ] );
    }
}
