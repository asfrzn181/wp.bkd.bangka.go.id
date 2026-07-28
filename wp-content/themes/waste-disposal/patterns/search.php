<?php
/** 
 * Search Section
 *  
 * slug: waste-disposal/search-page
 * title: Search Page
 * categories: waste-disposal
 */

return array(
    'title'      =>__( 'Search Page', 'waste-disposal' ),
    'categories' => array( 'waste-disposal' ),
    'content'    => '<!-- wp:spacer {"height":"50px"} -->
    <div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
    <!-- /wp:spacer -->

    <!-- wp:group {"layout":{"type":"constrained","contentSize":"80%"}} -->
    <div class="wp-block-group"><!-- wp:columns -->
    <div class="wp-block-columns"><!-- wp:column {"width":""} -->
    <div class="wp-block-column"><!-- wp:query {"queryId":46,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true}} -->
    <div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
    <!-- wp:post-featured-image {"isLink":true} /-->

    <!-- wp:group {"style":{"color":{"background":"#f9f9f9"}},"layout":{"type":"constrained"}} -->
    <div class="wp-block-group has-background" style="background-color:#f9f9f9"><!-- wp:post-title {"level":3,"isLink":true,"fontSize":"content-heading"} /-->

    <!-- wp:post-excerpt {"moreText":"Read More","fontSize":"upper-heading"} /--></div>
    <!-- /wp:group -->
    <!-- /wp:post-template -->

    <!-- wp:query-pagination {"paginationArrow":"arrow","align":"wide","layout":{"type":"flex","justifyContent":"space-between"}} -->
    <!-- wp:query-pagination-previous {"fontSize":"small"} /-->

    <!-- wp:query-pagination-numbers /-->

    <!-- wp:query-pagination-next {"fontSize":"small"} /-->
    <!-- /wp:query-pagination -->

    <!-- wp:query-no-results -->
    <!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results.","style":{"typography":{"textAlign":"center","fontSize":"20px"},"spacing":{"margin":{"bottom":"var:preset|spacing|70"}}}} -->
    <p class="has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--70);font-size:20px">'. esc_html__('No Results Found','waste-disposal').'</p>
    <!-- /wp:paragraph -->
    <!-- /wp:query-no-results --></div>
    <!-- /wp:query -->

    <!-- wp:group {"style":{"typography":{"fontStyle":"normal","fontWeight":"700"},"elements":{"link":{"color":{"text":"var:preset|color|secondary"}}}},"textColor":"secondary","fontSize":"medium","layout":{"type":"constrained","contentSize":""}} -->
    <div class="wp-block-group has-secondary-color has-text-color has-link-color has-medium-font-size" style="font-style:normal;font-weight:700"><!-- wp:post-navigation-link {"type":"previous"} /-->

    <!-- wp:post-navigation-link /--></div>
    <!-- /wp:group --></div>
    <!-- /wp:column --></div>
    <!-- /wp:columns --></div>
    <!-- /wp:group -->',
);