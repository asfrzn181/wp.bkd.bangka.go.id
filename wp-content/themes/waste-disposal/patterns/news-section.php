<?php
/**
 * News Section
 * 
 * slug: waste-disposal/news-section
 * title: News Section
 * categories: waste-disposal
 */

    return array(
        'title'      =>__( 'News Section', 'waste-disposal' ),
        'categories' => array( 'waste-disposal' ),
        'content'    => '<!-- wp:group {"className":"news-section","style":{"spacing":{"padding":{"top":"0","bottom":"0"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","contentSize":"100%"}} -->
        <div id="blog" class="wp-block-group news-section" style="padding-top:0;padding-bottom:0"><!-- wp:spacer {"height":"30px"} -->
        <div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
        <!-- /wp:spacer -->

        <!-- wp:paragraph {"align":"center","className":"testimonial-heading","style":{"typography":{"fontStyle":"normal","fontWeight":"600"},"elements":{"link":{"color":{"text":"var:preset|color|secaccent"}}},"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"border":{"radius":"5px"}},"textColor":"secaccent","fontSize":"medium","fontFamily":"playfair-display"} -->
        <p class="has-text-align-center testimonial-heading has-secaccent-color has-text-color has-link-color has-playfair-display-font-family has-medium-font-size" style="border-radius:5px;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--50);font-style:normal;font-weight:600">'. esc_html__('News & Blogs','waste-disposal').'</p>
        <!-- /wp:paragraph -->

        <!-- wp:heading {"textAlign":"center","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"27px"},"elements":{"link":{"color":{"text":"var:preset|color|accent"}}}},"textColor":"accent","fontFamily":"playfair-display"} -->
        <h2 class="wp-block-heading has-text-align-center has-accent-color has-text-color has-link-color has-playfair-display-font-family" style="font-size:27px;font-style:normal;font-weight:600">'. esc_html__('Our Latest News & blogs','waste-disposal').'</h2>
        <!-- /wp:heading -->

        <!-- wp:spacer {"height":"25px"} -->
        <div style="height:25px" aria-hidden="true" class="wp-block-spacer"></div>
        <!-- /wp:spacer -->

        <!-- wp:group {"layout":{"type":"constrained","contentSize":"100%"}} -->
        <div class="wp-block-group"><!-- wp:query {"queryId":15,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"false","inherit":false,"parents":[],"format":[]},"metadata":{"categories":["posts"],"patternName":"core/query-standard-posts","name":"Standard"},"layout":{"type":"default"}} -->
        <div class="wp-block-query"><!-- wp:post-template {"className":"news-post-template","style":{"border":{"width":"0px","style":"none"}},"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":null}} -->
        <!-- wp:group {"className":"news-image","layout":{"type":"constrained"}} -->
        <div class="wp-block-group news-image"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"auto","height":"300px","align":"wide"} /--></div>
        <!-- /wp:group -->

        <!-- wp:group {"className":"news-info","style":{"spacing":{"blockGap":"var:preset|spacing|30","margin":{"top":"0","bottom":"0"},"padding":{"right":"var:preset|spacing|30","left":"var:preset|spacing|30","top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
        <div class="wp-block-group news-info" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--30)"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
        <div class="wp-block-group"><!-- wp:post-author-name {"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"500"}},"textColor":"primary"} /-->

        <!-- wp:post-date {"format":"j/n/Y","isLink":true,"metadata":{"bindings":{"datetime":{"source":"core/post-data","args":{"field":"date"}}}},"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"500"}},"textColor":"primary"} /-->

        <!-- wp:comments {"style":{"spacing":{"padding":{"top":"0","bottom":"0"},"margin":{"top":"0","bottom":"0"}}}} -->
        <div class="wp-block-comments" style="margin-top:0;margin-bottom:0;padding-top:0;padding-bottom:0"><!-- wp:comments-title {"showPostTitle":false,"style":{"typography":{"fontStyle":"normal","fontWeight":"500"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary"} /--></div>
        <!-- /wp:comments --></div>
        <!-- /wp:group -->

        <!-- wp:post-title {"isLink":true,"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontStyle":"normal","fontWeight":"500"}},"textColor":"primary","fontFamily":"playfair-display"} /--></div>
        <!-- /wp:group -->
        <!-- /wp:post-template --></div>
        <!-- /wp:query --></div>
        <!-- /wp:group -->

        <!-- wp:spacer {"height":"102px"} -->
        <div style="height:102px" aria-hidden="true" class="wp-block-spacer"></div>
        <!-- /wp:spacer --></div>
        <!-- /wp:group -->',
    );