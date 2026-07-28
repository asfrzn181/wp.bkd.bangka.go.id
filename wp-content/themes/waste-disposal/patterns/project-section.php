<?php
/**
 * Project Section
 * 
 * slug: waste-disposal/project-section
 * title: Project Section
 * categories: waste-disposal
 */

return array(
    'title'      =>__( 'Project Section', 'waste-disposal' ),
    'categories' => array( 'waste-disposal' ),
    'content'    => '<!-- wp:group {"align":"full","className":"project-section","style":{"spacing":{"margin":{"top":"0","bottom":"0"},"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained","contentSize":"80%"}} -->
    <div class="wp-block-group alignfull project-section" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:group {"className":"project-head-box ","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"80%"}} -->
    <div class="wp-block-group project-head-box" style="margin-bottom:var(--wp--preset--spacing--60)"><!-- wp:heading {"level":3,"className":"project-sec-title wow fadeInUp","style":{"typography":{"textTransform":"capitalize","fontSize":"25px","fontStyle":"normal","fontWeight":"700","textAlign":"center","textDecoration":"none"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary","fontFamily":"source-code-pro"} -->
    <h3 class="wp-block-heading has-text-align-center project-sec-title wow fadeInUp has-primary-color has-text-color has-link-color has-source-code-pro-font-family" style="font-size:25px;font-style:normal;font-weight:700;text-decoration:none;text-transform:capitalize">'. esc_html__('See Our Project','waste-disposal').'</h3>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"className":"best-study-heading wow fadeInDown","style":{"typography":{"textAlign":"center","fontSize":"16px"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"0"}}},"textColor":"primary","fontFamily":"inter"} -->
    <p class="has-text-align-center best-study-heading wow fadeInDown has-primary-color has-text-color has-link-color has-inter-font-family" style="margin-top:var(--wp--preset--spacing--20);margin-bottom:0;font-size:16px">'. esc_html__('Lorem Ipsum is simply dummy text of the printing and typesetting industry.','waste-disposal').'</p>
    <!-- /wp:paragraph --></div>
    <!-- /wp:group -->

    <!-- wp:query {"queryId":17,"query":{"perPage":5,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"metadata":{"categories":["posts"],"patternName":"core/query-standard-posts","name":"Standard"},"className":"project-boxes wow fadeInUp"} -->
    <div class="wp-block-query project-boxes wow fadeInUp"><!-- wp:post-template {"className":"project-in-box owl-carousel","layout":{"type":"grid","columnCount":3}} -->
    <!-- wp:group {"align":"wide","className":"project-box","style":{"spacing":{"padding":{"top":"12px","bottom":"12px","left":"12px","right":"12px"}}},"layout":{"type":"default"}} -->
    <div class="wp-block-group alignwide project-box" style="padding-top:12px;padding-right:12px;padding-bottom:12px;padding-left:12px"><!-- wp:group {"align":"wide","className":"project-img-box","style":{"color":{"background":"#f6f6f6"},"dimensions":{"minHeight":"350px"},"spacing":{"padding":{"top":"0px","bottom":"0px","left":"0px","right":"0px"}}},"layout":{"type":"default"}} -->
    <div class="wp-block-group alignwide project-img-box has-background" style="background-color:#f6f6f6;min-height:350px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px"><!-- wp:post-featured-image {"isLink":true,"height":"350px","align":"wide","className":"project-img"} /--></div>
    <!-- /wp:group -->

    <!-- wp:group {"className":"project-content","style":{"spacing":{"padding":{"top":"0px","bottom":"0px"}}},"layout":{"type":"default"}} -->
    <div class="wp-block-group project-content" style="padding-top:0px;padding-bottom:0px"><!-- wp:group {"className":"project-content-box","style":{"spacing":{"padding":{"top":"16px","bottom":"16px","left":"20px","right":"20px"}}},"backgroundColor":"background","layout":{"type":"default"}} -->
    <div class="wp-block-group project-content-box has-background-background-color has-background" style="padding-top:16px;padding-right:20px;padding-bottom:16px;padding-left:20px"><!-- wp:post-title {"textAlign":"center","level":4,"isLink":true,"className":"project-title","style":{"typography":{"fontSize":"20px","fontStyle":"normal","fontWeight":"600"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary"} /-->

    <!-- wp:post-excerpt {"textAlign":"center","moreText":"Learn More","excerptLength":10,"className":"project-desc","style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontSize":"15px","fontStyle":"normal","fontWeight":"300"},"spacing":{"margin":{"top":"15px","bottom":"15px"}}},"textColor":"primary","fontFamily":"inter"} /--></div>
    <!-- /wp:group --></div>
    <!-- /wp:group --></div>
    <!-- /wp:group -->
    <!-- /wp:post-template --></div>
    <!-- /wp:query --></div>
    <!-- /wp:group -->',
);