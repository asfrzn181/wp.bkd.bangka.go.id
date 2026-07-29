<?php
/**
 * Meta Box — Custom fields per slide
 * Fields: subtitle, deskripsi, teks CTA, URL CTA, aktif/nonaktif
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BKBC_MetaBox {

    public static function register() {
        add_meta_box(
            'bkbc_slide_fields',
            '⚙️ Detail Slide',
            [ __CLASS__, 'render' ],
            'banner_slide',
            'normal',
            'high'
        );
    }

    public static function render( $post ) {
        wp_nonce_field( 'bkbc_save_slide', 'bkbc_nonce' );

        $subtitle    = get_post_meta( $post->ID, '_bkbc_subtitle',    true );
        $description = get_post_meta( $post->ID, '_bkbc_description', true );
        $cta_text    = get_post_meta( $post->ID, '_bkbc_cta_text',    true );
        $cta_url     = get_post_meta( $post->ID, '_bkbc_cta_url',     true );
        $active      = get_post_meta( $post->ID, '_bkbc_active',      true );
        $active      = $active === '' ? '1' : $active; // default aktif
        ?>
        <style>
            .bkbc-meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; padding:8px 0; }
            .bkbc-meta-grid.full { grid-template-columns:1fr; }
            .bkbc-meta-label { font-weight:600; font-size:12px; text-transform:uppercase; color:#555; margin-bottom:4px; display:block; }
            .bkbc-meta-input { width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:4px; font-size:14px; }
            .bkbc-meta-textarea { width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:4px; font-size:14px; height:80px; resize:vertical; }
            .bkbc-toggle-wrap { display:flex; align-items:center; gap:10px; }
            .bkbc-toggle-wrap input[type="checkbox"] { width:20px; height:20px; cursor:pointer; }
            .bkbc-tip { font-size:11px; color:#888; margin-top:4px; }
            .bkbc-section-title { font-size:13px; font-weight:700; color:#333; padding:12px 0 6px; border-top:1px solid #eee; margin-top:8px; }
        </style>

        <!-- Thumbnail hint -->
        <p class="bkbc-tip" style="margin-bottom:12px;">
            📸 <strong>Gambar Slide</strong>: Set lewat <em>Featured Image</em> di sidebar kanan.
            Ukuran ideal: 1920×700 px, ratio 16:5.
        </p>

        <!-- Status aktif -->
        <div class="bkbc-toggle-wrap" style="margin-bottom:16px;">
            <input type="checkbox" id="bkbc_active" name="bkbc_active" value="1"
                <?php checked( $active, '1' ); ?>>
            <label for="bkbc_active" style="font-size:14px;font-weight:600;">
                ✅ Slide Aktif (tampil di carousel)
            </label>
        </div>

        <!-- Subtitle -->
        <div class="bkbc-meta-grid full">
            <div>
                <label class="bkbc-meta-label" for="bkbc_subtitle">Subtitle / Tagline (opsional)</label>
                <input class="bkbc-meta-input" type="text" id="bkbc_subtitle" name="bkbc_subtitle"
                       value="<?php echo esc_attr( $subtitle ); ?>"
                       placeholder="misal: SELAMAT DATANG DI BKPSDMD">
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="bkbc-meta-grid full" style="margin-top:12px;">
            <div>
                <label class="bkbc-meta-label" for="bkbc_description">Deskripsi Singkat (opsional)</label>
                <textarea class="bkbc-meta-textarea" id="bkbc_description" name="bkbc_description"
                          placeholder="Teks penjelasan di bawah judul..."><?php echo esc_textarea( $description ); ?></textarea>
            </div>
        </div>

        <!-- CTA -->
        <p class="bkbc-section-title">Tombol CTA (opsional — kosongkan jika tidak perlu)</p>
        <div class="bkbc-meta-grid" style="margin-top:8px;">
            <div>
                <label class="bkbc-meta-label" for="bkbc_cta_text">Teks Tombol</label>
                <input class="bkbc-meta-input" type="text" id="bkbc_cta_text" name="bkbc_cta_text"
                       value="<?php echo esc_attr( $cta_text ); ?>"
                       placeholder="misal: Pelajari Lebih Lanjut">
            </div>
            <div>
                <label class="bkbc-meta-label" for="bkbc_cta_url">URL Tujuan</label>
                <input class="bkbc-meta-input" type="url" id="bkbc_cta_url" name="bkbc_cta_url"
                       value="<?php echo esc_attr( $cta_url ); ?>"
                       placeholder="https://...">
            </div>
        </div>
        <?php
    }

    public static function save( $post_id, $post ) {
        // Verifikasi nonce
        if ( ! isset( $_POST['bkbc_nonce'] ) || ! wp_verify_nonce( $_POST['bkbc_nonce'], 'bkbc_save_slide' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $fields = [
            '_bkbc_subtitle'    => sanitize_text_field( $_POST['bkbc_subtitle']    ?? '' ),
            '_bkbc_description' => sanitize_textarea_field( $_POST['bkbc_description'] ?? '' ),
            '_bkbc_cta_text'    => sanitize_text_field( $_POST['bkbc_cta_text']    ?? '' ),
            '_bkbc_cta_url'     => esc_url_raw( $_POST['bkbc_cta_url']             ?? '' ),
            '_bkbc_active'      => isset( $_POST['bkbc_active'] ) ? '1' : '0',
        ];

        foreach ( $fields as $key => $value ) {
            update_post_meta( $post_id, $key, $value );
        }
    }
}
