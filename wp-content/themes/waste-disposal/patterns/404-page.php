<?php
/**
 * 404 Section
 * 
 * slug: waste-disposal/404-page
 * title: 404 Page
 * categories: waste-disposal
 */

return array(
    'title'      =>__( '404 Page', 'waste-disposal' ),
    'categories' => array( 'waste-disposal' ),
    'content'    => '<!-- wp:heading {"textAlign":"center","className":"not-found-heading","style":{"typography":{"fontSize":"200px"},"elements":{"link":{"color":{"text":"var:preset|color|accent"}}}},"textColor":"accent"} -->
    <h2 class="wp-block-heading has-text-align-center not-found-heading has-accent-color has-text-color has-link-color" style="font-size:200px">'. esc_html__('404','waste-disposal') .'</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"align":"center","fontSize":"content-heading"} -->
    <p class="has-text-align-center has-content-heading-font-size"><strong>'. esc_html__('Unfortunately we can’t find that page.','waste-disposal') .'</strong></p>
    <!-- /wp:paragraph -->

    <!-- wp:paragraph {"align":"center"} -->
    <p class="has-text-align-center">'. esc_html__('The page you are looking for doesnt exist or has been moved. Try another url or go to the site homepage.','waste-disposal') .'</p>
    <!-- /wp:paragraph -->

    <!-- wp:search {"label":"Search","showLabel":false,"width":75,"widthUnit":"%","buttonText":"Search","align":"center","style":{"color":{"background":"var(--wp--preset--color--accent)"}}} /-->',
);