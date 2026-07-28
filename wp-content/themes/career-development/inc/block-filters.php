<?php
/**
 * Block Filters
 *
 * @package career-development
 * @since 1.0
 */

function career_development_block_wrapper( $career_development_block_content, $career_development_block ) {

	if ( 'core/button' === $career_development_block['blockName'] ) {
		
		if( isset( $career_development_block['attrs']['className'] ) && strpos( $career_development_block['attrs']['className'], 'has-arrow' ) ) {
			$career_development_block_content = str_replace( '</a>', career_development_get_svg( array( 'icon' => esc_attr( 'caret-circle-right' ) ) ) . '</a>', $career_development_block_content );
			return $career_development_block_content;
		}
	}

	if( ! is_single() ) {
	
		if ( 'core/post-terms'  === $career_development_block['blockName'] ) {
			if( 'post_tag' === $career_development_block['attrs']['term'] ) {
				$career_development_block_content = str_replace( '<div class="taxonomy-post_tag wp-block-post-terms">', '<div class="taxonomy-post_tag wp-block-post-terms flex">' . career_development_get_svg( array( 'icon' => esc_attr( 'tags' ) ) ), $career_development_block_content );
			}

			if( 'category' ===  $career_development_block['attrs']['term'] ) {
				$career_development_block_content = str_replace( '<div class="taxonomy-category wp-block-post-terms">', '<div class="taxonomy-category wp-block-post-terms flex">' . career_development_get_svg( array( 'icon' => esc_attr( 'category' ) ) ), $career_development_block_content );
			}
			return $career_development_block_content;
		}
		if ( 'core/post-date' === $career_development_block['blockName'] ) {
			$career_development_block_content = str_replace( '<div class="wp-block-post-date">', '<div class="wp-block-post-date flex">' . career_development_get_svg( array( 'icon' => esc_attr( 'calendar' ) ) ), $career_development_block_content );
			return $career_development_block_content;
		}
		if ( 'core/post-author' === $career_development_block['blockName'] ) {
			$career_development_block_content = str_replace( '<div class="wp-block-post-author">', '<div class="wp-block-post-author flex">' . career_development_get_svg( array( 'icon' => esc_attr( 'user' ) ) ), $career_development_block_content );
			return $career_development_block_content;
		}
	}
	if( is_single() ){

		// Add chevron icon to the navigations
		if ( 'core/post-navigation-link' === $career_development_block['blockName'] ) {
			if( isset( $career_development_block['attrs']['type'] ) && 'previous' === $career_development_block['attrs']['type'] ) {
				$career_development_block_content = str_replace( '<span class="post-navigation-link__label">', '<span class="post-navigation-link__label">' . career_development_get_svg( array( 'icon' => esc_attr( 'prev' ) ) ), $career_development_block_content );
			}
			else {
				$career_development_block_content = str_replace( '<span class="post-navigation-link__label">Next Post', '<span class="post-navigation-link__label">Next Post' . career_development_get_svg( array( 'icon' => esc_attr( 'next' ) ) ), $career_development_block_content );
			}
			return $career_development_block_content;
		}
		if ( 'core/post-date' === $career_development_block['blockName'] ) {
            $career_development_block_content = str_replace( '<div class="wp-block-post-date">', '<div class="wp-block-post-date flex">' . career_development_get_svg( array( 'icon' => 'calendar' ) ), $career_development_block_content );
            return $career_development_block_content;
        }
		if ( 'core/post-author' === $career_development_block['blockName'] ) {
            $career_development_block_content = str_replace( '<div class="wp-block-post-author">', '<div class="wp-block-post-author flex">' . career_development_get_svg( array( 'icon' => 'user' ) ), $career_development_block_content );
            return $career_development_block_content;
        }

	}
    return $career_development_block_content;
}
	
add_filter( 'render_block', 'career_development_block_wrapper', 10, 2 );
