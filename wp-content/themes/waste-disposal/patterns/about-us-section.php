<?php
/**
 * About Us Section
 * 
 * slug: waste-disposal/about-us-section
 * title: About Us Section
 * categories: waste-disposal
 */

    return array(
        'title'      =>__( 'About Us Section', 'waste-disposal' ),
        'categories' => array( 'waste-disposal' ),
        'content'    => '<!-- wp:group {"align":"full","className":"about-us-section","layout":{"type":"constrained","contentSize":"100%"},"anchor":"aboutus"} -->
         <div class="wp-block-group alignfull about-us-section" id="aboutus"><!-- wp:cover {"dimRatio":0,"isUserOverlayColor":true,"isDark":false,"sizeSlug":"large","align":"full","layout":{"type":"constrained","contentSize":"80%"}} -->
         <div class="wp-block-cover alignfull is-light"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:spacer {"height":"40px"} -->
         <div style="height:40px" aria-hidden="true" class="wp-block-spacer"></div>
         <!-- /wp:spacer -->

         <!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
         <div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"45%","className":"about-us-col01 wow zoomInLeft"} -->
         <div class="wp-block-column is-vertically-aligned-center about-us-col01 wow zoomInLeft" style="flex-basis:45%"><!-- wp:image {"id":1875,"sizeSlug":"full","linkDestination":"none"} -->
         <figure class="wp-block-image size-full"><img src="'.esc_url(get_template_directory_uri()) .'/assets/images/about-img.png" alt="" class="wp-image-1875"/></figure>
         <!-- /wp:image --></div>
         <!-- /wp:column -->

         <!-- wp:column {"verticalAlignment":"center","width":"55%","className":"about-us-col02 wow zoomInRight","style":{"spacing":{"blockGap":"var:preset|spacing|30"}}} -->
         <div class="wp-block-column is-vertically-aligned-center about-us-col02 wow zoomInRight" style="flex-basis:55%"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|accent"}}},"typography":{"letterSpacing":"1px","fontStyle":"normal","fontWeight":"700"}},"textColor":"accent","fontSize":"upper-heading","fontFamily":"source-code-pro"} -->
         <p class="has-accent-color has-text-color has-link-color has-source-code-pro-font-family has-upper-heading-font-size" style="font-style:normal;font-weight:700;letter-spacing:1px">'. esc_html__('About Us','waste-disposal').'</p>
         <!-- /wp:paragraph -->

         <!-- wp:heading {"className":"about-us-heading","style":{"typography":{"fontStyle":"normal","fontWeight":"700","fontSize":"29px","textTransform":"capitalize"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary","fontFamily":"source-code-pro"} -->
         <h2 class="wp-block-heading about-us-heading has-primary-color has-text-color has-link-color has-source-code-pro-font-family" style="font-size:29px;font-style:normal;font-weight:700;text-transform:capitalize">'. esc_html__('We want to give you the best services','waste-disposal').'</h2>
         <!-- /wp:heading -->

         <!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400","lineHeight":"1.6"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary","fontSize":"medium","fontFamily":"source-code-pro"} -->
         <p class="has-primary-color has-text-color has-link-color has-source-code-pro-font-family has-medium-font-size" style="font-style:normal;font-weight:400;line-height:1.6">'. esc_html__('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s.','waste-disposal').'</p>
         <!-- /wp:paragraph -->

         <!-- wp:columns {"className":"about-col02-list","style":{"spacing":{"margin":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|30"}}}} -->
         <div class="wp-block-columns about-col02-list" style="margin-top:var(--wp--preset--spacing--60);margin-bottom:var(--wp--preset--spacing--30)"><!-- wp:column {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}}} -->
         <div class="wp-block-column"><!-- wp:image {"id":8,"sizeSlug":"full","linkDestination":"none"} -->
         <figure class="wp-block-image size-full"><img src="'.esc_url(get_template_directory_uri()) .'/assets/images/about-icon01.png" alt="" class="wp-image-8"/></figure>
         <!-- /wp:image -->

         <!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"19px","textTransform":"capitalize"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"spacing":{"margin":{"top":"var:preset|spacing|40"}}},"textColor":"primary","fontFamily":"source-code-pro"} -->
         <h2 class="wp-block-heading has-primary-color has-text-color has-link-color has-source-code-pro-font-family" style="margin-top:var(--wp--preset--spacing--40);font-size:19px;font-style:normal;font-weight:600;text-transform:capitalize">'. esc_html__('Guaranteed Results','waste-disposal').'</h2>
         <!-- /wp:heading -->

         <!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400","lineHeight":"1.5"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary","fontSize":"extra-small","fontFamily":"inter"} -->
         <p class="has-primary-color has-text-color has-link-color has-inter-font-family has-extra-small-font-size" style="font-style:normal;font-weight:400;line-height:1.5">'. esc_html__('Lorem Ipsum is simply dummy text of the printing and typesetting industry.','waste-disposal').'</p>
         <!-- /wp:paragraph --></div>
         <!-- /wp:column -->

         <!-- wp:column {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}}} -->
         <div class="wp-block-column"><!-- wp:image {"id":7,"sizeSlug":"full","linkDestination":"none"} -->
         <figure class="wp-block-image size-full"><img src="'.esc_url(get_template_directory_uri()) .'/assets/images/about-icon02.png" alt="" class="wp-image-7"/></figure>
         <!-- /wp:image -->

         <!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"19px","textTransform":"capitalize"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"spacing":{"margin":{"top":"var:preset|spacing|40"}}},"textColor":"primary","fontFamily":"source-code-pro"} -->
         <h2 class="wp-block-heading has-primary-color has-text-color has-link-color has-source-code-pro-font-family" style="margin-top:var(--wp--preset--spacing--40);font-size:19px;font-style:normal;font-weight:600;text-transform:capitalize">'. esc_html__('Quality Services','waste-disposal').'</h2>
         <!-- /wp:heading -->

         <!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"400","lineHeight":"1.5"},"elements":{"link":{"color":{"text":"var:preset|color|primary"}}}},"textColor":"primary","fontSize":"extra-small","fontFamily":"rubik"} -->
         <p class="has-primary-color has-text-color has-link-color has-rubik-font-family has-extra-small-font-size" style="font-style:normal;font-weight:400;line-height:1.5">'. esc_html__('Lorem Ipsum is simply dummy text of the printing and typesetting industry.','waste-disposal').'</p>
         <!-- /wp:paragraph --></div>
         <!-- /wp:column --></div>
         <!-- /wp:columns --></div>
         <!-- /wp:column --></div>
         <!-- /wp:columns -->

         <!-- wp:spacer {"height":"40px"} -->
         <div style="height:40px" aria-hidden="true" class="wp-block-spacer"></div>
         <!-- /wp:spacer --></div></div>
         <!-- /wp:cover --></div>
         <!-- /wp:group -->',
    );