<?php
/**
 * Admin Page — Generate Short Link dari daftar Post/Page
 *
 * Alur:
 * 1. Panel "Buat Short Link"  → Cari post/page → klik Generate
 * 2. Panel "Kelola Short Link" → daftar yang sudah punya short link
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

    /* ── Tab aktif ── */
    $tab = sanitize_text_field( $_GET['tab'] ?? 'generate' );

    /* ── Data untuk tab Kelola ── */
    $search = sanitize_text_field( $_GET['s'] ?? '' );
    $paged  = max( 1, absint( $_GET['paged'] ?? 1 ) );
    $limit  = 15;
    $offset = ( $paged - 1 ) * $limit;
    $rows   = BKSL_DB::get_all( $limit, $offset, $search );
    $total  = BKSL_DB::count_all( $search );
    $pages  = (int) ceil( $total / $limit );

    /* ── Data untuk tab Generate: ambil semua post+page yang published ── */
    $all_posts = [];
    if ( $tab === 'generate' ) {
        // Post IDs yang sudah punya short link
        global $wpdb;
        $existing_ids = $wpdb->get_col(
            "SELECT post_id FROM {$wpdb->prefix}" . BKSL_TABLE
        );
        $existing_ids = array_map( 'intval', (array) $existing_ids );

        // Ambil semua published post+page, kecuali yang sudah punya short link
        $q_args = [
            'post_type'      => [ 'post', 'page' ],
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'post_type',
            'order'          => 'ASC',
            'fields'         => 'ids',
        ];
        if ( ! empty( $existing_ids ) ) {
            $q_args['post__not_in'] = $existing_ids;
        }
        $ids = get_posts( $q_args );

        foreach ( $ids as $pid ) {
            $all_posts[] = [
                'id'    => $pid,
                'title' => get_the_title( $pid ) ?: '(tanpa judul)',
                'type'  => get_post_type( $pid ),
                'url'   => get_permalink( $pid ),
            ];
        }
    }
    ?>
    <div class="wrap bksl-admin-wrap">

        <!-- ── Header ── -->
        <div class="bksl-admin-header">
            <div class="bksl-admin-header-left">
                <div class="bksl-admin-logo">🔗</div>
                <div>
                    <h1 class="bksl-admin-title">Short Link &amp; QR Code</h1>
                    <p class="bksl-admin-subtitle">Buat &amp; kelola short link untuk Post/Page — siap dibagikan ke sosmed</p>
                </div>
            </div>
            <div class="bksl-admin-stats">
                <div class="bksl-stat-card">
                    <span class="bksl-stat-num"><?php echo number_format( BKSL_DB::count_all() ); ?></span>
                    <span class="bksl-stat-label">Total Link</span>
                </div>
                <div class="bksl-stat-card">
                    <span class="bksl-stat-num"><?php echo count( $all_posts ?: [] ); ?></span>
                    <span class="bksl-stat-label">Belum Dibuat</span>
                </div>
            </div>
        </div>

        <!-- ── Tabs ── -->
        <div class="bksl-tabs">
            <a href="?page=bksl-shortlinks&tab=generate"
               class="bksl-tab <?php echo $tab === 'generate' ? 'active' : ''; ?>">
                ✨ Buat Short Link
            </a>
            <a href="?page=bksl-shortlinks&tab=manage"
               class="bksl-tab <?php echo $tab === 'manage' ? 'active' : ''; ?>">
                📋 Kelola Short Link
                <?php if ( $total > 0 ) : ?>
                <span class="bksl-tab-badge"><?php echo $total; ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- ════════════════════════════════════════
             TAB: BUAT SHORT LINK
             ════════════════════════════════════════ -->
        <?php if ( $tab === 'generate' ) : ?>
        <div class="bksl-generate-panel" id="bksl-generate-panel">

            <!-- Search filter (client-side) -->
            <div class="bksl-search-bar">
                <div class="bksl-search-row">
                    <input type="search" id="bksl-filter-input"
                           class="bksl-search-input"
                           placeholder="🔍 Filter judul post atau page..."
                           autocomplete="off">
                    <div class="bksl-filter-btns">
                        <button type="button" class="bksl-tab-filter active" data-type="all">Semua</button>
                        <button type="button" class="bksl-tab-filter" data-type="post">Post</button>
                        <button type="button" class="bksl-tab-filter" data-type="page">Page</button>
                    </div>
                </div>
            </div>

            <?php if ( empty( $all_posts ) ) : ?>
            <div class="bksl-empty-state">
                <div class="bksl-empty-icon">🎉</div>
                <h3>Semua Konten Sudah Punya Short Link!</h3>
                <p>Tidak ada post atau page yang belum dibuatkan short link.<br>
                   Kelola short link yang ada di tab <strong>Kelola Short Link</strong>.</p>
                <a href="?page=bksl-shortlinks&tab=manage" class="bksl-btn bksl-btn-primary">
                    📋 Lihat Kelola Short Link
                </a>
            </div>

            <?php else : ?>
            <div class="bksl-post-list" id="bksl-post-list">
                <?php foreach ( $all_posts as $p ) : ?>
                <div class="bksl-post-item"
                     data-title="<?php echo esc_attr( strtolower( $p['title'] ) ); ?>"
                     data-type="<?php echo esc_attr( $p['type'] ); ?>"
                     id="bksl-item-<?php echo esc_attr( $p['id'] ); ?>">
                    <div class="bksl-post-item-info">
                        <span class="bksl-type-badge bksl-type-<?php echo esc_attr( $p['type'] ); ?>">
                            <?php echo esc_html( $p['type'] ); ?>
                        </span>
                        <span class="bksl-post-item-title">
                            <?php echo esc_html( $p['title'] ); ?>
                        </span>
                        <span class="bksl-post-item-url">
                            <?php echo esc_html( $p['url'] ); ?>
                        </span>
                    </div>
                    <div class="bksl-post-item-action">
                        <button type="button"
                                class="bksl-btn bksl-btn-generate-item"
                                data-post-id="<?php echo esc_attr( $p['id'] ); ?>"
                                data-post-title="<?php echo esc_attr( $p['title'] ); ?>">
                            ✨ Generate
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="bksl-no-result" id="bksl-no-result" style="display:none;">
                    <p>Tidak ada hasil untuk kata kunci tersebut.</p>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /generate-panel -->

        <!-- ── Modal hasil generate ── -->
        <div id="bksl-result-modal" class="bksl-modal" style="display:none;" role="dialog" aria-modal="true">
            <div class="bksl-modal-overlay" id="bksl-modal-overlay"></div>
            <div class="bksl-modal-box">
                <div class="bksl-modal-header">
                    <h2 class="bksl-modal-title">🎉 Short Link Berhasil Dibuat!</h2>
                    <button type="button" class="bksl-modal-close" id="bksl-modal-close">✕</button>
                </div>
                <div class="bksl-modal-body">
                    <div class="bksl-modal-post-title" id="bksl-modal-post-title"></div>

                    <!-- Short URL -->
                    <div class="bksl-modal-section">
                        <div class="bksl-modal-label">Short Link</div>
                        <div class="bksl-modal-url-row">
                            <input type="text" id="bksl-modal-url" class="bksl-modal-url-input" readonly>
                            <button type="button" class="bksl-btn bksl-btn-copy-modal" id="bksl-modal-copy">📋 Salin</button>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="bksl-modal-section">
                        <div class="bksl-modal-label">QR Code</div>
                        <div class="bksl-modal-qr-wrap">
                            <img id="bksl-modal-qr" src="" alt="QR Code" class="bksl-modal-qr-img">
                            <a id="bksl-modal-dl-qr" href="" download="" class="bksl-btn bksl-btn-download">
                                ⬇ Download QR
                            </a>
                        </div>
                    </div>

                    <!-- Share -->
                    <div class="bksl-modal-section">
                        <div class="bksl-modal-label">Bagikan ke Sosmed</div>
                        <div class="bksl-modal-share-row">
                            <a id="bksl-modal-wa" href="#" target="_blank" class="bksl-share-btn bksl-wa">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WhatsApp
                            </a>
                            <a id="bksl-modal-tw" href="#" target="_blank" class="bksl-share-btn bksl-tw">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.259 5.629 5.905-5.629zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                X / Twitter
                            </a>
                            <a id="bksl-modal-fb" href="#" target="_blank" class="bksl-share-btn bksl-fb">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                Facebook
                            </a>
                        </div>
                    </div>

                    <!-- Custom slug -->
                    <div class="bksl-modal-section">
                        <div class="bksl-modal-label">Ganti Slug (opsional, 3–10 karakter)</div>
                        <div class="bksl-modal-slug-row">
                            <input type="text" id="bksl-modal-slug" class="bksl-custom-input"
                                   maxlength="10" placeholder="contoh: A1b2c">
                            <button type="button" class="bksl-btn bksl-btn-save" id="bksl-modal-save-slug">
                                Simpan
                            </button>
                        </div>
                        <div id="bksl-modal-slug-notice" class="bksl-notice" style="display:none;"></div>
                    </div>
                </div>
                <div class="bksl-modal-footer">
                    <button type="button" class="bksl-btn bksl-btn-regen" id="bksl-modal-regen">
                        🔄 Generate Slug Baru
                    </button>
                    <a href="?page=bksl-shortlinks&tab=manage" class="bksl-btn bksl-btn-primary">
                        📋 Lihat Semua Short Link
                    </a>
                </div>
            </div>
        </div>

        <?php endif; /* end tab=generate */ ?>

        <!-- ════════════════════════════════════════
             TAB: KELOLA SHORT LINK
             ════════════════════════════════════════ -->
        <?php if ( $tab === 'manage' ) : ?>
        <div class="bksl-manage-panel">

            <!-- Search -->
            <div class="bksl-search-bar">
                <form method="get" action="">
                    <input type="hidden" name="page" value="bksl-shortlinks">
                    <input type="hidden" name="tab"  value="manage">
                    <div class="bksl-search-row">
                        <input type="search" name="s" id="bksl-search"
                               class="bksl-search-input"
                               value="<?php echo esc_attr( $search ); ?>"
                               placeholder="🔍 Cari berdasarkan judul atau slug...">
                        <button type="submit" class="bksl-btn bksl-btn-search">Cari</button>
                        <?php if ( $search ) : ?>
                        <a href="?page=bksl-shortlinks&tab=manage" class="bksl-btn bksl-btn-reset">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <?php if ( empty( $rows ) ) : ?>
            <div class="bksl-empty-state">
                <div class="bksl-empty-icon">🔗</div>
                <h3>Belum Ada Short Link</h3>
                <p>Buat short link pertama Anda dari tab <strong>Buat Short Link</strong>.</p>
                <a href="?page=bksl-shortlinks&tab=generate" class="bksl-btn bksl-btn-primary">
                    ✨ Buat Short Link
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
                            $short_url = home_url( '/' . $row->slug );
                            $edit_url  = get_edit_post_link( $row->post_id );
                            $qr_b64    = BKSL_QRCode::generate_base64( $short_url, 4, 1 );
                            $wa_url    = 'https://api.whatsapp.com/send?text=' . rawurlencode( $row->post_title . ' ' . $short_url );
                            $row_num   = $offset + $i + 1;
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

            <!-- Pagination -->
            <?php if ( $pages > 1 ) : ?>
            <div class="bksl-pagination">
                <?php for ( $p = 1; $p <= $pages; $p++ ) : ?>
                <a href="?page=bksl-shortlinks&tab=manage&paged=<?php echo $p; ?><?php echo $search ? '&s=' . urlencode( $search ) : ''; ?>"
                   class="bksl-page-btn <?php echo $p === $paged ? 'active' : ''; ?>">
                   <?php echo $p; ?>
                </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>

        </div><!-- /manage-panel -->
        <?php endif; /* end tab=manage */ ?>

        <div id="bksl-admin-notice" class="bksl-notice" style="display:none;"></div>
    </div>
    <?php
}
