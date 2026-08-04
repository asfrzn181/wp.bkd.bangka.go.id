<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BKBC_CPT {

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_cpt' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post_bkpsdmd_banner', array( __CLASS__, 'save_meta_boxes' ) );
    }

    public static function register_cpt() {
        $labels = array(
            'name'               => 'Banner Carousel',
            'singular_name'      => 'Banner Carousel',
            'menu_name'          => 'Banner Carousel',
            'name_admin_bar'     => 'Banner Carousel',
            'add_new'            => 'Tambah Banner Baru',
            'add_new_item'       => 'Tambah Banner Carousel Baru',
            'new_item'           => 'Banner Baru',
            'edit_item'          => 'Edit Banner Carousel',
            'view_item'          => 'Lihat Banner',
            'all_items'          => 'Semua Banner Carousel',
            'search_items'       => 'Cari Banner',
            'not_found'          => 'Tidak ada banner ditemukan.',
            'not_found_in_trash' => 'Tidak ada banner di tong sampah.',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-images-alt2',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
        );

        register_post_type( 'bkpsdmd_banner', $args );
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'bkbc_banner_meta',
            'Pengaturan Detil Banner & Animasi Parallax',
            array( __CLASS__, 'render_meta_box' ),
            'bkpsdmd_banner',
            'normal',
            'high'
        );
    }

    public static function render_meta_box( $post ) {
        wp_nonce_field( 'bkbc_save_meta', 'bkbc_meta_nonce' );

        $sub_badge  = get_post_meta( $post->ID, '_bkbc_sub_badge', true );
        $btn1_text  = get_post_meta( $post->ID, '_bkbc_btn1_text', true );
        $btn1_url   = get_post_meta( $post->ID, '_bkbc_btn1_url', true );
        $btn2_text  = get_post_meta( $post->ID, '_bkbc_btn2_text', true );
        $btn2_url   = get_post_meta( $post->ID, '_bkbc_btn2_url', true );

        $card1_icon = get_post_meta( $post->ID, '_bkbc_card1_icon', true );
        $card1_val  = get_post_meta( $post->ID, '_bkbc_card1_val', true );
        $card1_lbl  = get_post_meta( $post->ID, '_bkbc_card1_lbl', true );

        $card2_icon = get_post_meta( $post->ID, '_bkbc_card2_icon', true );
        $card2_val  = get_post_meta( $post->ID, '_bkbc_card2_val', true );
        $card2_lbl  = get_post_meta( $post->ID, '_bkbc_card2_lbl', true );

        $bg_type     = get_post_meta( $post->ID, '_bkbc_bg_type', true );
        $word_cloud  = get_post_meta( $post->ID, '_bkbc_word_cloud_data', true );
        if ( empty( $bg_type ) ) {
            $bg_type = 'both'; // Default kombinasi
        }
        if ( empty( $word_cloud ) ) {
            $word_cloud = "BerAKHLAK | /layanan-asn/ | xl\nBKPSDMD Bangka | / | xl\nSurvei SKM | /survei-skm/ | lg\nDiklat ASN | /webinar/ | lg\nPengembangan Karir | /pengembangan-sdm/ | md\nPelayanan Prima | /layanan-asn/ | lg\nNetralitas ASN | /berita/ | sm\nSistem Informasi | /dashboard-asn/ | md\nKenaikan Pangkat | /layanan-asn/ | sm\nLayanan Pensiun | /layanan-asn/ | sm\nInovasi Digital | /dashboard-asn/ | md\nBangka Setara | / | sm";
        }
        ?>
        <style>
            .bkbc-meta-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .bkbc-meta-table th { width: 220px; text-align: left; padding: 12px 10px; font-weight: 600; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
            .bkbc-meta-table td { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; }
            .bkbc-input, .bkbc-textarea, .bkbc-select { width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; }
            .bkbc-row-flex { display: flex; gap: 10px; align-items: center; }
            .bkbc-section-h { margin-top: 20px; padding: 8px 12px; background: #1e3a8a; color: #fff; font-weight: 700; border-radius: 6px; font-size: 0.95rem; }
        </style>

        <div class="bkbc-section-h">Pengaturan Background (Gambar / Word Cloud)</div>
        <table class="bkbc-meta-table">
            <tr>
                <th><label for="bkbc_bg_type">Modus Background Slide</label></th>
                <td>
                    <select id="bkbc_bg_type" name="bkbc_bg_type" class="bkbc-select">
                        <option value="both" <?php selected( $bg_type, 'both' ); ?>>Kombinasi (Gambar Background + Word Cloud Overlay)</option>
                        <option value="wordcloud" <?php selected( $bg_type, 'wordcloud' ); ?>>Hanya Word Cloud / Tag Cloud (Dynamic Floating Text)</option>
                        <option value="image" <?php selected( $bg_type, 'image' ); ?>>Hanya Gambar Background Statis</option>
                    </select>
                    <p class="description">Pilih mode tampilan latar belakang untuk slide banner carousel ini.</p>
                </td>
            </tr>
            <tr>
                <th><label for="bkbc_word_cloud_data">Kustomisasi Kata Word Cloud & Link</label></th>
                <td>
                    <textarea id="bkbc_word_cloud_data" name="bkbc_word_cloud_data" rows="7" class="bkbc-textarea"><?php echo esc_textarea( $word_cloud ); ?></textarea>
                    <p class="description">Tuliskan kata/tag per baris dengan format: <code>Teks Tag | Link URL | Ukuran (sm/md/lg/xl)</code><br>Contoh: <code>BerAKHLAK | /layanan-asn/ | xl</code></p>
                </td>
            </tr>
            <tr>
                <th><label for="bkbc_bg_url">URL Gambar Background Parallax</label></th>
                <td>
                    <div class="bkbc-row-flex">
                        <input type="url" id="bkbc_bg_url" name="bkbc_bg_url" value="<?php echo esc_attr( $bg_url ); ?>" class="bkbc-input" placeholder="Atau gunakan Featured Image (Gambar Unggulan)">
                    </div>
                    <p class="description">Anda dapat mengisi URL gambar di atas atau mengatur <strong>Gambar Unggulan (Featured Image)</strong> di sebelah kanan.</p>
                </td>
            </tr>
            <tr>
                <th><label for="bkbc_sub_badge">Sub-Badge (Teks Kecil di Atas)</label></th>
                <td>
                    <input type="text" id="bkbc_sub_badge" name="bkbc_sub_badge" value="<?php echo esc_attr( $sub_badge ? $sub_badge : 'BKPSDMD KABUPATEN BANGKA' ); ?>" class="bkbc-input">
                    <p class="description">Contoh: BKPSDMD KABUPATEN BANGKA atau PENGEMBANGAN SDM</p>
                </td>
            </tr>
        </table>

        $buttons_list = get_post_meta( $post->ID, '_bkbc_buttons_list', true );
        if ( empty( $buttons_list ) ) {
            $btn1_text = get_post_meta( $post->ID, '_bkbc_btn1_text', true ) ?: 'Isi Survei SKM';
            $btn1_url  = get_post_meta( $post->ID, '_bkbc_btn1_url', true ) ?: '/survei-skm/';
            $btn2_text = get_post_meta( $post->ID, '_bkbc_btn2_text', true ) ?: 'Layanan ASN';
            $btn2_url  = get_post_meta( $post->ID, '_bkbc_btn2_url', true ) ?: '/layanan-asn/';
            $buttons_list = "{$btn1_text} | {$btn1_url} | primary\n{$btn2_text} | {$btn2_url} | secondary";
        }

        $cards_list = get_post_meta( $post->ID, '_bkbc_cards_list', true );
        if ( empty( $cards_list ) ) {
            $c1_icon = get_post_meta( $post->ID, '_bkbc_card1_icon', true ) ?: '⚡';
            $c1_val  = get_post_meta( $post->ID, '_bkbc_card1_val', true ) ?: '98.5%';
            $c1_lbl  = get_post_meta( $post->ID, '_bkbc_card1_lbl', true ) ?: 'Indeks Kepuasan Masyarakat';
            $c2_icon = get_post_meta( $post->ID, '_bkbc_card2_icon', true ) ?: '🏆';
            $c2_val  = get_post_meta( $post->ID, '_bkbc_card2_val', true ) ?: 'Prima';
            $c2_lbl  = get_post_meta( $post->ID, '_bkbc_card2_lbl', true ) ?: 'Pelayanan Publik Terpadu';
            $cards_list = "{$c1_icon} | {$c1_val} | {$c1_lbl}\n{$c2_icon} | {$c2_val} | {$c2_lbl}";
        }
        ?>
        <div class="bkbc-section-h">Daftar Tombol Aksi (Call To Action - Bebas Tambah Lebih dari 1)</div>
        <table class="bkbc-meta-table">
            <tr>
                <th><label for="bkbc_buttons_list">Daftar Tombol Aksi</label></th>
                <td>
                    <textarea id="bkbc_buttons_list" name="bkbc_buttons_list" rows="4" class="bkbc-textarea"><?php echo esc_textarea( $buttons_list ); ?></textarea>
                    <p class="description">Tuliskan tombol per baris dengan format: <code>Teks Tombol | Link URL | Tipe (primary / secondary / outline)</code><br>Contoh:<br><code>Isi Survei SKM | /survei-skm/ | primary</code><br><code>Layanan ASN | /layanan-asn/ | secondary</code><br><code>Webinar Diklat | /webinar/ | outline</code></p>
                </td>
            </tr>
        </table>

        <div class="bkbc-section-h">Daftar Kartu Melayang (Floating Glassmorphism Cards - Bebas Tambah Lebih dari 1)</div>
        <table class="bkbc-meta-table">
            <tr>
                <th><label for="bkbc_cards_list">Daftar Kartu Melayang</label></th>
                <td>
                    <textarea id="bkbc_cards_list" name="bkbc_cards_list" rows="5" class="bkbc-textarea"><?php echo esc_textarea( $cards_list ); ?></textarea>
                    <p class="description">Tuliskan kartu per baris dengan format: <code>Emoji/Icon | Angka/Judul | Keterangan</code><br>Contoh:<br><code>⚡ | 98.5% | Indeks Kepuasan Masyarakat</code><br><code>🏆 | Prima | Pelayanan Publik Terpadu</code><br><code>🎓 | 100+ | Program Diklat & Sertifikasi</code></p>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function save_meta_boxes( $post_id ) {
        if ( ! isset( $_POST['bkbc_meta_nonce'] ) || ! wp_verify_nonce( $_POST['bkbc_meta_nonce'], 'bkbc_save_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = array(
            '_bkbc_bg_type'          => 'bkbc_bg_type',
            '_bkbc_word_cloud_data'  => 'bkbc_word_cloud_data',
            '_bkbc_sub_badge'        => 'bkbc_sub_badge',
            '_bkbc_buttons_list'     => 'bkbc_buttons_list',
            '_bkbc_cards_list'       => 'bkbc_cards_list',
            '_bkbc_bg_url'           => 'bkbc_bg_url',
        );

        foreach ( $fields as $meta_key => $post_key ) {
            if ( isset( $_POST[$post_key] ) ) {
                if ( in_array( $meta_key, array( '_bkbc_word_cloud_data', '_bkbc_buttons_list', '_bkbc_cards_list' ), true ) ) {
                    update_post_meta( $post_id, $meta_key, sanitize_textarea_field( $_POST[$post_key] ) );
                } else {
                    update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[$post_key] ) );
                }
            }
        }
    }
}
