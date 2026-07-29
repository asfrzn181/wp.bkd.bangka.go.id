<?php
/**
 * Shortcode [bkpsdmd_banner_carousel]
 * Render HTML carousel dari CPT banner_slide
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BKBC_Shortcode {

    public static function render( $atts = [] ) {
        $s = BKBC_Settings::get();

        // Query: hanya slide aktif, diurutkan menu_order
        $slides = get_posts( [
            'post_type'      => 'banner_slide',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'meta_query'     => [
                [
                    'key'     => '_bkbc_active',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
        ] );

        // Fallback: tidak ada slide aktif
        if ( empty( $slides ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<div style="padding:40px;text-align:center;background:#f0f0f0;color:#555;">
                    <strong>⚠ Banner Carousel:</strong> Belum ada slide aktif.
                    <a href="' . esc_url( admin_url( 'admin.php?page=bkbc-carousel' ) ) . '" style="margin-left:10px;">Kelola Slide →</a>
                </div>';
            }
            return '';
        }

        $animation       = esc_attr( $s['animation'] );
        $duration        = (int) $s['duration'];
        $autoplay        = $s['autoplay'] === '1' ? 'true' : 'false';
        $interval        = (int) $s['interval'] * 1000;
        $show_dots       = $s['show_dots'] === '1';
        $show_arrows     = $s['show_arrows'] === '1';
        $overlay_opacity = (int) $s['overlay_opacity'];

        ob_start();
        ?>
        <section class="bkbc-carousel"
                 data-animation="<?php echo $animation; ?>"
                 data-duration="<?php echo $duration; ?>"
                 data-autoplay="<?php echo $autoplay; ?>"
                 data-interval="<?php echo $interval; ?>"
                 aria-label="Banner Carousel"
                 role="region">

            <div class="bkbc-track">
                <?php foreach ( $slides as $i => $slide ) :
                    $img_id  = get_post_thumbnail_id( $slide->ID );
                    $img_url = $img_id
                        ? wp_get_attachment_image_url( $img_id, 'full' )
                        : BKBC_URL . 'assets/img/placeholder.jpg';
                    $img_srcset = $img_id ? wp_get_attachment_image_srcset( $img_id, 'full' ) : '';
                    $img_sizes  = '100vw';

                    $subtitle    = get_post_meta( $slide->ID, '_bkbc_subtitle',    true );
                    $description = get_post_meta( $slide->ID, '_bkbc_description', true );
                    $cta_text    = get_post_meta( $slide->ID, '_bkbc_cta_text',    true );
                    $cta_url     = get_post_meta( $slide->ID, '_bkbc_cta_url',     true );
                ?>
                <div class="bkbc-slide <?php echo $i === 0 ? 'active' : ''; ?>"
                     aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>"
                     data-index="<?php echo $i; ?>">

                    <!-- Background image dengan lazy-load -->
                    <div class="bkbc-slide-bg"
                         role="img"
                         aria-label="<?php echo esc_attr( get_the_title( $slide->ID ) ); ?>">
                        <img class="bkbc-slide-img"
                             src="<?php echo esc_url( $img_url ); ?>"
                             <?php if ( $img_srcset ) : ?>
                             srcset="<?php echo esc_attr( $img_srcset ); ?>"
                             sizes="<?php echo esc_attr( $img_sizes ); ?>"
                             <?php endif; ?>
                             alt="<?php echo esc_attr( get_the_title( $slide->ID ) ); ?>"
                             loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>"
                             decoding="async">
                        <!-- Overlay gelap -->
                        <div class="bkbc-slide-overlay"
                             style="background:rgba(0,0,0,<?php echo $overlay_opacity / 100; ?>);"></div>
                    </div>

                    <!-- Konten teks -->
                    <div class="bkbc-slide-content">
                        <?php if ( $subtitle ) : ?>
                        <p class="bkbc-slide-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>

                        <h2 class="bkbc-slide-title">
                            <?php echo esc_html( get_the_title( $slide->ID ) ); ?>
                        </h2>

                        <?php if ( $description ) : ?>
                        <p class="bkbc-slide-desc">
                            <?php echo esc_html( $description ); ?>
                        </p>
                        <?php endif; ?>

                        <?php if ( $cta_text && $cta_url ) : ?>
                        <a href="<?php echo esc_url( $cta_url ); ?>"
                           class="bkbc-slide-cta">
                            <?php echo esc_html( $cta_text ); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                 viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                    </div>

                </div><!-- /bkbc-slide -->
                <?php endforeach; ?>
            </div><!-- /bkbc-track -->

            <?php if ( $show_arrows && count( $slides ) > 1 ) : ?>
            <!-- Navigasi panah -->
            <button class="bkbc-arrow bkbc-prev" aria-label="Slide sebelumnya" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                     viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </button>
            <button class="bkbc-arrow bkbc-next" aria-label="Slide berikutnya" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                     viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </button>
            <?php endif; ?>

            <?php if ( $show_dots && count( $slides ) > 1 ) : ?>
            <!-- Navigation dots -->
            <div class="bkbc-dots" role="tablist" aria-label="Pilih slide">
                <?php foreach ( $slides as $i => $slide ) : ?>
                <button class="bkbc-dot <?php echo $i === 0 ? 'active' : ''; ?>"
                        role="tab"
                        aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                        aria-label="Slide <?php echo $i + 1; ?>"
                        data-index="<?php echo $i; ?>"
                        type="button"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Progress bar -->
            <?php if ( $s['autoplay'] === '1' ) : ?>
            <div class="bkbc-progress" aria-hidden="true">
                <div class="bkbc-progress-bar"></div>
            </div>
            <?php endif; ?>

        </section>
        <?php
        return ob_get_clean();
    }
}
