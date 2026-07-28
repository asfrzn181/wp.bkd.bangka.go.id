<?php
/**
 * Shortcode Class - Render Struktur Organisasi
 * Shortcode: [struktur_organisasi]
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BKPSDMD_Org_Shortcode {

    public static function init() {
        add_shortcode( 'struktur_organisasi', array( __CLASS__, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
    }

    public static function enqueue_assets() {
        wp_enqueue_style(
            'bkpsdmd-org-chart',
            BKPSDMD_ORG_URL . 'assets/css/org-chart.css',
            array(),
            BKPSDMD_ORG_VERSION
        );
        wp_enqueue_script(
            'bkpsdmd-org-chart',
            BKPSDMD_ORG_URL . 'assets/js/org-chart.js',
            array( 'jquery' ),
            BKPSDMD_ORG_VERSION,
            true
        );
    }

    public static function render( $atts ) {
        $atts = shortcode_atts( array(
            'judul'  => 'Struktur Organisasi',
            'root'   => 0,   // 0 = tampilkan semua dari root
        ), $atts, 'struktur_organisasi' );

        $rows = BKPSDMD_Org_DB::get_all( true ); // aktif saja
        if ( empty( $rows ) ) {
            return '<div class="bkpsdmd-org-empty"><p>Belum ada data struktur organisasi. Silakan tambahkan melalui <a href="' . esc_url( admin_url( 'admin.php?page=bkpsdmd-org-chart' ) ) . '">halaman admin</a>.</p></div>';
        }

        $tree = BKPSDMD_Org_DB::build_tree( $rows, intval( $atts['root'] ) );

        ob_start();
        ?>
        <div class="bkpsdmd-org-wrap" id="bkpsdmd-org-wrap">

            <?php if ( ! empty( $atts['judul'] ) ) : ?>
            <div class="bkpsdmd-org-header">
                <h2 class="bkpsdmd-org-title"><?php echo esc_html( $atts['judul'] ); ?></h2>
                <div class="bkpsdmd-org-controls">
                    <button class="bkpsdmd-ctrl-btn" id="bkpsdmd-expand-all" title="Perluas Semua">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                        Perluas Semua
                    </button>
                    <button class="bkpsdmd-ctrl-btn" id="bkpsdmd-collapse-all" title="Ciutkan Semua">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"/></svg>
                        Ciutkan Semua
                    </button>
                    <button class="bkpsdmd-ctrl-btn" id="bkpsdmd-zoom-in" title="Perbesar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    </button>
                    <button class="bkpsdmd-ctrl-btn" id="bkpsdmd-zoom-out" title="Perkecil">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    </button>
                    <button class="bkpsdmd-ctrl-btn" id="bkpsdmd-zoom-reset" title="Reset Tampilan">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Hover popup foto -->
            <div class="bkpsdmd-photo-popup" id="bkpsdmd-photo-popup">
                <div class="bkpsdmd-popup-inner">
                    <img src="" alt="" id="bkpsdmd-popup-img" class="bkpsdmd-popup-img">
                    <div class="bkpsdmd-popup-info">
                        <p class="bkpsdmd-popup-jabatan" id="bkpsdmd-popup-jabatan"></p>
                        <p class="bkpsdmd-popup-nama" id="bkpsdmd-popup-nama"></p>
                        <p class="bkpsdmd-popup-nip" id="bkpsdmd-popup-nip"></p>
                    </div>
                </div>
            </div>

            <!-- Area chart (scrollable + zoomable) -->
            <div class="bkpsdmd-org-canvas-wrap">
                <div class="bkpsdmd-org-canvas" id="bkpsdmd-org-canvas">
                    <div class="bkpsdmd-org-tree">
                        <ul class="bkpsdmd-tree-root">
                            <?php self::render_tree( $tree ); ?>
                        </ul>
                    </div>
                </div>
            </div>

        </div><!-- /.bkpsdmd-org-wrap -->
        <?php
        return ob_get_clean();
    }

    /**
     * Render rekursif node pohon
     */
    private static function render_tree( $nodes, $level = 0 ) {
        foreach ( $nodes as $node ) {
            $has_children  = ! empty( $node->children );
            $foto_url      = ! empty( $node->foto_url )
                             ? esc_url( $node->foto_url )
                             : BKPSDMD_ORG_URL . 'assets/images/default-avatar.svg';
            $level_class   = 'bkpsdmd-node-level-' . min( $level, 3 );
            $has_child_cls = $has_children ? 'has-children' : '';
            $is_fungsional = ( strpos( $node->keterangan ?? '', 'Fungsional' ) !== false );
            $fungsional_cls= $is_fungsional ? 'is-fungsional' : '';
            $nama_display  = ! empty( $node->nama ) ? $node->nama : '';
            ?>
            <li class="bkpsdmd-tree-node <?php echo esc_attr( $has_child_cls ); ?>">
                <!-- Outer wrapper untuk menampung garis bawah ke children -->
                <div class="bkpsdmd-node-outer">
                    <div class="bkpsdmd-node-card <?php echo esc_attr( "$level_class $fungsional_cls" ); ?>"
                         data-id="<?php echo esc_attr( $node->id ); ?>"
                         data-jabatan="<?php echo esc_attr( $node->jabatan ); ?>"
                         data-nama="<?php echo esc_attr( $nama_display ); ?>"
                         data-nip="<?php echo esc_attr( $node->nip ); ?>"
                         data-foto="<?php echo esc_attr( $foto_url ); ?>">

                        <?php if ( $is_fungsional ) : ?>
                        <span class="bkpsdmd-node-badge">Fungsional</span>
                        <?php endif; ?>

                        <!-- Avatar -->
                        <div class="bkpsdmd-node-avatar">
                            <img src="<?php echo esc_attr( $foto_url ); ?>"
                                 alt="<?php echo esc_attr( $nama_display ?: $node->jabatan ); ?>"
                                 loading="lazy"
                                 onerror="this.src='<?php echo esc_js( BKPSDMD_ORG_URL . 'assets/images/default-avatar.svg' ); ?>'">
                        </div>

                        <!-- Info jabatan -->
                        <div class="bkpsdmd-node-info">
                            <span class="bkpsdmd-node-jabatan"><?php echo esc_html( $node->jabatan ); ?></span>
                            <?php if ( ! empty( $nama_display ) ) : ?>
                            <span class="bkpsdmd-node-nama"><?php echo esc_html( $nama_display ); ?></span>
                            <?php else : ?>
                            <span class="bkpsdmd-node-nama" style="font-style:italic;opacity:0.5">(kosong)</span>
                            <?php endif; ?>
                            <?php if ( ! empty( $node->nip ) ) : ?>
                            <span class="bkpsdmd-node-nip">NIP: <?php echo esc_html( $node->nip ); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Tombol collapse jika ada anak -->
                        <?php if ( $has_children ) : ?>
                        <button class="bkpsdmd-toggle-btn" title="Ciutkan / Perluas">
                            <svg class="bkpsdmd-icon-minus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <svg class="bkpsdmd-icon-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="display:none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                        <?php endif; ?>

                    </div><!-- /.bkpsdmd-node-card -->

                    <?php if ( $has_children ) : ?>
                    <ul class="bkpsdmd-tree-children">
                        <?php self::render_tree( $node->children, $level + 1 ); ?>
                    </ul>
                    <?php endif; ?>

                </div><!-- /.bkpsdmd-node-outer -->
            </li>
            <?php
        }
    }
}

// Init shortcode
add_action( 'init', array( 'BKPSDMD_Org_Shortcode', 'init' ) );
