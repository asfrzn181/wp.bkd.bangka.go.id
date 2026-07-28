<?php
/**
 * Block Styles
 *
 * @package career-development
 * @since 1.0
 */

if ( function_exists( 'register_block_style' ) ) {
	function career_development_register_block_styles() {

		//Wp Block Padding Zero
		register_block_style(
			'core/group',
			array(
				'name'  => 'career-development-padding-0',
				'label' => esc_html__( 'No Padding', 'career-development' ),
			)
		);

		//Wp Block Post Author Style
		register_block_style(
			'core/post-author',
			array(
				'name'  => 'career-development-post-author-card',
				'label' => esc_html__( 'Theme Style', 'career-development' ),
			)
		);

		//Wp Block Button Style
		register_block_style(
			'core/button',
			array(
				'name'         => 'career-development-button',
				'label'        => esc_html__( 'Plain', 'career-development' ),
			)
		);

		//Post Comments Style
		register_block_style(
			'core/post-comments',
			array(
				'name'         => 'career-development-post-comments',
				'label'        => esc_html__( 'Theme Style', 'career-development' ),
			)
		);

		//Latest Comments Style
		register_block_style(
			'core/latest-comments',
			array(
				'name'         => 'career-development-latest-comments',
				'label'        => esc_html__( 'Theme Style', 'career-development' ),
			)
		);


		//Wp Block Table Style
		register_block_style(
			'core/table',
			array(
				'name'         => 'career-development-wp-table',
				'label'        => esc_html__( 'Theme Style', 'career-development' ),
			)
		);


		//Wp Block Pre Style
		register_block_style(
			'core/preformatted',
			array(
				'name'         => 'career-development-wp-preformatted',
				'label'        => esc_html__( 'Theme Style', 'career-development' ),
			)
		);

		//Wp Block Verse Style
		register_block_style(
			'core/verse',
			array(
				'name'         => 'career-development-wp-verse',
				'label'        => esc_html__( 'Theme Style', 'career-development' ),
			)
		);
	}
	add_action( 'init', 'career_development_register_block_styles' );
}
