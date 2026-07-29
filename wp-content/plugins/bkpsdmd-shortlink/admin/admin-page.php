<?php
/**
 * Admin Page — Kelola semua short links
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'bksl_register_admin_menu' );
function bksl_register_admin_menu() {
    add_menu_page(
        'Short Link & QR Code',
        'Short Links',
        'manage_options',
        'bksl-shortlinks',
        'bksl_render_admin_page',
        'dashicons-admin-links',
        30
    );
}

function bksl_render_admin_page() {
    $search  = sanitize_text_field( $_GET['s'] ?? '' );
    $paged   = max( 1, absint( $_GET['paged'] ?? 1 ) );
    $limit   = 15;
    $offset  = ( $paged - 1 ) * $limit;
    $rows    = BKSL_DB::get_all( $limit, $offset, $search );
    $total   = BKSL_DB::count_all( $search );
    $pages   = ceil( $total / $limit );
    ?>
    <div class="wrap bksl-admin-wrap">
        <!-- ── Header ── -->
        <div class="bksl-admin-header">
            <div class="bksl-admin-header-left">
                <div class="bksl-admin-logo">🔗</div>
                <div>
                    <h1 class="bksl-admin-title">Short Link & QR Code</h1>
                    <p class="bksl-admin-subtitle">Kelola short link untuk Post & Page — siap dibagikan ke sosmed</p>
                </div>
            </div>
            <div class="bksl-admin-stats">
                <div class="bksl-stat-card">
                    <span class="bksl-stat-num"><?php echo number_format( BKSL_DB::count_all() ); ?></span>
                    <span class="bksl-stat-label">Total Link</span>
                </div>
            </div>
        </div>

        <!-- ── Search Bar ── -->
        <div class="bksl-search-bar">
            <form method="get" action="">
                <input type="hidden" name="page" value="bksl-shortlinks">
                <div class="bksl-search-row">
                    <input type="search" name="s" id="bksl-search"
                           class="bksl-search-input"
                           value="<?php echo esc_attr( $search ); ?>"
                           placeholder="🔍 Cari berdasarkan judul atau slug...">
                    <button type="submit" class="bksl-btn bksl-btn-search">Cari</button>
                    <?php if ( $search ) : ?>
                    <a href="?page=bksl-shortlinks" class="bksl-btn bksl-btn-reset">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ── Table ── -->
        <?php if ( empty( $rows ) ) : ?>
        <div class="bksl-empty-state">
            <div class="bksl-empty-icon">🔗</div>
            <h3>Belum Ada Short Link</h3>
            <p>Short link akan muncul di sini setelah Anda publish post atau page.<br>
               Anda juga bisa membuat short link langsung dari editor post/page.</p>
            <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="bksl-btn bksl-btn-primary">
                + Buat Post Baru
            </a>
        </div>
        <?php else : ?>
        <div class="bksl-table-wrap">
            <table class="bksl-table" id="bksl-admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Judul Konten</th>
                        <th>Short Link</th>
                        <th>QR Code</th>
                        <th>Tipe</th>
                        <th>Klik</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $rows as $i => $row ) :
                        $short_url   = home_url( '/' . $row->slug );
                        $edit_url    = get_edit_post_link( $row->post_id );
                        $qr_b64      = BKSL_QRCode::generate_base64( $short_url, 4, 1 );
                        $wa_url      = 'https://api.whatsapp.com/send?text=' . rawurlencode( $row->post_title . ' ' . $short_url );
                        $row_num     = $offset + $i + 1;
                    ?>
                    <tr id="bksl-row-<?php echo esc_attr( $row->id ); ?>">
                        <td class="bksl-td-num"><?php echo esc_html( $row_num ); ?></td>
                        <td class="bksl-td-title">
                            <a href="<?php echo esc_url( $edit_url ); ?>" class="bksl-post-title">
                                <?php echo esc_html( $row->post_title ?: '(tanpa judul)' ); ?>
                            </a>
                            <div class="bksl-post-status <?php echo esc_attr( $row->post_status ); ?>">
                                <?php echo esc_html( $row->post_status ); ?>
                            </div>
                        </td>
                        <td class="bksl-td-link">
                            <div class="bksl-link-wrap">
                                <code class="bksl-slug-code"><?php echo esc_html( $short_url ); ?></code>
                                <button type="button"
                                        class="bksl-btn-icon bksl-copy-admin"
                                        data-url="<?php echo esc_attr( $short_url ); ?>"
                                        title="Salin link">📋</button>
                            </div>
                        </td>
                        <td class="bksl-td-qr">
                            <div class="bksl-qr-thumb-wrap">
                                <img src="<?php echo esc_attr( $qr_b64 ); ?>"
                                     alt="QR" class="bksl-qr-thumb"
                                     title="<?php echo esc_attr( $short_url ); ?>">
                                <a href="<?php echo esc_attr( $qr_b64 ); ?>"
                                   download="qr-<?php echo esc_attr( $row->slug ); ?>.png"
                                   class="bksl-qr-dl-btn" title="Download QR">⬇</a>
                            </div>
                        </td>
                        <td class="bksl-td-type">
                            <span class="bksl-type-badge bksl-type-<?php echo esc_attr( $row->post_type ); ?>">
                                <?php echo esc_html( $row->post_type ); ?>
                            </span>
                        </td>
                        <td class="bksl-td-clicks">
                            <span class="bksl-clicks-badge"><?php echo number_format( (int) $row->click_count ); ?></span>
                        </td>
                        <td class="bksl-td-date">
                            <?php echo esc_html( wp_date( 'd M Y', strtotime( $row->created_at ) ) ); ?>
                        </td>
                        <td class="bksl-td-action">
                            <div class="bksl-action-wrap">
                                <a href="<?php echo esc_url( $short_url ); ?>" target="_blank"
                                   class="bksl-btn-icon" title="Buka link">🔗</a>
                                <a href="<?php echo esc_url( $wa_url ); ?>" target="_blank"
                                   class="bksl-btn-icon bksl-wa-icon" title="Bagikan ke WhatsApp">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </a>
                                <button type="button"
                                        class="bksl-btn-icon bksl-del-btn"
                                        data-id="<?php echo esc_attr( $row->id ); ?>"
                                        title="Hapus short link">🗑</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ── Pagination ── -->
        <?php if ( $pages > 1 ) : ?>
        <div class="bksl-pagination">
            <?php for ( $p = 1; $p <= $pages; $p++ ) : ?>
            <a href="?page=bksl-shortlinks&paged=<?php echo $p; ?><?php echo $search ? '&s=' . urlencode( $search ) : ''; ?>"
               class="bksl-page-btn <?php echo $p === $paged ? 'active' : ''; ?>">
               <?php echo $p; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <div id="bksl-admin-notice" class="bksl-notice" style="display:none;"></div>
    </div>
    <?php
}
