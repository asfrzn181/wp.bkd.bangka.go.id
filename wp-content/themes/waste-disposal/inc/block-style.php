<?php
/**
 * Block Styles
 *
 * @link https://developer.wordpress.org/reference/functions/register_block_style/
 *
 * @package WordPress
 * @subpackage waste-disposal
 * @since waste-disposal 1.0
 */

if ( function_exists( 'register_block_style' ) ) {
	/**
	 * Register block styles.
	 *
	 * @since waste-disposal 1.0
	 *
	 * @return void
	 */
	function waste_disposal_register_block_styles() {
		

		// Image: Borders.
		register_block_style(
			'core/image',
			array(
				'name'  => 'waste-disposal-border',
				'label' => esc_html__( 'Borders', 'waste-disposal' ),
			)
		);

		
	}
	add_action( 'init', 'waste_disposal_register_block_styles' );
}