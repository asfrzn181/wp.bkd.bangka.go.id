<?php
/**
 * Admin View: SK Minut Management
 */
if ( ! defined( 'ABSPATH' ) ) exit;
WBR_Roles::require_cap( 'generate_sk' );

global $wpdb;
$webinar_id = absint( $_GET['webinar_id'] ?? 0 );

// Daftar webinar selesai yang butuh/sudah punya SK
$webinars = $wpdb->get_results(
    "SELECT p.ID, p.post_title, m.end_datetime,
            sk.id AS sk_id, sk.status AS sk_status, sk.sk_number
     FROM {$wpdb->posts} p
     JOIN {$wpdb->prefix}webinar_meta m ON m.post_id = p.ID
     LEFT JOIN {$wpdb->prefix}webinar_sk sk ON sk.webinar_id = p.ID
     WHERE p.post_type='webinar' AND p.post_status='publish'
     ORDER BY m.end_datetime DESC"
);

$sk = null;
$attendees_count = 0;
$webinar_title = '';
if ( $webinar_id ) {
    $sk = WBR_SK::get_by_webinar( $webinar_id );
    $attendees_count = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}webinar_attendance WHERE webinar_id = %d", $webinar_id
    ) );
    $webinar_title = get_the_title( $webinar_id );
}
?>
<div class="wrap wbr-admin-wrap">
    <div class="wbr-admin-header">
        <div class="wbr-header-left">
            <div class="wbr-logo">📄</div>
            <div>
                <h1 class="wbr-title">SK Minut</h1>
                <p class="wbr-subtitle">Manajemen Surat Keputusan Menit webinar</p>
            </div>
        </div>
    </div>

    <div class="wbr-two-col">
        <!-- Daftar SK / Webinar -->
        <div class="wbr-card" style="max-height:75vh;overflow-y:auto;">
            <h2 class="wbr-card-title">📋 Daftar Webinar</h2>
            <div class="wbr-sk-list">
                <?php foreach ( $webinars as $w ) :
                    $is_done  = strtotime( $w->end_datetime ) < time();
                    $label    = $w->sk_status ? WBR_SK::status_label( $w->sk_status ) : '—';
                    $classes  = 'wbr-sk-row';
                    if ( (int) $w->ID === $webinar_id ) $classes .= ' selected';
                ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wbr-sk&webinar_id=' . $w->ID ) ); ?>"
                   class="<?php echo $classes; ?>">
                    <div>
                        <strong><?php echo esc_html( $w->post_title ); ?></strong>
                        <div class="wbr-date"><?php echo esc_html( wp_date( 'd M Y', strtotime( $w->end_datetime ) ) ); ?></div>
                    </div>
                    <div class="wbr-sk-status-badge"><?php echo $label; ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Panel SK per webinar -->
        <div class="wbr-card">
            <?php if ( ! $webinar_id ) : ?>
            <p class="wbr-empty">Pilih webinar dari daftar di kiri untuk mengelola SK-nya.</p>
            <?php else : ?>

            <h2 class="wbr-card-title">📄 SK untuk: <?php echo esc_html( $webinar_title ); ?></h2>
            <p>👥 Peserta hadir: <strong><?php echo $attendees_count; ?> orang</strong></p>

            <?php if ( ! $sk ) : ?>
            <!-- Belum ada SK — form create -->
            <div class="wbr-sk-form">
                <h3>Buat SK Baru</h3>
                <div class="wbr-form-row">
                    <label>Nomor SK</label>
                    <input type="text" id="sk_number" class="wbr-input" placeholder="800.1.3.2/001/BKPSDMD/2026">
                </div>
                <div class="wbr-form-row">
                    <label>Tanggal SK</label>
                    <input type="date" id="sk_date" class="wbr-input" value="<?php echo wp_date( 'Y-m-d' ); ?>">
                </div>
                <div class="wbr-form-row">
                    <label>Pejabat Penandatangan</label>
                    <input type="text" id="signing_official" class="wbr-input" placeholder="Nama dan jabatan">
                </div>
                <div class="wbr-form-row">
                    <label>Metode Penandatanganan</label>
                    <select id="signing_method" class="wbr-input">
                        <option value="wet_signature">Tanda Tangan Basah</option>
                        <option value="tte_srikandi">TTE via Srikandi</option>
                    </select>
                </div>
                <button type="button" class="wbr-btn wbr-btn-primary" id="wbr-create-sk"
                        data-webinar="<?php echo $webinar_id; ?>">
                    📝 Generate Draft SK
                </button>
            </div>

            <?php else : ?>
            <!-- Ada SK — tampilkan status & actions -->
            <div class="wbr-sk-detail">
                <div class="wbr-detail-row"><span>Nomor SK:</span> <strong><?php echo esc_html( $sk->sk_number ?: '—' ); ?></strong></div>
                <div class="wbr-detail-row"><span>Tanggal SK:</span> <?php echo $sk->sk_date ? esc_html( wp_date( 'd F Y', strtotime( $sk->sk_date ) ) ) : '—'; ?></div>
                <div class="wbr-detail-row"><span>Pejabat:</span> <?php echo esc_html( $sk->signing_official ?: '—' ); ?></div>
                <div class="wbr-detail-row"><span>Metode:</span> <?php echo $sk->signing_method === 'tte_srikandi' ? 'TTE Srikandi' : 'Tanda Tangan Basah'; ?></div>
                <div class="wbr-detail-row">
                    <span>Status:</span>
                    <span class="wbr-badge <?php echo $sk->status === 'final' ? 'success' : ($sk->status === 'menunggu_ttd' ? 'warning' : 'info'); ?>">
                        <?php echo WBR_SK::status_label( $sk->status ); ?>
                    </span>
                </div>
            </div>

            <div class="wbr-sk-actions">
                <?php if ( $sk->sk_draft_file ) : ?>
                <a href="<?php echo esc_url( WBR_Document::download_url( 'sk_draft', $sk->id ) ); ?>"
                   class="wbr-btn wbr-btn-secondary">📥 Download Draft</a>
                <?php endif; ?>

                <?php if ( $sk->status === 'draft' ) : ?>
                <button type="button" class="wbr-btn wbr-btn-secondary" id="wbr-regen-sk" data-id="<?php echo $sk->id; ?>">🔄 Regenerate Draft</button>
                <button type="button" class="wbr-btn wbr-btn-primary" id="wbr-submit-signing" data-id="<?php echo $sk->id; ?>">📤 Ajukan ke TTD</button>
                <?php endif; ?>

                <?php if ( $sk->status === 'menunggu_ttd' ) : ?>
                <div class="wbr-upload-signed">
                    <label class="wbr-meta-label">Upload SK yang Sudah Ditandatangani</label>
                    <input type="file" id="wbr-sk-signed-file" accept=".pdf,.docx">
                    <button type="button" class="wbr-btn wbr-btn-primary" id="wbr-upload-signed" data-id="<?php echo $sk->id; ?>">
                        ✅ Upload &amp; Finalkan SK
                    </button>
                </div>
                <?php endif; ?>

                <?php if ( $sk->status === 'final' && $sk->sk_signed_file ) : ?>
                <a href="<?php echo esc_url( WBR_Document::download_url( 'sk_signed', $sk->id ) ); ?>"
                   class="wbr-btn wbr-btn-primary">📥 Download SK Final</a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wbr-certificates&sk_id=' . $sk->id ) ); ?>"
                   class="wbr-btn wbr-btn-secondary">📜 Lihat Petikan</a>
                <?php endif; ?>
            </div>

            <div id="wbr-sk-notice" style="margin-top:12px;"></div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
