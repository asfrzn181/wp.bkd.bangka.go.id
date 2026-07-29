<?php
/**
 * Enqueue admin styles & scripts
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_enqueue_scripts', 'bksl_enqueue_admin_assets' );
function bksl_enqueue_admin_assets( $hook ) {
    // Hanya load di halaman admin plugin & post/page editor
    $allowed_hooks = [
        'post.php',
        'post-new.php',
        'toplevel_page_bksl-shortlinks',
    ];
    if ( ! in_array( $hook, $allowed_hooks, true ) ) {
        return;
    }

    wp_enqueue_style(
        'bksl-admin',
        BKSL_URL . 'assets/css/admin.css',
        [],
        filemtime( BKSL_PATH . 'assets/css/admin.css' )
    );

    wp_enqueue_script(
        'bksl-admin',
        BKSL_URL . 'assets/js/admin.js',
        [ 'jquery' ],
        filemtime( BKSL_PATH . 'assets/js/admin.js' ),
        true
    );


    wp_localize_script( 'bksl-admin', 'bkslData', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'bksl_nonce' ),
        'homeUrl' => home_url( '/' ),
        'strings' => [
            'copied'      => 'Link berhasil disalin!',
            'copy_fail'   => 'Gagal menyalin, silakan salin manual.',
            'confirm_del' => 'Yakin ingin menghapus short link ini?',
            'regenerated' => 'Slug berhasil diperbarui!',
            'saved'       => 'Slug custom berhasil disimpan!',
            'generating'  => 'Membuat short link...',
            'error'       => 'Terjadi kesalahan. Coba lagi.',
        ],
    ] );
}
