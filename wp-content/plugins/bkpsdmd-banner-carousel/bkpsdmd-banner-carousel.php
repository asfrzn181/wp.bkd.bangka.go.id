<?php
/**
 * Plugin Name: BKPSDMD Banner Carousel (Parallax 3D)
 * Plugin URI:  https://bkpsdmd.bangka.go.id
 * Description: Kelola Banner Carousel Utama dengan animasi Full Parallax JavaScript 3D langsung dari WP Admin.
 * Version:     1.0.0
 * Author:      BKPSDMD Kabupaten Bangka
 * Author URI:  https://bkpsdmd.bangka.go.id
 * Text Domain: bkpsdmd-bc
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BKBC_VERSION', '1.0.0' );
define( 'BKBC_PATH', plugin_dir_path( __FILE__ ) );
define( 'BKBC_URL', plugin_dir_url( __FILE__ ) );

require_once BKBC_PATH . 'includes/class-cpt.php';
require_once BKBC_PATH . 'includes/class-render.php';

add_action( 'plugins_loaded', function() {
    BKBC_CPT::init();
    BKBC_Render::init();
} );
