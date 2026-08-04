<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BKBC_Render {

    public static function init() {
        add_shortcode( 'bkpsdmd_banner_carousel', array( __CLASS__, 'render_shortcode' ) );
    }

    public static function parse_word_cloud_data( $raw ) {
        if ( empty( trim( $raw ) ) ) {
            return array();
        }
        $lines = explode( "\n", str_replace( "\r", "", $raw ) );
        $tags = array();
        foreach ( $lines as $line ) {
            $parts = explode( '|', $line );
            $text = trim( $parts[0] ?? '' );
            if ( ! empty( $text ) ) {
                $url  = trim( $parts[1] ?? '#' );
                $size = trim( strtolower( $parts[2] ?? 'md' ) );
                if ( ! in_array( $size, array( 'sm', 'md', 'lg', 'xl' ), true ) ) {
                    $size = 'md';
                }
                $tags[] = array(
                    'text' => $text,
                    'url'  => $url,
                    'size' => $size,
                );
            }
        }
        return $tags;
    }

    public static function parse_buttons_data( $raw ) {
        if ( empty( trim( $raw ) ) ) {
            return array();
        }
        $lines = explode( "\n", str_replace( "\r", "", $raw ) );
        $buttons = array();
        foreach ( $lines as $line ) {
            $parts = explode( '|', $line );
            $text = trim( $parts[0] ?? '' );
            if ( ! empty( $text ) ) {
                $url  = trim( $parts[1] ?? '#' );
                $type = trim( strtolower( $parts[2] ?? 'primary' ) );
                if ( ! in_array( $type, array( 'primary', 'secondary', 'outline' ), true ) ) {
                    $type = 'secondary';
                }
                $buttons[] = array(
                    'text' => $text,
                    'url'  => $url,
                    'type' => $type,
                );
            }
        }
        return $buttons;
    }

    public static function parse_cards_data( $raw ) {
        if ( empty( trim( $raw ) ) ) {
            return array();
        }
        $lines = explode( "\n", str_replace( "\r", "", $raw ) );
        $cards = array();
        foreach ( $lines as $line ) {
            $parts = explode( '|', $line );
            $icon = trim( $parts[0] ?? '✨' );
            $val  = trim( $parts[1] ?? '' );
            $lbl  = trim( $parts[2] ?? '' );
            if ( ! empty( $val ) || ! empty( $lbl ) ) {
                $cards[] = array(
                    'icon' => $icon,
                    'val'  => $val,
                    'lbl'  => $lbl,
                );
            }
        }
        return $cards;
    }

    public static function render_shortcode( $atts ) {
        $query = new WP_Query( array(
            'post_type'      => 'bkpsdmd_banner',
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
        ) );

        $slides = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $id = get_the_ID();

                $bg_url = get_post_meta( $id, '_bkbc_bg_url', true );
                if ( empty( $bg_url ) && has_post_thumbnail( $id ) ) {
                    $thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'full' );
                    if ( $thumb ) {
                        $bg_url = $thumb[0];
                    }
                }
                if ( empty( $bg_url ) ) {
                    $bg_url = 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1920&q=80';
                }

                $bg_type    = get_post_meta( $id, '_bkbc_bg_type', true ) ?: 'both';
                $wc_raw     = get_post_meta( $id, '_bkbc_word_cloud_data', true );
                $wc_tags    = self::parse_word_cloud_data( $wc_raw );

                $btn_raw    = get_post_meta( $id, '_bkbc_buttons_list', true );
                $buttons    = self::parse_buttons_data( $btn_raw );
                if ( empty( $buttons ) ) {
                    $b1_txt = get_post_meta( $id, '_bkbc_btn1_text', true );
                    $b1_url = get_post_meta( $id, '_bkbc_btn1_url', true );
                    $b2_txt = get_post_meta( $id, '_bkbc_btn2_text', true );
                    $b2_url = get_post_meta( $id, '_bkbc_btn2_url', true );
                    if ( $b1_txt ) $buttons[] = array( 'text' => $b1_txt, 'url' => $b1_url ?: '#', 'type' => 'primary' );
                    if ( $b2_txt ) $buttons[] = array( 'text' => $b2_txt, 'url' => $b2_url ?: '#', 'type' => 'secondary' );
                }

                $card_raw   = get_post_meta( $id, '_bkbc_cards_list', true );
                $cards      = self::parse_cards_data( $card_raw );
                if ( empty( $cards ) ) {
                    $c1_icon = get_post_meta( $id, '_bkbc_card1_icon', true ) ?: '⚡';
                    $c1_val  = get_post_meta( $id, '_bkbc_card1_val', true ) ?: '98.5%';
                    $c1_lbl  = get_post_meta( $id, '_bkbc_card1_lbl', true ) ?: 'Indeks Kepuasan Masyarakat';
                    $c2_icon = get_post_meta( $id, '_bkbc_card2_icon', true ) ?: '🏆';
                    $c2_val  = get_post_meta( $id, '_bkbc_card2_val', true ) ?: 'Prima';
                    $c2_lbl  = get_post_meta( $id, '_bkbc_card2_lbl', true ) ?: 'Pelayanan Publik Terpadu';
                    $cards[] = array( 'icon' => $c1_icon, 'val' => $c1_val, 'lbl' => $c1_lbl );
                    $cards[] = array( 'icon' => $c2_icon, 'val' => $c2_val, 'lbl' => $c2_lbl );
                }

                $slides[] = array(
                    'bg_type'    => $bg_type,
                    'wc_tags'    => $wc_tags,
                    'sub_badge'  => get_post_meta( $id, '_bkbc_sub_badge', true ) ?: 'BKPSDMD KABUPATEN BANGKA',
                    'title'      => get_the_title(),
                    'desc'       => get_the_content(),
                    'buttons'    => $buttons,
                    'cards'      => $cards,
                    'bg_url'     => $bg_url,
                );
            }
            wp_reset_postdata();
        }

        // Fallback default slides jika belum ada data di WP Admin
        if ( empty( $slides ) ) {
            $default_wc = self::parse_word_cloud_data( "BerAKHLAK | /layanan-asn/ | xl\nBKPSDMD Bangka | / | xl\nSurvei SKM | /survei-skm/ | lg\nDiklat ASN | /webinar/ | lg\nPengembangan Karir | /pengembangan-sdm/ | md\nPelayanan Prima | /layanan-asn/ | lg\nNetralitas ASN | /berita/ | sm\nSistem Informasi | /dashboard-asn/ | md\nKenaikan Pangkat | /layanan-asn/ | sm\nLayanan Pensiun | /layanan-asn/ | sm\nInovasi Digital | /dashboard-asn/ | md\nBangka Setara | / | sm" );

            $slides = array(
                array(
                    'bg_type'    => 'both',
                    'wc_tags'    => $default_wc,
                    'sub_badge'  => 'BKPSDMD KABUPATEN BANGKA',
                    'title'      => 'Pelayanan Kepegawaian <br><span class="gradient-text">Digital & Profesional</span>',
                    'desc'       => 'Mewujudkan ASN Kabupaten Bangka yang BerAKHLAK, Terintegrasi, dan Berdaya Saing Tinggi melalui Pelayanan Publik Prima.',
                    'btn1_text'  => 'Isi Survei SKM',
                    'btn1_url'   => '/survei-skm/',
                    'btn2_text'  => 'Layanan ASN',
                    'btn2_url'   => '/layanan-asn/',
                    'card1_icon' => '⚡',
                    'card1_val'  => '98.5%',
                    'card1_lbl'  => 'Indeks Kepuasan Masyarakat',
                    'card2_icon' => '🏆',
                    'card2_val'  => 'Prima',
                    'card2_lbl'  => 'Pelayanan Publik Terpadu',
                    'bg_url'     => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1920&q=80',
                ),
                array(
                    'bg_type'    => 'both',
                    'wc_tags'    => $default_wc,
                    'sub_badge'  => 'PENGEMBANGAN SDM',
                    'title'      => 'Program Pelatihan & <br><span class="gradient-text">Pengembangan Karir</span>',
                    'desc'       => 'Tingkatkan potensi, kualifikasi, dan kompetensi ASN melalui pendidikan dan pelatihan terstruktur berkelanjutan.',
                    'btn1_text'  => 'Info Diklat & Webinar',
                    'btn1_url'   => '/webinar/',
                    'btn2_text'  => 'Pelajari Selengkapnya',
                    'btn2_url'   => '/pengembangan-sdm/',
                    'card1_icon' => '🎓',
                    'card1_val'  => '100+',
                    'card1_lbl'  => 'Program Diklat & Webinar',
                    'card2_icon' => '⭐',
                    'card2_val'  => 'Unggul',
                    'card2_lbl'  => 'Sertifikasi Kompetensi',
                    'bg_url'     => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1920&q=80',
                ),
            );
        }

        ob_start();
        ?>
        <section class="bkpsdmd-hero-parallax-carousel" id="bkpsdmdHeroCarousel">
            <div class="parallax-carousel-slides">
                <?php foreach ( $slides as $i => $slide ) : ?>
                    <div class="parallax-slide <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo $i; ?>">
                        <?php if ( in_array( $slide['bg_type'], array( 'image', 'both' ), true ) ) : ?>
                            <div class="parallax-bg-layer" data-depth="0.15" style="background-image: url('<?php echo esc_url( $slide['bg_url'] ); ?>');"></div>
                        <?php endif; ?>

                        <div class="parallax-overlay-glow" data-depth="0.25"></div>

                        <!-- Word Cloud Background Layer -->
                        <?php if ( in_array( $slide['bg_type'], array( 'wordcloud', 'both' ), true ) && ! empty( $slide['wc_tags'] ) ) : ?>
                            <div class="parallax-word-cloud-layer" data-depth="0.35">
                                <div class="word-cloud-container">
                                    <?php foreach ( $slide['wc_tags'] as $idx => $tag ) : ?>
                                        <a href="<?php echo esc_url( home_url( $tag['url'] ) ); ?>" class="wc-tag wc-sz-<?php echo esc_attr( $tag['size'] ); ?> wc-pos-<?php echo ($idx % 12) + 1; ?>">
                                            <?php echo esc_html( $tag['text'] ); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="parallax-shapes-layer" data-depth="0.45">
                            <div class="floating-orb orb-1"></div>
                            <div class="floating-orb orb-2"></div>
                        </div>
                        <div class="parallax-content-container">
                            <div class="parallax-content-left" data-depth="0.65">
                                <div class="hero-badge">
                                    <span class="pulse-dot"></span>
                                    <?php echo esc_html( $slide['sub_badge'] ); ?>
                                </div>
                                <h1 class="hero-title"><?php echo wp_kses_post( $slide['title'] ); ?></h1>
                                <div class="hero-desc"><?php echo wp_kses_post( wpautop( $slide['desc'] ) ); ?></div>
                                <div class="hero-btn-group">
                                    <?php if ( ! empty( $slide['buttons'] ) ) :
                                        foreach ( $slide['buttons'] as $b_idx => $btn ) :
                                            $btn_class = ($btn['type'] === 'primary') ? 'primary-btn' : (($btn['type'] === 'outline') ? 'outline-btn' : 'secondary-btn');
                                    ?>
                                        <a href="<?php echo esc_url( home_url( $btn['url'] ) ); ?>" class="hero-btn <?php echo esc_attr( $btn_class ); ?>">
                                            <span><?php echo esc_html( $btn['text'] ); ?></span>
                                            <?php if ( $btn['type'] === 'primary' ) : ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach;
                                    endif; ?>
                                </div>
                            </div>
                            <div class="parallax-content-right" data-depth="0.95">
                                <?php if ( ! empty( $slide['cards'] ) ) :
                                    foreach ( $slide['cards'] as $c_idx => $card ) :
                                        $sec_class = ($c_idx % 2 !== 0) ? 'card-secondary' : '';
                                ?>
                                    <div class="glass-hero-card <?php echo esc_attr( $sec_class ); ?>">
                                        <div class="card-icon"><?php echo esc_html( $card['icon'] ); ?></div>
                                        <div class="card-info">
                                            <div class="card-val"><?php echo esc_html( $card['val'] ); ?></div>
                                            <div class="card-lbl"><?php echo esc_html( $card['lbl'] ); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Controls & Navigation -->
            <div class="carousel-nav-wrap">
                <button class="nav-btn prev-btn" aria-label="Slide Sebelumnya">&larr;</button>
                <div class="carousel-indicators">
                    <?php foreach ( $slides as $i => $slide ) : ?>
                        <span class="indicator <?php echo $i === 0 ? 'active' : ''; ?>" data-goto="<?php echo $i; ?>"><span class="progress-bar"></span></span>
                    <?php endforeach; ?>
                </div>
                <button class="nav-btn next-btn" aria-label="Slide Selanjutnya">&rarr;</button>
                <div class="slide-counter">
                    <span class="current-slide">01</span> / <span class="total-slides"><?php echo sprintf( '%02d', count( $slides ) ); ?></span>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}
