<?php
/**
 * Public View: [webinar_detail id="123"]
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$post_id = isset( $webinar_id ) ? $webinar_id : get_the_ID();
$post    = get_post( $post_id );

if ( ! $post || $post->post_type !== 'webinar' ) {
    echo '<div class="wbr-alert error">Webinar tidak ditemukan.</div>';
    return;
}

$meta = $wpdb->get_row( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}webinar_meta WHERE post_id = %d", $post->ID
) );

$start   = strtotime( $meta->start_datetime ?? '' );
$end     = strtotime( $meta->end_datetime ?? '' );
$now     = time();
$is_past = $end && $end < $now;
$is_live = $start && $end && $now >= $start && $now <= $end;

$thumb_url = get_the_post_thumbnail_url( $post->ID, 'large' );
?>
<div class="wbr-pub-detail">
    <?php if ( $thumb_url ) : ?>
    <div class="wbr-pub-detail-banner">
        <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $post->post_title ); ?>">
    </div>
    <?php endif; ?>

    <div class="wbr-pub-detail-header">
        <div class="wbr-pub-tags">
            <?php if ( $is_past ) : ?>
            <span class="wbr-tag past">Webinar Selesai</span>
            <?php elseif ( $is_live ) : ?>
            <span class="wbr-tag live">🔴 Sedang Berlangsung</span>
            <?php else : ?>
            <span class="wbr-tag upcoming">📅 Akan Datang</span>
            <?php endif; ?>
        </div>

        <h1 class="wbr-pub-detail-title"><?php echo esc_html( $post->post_title ); ?></h1>

        <div class="wbr-pub-meta-bar">
            <?php if ( $start ) : ?>
            <div class="wbr-meta-item">
                <span class="wbr-icon">📅</span>
                <div>
                    <strong>Tanggal &amp; Waktu</strong>
                    <p><?php echo esc_html( wp_date( 'l, d F Y', $start ) ); ?><br>
                       <?php echo esc_html( wp_date( 'H:i', $start ) ); ?> – <?php echo esc_html( wp_date( 'H:i', $end ) ); ?> WIB</p>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( $is_live || ! $is_past ) : ?>
            <?php if ( ! empty( $meta->zoom_link ) ) : ?>
            <div class="wbr-meta-item">
                <span class="wbr-icon">💻</span>
                <div>
                    <strong>Link Zoom Meeting</strong>
                    <p><a href="<?php echo esc_url( $meta->zoom_link ); ?>" target="_blank" rel="noopener" class="wbr-link-btn">Gabung Zoom Meeting</a></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $meta->youtube_link ) ) : ?>
            <div class="wbr-meta-item">
                <span class="wbr-icon">▶</span>
                <div>
                    <strong>Live Streaming</strong>
                    <p><a href="<?php echo esc_url( $meta->youtube_link ); ?>" target="_blank" rel="noopener" class="wbr-link-btn yt">Tonton di YouTube</a></p>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Deskripsi -->
    <div class="wbr-pub-detail-content">
        <?php echo apply_filters( 'the_content', $post->post_content ); ?>
    </div>

    <!-- Form Pendaftaran / Pesan Selesai -->
    <div class="wbr-pub-form-section" id="form-pendaftaran">
        <?php if ( $is_past ) : ?>
        <div class="wbr-pub-closed-box">
            <h3>⏳ Pendaftaran Ditutup</h3>
            <p>Webinar ini telah selesai dilaksanakan. Terima kasih atas partisipasi Anda.</p>
        </div>
        <?php elseif ( isset($meta->is_registration_open) && $meta->is_registration_open == 0 ) : ?>
        <div class="wbr-pub-closed-box" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 20px; text-align: center; border-radius: 8px; margin-top: 20px;">
            <h3 style="color: #fca5a5; margin-bottom: 8px;">🔒 Pendaftaran Ditutup</h3>
            <p style="color: #cbd5e1; font-size: 14px;">Pendaftaran untuk webinar ini telah ditutup oleh panitia.</p>
        </div>
        <?php else : ?>
        <h2 class="wbr-pub-section-title">📝 Form Pendaftaran Peserta</h2>
        <?php require WBR_PATH . 'public/views/registration-form.php'; ?>
        <?php endif; ?>
    </div>
</div>
