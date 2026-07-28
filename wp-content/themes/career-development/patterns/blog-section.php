<?php
 /**
  * Title: Blog Section
  * Slug: career-development/blog-section
  */
?>
<!-- wp:group {"align":"full","className":"blog-section","style":{"spacing":{"padding":{"top":"0","bottom":"var:preset|spacing|80","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained","contentSize":"85%"}} -->
<div class="wp-block-group alignfull blog-section" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:var(--wp--preset--spacing--80);padding-left:0"><!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontSize":"18px","fontStyle":"normal","fontWeight":"500"},"spacing":{"margin":{"top":"0","bottom":"0","left":"0","right":"0"}}},"textColor":"primary","fontFamily":"plus-jakarta-sans"} -->
<p class="has-text-align-center has-primary-color has-text-color has-link-color has-plus-jakarta-sans-font-family" style="margin-top:0;margin-right:0;margin-bottom:0;margin-left:0;font-size:18px;font-style:normal;font-weight:500"><?php esc_html_e( 'Our Blogs', 'career-development' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":3,"style":{"elements":{"link":{"color":{"text":"var:preset|color|heading"}}},"typography":{"fontSize":"28px","fontStyle":"normal","fontWeight":"700"},"spacing":{"margin":{"bottom":"var:preset|spacing|70"}}},"textColor":"heading","fontFamily":"plus-jakarta-sans"} -->
<h3 class="wp-block-heading has-text-align-center has-heading-color has-text-color has-link-color has-plus-jakarta-sans-font-family" style="margin-bottom:var(--wp--preset--spacing--70);font-size:28px;font-style:normal;font-weight:700"><?php esc_html_e( 'Latest News', 'career-development' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":11,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"metadata":{"categories":["posts"],"patternName":"core/query-grid-posts","name":"Grid"}} -->
<div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"style":{"spacing":{"padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px"}},"border":{"width":"0px","style":"none"}},"layout":{"inherit":false}} -->
<div class="wp-block-group" style="border-style:none;border-width:0px;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px"><!-- wp:group {"className":"blog-img","layout":{"type":"constrained"}} -->
<div class="wp-block-group blog-img"><!-- wp:post-featured-image /--></div>
<!-- /wp:group -->

<!-- wp:post-title {"level":4,"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|heading"}}},"typography":{"fontSize":"20px","fontStyle":"normal","fontWeight":"600"},"spacing":{"margin":{"top":"var:preset|spacing|30"}}},"textColor":"heading","fontFamily":"plus-jakarta-sans"} /-->

<!-- wp:post-date {"metadata":{"bindings":{"datetime":{"source":"core/post-data","args":{"field":"date"}}}},"style":{"elements":{"link":{"color":{"text":"var:preset|color|Text Color"}}},"typography":{"fontSize":"16px","textTransform":"capitalize","fontStyle":"normal","fontWeight":"500"},"spacing":{"margin":{"top":"var:preset|spacing|30"}}},"textColor":"Text Color","fontFamily":"plus-jakarta-sans"} /-->

<!-- wp:post-excerpt {"moreText":"Read More","style":{"elements":{"link":{"color":{"text":"var:preset|color|Text Color"}}},"typography":{"fontSize":"16px"},"spacing":{"margin":{"top":"var:preset|spacing|30"}}},"textColor":"Text Color","fontFamily":"plus-jakarta-sans"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->