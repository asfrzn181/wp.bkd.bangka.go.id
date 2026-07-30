<?php
/**
 * Admin View: Petikan Sertifikat (Filter per Webinar / SK)
 */
if ( ! defined( 'ABSPATH' ) ) exit;
WBR_Roles::require_cap( 'generate_certificates' );

global $wpdb;
$webinar_id = absint( $_GET['webinar_id'] ?? 0 );

// Daftar webinar
$webinars = $wpdb->get_results(
    "SELECT p.ID, p.post_title,
            (SELECT COUNT(*) FROM {$wpdb->prefix}webinar_certificate c WHERE c.webinar_id = p.ID) AS cert_count
     FROM {$wpdb->posts} p
     WHERE p.post_type = 'webinar' AND p.post_status != 'trash'
     ORDER BY p.post_date DESC"
);

if ( ! $webinar_id && ! empty( $webinars ) ) {
    $webinar_id = $webinars[0]->ID;
}

$certs           = $webinar_id ? WBR_Certificate::get_by_webinar( $webinar_id ) : [];
$current_webinar = $webinar_id ? get_post( $webinar_id ) : null;
$sk              = $webinar_id ? WBR_SK::get_by_webinar( $webinar_id ) : null;
?>
<div class="wrap wbr-admin-wrap">
    <div class="wbr-admin-header">
        <div class="wbr-header-left">
            <div class="wbr-logo">🏆</div>
            <div>
                <h1 class="wbr-title">Petikan Sertifikat</h1>
                <p class="wbr-subtitle"><?php echo $current_webinar ? esc_html( $current_webinar->post_title ) : 'Pilih webinar untuk melihat petikan sertifikat'; ?></p>
            </div>
        </div>
        <?php if ( $webinar_id ) : ?>
        <div class="wbr-header-actions">
            <button type="button" id="wbr-generate-batch" data-webinar="<?php echo $webinar_id; ?>" class="wbr-btn wbr-btn-primary">
                🔄 Generate Ulang Petikan (Batch)
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filter Webinar Dropdown -->
    <div class="wbr-filter-bar">
        <form method="get" style="display:flex;align-items:center;gap:12px;width:100%;">
            <input type="hidden" name="page" value="wbr-certificates">
            <select name="webinar_id" onchange="this.form.submit()" class="wbr-input" style="flex:1;">
                <option value="">-- Pilih Webinar --</option>
                <?php foreach ( $webinars as $w ) : ?>
                <option value="<?php echo $w->ID; ?>" <?php selected( $webinar_id, $w->ID ); ?>>
                    <?php echo esc_html( $w->post_title ); ?> (<?php echo intval( $w->cert_count ); ?> sertifikat)
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ( ! $webinar_id ) : ?>
    <div class="wbr-empty-state"><p>Pilih webinar dari dropdown di atas.</p></div>

    <?php elseif ( empty( $certs ) ) : ?>
    <div class="wbr-empty-state">
        <div class="wbr-empty-icon">📜</div>
        <h3>Belum Ada Sertifikat Diterbitkan</h3>
        <p>Sertifikat diterbitkan secara otomatis saat peserta submit kehadiran (absensi).</p>
        <button type="button" id="wbr-generate-batch" data-webinar="<?php echo $webinar_id; ?>" class="wbr-btn wbr-btn-primary" style="margin-top:12px;">
            ⚡ Trigger Batch Generate Sertifikat
        </button>
    </div>

    <?php else : ?>
    <!-- Status SK & Mini Stats -->
    <?php
    $total_c   = count( $certs );
    $total_rev = count( array_filter( $certs, fn($c) => $c->status === 'revoked' ) );
    ?>
    <div class="wbr-mini-stats">
        <span>📜 Total Sertifikat: <strong><?php echo $total_c; ?></strong></span>
        <span>✅ Aktif: <strong><?php echo $total_c - $total_rev; ?></strong></span>
        <span>🚫 Dicabut (Revoked): <strong><?php echo $total_rev; ?></strong></span>
        <span>📄 Status SK Minut: <strong><?php echo $sk ? WBR_SK::status_label( $sk->status ) : 'Belum dibuat (sertifikat berdiri sendiri)'; ?></strong></span>
    </div>

    <!-- Tabel petikan -->
    <div id="wbr-cert-notice" style="margin-bottom:12px;"></div>
    <div class="wbr-table-wrap">
        <table class="wbr-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nomor Petikan</th>
                    <th>Nama Peserta</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>SK Minut</th>
                    <th>QR / Verifikasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $certs as $i => $c ) :
                    $verify_url = home_url( '/verifikasi-petikan/' . $c->qr_verification_hash );
                    $is_revoked = $c->status === 'revoked';
                ?>
                <tr id="wbr-cert-row-<?php echo $c->id; ?>" class="<?php echo $is_revoked ? 'wbr-row-revoked' : ''; ?>">
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo esc_html( $c->petikan_number ); ?></strong></td>
                    <td><strong><?php echo esc_html( $c->holder_name ?: '—' ); ?></strong></td>
                    <td><?php echo esc_html( $c->holder_email ); ?></td>
                    <td>
                        <?php if ( $is_revoked ) : ?>
                        <span class="wbr-badge danger" title="Alasan: <?php echo esc_attr( $c->revoke_reason ); ?>">
                            🚫 Dicabut
                        </span>
                        <?php else : ?>
                        <span class="wbr-badge success">✅ Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ( $c->sk_number ) : ?>
                        <span style="font-size:12px;font-weight:600;color:#818cf8;"><?php echo esc_html( $c->sk_number ); ?></span>
                        <?php else : ?>
                        <span style="font-size:11px;color:#94a3b8;">Belum di-link SK</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo esc_url( $verify_url ); ?>" target="_blank" class="wbr-link" style="font-size:11px;">
                            🔍 Link Verifikasi
                        </a>
                    </td>
                    <td>
                        <div class="wbr-item-actions">
                            <a href="<?php echo esc_url( WBR_Document::download_url( 'certificate', $c->id ) ); ?>"
                               class="wbr-btn wbr-btn-secondary wbr-btn-sm" title="Download PDF">
                                📥 PDF
                            </a>
                            <button type="button" class="wbr-btn wbr-btn-primary wbr-btn-sm wbr-regen-btn"
                                    data-id="<?php echo $c->id; ?>" title="Cetak ulang sertifikat ini">
                                🔄 Regenerate
                            </button>
                            <?php if ( ! $is_revoked ) : ?>
                            <button type="button" class="wbr-btn wbr-btn-danger wbr-btn-sm wbr-revoke-btn"
                                    data-id="<?php echo $c->id; ?>" data-name="<?php echo esc_attr( $c->holder_name ); ?>">
                                🚫 Revoke
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
(function($) {
    'use strict';

    // Batch generate button
    $('#wbr-generate-batch').on('click', function() {
        var webinarId = $(this).data('webinar');
        var $btn = $(this);
        $btn.prop('disabled', true).text('Generating...');

        $.post(wbrAdmin.ajaxUrl, {
            action: 'wbr_batch_generate_certs', // triggers batch
            webinar_id: webinarId,
            nonce: wbrAdmin.nonce
        }).done(function() {
            location.reload();
        }).fail(function() {
            alert('Gagal trigger batch generate.');
            location.reload();
        });
    });

    // Individual regenerate button
    $('.wbr-regen-btn').on('click', function() {
        var id   = $(this).data('id');
        var $btn = $(this);
        $btn.prop('disabled', true).text('⏳');

        $.post(wbrAdmin.ajaxUrl, {
            action: 'wbr_regenerate_cert',
            cert_id: id,
            nonce: wbrAdmin.nonce
        }).done(function(res) {
            if (res.success) {
                alert('Sertifikat berhasil dibuat ulang!');
                location.reload();
            } else {
                alert('Gagal: ' + (res.data || 'Error'));
                $btn.prop('disabled', false).text('🔄 Regenerate');
            }
        });
    });

    // Revoke cert button
    $('.wbr-revoke-btn').on('click', function() {
        var id   = $(this).data('id');
        var name = $(this).data('name');
        var reason = prompt('Masukkan alasan pencabutan sertifikat untuk ' + name + ':');
        if (reason === null) return; // cancel

        $.post(wbrAdmin.ajaxUrl, {
            action: 'wbr_revoke_cert',
            cert_id: id,
            reason: reason,
            nonce: wbrAdmin.nonce
        }).done(function(res) {
            if (res.success) {
                alert('Sertifikat berhasil dicabut.');
                location.reload();
            } else {
                alert('Gagal: ' + (res.data || 'Error'));
            }
        });
    });
})(jQuery);
</script>
