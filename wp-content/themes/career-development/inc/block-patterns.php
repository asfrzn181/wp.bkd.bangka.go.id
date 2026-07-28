<?php
/**
 * Block Patterns
 *
 * @package career-development
 * @since 1.0
 */

function career_development_register_block_patterns() {
	$career_development_block_pattern_categories = array(
		'career-development' => array( 'label' => esc_html__( 'BKPSDMD Prima', 'career-development' ) ),
		'pages' => array( 'label' => esc_html__( 'Pages', 'career-development' ) ),
	);

	$career_development_block_pattern_categories = apply_filters( 'career_development_career_development_block_pattern_categories', $career_development_block_pattern_categories );

	foreach ( $career_development_block_pattern_categories as $name => $properties ) {
		if ( ! WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( $name ) ) {
			register_block_pattern_category( $name, $properties );
		}
	}

	// Daftarkan pattern Blog Grid Layout secara manual
	if ( ! WP_Block_Patterns_Registry::get_instance()->is_registered( 'career-development/blog-grid-layout' ) ) {
		register_block_pattern(
			'career-development/blog-grid-layout',
			array(
				'title'       => esc_html__( 'Blog Grid Layout', 'career-development' ),
				'description' => esc_html__( 'Menampilkan postingan dalam tata letak grid 3 kolom dengan filter tanggal dan kategori.', 'career-development' ),
				'categories'  => array( 'career-development' ),
				'content'     => '<!-- wp:shortcode -->[blog_grid_filter posts_per_page="6"]<!-- /wp:shortcode -->',
			)
		);
	}
}
add_action( 'init', 'career_development_register_block_patterns', 9 );