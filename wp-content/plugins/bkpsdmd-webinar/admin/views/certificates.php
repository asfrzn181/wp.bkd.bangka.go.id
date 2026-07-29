<?php
/**
 * Admin View: Petikan Sertifikat
 */
if ( ! defined( 'ABSPATH' ) ) exit;
WBR_Roles::require_cap( 'generate_certificates' );

global $wpdb;
$sk_id = absint( $_GET['sk_id'] ?? 0 );

// Daftar SK yang sudah final
$sk_list = $wpdb->get_results(
    "SELECT sk.*, p.post_title AS webinar_title
     FROM {$wpdb->prefix}webinar_sk sk
     JOIN {$wpdb->posts} p ON p.ID = sk.webinar_id
     ORDER BY sk.created_at DESC"
);

$certs = [];
$current_sk = null;
if ( $sk_id ) {
    $certs      = WBR_Certificate::get_by_sk( $sk_id );
    $current_sk = WBR_SK::get_by_id( $sk_id );
}
?>
<div class="wrap wbr-admin-wrap">
    <div class="wbr-admin-header">
        <div class="wbr-header-left">
            <div class="wbr-logo">📜</div>
            <div>
                <h1 class="wbr-title">Petikan Sertifikat</h1>
                <p class="wbr-subtitle"><?php echo $current_sk ? esc_html( $current_sk->webinar_title ) : 'Pilih SK untuk melihat petikan'; ?></p>
            </div>
        </div>
    </div>

    <!-- Filter SK -->
    <div class="wbr-filter-bar">
        <form method="get">
            <input type="hidden" name="page" value="wbr-certificates">
            <select name="sk_id" onchange="this.form.submit()" class="wbr-input">
                <option value="">-- Pilih SK --</option>
                <?php foreach ( $sk_list as $sk ) : ?>
                <option value="<?php echo $sk->id; ?>" <?php selected( $sk_id, $sk->id ); ?>>
                    <?php echo esc_html( $sk->webinar_title ); ?> —
                    <?php echo esc_html( $sk->sk_number ?: 'Tanpa nomor' ); ?>
                    (<?php echo WBR_SK::status_label( $sk->status ); ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ( ! $sk_id ) : ?>
    <div class="wbr-empty-state"><p>Pilih SK dari dropdown di atas.</p></div>

    <?php elseif ( empty( $certs ) ) : ?>
    <div class="wbr-empty-state">
        <div class="wbr-empty-icon">📜</div>
        <h3>Belum Ada Petikan</h3>
        <?php if ( $current_sk && $current_sk->status !== WBR_SK::STATUS_FINAL ) : ?>
        <p>Petikan akan digenerate otomatis setelah SK berstatus <strong>Final</strong>.</p>
        <?php else : ?>
        <p>Belum ada petikan yang digenerate untuk SK ini. Proses mungkin sedang berjalan di background.</p>
        <?php endif; ?>
    </div>

    <?php else : ?>
    <!-- Stats -->
    <?php
    $total_c    = count( $certs );
    $total_rev  = count( array_filter( $certs, fn($c) => $c->status === 'revoked' ) );
    ?>
    <div class="wbr-mini-stats">
        <span>📜 Total: <strong><?php echo $total_c; ?></strong></span>
        <span>✅ Aktif: <strong><?php echo $total_c - $total_rev; ?></strong></span>
        <span>🚫 Dicabut: <strong><?php echo $total_rev; ?></strong></span>
    </div>

    <!-- Tabel petikan -->
    <div id="wbr-cert-notice" style="margin-bottom:12px;"></div>
    <div class="wbr-table-wrap">
        <table class="wbr-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nomor Petikan</th>
                    <th>Peserta</th>
                    <th>Status</th>
                    <th>QR / Verifikasi</th>
                    <th>Digenerate</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $certs as $i => $c ) :
                    $reg     = json_decode( $c->reg_data, true ) ?: [];
                    $nama    = '';
                    foreach ( $reg as $k => $v ) { if ( is_string($v) && stripos($k,'nama') !== false ) { $nama = $v; break; } }
                    $verify_url = home_url( '/verifikasi-petikan/' . $c->qr_verification_hash );
                    $is_revoked = $c->status === 'revoked';
                ?>
                <tr id="wbr-cert-row-<?php echo $c->id; ?>" class="<?php echo $is_revoked ? 'wbr-row-revoked' : ''; ?>">
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo esc_html( $c->petikan_number ); ?></td>
                    <td>
                        <strong><?php echo esc_html( $nama ?: '—' ); ?></strong><br>
                        <small><?php echo esc_html( $c->email ); ?></small>
                    </td>
                    <td>
                        <?php if ( $is_revoked ) : ?>
                        <span class="wbr-badge danger">🚫 Dicabut</span>
                        <?php if ( $c->revoke_reason ) : ?>
                        <br><small style="color:#888"><?php echo esc_html( $c->revoke_reason ); ?></small>
                        <?php endif; ?>
                        <?php else : ?>
                        <span class="wbr-badge success">✅ Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo esc_url( $verify_url ); ?>" target="_blank" class="wbr-verify-link">🔗 Verifikasi</a>
                    </td>
                    <td><?php echo esc_html( wp_date( 'd M Y H:i', strtotime( $c->generated_at ) ) ); ?></td>
                    <td>
                        <div class="wbr-cert-actions">
                            <?php if ( $c->file_path_pdf ) : ?>
                            <a href="<?php echo esc_url( WBR_Document::download_url( 'certificate', $c->id ) ); ?>" class="wbr-btn wbr-btn-sm">📥 PDF</a>
                            <?php endif; ?>
                            <button type="button" class="wbr-btn wbr-btn-sm" id="wbr-regen-cert-<?php echo $c->id; ?>"
                                    onclick="wbrRegenCert(<?php echo $c->id; ?>)">🔄</button>
                            <?php if ( ! $is_revoked ) : ?>
                            <button type="button" class="wbr-btn wbr-btn-danger wbr-btn-sm"
                                    onclick="wbrRevokeCert(<?php echo $c->id; ?>)">🚫 Cabut</button>
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

<!-- Modal: alasan revoke -->
<div id="wbr-revoke-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:28px;max-width:420px;width:90%">
        <h3 style="margin:0 0 16px">Cabut Petikan Sertifikat</h3>
        <input type="hidden" id="wbr-revoke-cert-id">
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px">Alasan Pencabutan</label>
        <textarea id="wbr-revoke-reason" style="width:100%;height:80px;border:1px solid #ddd;border-radius:4px;padding:8px;font-size:13px;"
                  placeholder="Masukkan alasan pencabutan..."></textarea>
        <div style="display:flex;gap:10px;margin-top:14px">
            <button type="button" class="wbr-btn wbr-btn-danger" onclick="wbrDoRevoke()">🚫 Cabut</button>
            <button type="button" class="wbr-btn wbr-btn-secondary" onclick="document.getElementById('wbr-revoke-modal').style.display='none'">Batal</button>
        </div>
    </div>
</div>
<script>
function wbrRevokeCert(id) {
    document.getElementById('wbr-revoke-cert-id').value = id;
    document.getElementById('wbr-revoke-reason').value = '';
    document.getElementById('wbr-revoke-modal').style.display = 'flex';
}
function wbrRegenCert(id) {
    if (!confirm('Regenerate PDF petikan ini?')) return;
    jQuery.post(wbrAdmin.ajaxUrl, {action:'wbr_regenerate_cert', cert_id:id, nonce:wbrAdmin.nonce}, function(res){
        if (res.success) { document.getElementById('wbr-cert-notice').innerHTML = '<div class="wbr-notice success">'+res.data+'</div>'; }
        else { alert(res.data); }
    });
}
function wbrDoRevoke() {
    var id     = document.getElementById('wbr-revoke-cert-id').value;
    var reason = document.getElementById('wbr-revoke-reason').value;
    jQuery.post(wbrAdmin.ajaxUrl, {action:'wbr_revoke_cert', cert_id:id, reason:reason, nonce:wbrAdmin.nonce}, function(res){
        document.getElementById('wbr-revoke-modal').style.display = 'none';
        if (res.success) {
            var row = document.getElementById('wbr-cert-row-'+id);
            if (row) row.classList.add('wbr-row-revoked');
            document.getElementById('wbr-cert-notice').innerHTML = '<div class="wbr-notice success">'+res.data+'</div>';
        } else { alert(res.data); }
    });
}
</script>
