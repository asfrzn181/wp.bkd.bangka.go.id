<?php
/**
 * Waste Disposal functions and definitions
 *
 * @package waste-disposal
 */

if ( ! function_exists( 'waste_disposal_support' ) ) :

	function waste_disposal_support() {

		load_theme_textdomain( 'waste-disposal', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'experimental-link-color' );

		// Editor style
		add_editor_style( 'style.css' );
	}

endif;
add_action( 'after_setup_theme', 'waste_disposal_support' );

/**
 * Enqueue Styles & Scripts
 */
function waste_disposal_assets() {

	$theme_version = wp_get_theme()->get( 'Version' );

	/**
	 * ======================
	 * STYLES
	 * ======================
	 */

	// Main stylesheet
	wp_enqueue_style(
		'waste-disposal-style',
		get_template_directory_uri() . '/style.css',
		array(),
		$theme_version
	);

	wp_style_add_data( 'waste-disposal-style', 'rtl', 'replace' );

	// Dashicons
	wp_enqueue_style( 'dashicons' );

	// Font Awesome
	wp_enqueue_style(
		'fontawesome',
		get_template_directory_uri() . '/inc/fontawesome/css/all.css',
		array(),
		'6.7.0'
	);

	// Animate CSS
	wp_enqueue_style(
		'animate-css',
		get_template_directory_uri() . '/assets/css/animate.css',
		array(),
		$theme_version
	);

	// Owl Carousel
	wp_enqueue_style(
		'owl-carousel-style',
		get_template_directory_uri() . '/assets/css/owl-carousel.css',
		array(),
		$theme_version
	);

	// Swiper CSS
	wp_enqueue_style(
		'swiper-css',
		get_template_directory_uri() . '/assets/css/swiper-bundle.css',
		array(),
		$theme_version
	);

	/**
	 * ======================
	 * SCRIPTS
	 * ======================
	 */

	// WOW JS
	wp_enqueue_script(
		'wow',
		get_template_directory_uri() . '/assets/js/wow.js',
		array('jquery'),
		$theme_version,
		true
	);

	// Custom JS
	wp_enqueue_script(
		'waste-disposal-custom',
		get_template_directory_uri() . '/assets/js/custom.js',
		array('jquery'),
		$theme_version,
		true
	);

	// Scroll to Top
	wp_enqueue_script(
		'waste-disposal-scroll-to-top',
		get_template_directory_uri() . '/assets/js/scroll-to-top.js',
		array(),
		$theme_version,
		true
	);

	// Swiper JS
	wp_enqueue_script(
		'swiper-js',
		get_template_directory_uri() . '/assets/js/swiper-bundle.js',
		array(),
		$theme_version,
		true
	);

	// Owl Carousel JS
	wp_enqueue_script(
		'owl-carousel-js',
		get_template_directory_uri() . '/assets/js/owl-carousel.js',
		array('jquery'),
		$theme_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'waste_disposal_assets' );

/**
 * Theme Settings & Required Files
 */
function waste_disposal_theme_setting() {

	require get_template_directory() . '/inc/block-pattern.php';
	require get_template_directory() . '/inc/block-style.php';

}
add_action( 'after_setup_theme', 'waste_disposal_theme_setting' );