<?php
/**
 * Blog Grid Filter - Shortcode dengan filter AJAX
 * Shortcode: [blog_grid_filter posts_per_page="6"]
 *
 * @package career-development
 * @since 1.0
 */

if ( ! function_exists( 'bkpsdmd_blog_filter_shortcode' ) ) :

/**
 * Render output HTML filter + grid postingan
 */
function bkpsdmd_blog_filter_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'posts_per_page' => 6,
        'judul'          => 'Blog &amp; Berita',
        'subjudul'       => 'Artikel Terbaru',
    ), $atts, 'blog_grid_filter' );

    // Ambil semua kategori yang memiliki postingan
    $categories = get_categories( array(
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ) );

    // Query awal: tanpa filter
    $initial_query = new WP_Query( array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => intval( $atts['posts_per_page'] ),
        'paged'          => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );

    ob_start();
    ?>
    <div class="bkpsdmd-blog-filter-wrap"
         data-posts-per-page="<?php echo esc_attr( intval( $atts['posts_per_page'] ) ); ?>"
         data-nonce="<?php echo esc_attr( wp_create_nonce( 'bkpsdmd_blog_filter_nonce' ) ); ?>">

        <!-- ===== HEADER ===== -->
        <div class="bkpsdmd-filter-header">
            <p class="bkpsdmd-filter-subtitle"><?php echo esc_html( $atts['subjudul'] ); ?></p>
            <h2 class="bkpsdmd-filter-title"><?php echo wp_kses_post( $atts['judul'] ); ?></h2>
        </div>

        <!-- ===== FILTER BAR ===== -->
        <div class="bkpsdmd-filter-bar">

            <!-- Baris label -->
            <div class="bkpsdmd-filter-labels">
                <div class="bkpsdmd-fc bkpsdmd-fc--keyword">
                    <span class="bkpsdmd-fb-label">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Kata Kunci
                    </span>
                </div>
                <div class="bkpsdmd-fc bkpsdmd-fc--date">
                    <span class="bkpsdmd-fb-label">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Dari Tanggal
                    </span>
                </div>
                <div class="bkpsdmd-fc bkpsdmd-fc--date">
                    <span class="bkpsdmd-fb-label">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Sampai Tanggal
                    </span>
                </div>
                <?php if ( ! empty( $categories ) ) : ?>
                <div class="bkpsdmd-fc bkpsdmd-fc--cat">
                    <span class="bkpsdmd-fb-label">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        Kategori
                    </span>
                </div>
                <?php endif; ?>
                <div class="bkpsdmd-fc bkpsdmd-fc--actions"></div>
            </div>

            <!-- Baris input (semua sejajar) -->
            <div class="bkpsdmd-filter-inputs">
                <div class="bkpsdmd-fc bkpsdmd-fc--keyword">
                    <input type="text"
                           id="bkpsdmd-keyword"
                           class="bkpsdmd-keyword-input bkpsdmd-input"
                           placeholder="Judul atau kata kunci..."
                           autocomplete="off">
                </div>

                <div class="bkpsdmd-fc bkpsdmd-fc--date">
                    <input type="text"
                           id="bkpsdmd-date-from"
                           class="bkpsdmd-date-from bkpsdmd-input bkpsdmd-datepicker"
                           placeholder="Pilih tanggal"
                           readonly>
                </div>

                <div class="bkpsdmd-fc bkpsdmd-fc--date">
                    <input type="text"
                           id="bkpsdmd-date-to"
                           class="bkpsdmd-date-to bkpsdmd-input bkpsdmd-datepicker"
                           placeholder="Pilih tanggal"
                           readonly>
                </div>

                <?php if ( ! empty( $categories ) ) : ?>
                <div class="bkpsdmd-fc bkpsdmd-fc--cat">
                    <select id="bkpsdmd-category" class="bkpsdmd-cat-select">
                        <option value="">— Semua Kategori —</option>
                        <?php foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>">
                            <?php echo esc_html( $cat->name ); ?> (<?php echo intval( $cat->count ); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="bkpsdmd-fc bkpsdmd-fc--actions">
                    <div class="bkpsdmd-fb-btns">
                        <button type="button" class="bkpsdmd-search-btn" id="bkpsdmd-search-btn">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            Cari
                        </button>
                        <button type="button" class="bkpsdmd-reset-btn" id="bkpsdmd-reset-filter" title="Reset filter">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
                        </button>
                    </div>
                </div>
            </div>


        <!-- Info hasil -->

        <div class="bkpsdmd-result-bar" id="bkpsdmd-result-bar" style="display:none;">
            <span class="bkpsdmd-result-info" id="bkpsdmd-result-info"></span>
            <span class="bkpsdmd-active-filters" id="bkpsdmd-active-filters"></span>
        </div>

        <!-- ===== LOADING ===== -->
        <div class="bkpsdmd-loading-overlay" id="bkpsdmd-loading" style="display:none;">
            <div class="bkpsdmd-spinner"><div class="bkpsdmd-spinner-ring"></div></div>
            <p>Memuat artikel...</p>
        </div>

        <!-- ===== GRID ===== -->
        <div class="bkpsdmd-posts-grid" id="bkpsdmd-posts-grid">
            <?php
            if ( $initial_query->have_posts() ) :
                while ( $initial_query->have_posts() ) :
                    $initial_query->the_post();
                    bkpsdmd_render_post_card();
                endwhile;
                wp_reset_postdata();
            else :
                echo '<div class="bkpsdmd-no-posts"><p>' . esc_html__( 'Belum ada artikel.', 'career-development' ) . '</p></div>';
            endif;
            ?>
        </div>

        <!-- ===== PAGINATION ===== -->
        <div class="bkpsdmd-pagination" id="bkpsdmd-pagination">
            <?php
            $total_pages = $initial_query->max_num_pages;
            if ( $total_pages > 1 ) :
                for ( $i = 1; $i <= $total_pages; $i++ ) :
                    $active = ( $i === 1 ) ? ' active' : '';
                    echo '<button class="bkpsdmd-page-btn' . $active . '" data-page="' . intval( $i ) . '">' . intval( $i ) . '</button>';
                endfor;
            endif;
            ?>
        </div>

    </div><!-- /.bkpsdmd-blog-filter-wrap -->
    <?php

    return ob_get_clean();
}
add_shortcode( 'blog_grid_filter', 'bkpsdmd_blog_filter_shortcode' );

endif;


/**
 * Helper: Render satu kartu postingan
 */
function bkpsdmd_render_post_card() {
    $post_id   = get_the_ID();
    $permalink = get_permalink( $post_id );
    $title     = get_the_title( $post_id );
    $excerpt   = wp_trim_words( get_the_excerpt(), 18, '...' );
    $date      = get_the_date( 'd M Y', $post_id );
    $author    = get_the_author();
    $cats      = get_the_category( $post_id );

    ?>
    <div class="bkpsdmd-post-card inner-post-box">
        <?php if ( has_post_thumbnail( $post_id ) ) : ?>
        <a href="<?php echo esc_url( $permalink ); ?>" class="bkpsdmd-post-thumb">
            <?php echo get_the_post_thumbnail( $post_id, 'medium_large', array( 'loading' => 'lazy' ) ); ?>
            <?php if ( ! empty( $cats ) ) : ?>
            <span class="bkpsdmd-cat-badge"><?php echo esc_html( $cats[0]->name ); ?></span>
            <?php endif; ?>
        </a>
        <?php else : ?>
        <a href="<?php echo esc_url( $permalink ); ?>" class="bkpsdmd-post-thumb bkpsdmd-no-thumb">
            <div class="bkpsdmd-thumb-placeholder">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            <?php if ( ! empty( $cats ) ) : ?>
            <span class="bkpsdmd-cat-badge"><?php echo esc_html( $cats[0]->name ); ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>

        <div class="bkpsdmd-post-body">
            <div class="bkpsdmd-post-meta">
                <span class="bkpsdmd-meta-date">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <?php echo esc_html( $date ); ?>
                </span>
                <span class="bkpsdmd-meta-author">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <?php echo esc_html( $author ); ?>
                </span>
            </div>
            <h4 class="bkpsdmd-post-title">
                <a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
            </h4>
            <p class="bkpsdmd-post-excerpt"><?php echo esc_html( $excerpt ); ?></p>
            <a href="<?php echo esc_url( $permalink ); ?>" class="bkpsdmd-read-more">
                Baca Selengkapnya
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
    <?php
}


/**
 * AJAX Handler: Filter & search posts
 */
function bkpsdmd_ajax_filter_posts() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'bkpsdmd_blog_filter_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Akses ditolak.' ) );
        return;
    }

    $posts_per_page = isset( $_POST['posts_per_page'] ) ? intval( $_POST['posts_per_page'] ) : 6;
    $paged          = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
    $keyword        = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
    $category       = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
    $date_from      = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
    $date_to        = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';

    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => false,
    );

    // Keyword search
    if ( ! empty( $keyword ) ) {
        $args['s'] = $keyword;
    }

    // Filter kategori (single)
    if ( $category > 0 ) {
        $args['cat'] = $category;
    }

    // Filter tanggal — parse format d/m/Y dari flatpickr
    $date_query = array( 'inclusive' => true );
    $has_date   = false;
    if ( ! empty( $date_from ) ) {
        $dt = DateTime::createFromFormat( 'd/m/Y', $date_from );
        if ( $dt ) {
            $date_query['after'] = $dt->format( 'Y-m-d' );
            $has_date = true;
        }
    }
    if ( ! empty( $date_to ) ) {
        $dt = DateTime::createFromFormat( 'd/m/Y', $date_to );
        if ( $dt ) {
            $date_query['before'] = $dt->format( 'Y-m-d' );
            $has_date = true;
        }
    }
    if ( $has_date ) {
        $args['date_query'] = array( $date_query );
    }

    $query = new WP_Query( $args );

    ob_start();
    if ( $query->have_posts() ) :
        while ( $query->have_posts() ) :
            $query->the_post();
            bkpsdmd_render_post_card();
        endwhile;
        wp_reset_postdata();
    else :
        echo '<div class="bkpsdmd-no-posts">';
        echo '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';
        echo '<p>Tidak ada artikel yang sesuai.</p>';
        echo '<small>Coba ubah kata kunci, kategori, atau rentang tanggal.</small>';
        echo '</div>';
    endif;
    $html = ob_get_clean();

    // Pagination
    $total_pages     = $query->max_num_pages;
    $pagination_html = '';
    if ( $total_pages > 1 ) {
        // Prev
        if ( $paged > 1 ) {
            $pagination_html .= '<button class="bkpsdmd-page-btn bkpsdmd-page-prev" data-page="' . ( $paged - 1 ) . '">&laquo;</button>';
        }
        for ( $i = 1; $i <= $total_pages; $i++ ) {
            $active           = ( $i === $paged ) ? ' active' : '';
            $pagination_html .= '<button class="bkpsdmd-page-btn' . $active . '" data-page="' . intval( $i ) . '">' . intval( $i ) . '</button>';
        }
        // Next
        if ( $paged < $total_pages ) {
            $pagination_html .= '<button class="bkpsdmd-page-btn bkpsdmd-page-next" data-page="' . ( $paged + 1 ) . '">&raquo;</button>';
        }
    }

    wp_send_json_success( array(
        'html'        => $html,
        'pagination'  => $pagination_html,
        'total_posts' => $query->found_posts,
        'total_pages' => $total_pages,
    ) );
}
add_action( 'wp_ajax_bkpsdmd_filter_posts',        'bkpsdmd_ajax_filter_posts' );
add_action( 'wp_ajax_nopriv_bkpsdmd_filter_posts', 'bkpsdmd_ajax_filter_posts' );


/**
 * Enqueue assets — versi filemtime agar tidak ter-cache
 */
function bkpsdmd_enqueue_blog_filter_assets() {
    if ( ! is_singular() && ! is_page() && ! is_home() && ! is_front_page() ) {
        return;
    }

    $theme_uri = get_template_directory_uri();
    $theme_dir = get_template_directory();

    // Flatpickr (date picker modern)
    wp_enqueue_style( 'flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), '4.6.13' );
    wp_enqueue_script( 'flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', array(), '4.6.13', true );
    wp_enqueue_script( 'flatpickr-id', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js', array( 'flatpickr' ), '4.6.13', true );

    // Select2
    wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0' );
    wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '4.1.0', true );

    // Custom blog filter
    $ver_css = file_exists( $theme_dir . '/assets/css/blog-filter.css' ) ? filemtime( $theme_dir . '/assets/css/blog-filter.css' ) : '1.0';
    $ver_js  = file_exists( $theme_dir . '/assets/js/blog-filter.js' )  ? filemtime( $theme_dir . '/assets/js/blog-filter.js' )  : '1.0';

    wp_enqueue_style(
        'bkpsdmd-blog-filter',
        $theme_uri . '/assets/css/blog-filter.css',
        array( 'flatpickr', 'select2' ),
        $ver_css
    );

    wp_enqueue_script(
        'bkpsdmd-blog-filter',
        $theme_uri . '/assets/js/blog-filter.js',
        array( 'jquery', 'flatpickr', 'flatpickr-id', 'select2' ),
        $ver_js,
        true
    );

    wp_localize_script( 'bkpsdmd-blog-filter', 'bkpsdmdFilter', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'action'  => 'bkpsdmd_filter_posts',
    ) );
}
add_action( 'wp_enqueue_scripts', 'bkpsdmd_enqueue_blog_filter_assets' );
