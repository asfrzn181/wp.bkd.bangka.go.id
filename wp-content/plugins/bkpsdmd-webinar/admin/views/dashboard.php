<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) exit;
WBR_Roles::require_cap( 'manage_webinars' );

global $wpdb;
$total_webinar = wp_count_posts( 'webinar' )->publish ?? 0;
$total_reg     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}webinar_registrant" );
$total_att     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}webinar_attendance" );
$total_cert    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}webinar_certificate WHERE status='active'" );

// Webinar mendatang
$upcoming = $wpdb->get_results(
    "SELECT p.ID, p.post_title, m.start_datetime, m.end_datetime
     FROM {$wpdb->posts} p
     JOIN {$wpdb->prefix}webinar_meta m ON m.post_id = p.ID
     WHERE p.post_type='webinar' AND p.post_status='publish'
       AND m.start_datetime > NOW()
     ORDER BY m.start_datetime ASC LIMIT 5"
);

// Webinar selesai
$past = $wpdb->get_results(
    "SELECT p.ID, p.post_title, m.start_datetime, m.end_datetime,
            (SELECT COUNT(*) FROM {$wpdb->prefix}webinar_registrant r WHERE r.webinar_id = p.ID) AS reg_count,
            (SELECT COUNT(*) FROM {$wpdb->prefix}webinar_attendance a WHERE a.webinar_id = p.ID) AS att_count,
            (SELECT status FROM {$wpdb->prefix}webinar_sk sk WHERE sk.webinar_id = p.ID LIMIT 1) AS sk_status
     FROM {$wpdb->posts} p
     JOIN {$wpdb->prefix}webinar_meta m ON m.post_id = p.ID
     WHERE p.post_type='webinar' AND p.post_status='publish'
       AND m.end_datetime <= NOW()
     ORDER BY m.end_datetime DESC LIMIT 10"
);
?>
<div class="wrap wbr-admin-wrap">
    <!-- Header -->
    <div class="wbr-admin-header">
        <div class="wbr-header-left">
            <div class="wbr-logo">🎓</div>
            <div>
                <h1 class="wbr-title">Manajemen Webinar</h1>
                <p class="wbr-subtitle">Sistem pendaftaran, absensi &amp; sertifikasi webinar BKPSDMD</p>
            </div>
        </div>
        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=webinar' ) ); ?>" class="wbr-btn wbr-btn-primary">
            + Webinar Baru
        </a>
    </div>

    <!-- Stats -->
    <div class="wbr-stats-grid">
        <div class="wbr-stat-card">
            <div class="wbr-stat-icon">🎙</div>
            <div class="wbr-stat-num"><?php echo number_format( $total_webinar ); ?></div>
            <div class="wbr-stat-label">Total Webinar</div>
        </div>
        <div class="wbr-stat-card">
            <div class="wbr-stat-icon">👥</div>
            <div class="wbr-stat-num"><?php echo number_format( $total_reg ); ?></div>
            <div class="wbr-stat-label">Total Pendaftar</div>
        </div>
        <div class="wbr-stat-card">
            <div class="wbr-stat-icon">✅</div>
            <div class="wbr-stat-num"><?php echo number_format( $total_att ); ?></div>
            <div class="wbr-stat-label">Total Hadir</div>
        </div>
        <div class="wbr-stat-card">
            <div class="wbr-stat-icon">📜</div>
            <div class="wbr-stat-num"><?php echo number_format( $total_cert ); ?></div>
            <div class="wbr-stat-label">Petikan Aktif</div>
        </div>
    </div>

    <div class="wbr-two-col">
        <!-- Webinar Mendatang -->
        <div class="wbr-card">
            <div class="wbr-card-header">
                <h2>📅 Webinar Mendatang</h2>
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=webinar' ) ); ?>" class="wbr-btn wbr-btn-sm">+ Tambah</a>
            </div>
            <?php if ( empty( $upcoming ) ) : ?>
            <p class="wbr-empty">Tidak ada webinar mendatang.</p>
            <?php else : ?>
            <div class="wbr-webinar-list">
                <?php foreach ( $upcoming as $w ) :
                    $start = wp_date( 'd M Y, H:i', strtotime( $w->start_datetime ) );
                ?>
                <div class="wbr-webinar-item">
                    <div>
                        <strong><?php echo esc_html( $w->post_title ); ?></strong>
                        <div class="wbr-date">📅 <?php echo esc_html( $start ); ?></div>
                    </div>
                    <div class="wbr-item-actions">
                        <a href="<?php echo esc_url( get_edit_post_link( $w->ID ) ); ?>" class="wbr-btn wbr-btn-sm">Edit</a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wbr-registrants&webinar_id=' . $w->ID ) ); ?>" class="wbr-btn wbr-btn-sm">Peserta</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Webinar Selesai -->
        <div class="wbr-card">
            <div class="wbr-card-header">
                <h2>✅ Webinar Selesai</h2>
            </div>
            <?php if ( empty( $past ) ) : ?>
            <p class="wbr-empty">Tidak ada webinar yang selesai.</p>
            <?php else : ?>
            <div class="wbr-webinar-list">
                <?php foreach ( $past as $w ) :
                    $start      = wp_date( 'd M Y', strtotime( $w->start_datetime ) );
                    $sk_status  = $w->sk_status ? WBR_SK::status_label( $w->sk_status ) : '—';
                ?>
                <div class="wbr-webinar-item">
                    <div>
                        <strong><?php echo esc_html( $w->post_title ); ?></strong>
                        <div class="wbr-date">📅 <?php echo esc_html( $start ); ?> &bull;
                            👥 <?php echo $w->reg_count; ?> daftar &bull;
                            ✅ <?php echo $w->att_count; ?> hadir</div>
                        <div class="wbr-sk-status">SK: <?php echo $sk_status; ?></div>
                    </div>
                    <div class="wbr-item-actions">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wbr-registrants&webinar_id=' . $w->ID ) ); ?>" class="wbr-btn wbr-btn-sm">Peserta</a>
                        <?php if ( current_user_can( 'generate_sk' ) ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wbr-sk&webinar_id=' . $w->ID ) ); ?>" class="wbr-btn wbr-btn-sm">SK</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
