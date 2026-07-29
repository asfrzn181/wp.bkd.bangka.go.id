<?php
/**
 * Meta Box — tampil di editor Post & Page
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'add_meta_boxes', 'bksl_register_meta_box' );
function bksl_register_meta_box() {
    $screens = [ 'post', 'page' ];
    foreach ( $screens as $screen ) {
        add_meta_box(
            'bksl-meta-box',
            '🔗 Short Link & QR Code',
            'bksl_render_meta_box',
            $screen,
            'side',
            'high'
        );
    }
}

function bksl_render_meta_box( $post ) {
    $row       = BKSL_DB::get_by_post_id( $post->ID );
    $has_link  = ! empty( $row );
    $short_url = $has_link ? home_url( '/' . $row->slug ) : '';
    $qr_b64    = $has_link ? BKSL_QRCode::generate_base64( $short_url, 5, 1 ) : '';
    $slug      = $has_link ? $row->slug : '';
    $clicks    = $has_link ? (int) $row->click_count : 0;

    // WhatsApp & Twitter share
    $wa_url  = $has_link ? 'https://api.whatsapp.com/send?text=' . rawurlencode( get_the_title( $post->ID ) . ' ' . $short_url ) : '#';
    $tw_url  = $has_link ? 'https://twitter.com/intent/tweet?text=' . rawurlencode( get_the_title( $post->ID ) ) . '&url=' . rawurlencode( $short_url ) : '#';
    $fb_url  = $has_link ? 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $short_url ) : '#';
    ?>
    <div id="bksl-metabox" data-post-id="<?php echo esc_attr( $post->ID ); ?>">

        <?php if ( $has_link ) : ?>
        <!-- ── Short URL Display ── -->
        <div class="bksl-url-wrap">
            <div class="bksl-url-label">Short Link</div>
            <div class="bksl-url-row">
                <input type="text" id="bksl-short-url" class="bksl-url-input"
                       value="<?php echo esc_attr( $short_url ); ?>" readonly>
                <button type="button" class="bksl-btn bksl-btn-copy" id="bksl-copy-btn" title="Salin link">
                    📋
                </button>
            </div>
            <div class="bksl-click-count">
                <span>👆 <?php echo esc_html( $clicks ); ?> klik</span>
            </div>
        </div>

        <!-- ── QR Code ── -->
        <div class="bksl-qr-wrap">
            <div class="bksl-qr-label">QR Code</div>
            <div class="bksl-qr-img-wrap">
                <img id="bksl-qr-img" src="<?php echo esc_attr( $qr_b64 ); ?>"
                     alt="QR Code" class="bksl-qr-img">
            </div>
            <a id="bksl-download-qr" href="<?php echo esc_attr( $qr_b64 ); ?>"
               download="qr-<?php echo esc_attr( $slug ); ?>.png"
               class="bksl-btn bksl-btn-download">
                ⬇ Download QR
            </a>
        </div>

        <!-- ── Share Buttons ── -->
        <div class="bksl-share-wrap">
            <div class="bksl-url-label">Bagikan ke Sosmed</div>
            <div class="bksl-share-row">
                <a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" class="bksl-share-btn bksl-wa" title="WhatsApp">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WA
                </a>
                <a href="<?php echo esc_url( $tw_url ); ?>" target="_blank" class="bksl-share-btn bksl-tw" title="Twitter/X">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.259 5.629 5.905-5.629zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    X
                </a>
                <a href="<?php echo esc_url( $fb_url ); ?>" target="_blank" class="bksl-share-btn bksl-fb" title="Facebook">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    FB
                </a>
            </div>
        </div>

        <!-- ── Custom Slug ── -->
        <div class="bksl-custom-wrap">
            <div class="bksl-url-label">Ganti Slug (3-10 char)</div>
            <div class="bksl-custom-row">
                <input type="text" id="bksl-custom-slug" class="bksl-custom-input"
                       value="<?php echo esc_attr( $slug ); ?>"
                       placeholder="contoh: A1b2c" maxlength="10">
                <button type="button" class="bksl-btn bksl-btn-save" id="bksl-save-slug">Simpan</button>
            </div>
        </div>

        <!-- ── Regenerate ── -->
        <div class="bksl-regen-wrap">
            <button type="button" class="bksl-btn bksl-btn-regen" id="bksl-regen-btn">
                🔄 Generate Slug Baru
            </button>
        </div>

        <?php else : ?>
        <!-- ── No link yet ── -->
        <div class="bksl-empty-wrap">
            <p class="bksl-empty-text">Short link belum dibuat.<br>Publish konten atau klik tombol di bawah.</p>
            <button type="button" class="bksl-btn bksl-btn-generate" id="bksl-generate-btn">
                ✨ Buat Short Link
            </button>
        </div>
        <?php endif; ?>

        <div id="bksl-notice" class="bksl-notice" style="display:none;"></div>
    </div>
    <?php
}
