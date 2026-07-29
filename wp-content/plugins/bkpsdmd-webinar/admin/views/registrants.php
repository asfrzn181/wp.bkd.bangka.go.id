<?php
/**
 * Admin View: Peserta & Absensi
 */
if ( ! defined( 'ABSPATH' ) ) exit;
WBR_Roles::require_cap( 'view_registrants' );

global $wpdb;
$webinar_id = absint( $_GET['webinar_id'] ?? 0 );

// Daftar semua webinar untuk filter dropdown
$webinars = $wpdb->get_results(
    "SELECT p.ID, p.post_title FROM {$wpdb->posts} p
     WHERE p.post_type='webinar' AND p.post_status='publish'
     ORDER BY p.post_title ASC"
);

$registrants = [];
$webinar_title = '';
if ( $webinar_id ) {
    $registrants = WBR_Registrant::get_all( $webinar_id, true );
    $webinar_title = get_the_title( $webinar_id );
}
?>
<div class="wrap wbr-admin-wrap">
    <div class="wbr-admin-header">
        <div class="wbr-header-left">
            <div class="wbr-logo">👥</div>
            <div>
                <h1 class="wbr-title">Peserta &amp; Absensi</h1>
                <p class="wbr-subtitle"><?php echo $webinar_title ? esc_html( $webinar_title ) : 'Pilih webinar untuk melihat data'; ?></p>
            </div>
        </div>
        <div class="wbr-header-actions">
            <?php if ( $webinar_id ) : ?>
            <button type="button" class="wbr-btn wbr-btn-secondary" id="wbr-export-csv" data-webinar="<?php echo $webinar_id; ?>">
                📥 Export CSV
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter webinar -->
    <div class="wbr-filter-bar">
        <form method="get">
            <input type="hidden" name="page" value="wbr-registrants">
            <select name="webinar_id" id="wbr-webinar-select" onchange="this.form.submit()">
                <option value="">-- Pilih Webinar --</option>
                <?php foreach ( $webinars as $w ) : ?>
                <option value="<?php echo $w->ID; ?>" <?php selected( $webinar_id, $w->ID ); ?>>
                    <?php echo esc_html( $w->post_title ); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Scan QR oleh panitia -->
        <?php if ( $webinar_id && current_user_can( 'manage_attendance' ) ) : ?>
        <div class="wbr-scan-box">
            <input type="text" id="wbr-scan-token" placeholder="Scan/paste token peserta..." class="wbr-input">
            <button type="button" class="wbr-btn wbr-btn-primary" id="wbr-record-attendance">✅ Catat Hadir</button>
            <span id="wbr-scan-result" class="wbr-scan-result"></span>
        </div>
        <?php endif; ?>
    </div>

    <?php if ( ! $webinar_id ) : ?>
    <div class="wbr-empty-state"><p>Pilih webinar di atas untuk melihat daftar peserta.</p></div>

    <?php elseif ( empty( $registrants ) ) : ?>
    <div class="wbr-empty-state">
        <div class="wbr-empty-icon">👥</div>
        <h3>Belum Ada Peserta</h3>
        <p>Belum ada yang mendaftar untuk webinar ini.</p>
    </div>

    <?php else : ?>
    <!-- Stats mini -->
    <?php
    $total_r   = count( $registrants );
    $total_h   = count( array_filter( $registrants, fn($r) => $r->has_attended ) );
    $pct       = $total_r ? round( $total_h / $total_r * 100 ) : 0;
    ?>
    <div class="wbr-mini-stats">
        <span>📋 Terdaftar: <strong><?php echo $total_r; ?></strong></span>
        <span>✅ Hadir: <strong><?php echo $total_h; ?></strong></span>
        <span>📊 Kehadiran: <strong><?php echo $pct; ?>%</strong></span>
    </div>

    <!-- Tabel peserta -->
    <div class="wbr-table-wrap">
        <table class="wbr-table" id="wbr-registrant-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Email</th>
                    <th>Data Pendaftaran</th>
                    <th>Status</th>
                    <th>Waktu Daftar</th>
                    <th>Waktu Hadir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $registrants as $i => $r ) :
                    $data = json_decode( $r->submission_data, true ) ?: [];
                    $nama = '';
                    foreach ( $data as $k => $v ) {
                        if ( is_string( $v ) && stripos( $k, 'nama' ) !== false ) { $nama = $v; break; }
                    }
                ?>
                <tr id="wbr-reg-row-<?php echo $r->id; ?>">
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo esc_html( $r->email ); ?></td>
                    <td>
                        <strong><?php echo esc_html( $nama ?: '—' ); ?></strong>
                        <button type="button" class="wbr-data-toggle" data-id="<?php echo $r->id; ?>">Lihat semua ▼</button>
                        <div class="wbr-data-detail" id="wbr-data-<?php echo $r->id; ?>" style="display:none">
                            <?php foreach ( $data as $k => $v ) : ?>
                            <div><em><?php echo esc_html( $k ); ?>:</em> <?php echo esc_html( is_array($v) ? implode(', ',$v) : $v ); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td>
                        <?php if ( $r->has_attended ) : ?>
                        <span class="wbr-badge success">✅ Hadir</span>
                        <?php else : ?>
                        <span class="wbr-badge warning">⏳ Belum Hadir</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html( wp_date( 'd M Y H:i', strtotime( $r->registered_at ) ) ); ?></td>
                    <td><?php echo $r->attended_at ? esc_html( wp_date( 'd M Y H:i', strtotime( $r->attended_at ) ) ) : '—'; ?></td>
                    <td>
                        <button type="button" class="wbr-btn wbr-btn-danger wbr-del-reg"
                                data-id="<?php echo $r->id; ?>">🗑</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
