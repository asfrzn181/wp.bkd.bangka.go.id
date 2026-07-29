<?php
/**
 * Public View: [webinar_list]
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$limit = isset( $atts['limit'] ) ? absint( $atts['limit'] ) : 10;

$webinars = $wpdb->get_results( $wpdb->prepare(
    "SELECT p.ID, p.post_title, p.post_excerpt, m.start_datetime, m.end_datetime
     FROM {$wpdb->posts} p
     JOIN {$wpdb->prefix}webinar_meta m ON m.post_id = p.ID
     WHERE p.post_type = 'webinar' AND p.post_status = 'publish'
     ORDER BY m.start_datetime DESC LIMIT %d",
    $limit
) );

if ( empty( $webinars ) ) {
    echo '<div class="wbr-pub-empty">Belum ada webinar yang dijadwalkan.</div>';
    return;
}
?>
<div class="wbr-pub-container">
    <div class="wbr-pub-grid">
        <?php foreach ( $webinars as $w ) :
            $start     = strtotime( $w->start_datetime );
            $end       = strtotime( $w->end_datetime );
            $now       = time();
            $is_past   = $end < $now;
            $is_live   = $now >= $start && $now <= $end;
            $thumb_url = get_the_post_thumbnail_url( $w->ID, 'medium' );
            $link      = get_permalink( $w->ID );
        ?>
        <div class="wbr-pub-card <?php echo $is_past ? 'is-past' : ( $is_live ? 'is-live' : '' ); ?>">
            <?php if ( $thumb_url ) : ?>
            <div class="wbr-pub-thumb">
                <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $w->post_title ); ?>">
                <?php if ( $is_live ) : ?><span class="wbr-tag live">🔴 BERLANGSUNG</span><?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="wbr-pub-card-body">
                <div class="wbr-pub-status">
                    <?php if ( $is_past ) : ?>
                    <span class="wbr-tag past">Selesai</span>
                    <?php elseif ( $is_live ) : ?>
                    <span class="wbr-tag live">Sedang Berlangsung</span>
                    <?php else : ?>
                    <span class="wbr-tag upcoming">Akan Datang</span>
                    <?php endif; ?>
                    <span class="wbr-pub-date">📅 <?php echo esc_html( wp_date( 'd M Y, H:i', $start ) ); ?> WIB</span>
                </div>
                <h3 class="wbr-pub-title">
                    <a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $w->post_title ); ?></a>
                </h3>
                <?php if ( $w->post_excerpt ) : ?>
                <p class="wbr-pub-excerpt"><?php echo esc_html( wp_trim_words( $w->post_excerpt, 20 ) ); ?></p>
                <?php endif; ?>
                <div class="wbr-pub-card-footer">
                    <a href="<?php echo esc_url( $link ); ?>" class="wbr-pub-btn">
                        <?php echo $is_past ? 'Lihat Detail' : 'Daftar Sekarang →'; ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
