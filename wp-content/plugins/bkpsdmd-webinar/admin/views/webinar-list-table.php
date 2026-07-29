<?php
/**
 * Admin View — Daftar Webinar (Custom List Table, bukan edit.php WP)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

// ── Handle delete action ───────────────────────────────────────────────────
if ( isset( $_GET['action'], $_GET['webinar_id'], $_GET['_wpnonce'] ) &&
     $_GET['action'] === 'delete' &&
     wp_verify_nonce( $_GET['_wpnonce'], 'wbr_delete_webinar_' . intval( $_GET['webinar_id'] ) ) &&
     current_user_can( 'manage_webinars' ) ) {

    $del_id = intval( $_GET['webinar_id'] );
    wp_delete_post( $del_id, true );
    $wpdb->delete( $wpdb->prefix . 'webinar_meta', [ 'post_id' => $del_id ], [ '%d' ] );
    $wpdb->delete( $wpdb->prefix . 'webinar_form_field', [ 'webinar_id' => $del_id ], [ '%d' ] );
    echo '<div class="notice notice-success"><p>Webinar berhasil dihapus.</p></div>';
}

// ── Query webinars ─────────────────────────────────────────────────────────
$webinars = $wpdb->get_results(
    "SELECT p.ID, p.post_title, p.post_status,
            m.start_datetime, m.end_datetime,
            (SELECT COUNT(*) FROM {$wpdb->prefix}webinar_registrant r WHERE r.webinar_id = p.ID) AS reg_count,
            (SELECT COUNT(*) FROM {$wpdb->prefix}webinar_attendance a WHERE a.webinar_id = p.ID) AS att_count,
            sk.status AS sk_status, sk.id AS sk_id
     FROM {$wpdb->posts} p
     LEFT JOIN {$wpdb->prefix}webinar_meta m ON m.post_id = p.ID
     LEFT JOIN {$wpdb->prefix}webinar_sk sk   ON sk.webinar_id = p.ID
     WHERE p.post_type = 'webinar' AND p.post_status != 'trash'
     ORDER BY m.start_datetime DESC"
);

$now = current_time( 'timestamp' );

function wbr_webinar_status_badge( $start, $end, $now ) {
    $s = strtotime( $start );
    $e = strtotime( $end );
    if ( ! $s ) return '<span class="wbr-tag wbr-tag--gray">—</span>';
    if ( $now < $s )       return '<span class="wbr-tag wbr-tag--blue">📅 Akan Datang</span>';
    if ( $now <= $e )      return '<span class="wbr-tag wbr-tag--red">🔴 Berlangsung</span>';
    return '<span class="wbr-tag wbr-tag--gray">✅ Selesai</span>';
}

function wbr_sk_badge( $status ) {
    $map = [
        'draft'        => '<span class="wbr-tag wbr-tag--yellow">📝 Draft SK</span>',
        'menunggu_ttd' => '<span class="wbr-tag wbr-tag--orange">⏳ Menunggu TTD</span>',
        'final'        => '<span class="wbr-tag wbr-tag--green">✅ SK Final</span>',
    ];
    return $map[ $status ] ?? '<span class="wbr-tag wbr-tag--gray">Belum Ada SK</span>';
}
?>
<div class="wrap wbr-admin-wrap">
    <div class="wbr-admin-header">
        <div class="wbr-header-left">
            <span class="wbr-logo">🎓</span>
            <div>
                <h1 class="wbr-title">Kelola Webinar</h1>
                <p class="wbr-subtitle">Daftar semua webinar yang terdaftar di sistem</p>
            </div>
        </div>
        <div class="wbr-header-actions">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wbr-webinar-edit' ) ); ?>"
               class="wbr-btn wbr-btn-primary">+ Tambah Webinar Baru</a>
        </div>
    </div>

    <?php if ( empty( $webinars ) ) : ?>
    <div class="wbr-empty-state">
        <div class="wbr-empty-icon">🎓</div>
        <h3>Belum Ada Webinar</h3>
        <p>Klik tombol <strong>+ Tambah Webinar Baru</strong> untuk memulai.</p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wbr-webinar-edit' ) ); ?>"
           class="wbr-btn wbr-btn-primary" style="margin-top:12px;">+ Tambah Webinar Baru</a>
    </div>

    <?php else : ?>
    <div class="wbr-table-wrap">
        <table class="wbr-table">
            <thead>
                <tr>
                    <th>Webinar</th>
                    <th>Jadwal</th>
                    <th>Status</th>
                    <th class="wbr-center">Pendaftar</th>
                    <th class="wbr-center">Hadir</th>
                    <th>SK Minut</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $webinars as $w ) :
                $edit_url      = admin_url( 'admin.php?page=wbr-webinar-edit&id=' . $w->ID );
                $reg_url       = admin_url( 'admin.php?page=wbr-registrants&webinar_id=' . $w->ID );
                $sk_url        = admin_url( 'admin.php?page=wbr-sk&webinar_id=' . $w->ID );
                $cert_url      = admin_url( 'admin.php?page=wbr-certificates&webinar_id=' . $w->ID );
                $view_url      = get_permalink( $w->ID );
                $delete_url    = wp_nonce_url(
                    admin_url( 'admin.php?page=wbr-webinars&action=delete&webinar_id=' . $w->ID ),
                    'wbr_delete_webinar_' . $w->ID
                );
                $thumb = get_the_post_thumbnail_url( $w->ID, 'thumbnail' );
            ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <?php if ( $thumb ) : ?>
                        <img src="<?php echo esc_url( $thumb ); ?>" alt="" style="width:48px;height:36px;object-fit:cover;border-radius:4px;flex-shrink:0;">
                        <?php endif; ?>
                        <div>
                            <strong><a href="<?php echo esc_url( $edit_url ); ?>" style="text-decoration:none;color:inherit;"><?php echo esc_html( $w->post_title ); ?></a></strong>
                            <br><span style="font-size:11px;color:#888;">#<?php echo esc_html( $w->ID ); ?></span>
                        </div>
                    </div>
                </td>
                <td>
                    <?php if ( $w->start_datetime ) : ?>
                    <span style="font-size:12px;">
                        📅 <?php echo esc_html( wp_date( 'd M Y', strtotime( $w->start_datetime ) ) ); ?><br>
                        🕐 <?php echo esc_html( wp_date( 'H:i', strtotime( $w->start_datetime ) ) ); ?> –
                            <?php echo esc_html( wp_date( 'H:i', strtotime( $w->end_datetime ) ) ); ?> WIB
                    </span>
                    <?php else : ?>
                    <span style="color:#aaa;font-size:12px;">— belum diatur</span>
                    <?php endif; ?>
                </td>
                <td><?php echo wbr_webinar_status_badge( $w->start_datetime, $w->end_datetime, $now ); ?></td>
                <td class="wbr-center">
                    <a href="<?php echo esc_url( $reg_url ); ?>" class="wbr-badge info"><?php echo intval( $w->reg_count ); ?></a>
                </td>
                <td class="wbr-center">
                    <a href="<?php echo esc_url( $cert_url ); ?>" class="wbr-badge success"><?php echo intval( $w->att_count ); ?></a>
                </td>
                <td><a href="<?php echo esc_url( $sk_url ); ?>"><?php echo wbr_sk_badge( $w->sk_status ); ?></a></td>
                <td>
                    <div class="wbr-item-actions">
                        <a href="<?php echo esc_url( $edit_url ); ?>" class="wbr-btn wbr-btn-secondary wbr-btn-sm" title="Edit">✏️</a>
                        <a href="<?php echo esc_url( $reg_url ); ?>" class="wbr-btn wbr-btn-secondary wbr-btn-sm" title="Peserta">👥</a>
                        <a href="<?php echo esc_url( $cert_url ); ?>" class="wbr-btn wbr-btn-secondary wbr-btn-sm" title="Petikan">🏆</a>
                        <?php if ( $view_url ) : ?>
                        <a href="<?php echo esc_url( $view_url ); ?>" class="wbr-btn wbr-btn-secondary wbr-btn-sm" title="Lihat" target="_blank">🔗</a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( $delete_url ); ?>" class="wbr-btn wbr-btn-danger wbr-btn-sm"
                           onclick="return confirm('Hapus webinar ini dan semua datanya?')" title="Hapus">🗑</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<style>
.wbr-center { text-align: center !important; }
.wbr-tag { display:inline-block; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:700; }
.wbr-tag--blue   { background:#e0f2fe; color:#0369a1; }
.wbr-tag--red    { background:#fee2e2; color:#dc2626; }
.wbr-tag--green  { background:#dcfce7; color:#15803d; }
.wbr-tag--yellow { background:#fefce8; color:#854d0e; }
.wbr-tag--orange { background:#fff7ed; color:#c2410c; }
.wbr-tag--gray   { background:#f1f5f9; color:#64748b; }
</style>
