<?php
/**
 * Admin Page — Kelola Slide (drag-and-drop reorder, toggle, delete)
 * Dipanggil dari BKBC_Settings::render_manage_page()
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function bkbc_render_manage_page() {
    $slides = get_posts( [
        'post_type'      => 'banner_slide',
        'post_status'    => [ 'publish', 'draft' ],
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ] );
    ?>
    <div class="wrap bkbc-admin-wrap">

        <!-- Header -->
        <div class="bkbc-admin-header">
            <div class="bkbc-admin-header-left">
                <div class="bkbc-admin-logo">🖼️</div>
                <div>
                    <h1 class="bkbc-admin-title">Banner Carousel</h1>
                    <p class="bkbc-admin-subtitle">Kelola slide, drag untuk mengubah urutan tampilan</p>
                </div>
            </div>
            <div class="bkbc-admin-actions">
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=banner_slide' ) ); ?>"
                   class="bkbc-btn bkbc-btn-primary">
                    + Tambah Slide
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=bkbc-settings' ) ); ?>"
                   class="bkbc-btn bkbc-btn-secondary">
                    ⚙️ Pengaturan
                </a>
            </div>
        </div>

        <!-- Tips drag-drop -->
        <div class="bkbc-drag-tip">
            <span>💡 Drag baris untuk mengubah urutan slide. Perubahan urutan tersimpan otomatis.</span>
        </div>

        <!-- Daftar slide -->
        <?php if ( empty( $slides ) ) : ?>
        <div class="bkbc-empty-state">
            <div class="bkbc-empty-icon">🖼️</div>
            <h3>Belum Ada Slide</h3>
            <p>Klik tombol <strong>+ Tambah Slide</strong> untuk membuat slide pertama Anda.<br>
               Setiap slide dapat memiliki gambar, judul, deskripsi, dan tombol CTA.</p>
            <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=banner_slide' ) ); ?>"
               class="bkbc-btn bkbc-btn-primary">+ Tambah Slide Pertama</a>
        </div>
        <?php else : ?>

        <div id="bkbc-notice" class="bkbc-notice" style="display:none;"></div>

        <div class="bkbc-slide-list" id="bkbc-sortable">
            <?php foreach ( $slides as $slide ) :
                $active    = get_post_meta( $slide->ID, '_bkbc_active', true );
                $active    = $active === '' ? '1' : $active;
                $thumb_url = get_the_post_thumbnail_url( $slide->ID, 'thumbnail' );
                $subtitle  = get_post_meta( $slide->ID, '_bkbc_subtitle',    true );
                $cta_text  = get_post_meta( $slide->ID, '_bkbc_cta_text',    true );
                $cta_url   = get_post_meta( $slide->ID, '_bkbc_cta_url',     true );
                $edit_url  = get_edit_post_link( $slide->ID );
            ?>
            <div class="bkbc-slide-row <?php echo $active === '1' ? 'is-active' : 'is-inactive'; ?>"
                 data-id="<?php echo esc_attr( $slide->ID ); ?>">

                <!-- Handle drag -->
                <div class="bkbc-drag-handle" title="Drag untuk mengubah urutan">
                    <span></span><span></span><span></span>
                </div>

                <!-- Thumbnail -->
                <div class="bkbc-slide-thumb">
                    <?php if ( $thumb_url ) : ?>
                    <img src="<?php echo esc_url( $thumb_url ); ?>"
                         alt="<?php echo esc_attr( get_the_title( $slide->ID ) ); ?>"
                         loading="lazy">
                    <?php else : ?>
                    <div class="bkbc-no-thumb">📷</div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="bkbc-slide-info">
                    <div class="bkbc-slide-name">
                        <?php echo esc_html( get_the_title( $slide->ID ) ?: '(tanpa judul)' ); ?>
                    </div>
                    <?php if ( $subtitle ) : ?>
                    <div class="bkbc-slide-meta">Subtitle: <?php echo esc_html( $subtitle ); ?></div>
                    <?php endif; ?>
                    <?php if ( $cta_text ) : ?>
                    <div class="bkbc-slide-meta">CTA: <em><?php echo esc_html( $cta_text ); ?></em></div>
                    <?php endif; ?>
                    <div class="bkbc-slide-status">
                        <span class="bkbc-status-badge <?php echo $active === '1' ? 'active' : 'inactive'; ?>">
                            <?php echo $active === '1' ? '✅ Aktif' : '⏸ Nonaktif'; ?>
                        </span>
                        <span class="bkbc-status-badge post-status">
                            <?php echo esc_html( $slide->post_status ); ?>
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bkbc-slide-actions">
                    <button type="button"
                            class="bkbc-btn bkbc-btn-toggle"
                            data-id="<?php echo esc_attr( $slide->ID ); ?>"
                            data-active="<?php echo esc_attr( $active ); ?>">
                        <?php echo $active === '1' ? 'Nonaktifkan' : 'Aktifkan'; ?>
                    </button>
                    <a href="<?php echo esc_url( $edit_url ); ?>"
                       class="bkbc-btn bkbc-btn-edit">✏️ Edit</a>
                    <a href="<?php echo esc_url( get_delete_post_link( $slide->ID ) ); ?>"
                       class="bkbc-btn bkbc-btn-delete"
                       onclick="return confirm('Hapus slide ini?')">🗑 Hapus</a>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <p class="bkbc-total-count">
            <?php echo count( $slides ); ?> slide &bull;
            <?php echo count( array_filter( $slides, fn($s) => get_post_meta( $s->ID, '_bkbc_active', true ) === '1' ) ); ?> aktif
        </p>

        <?php endif; ?>

        <!-- Preview shortcode -->
        <div class="bkbc-shortcode-box">
            <strong>Shortcode:</strong>
            <code>[bkpsdmd_banner_carousel]</code>
            <button type="button" class="bkbc-copy-sc" data-sc="[bkpsdmd_banner_carousel]">Salin</button>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="bkbc-btn bkbc-btn-secondary bkbc-preview-btn">
                👁 Preview Halaman Utama
            </a>
        </div>

    </div><!-- /wrap -->
    <?php
}
