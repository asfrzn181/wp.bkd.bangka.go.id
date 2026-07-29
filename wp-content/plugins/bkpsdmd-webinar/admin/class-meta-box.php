<?php
/**
 * Meta Box — di editor webinar (CPT)
 * Field: tanggal, link zoom/youtube, pola nomor petikan, upload template docx
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_MetaBox {

    public static function init() {
        add_action( 'add_meta_boxes', [ __CLASS__, 'register' ] );
        add_action( 'save_post_webinar', [ __CLASS__, 'save' ], 10, 2 );
        // Form builder meta box
        add_action( 'add_meta_boxes', [ __CLASS__, 'register_form_builder' ] );
    }

    public static function register() {
        add_meta_box( 'wbr_webinar_detail', '⚙️ Detail Webinar', [ __CLASS__, 'render_detail' ], 'webinar', 'normal', 'high' );
    }

    public static function register_form_builder() {
        add_meta_box( 'wbr_form_builder', '📋 Form Builder', [ __CLASS__, 'render_form_builder' ], 'webinar', 'normal', 'default' );
    }

    // ── Render: Detail Webinar ────────────────────────────────────────────────
    public static function render_detail( $post ) {
        global $wpdb;
        wp_nonce_field( 'wbr_save_meta', 'wbr_meta_nonce' );

        $meta = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_meta WHERE post_id = %d", $post->ID
        ) );
        $m = (array) $meta;
        ?>
        <style>
            .wbr-meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
            .wbr-meta-full { grid-column:1/-1; }
            .wbr-meta-label { display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:#555; margin-bottom:4px; }
            .wbr-meta-input { width:100%; padding:7px 9px; border:1px solid #ddd; border-radius:4px; font-size:13px; }
            .wbr-tip { font-size:11px; color:#888; margin-top:3px; }
        </style>
        <div class="wbr-meta-grid">
            <div>
                <label class="wbr-meta-label">Tanggal & Waktu Mulai</label>
                <input type="datetime-local" name="start_datetime" class="wbr-meta-input"
                       value="<?php echo esc_attr( str_replace( ' ', 'T', $m['start_datetime'] ?? '' ) ); ?>">
            </div>
            <div>
                <label class="wbr-meta-label">Tanggal & Waktu Selesai</label>
                <input type="datetime-local" name="end_datetime" class="wbr-meta-input"
                       value="<?php echo esc_attr( str_replace( ' ', 'T', $m['end_datetime'] ?? '' ) ); ?>">
            </div>
            <div>
                <label class="wbr-meta-label">Link Zoom</label>
                <input type="url" name="zoom_link" class="wbr-meta-input"
                       value="<?php echo esc_attr( $m['zoom_link'] ?? '' ); ?>"
                       placeholder="https://zoom.us/j/...">
            </div>
            <div>
                <label class="wbr-meta-label">Link YouTube</label>
                <input type="url" name="youtube_link" class="wbr-meta-input"
                       value="<?php echo esc_attr( $m['youtube_link'] ?? '' ); ?>"
                       placeholder="https://youtube.com/watch?v=...">
            </div>
            <div class="wbr-meta-full">
                <label class="wbr-meta-label">Pola Nomor Petikan</label>
                <input type="text" name="cert_number_pattern" class="wbr-meta-input"
                       value="<?php echo esc_attr( $m['cert_number_pattern'] ?? 'PTKAN/{nomor}/{tahun}' ); ?>"
                       placeholder="PTKAN/{nomor}/{tahun}">
                <p class="wbr-tip">Variabel: <code>{nomor}</code> (urut, 3 digit), <code>{tahun}</code>, <code>{counter}</code></p>
            </div>
            <div>
                <label class="wbr-meta-label">Template SK Minut (.docx)</label>
                <input type="file" name="sk_template_file" accept=".docx">
                <?php if ( ! empty( $m['sk_template_file'] ) ) : ?>
                <p class="wbr-tip">Saat ini: <?php echo esc_html( $m['sk_template_file'] ); ?></p>
                <?php endif; ?>
            </div>
            <div>
                <label class="wbr-meta-label">Template Petikan (.docx)</label>
                <input type="file" name="petikan_template_file" accept=".docx">
                <?php if ( ! empty( $m['petikan_template_file'] ) ) : ?>
                <p class="wbr-tip">Saat ini: <?php echo esc_html( $m['petikan_template_file'] ); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    // ── Render: Form Builder ──────────────────────────────────────────────────
    public static function render_form_builder( $post ) {
        global $wpdb;
        $reg_fields = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_form_field
             WHERE webinar_id = %d AND form_type = 'registration' ORDER BY sort_order ASC",
            $post->ID
        ) );
        $att_fields = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_form_field
             WHERE webinar_id = %d AND form_type = 'attendance' ORDER BY sort_order ASC",
            $post->ID
        ) );
        ?>
        <div id="wbr-form-builder" data-webinar-id="<?php echo esc_attr( $post->ID ); ?>">
            <!-- Tabs -->
            <div class="wbr-fb-tabs">
                <button type="button" class="wbr-fb-tab active" data-form="registration">📝 Form Pendaftaran</button>
                <button type="button" class="wbr-fb-tab" data-form="attendance">✅ Form Absensi</button>
            </div>

            <?php foreach ( [ 'registration' => $reg_fields, 'attendance' => $att_fields ] as $type => $fields ) : ?>
            <div class="wbr-fb-panel <?php echo $type === 'registration' ? 'active' : ''; ?>"
                 data-form="<?php echo $type; ?>">
                <div class="wbr-fb-toolbar">
                    <button type="button" class="button wbr-add-field" data-form="<?php echo $type; ?>">
                        + Tambah Field
                    </button>
                    <button type="button" class="button button-primary wbr-save-fields" data-form="<?php echo $type; ?>">
                        💾 Simpan Field
                    </button>
                </div>
                <div class="wbr-field-list" id="wbr-fields-<?php echo $type; ?>">
                    <?php foreach ( $fields as $f ) : ?>
                    <?php self::render_field_row( $f ); ?>
                    <?php endforeach; ?>
                    <?php if ( empty( $fields ) ) : ?>
                    <p class="wbr-no-fields">Belum ada field. Klik "+ Tambah Field" untuk mulai.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private static function render_field_row( $f, $is_new = false ) {
        $f = (object) $f;
        $options_str = '';
        if ( $f->options ) {
            $opts = is_string( $f->options ) ? json_decode( $f->options, true ) : $f->options;
            $options_str = implode( "\n", (array) $opts );
        }
        ?>
        <div class="wbr-field-row" data-key="<?php echo esc_attr( $f->field_key ?? '' ); ?>">
            <div class="wbr-field-drag">☰</div>
            <div class="wbr-field-body">
                <div class="wbr-field-main-row">
                    <input type="text" class="wbr-field-label" placeholder="Label field"
                           value="<?php echo esc_attr( $f->label ?? '' ); ?>">
                    <input type="text" class="wbr-field-key" placeholder="key (a-z_)"
                           value="<?php echo esc_attr( $f->field_key ?? '' ); ?>">
                    <select class="wbr-field-type">
                        <?php foreach ( [ 'text','textarea','email','phone','radio','checkbox','select','date','file_upload','number' ] as $t ) : ?>
                        <option value="<?php echo $t; ?>" <?php selected( $f->field_type ?? 'text', $t ); ?>><?php echo $t; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="wbr-field-required-label">
                        <input type="checkbox" class="wbr-field-required" <?php checked( $f->is_required ?? 0, 1 ); ?>> Wajib
                    </label>
                    <label class="wbr-field-identity-label">
                        <input type="checkbox" class="wbr-field-identity" <?php checked( $f->is_identity_field ?? 0, 1 ); ?>> Identity
                    </label>
                    <button type="button" class="button wbr-remove-field">✕</button>
                </div>
                <textarea class="wbr-field-options" placeholder="Opsi (satu per baris) — untuk radio/checkbox/select"
                          style="display:<?php echo in_array( $f->field_type ?? '', ['radio','checkbox','select'] ) ? 'block' : 'none'; ?>;"
                          ><?php echo esc_textarea( $options_str ); ?></textarea>
            </div>
        </div>
        <?php
    }

    // ── Save (dari Publish/Update) ────────────────────────────────────────────
    public static function save( $post_id, $post ) {
        if ( ! isset( $_POST['wbr_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wbr_meta_nonce'], 'wbr_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Redirect ke AJAX handler (untuk handle file upload)
        $_POST['post_id'] = $post_id;
        $_POST['nonce']   = wp_create_nonce( 'wbr_admin_nonce' );
        WBR_Ajax::save_webinar_meta();
    }
}
