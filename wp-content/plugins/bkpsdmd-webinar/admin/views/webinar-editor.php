<?php
/**
 * Admin View — Dedicated Webinar Editor (5 Tab)
 * Diakses via: admin.php?page=wbr-webinar-edit&id={post_id}  (edit)
 *           atau admin.php?page=wbr-webinar-edit              (create new)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$post_id  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$is_edit  = $post_id > 0;

global $wpdb;

// ── Load existing data ─────────────────────────────────────────────────────
$post = $is_edit ? get_post( $post_id ) : null;
$meta = $is_edit ? $wpdb->get_row( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}webinar_meta WHERE post_id = %d", $post_id
) ) : null;

$thumb_url = $is_edit ? get_the_post_thumbnail_url( $post_id, 'medium' ) : '';
$thumb_id  = $is_edit ? get_post_thumbnail_id( $post_id ) : 0;

// ── Load form fields ───────────────────────────────────────────────────────
$reg_fields = $is_edit ? $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}webinar_form_field WHERE webinar_id = %d AND form_type = 'registration' ORDER BY sort_order ASC",
    $post_id
) ) : [];

$att_fields = $is_edit ? $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}webinar_form_field WHERE webinar_id = %d AND form_type = 'attendance' ORDER BY sort_order ASC",
    $post_id
) ) : [];

// ── Saved notice ───────────────────────────────────────────────────────────
$saved_msg = '';
if ( isset( $_GET['saved'] ) ) {
    $saved_msg = '<div class="notice notice-success is-dismissible"><p>✅ Webinar berhasil disimpan.</p></div>';
}
?>

<?php echo $saved_msg; ?>

<div class="wrap wbr-admin-wrap">
    <!-- Header -->
    <div class="wbr-admin-header">
        <div class="wbr-header-left">
            <span class="wbr-logo"><?php echo $is_edit ? '✏️' : '➕'; ?></span>
            <div>
                <h1 class="wbr-title"><?php echo $is_edit ? 'Edit Webinar' : 'Tambah Webinar Baru'; ?></h1>
                <p class="wbr-subtitle"><?php echo $is_edit ? esc_html( $post->post_title ) : 'Isi informasi webinar di bawah ini'; ?></p>
            </div>
        </div>
        <div class="wbr-header-actions">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wbr-webinars' ) ); ?>"
               class="wbr-btn wbr-btn-secondary">← Kembali ke Daftar</a>
            <button type="button" id="wbr-save-webinar" class="wbr-btn wbr-btn-primary">
                💾 Simpan Webinar
            </button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="wbr-editor-tabs">
        <button class="wbr-etab wbr-etab-active" data-tab="info">📝 1. Informasi Umum</button>
        <button class="wbr-etab" data-tab="schedule">⏰ 2. Jadwal &amp; Link</button>
        <button class="wbr-etab" data-tab="templates">📄 3. Template DOCX</button>
        <button class="wbr-etab" data-tab="form-reg">📋 4. Form Pendaftaran</button>
        <button class="wbr-etab" data-tab="form-att">✅ 5. Form Absensi</button>
    </div>

    <div id="wbr-save-notice" style="display:none;" class="notice notice-success"><p></p></div>

    <!-- ══════════════════════════════════════════════════════
         TAB 1: INFORMASI UMUM
    ══════════════════════════════════════════════════════ -->
    <div class="wbr-epanel wbr-epanel-active" data-tab="info">
        <div class="wbr-editor-grid">
            <!-- Kiri: Judul, Excerpt, Editor -->
            <div class="wbr-editor-main">
                <div class="wbr-card" style="padding:24px;">
                    <div class="wbr-eform-row">
                        <label class="wbr-elabel">Judul Webinar <span class="wbr-req">*</span></label>
                        <input type="text" id="wbr-post-title" class="wbr-einput wbr-title-input"
                               placeholder="Contoh: Webinar Manajemen ASN 2025..."
                               value="<?php echo esc_attr( $post->post_title ?? '' ); ?>">
                    </div>
                    <div class="wbr-eform-row">
                        <label class="wbr-elabel">Ringkasan / Excerpt</label>
                        <textarea id="wbr-post-excerpt" class="wbr-einput" rows="2"
                                  placeholder="Deskripsi singkat 1-2 kalimat..."><?php echo esc_textarea( $post->post_excerpt ?? '' ); ?></textarea>
                    </div>
                    <div class="wbr-eform-row">
                        <label class="wbr-elabel">Deskripsi / Materi Webinar</label>
                        <div id="wbr-post-content-wrap">
                            <?php
                            $editor_content = $post->post_content ?? '';
                            wp_editor( $editor_content, 'wbr_post_content', [
                                'textarea_name' => 'wbr_post_content',
                                'textarea_rows' => 14,
                                'media_buttons' => true,
                                'teeny'         => false,
                                'quicktags'     => true,
                            ] );
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kanan: Gambar Utama -->
            <div class="wbr-editor-sidebar">
                <div class="wbr-card" style="padding:20px;">
                    <h3 class="wbr-card-title">🖼 Gambar Utama</h3>
                    <div id="wbr-thumbnail-wrap">
                        <?php if ( $thumb_url ) : ?>
                        <img src="<?php echo esc_url( $thumb_url ); ?>" id="wbr-thumb-preview"
                             style="width:100%;height:160px;object-fit:cover;border-radius:8px;margin-bottom:10px;">
                        <?php else : ?>
                        <div id="wbr-thumb-placeholder"
                             style="width:100%;height:160px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;border:2px dashed #e2e8f0;color:#94a3b8;font-size:14px;">
                            Belum ada gambar
                        </div>
                        <?php endif; ?>
                        <input type="hidden" id="wbr-thumbnail-id" value="<?php echo esc_attr( $thumb_id ); ?>">
                        <button type="button" id="wbr-set-thumbnail" class="wbr-btn wbr-btn-secondary" style="width:100%;margin-bottom:6px;">
                            📷 Pilih Gambar
                        </button>
                        <?php if ( $thumb_id ) : ?>
                        <button type="button" id="wbr-remove-thumbnail" class="wbr-btn wbr-btn-danger" style="width:100%;">
                            🗑 Hapus Gambar
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="wbr-card" style="padding:20px;margin-top:16px;">
                    <h3 class="wbr-card-title">📢 Status Publikasi</h3>
                    <div class="wbr-eform-row">
                        <select id="wbr-post-status" class="wbr-einput">
                            <option value="publish" <?php selected( $post->post_status ?? 'publish', 'publish' ); ?>>✅ Dipublikasikan</option>
                            <option value="draft" <?php selected( $post->post_status ?? '', 'draft' ); ?>>📝 Draft</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         TAB 2: JADWAL & LINK
    ══════════════════════════════════════════════════════ -->
    <div class="wbr-epanel" data-tab="schedule">
        <div class="wbr-card" style="padding:28px;max-width:700px;">
            <div class="wbr-eform-grid-2">
                <div class="wbr-eform-row">
                    <label class="wbr-elabel">⏰ Tanggal &amp; Jam Mulai <span class="wbr-req">*</span></label>
                    <input type="datetime-local" id="wbr-start-datetime" class="wbr-einput"
                           value="<?php echo esc_attr( $meta ? substr( str_replace( ' ', 'T', $meta->start_datetime ), 0, 16 ) : '' ); ?>">
                </div>
                <div class="wbr-eform-row">
                    <label class="wbr-elabel">⏰ Tanggal &amp; Jam Selesai <span class="wbr-req">*</span></label>
                    <input type="datetime-local" id="wbr-end-datetime" class="wbr-einput"
                           value="<?php echo esc_attr( $meta ? substr( str_replace( ' ', 'T', $meta->end_datetime ), 0, 16 ) : '' ); ?>">
                </div>
            </div>
            <div class="wbr-eform-row">
                <label class="wbr-elabel">💻 Link Zoom Meeting</label>
                <input type="url" id="wbr-zoom-link" class="wbr-einput"
                       placeholder="https://zoom.us/j/..."
                       value="<?php echo esc_attr( $meta->zoom_link ?? '' ); ?>">
                <small class="wbr-ehelp">Hanya tampil pada saat webinar berlangsung sesuai jadwal.</small>
            </div>
            <div class="wbr-eform-row">
                <label class="wbr-elabel">▶ Link YouTube Live / Rekaman</label>
                <input type="url" id="wbr-youtube-link" class="wbr-einput"
                       placeholder="https://youtube.com/watch?v=..."
                       value="<?php echo esc_attr( $meta->youtube_link ?? '' ); ?>">
            </div>
            <div class="wbr-eform-row">
                <label class="wbr-elabel">🕒 Jam Pelajaran (JP)</label>
                <input type="number" id="wbr-jam-pelajaran" class="wbr-einput" min="0" step="1"
                       placeholder="Contoh: 4"
                       value="<?php echo esc_attr( $meta->jam_pelajaran ?? '0' ); ?>">
                <small class="wbr-ehelp">Jumlah jam pelajaran yang akan tercetak di sertifikat.</small>
            </div>
            <div class="wbr-eform-row">
                <label class="wbr-elabel">🏆 Pola Nomor Sertifikat (Petikan)</label>
                <input type="text" id="wbr-cert-pattern" class="wbr-einput"
                       placeholder="PTKAN/{nomor}/{tahun}"
                       value="<?php echo esc_attr( $meta->cert_number_pattern ?? 'PTKAN/{nomor}/{tahun}' ); ?>">
                <small class="wbr-ehelp">Variabel yang tersedia: <code>{nomor}</code> (urut 3 digit), <code>{tahun}</code></small>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         TAB 3: TEMPLATE DOCX
    ══════════════════════════════════════════════════════ -->
    <div class="wbr-epanel" data-tab="templates">
        <div class="wbr-card" style="padding:28px;max-width:700px;">
            <p style="color:#64748b;font-size:13px;margin-bottom:20px;">
                Upload file <code>.docx</code> berisi placeholder <code>${variable}</code>. Placeholder yang tersedia untuk masing-masing template tercantum di bawah.
            </p>

            <div class="wbr-template-box">
                <h3 class="wbr-template-title">📄 Template SK Minut</h3>
                <div class="wbr-template-vars">
                    Placeholder tersedia: <code>${sk_number}</code> <code>${sk_date}</code> <code>${nama_webinar}</code>
                    <code>${tanggal_pelaksanaan}</code> <code>${jam_mulai}</code> <code>${jam_selesai}</code> <code>${jam_pelajaran}</code>
                    <code>${jumlah_peserta}</code> <code>${daftar_peserta}</code> <code>${signing_official}</code>
                </div>
                <div class="wbr-template-upload">
                    <label for="wbr-sk-template" class="wbr-btn wbr-btn-secondary">📁 Pilih File .docx</label>
                    <input type="file" id="wbr-sk-template" name="wbr_sk_template" accept=".docx" style="display:none;">
                    <?php if ( $meta && $meta->sk_template_file ) : ?>
                    <span class="wbr-template-current">📄 File aktif: <strong><?php echo esc_html( basename( $meta->sk_template_file ) ); ?></strong></span>
                    <?php else : ?>
                    <span class="wbr-template-current" style="color:#94a3b8;">Belum ada template — sistem akan pakai template default.</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="wbr-template-box" style="margin-top:20px;">
                <h3 class="wbr-template-title">🏆 Template Petikan Sertifikat</h3>
                <div class="wbr-template-vars">
                    Placeholder tersedia: <code>${petikan_number}</code> <code>${nama_peserta}</code>
                    <code>${email_peserta}</code> <code>${jabatan}</code> <code>${instansi}</code>
                    <code>${nama_webinar}</code> <code>${tanggal_pelaksanaan}</code> <code>${jam_pelajaran}</code>
                    <code>${sk_number}</code> <code>${sk_date}</code> <code>${signing_official}</code>
                    <code>${qr_url}</code> + <code>${qr_image}</code> (untuk embed QR gambar)
                </div>
                <div class="wbr-template-upload">
                    <label for="wbr-petikan-template" class="wbr-btn wbr-btn-secondary">📁 Pilih File .docx</label>
                    <input type="file" id="wbr-petikan-template" name="wbr_petikan_template" accept=".docx" style="display:none;">
                    <?php if ( $meta && $meta->petikan_template_file ) : ?>
                    <span class="wbr-template-current">📄 File aktif: <strong><?php echo esc_html( basename( $meta->petikan_template_file ) ); ?></strong></span>
                    <?php else : ?>
                    <span class="wbr-template-current" style="color:#94a3b8;">Belum ada template — sistem akan pakai template default.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         TAB 4: FORM PENDAFTARAN (opsional)
    ══════════════════════════════════════════════════════ -->
    <div class="wbr-epanel" data-tab="form-reg">
        <div class="wbr-card" style="padding:24px;">
            <div class="wbr-fb-toolbar">
                <button type="button" class="wbr-add-field wbr-btn wbr-btn-secondary" data-form="registration">
                    + Tambah Field
                </button>
                <button type="button" class="wbr-save-fields wbr-btn wbr-btn-primary" data-form="registration" data-webinar-id="<?php echo esc_attr( $post_id ); ?>">
                    💾 Simpan Field Pendaftaran
                </button>
            </div>
            <p style="color:#64748b;font-size:12px;margin-bottom:16px;">
                ℹ️ Form pendaftaran bersifat <strong>opsional</strong>. Peserta yang tidak daftar tetap dapat hadir melalui form absensi walk-in.
            </p>
            <div class="wbr-field-list" id="wbr-fields-registration">
                <?php if ( empty( $reg_fields ) ) : ?>
                <p class="wbr-no-fields">Belum ada field. Klik "Tambah Field" untuk mulai membangun form pendaftaran.</p>
                <?php else : ?>
                <?php foreach ( $reg_fields as $f ) : ?>
                <?php include __DIR__ . '/partials/field-row.php'; ?>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         TAB 5: FORM ABSENSI (wajib diisi oleh peserta)
    ══════════════════════════════════════════════════════ -->
    <div class="wbr-epanel" data-tab="form-att">
        <div class="wbr-card" style="padding:24px;">
            <div class="wbr-fb-toolbar">
                <button type="button" class="wbr-add-field wbr-btn wbr-btn-secondary" data-form="attendance">
                    + Tambah Field
                </button>
                <button type="button" class="wbr-save-fields wbr-btn wbr-btn-primary" data-form="attendance" data-webinar-id="<?php echo esc_attr( $post_id ); ?>">
                    💾 Simpan Field Absensi
                </button>
            </div>
            <p style="color:#64748b;font-size:12px;margin-bottom:16px;">
                ℹ️ Tandai <strong>🔒 Identity Field</strong> untuk field yang otomatis terisi dari data pendaftaran (jika peserta sudah daftar sebelumnya) dan tidak dapat diubah saat absensi.
            </p>
            <div class="wbr-field-list" id="wbr-fields-attendance">
                <?php if ( empty( $att_fields ) ) : ?>
                <p class="wbr-no-fields">Belum ada field. Klik "Tambah Field" untuk mulai membangun form absensi.</p>
                <?php else : ?>
                <?php foreach ( $att_fields as $f ) : ?>
                <?php include __DIR__ . '/partials/field-row.php'; ?>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- .wbr-admin-wrap -->

<!-- Hidden: post_id untuk AJAX save -->
<input type="hidden" id="wbr-post-id" value="<?php echo esc_attr( $post_id ); ?>">

<style>
/* ── Webinar Editor Styles ─── */
.wbr-editor-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 2px solid rgba(255,255,255,0.1);
    margin: 0 0 20px;
    overflow-x: auto;
    background: var(--wbr-surface, #1a1a2e);
    border-radius: var(--wbr-radius, 12px) var(--wbr-radius, 12px) 0 0;
    padding: 12px 16px 0;
}
.wbr-etab {
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 700;
    color: var(--wbr-text-muted, #94a3b8);
    cursor: pointer;
    font-family: var(--wbr-font, inherit);
    white-space: nowrap;
    transition: color 0.2s, border-color 0.2s;
}
.wbr-etab:hover { color: #fff; }
.wbr-etab.wbr-etab-active {
    color: var(--wbr-primary, #6366f1);
    border-bottom-color: var(--wbr-primary, #6366f1);
}
.wbr-epanel { display: none; }
.wbr-epanel.wbr-epanel-active { display: block; }

.wbr-editor-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
    align-items: start;
}
@media(max-width:900px){ .wbr-editor-grid { grid-template-columns: 1fr; } }

.wbr-eform-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
@media(max-width:600px){ .wbr-eform-grid-2 { grid-template-columns: 1fr; } }

.wbr-eform-row { margin-bottom: 18px; }
.wbr-eform-row:last-child { margin-bottom: 0; }
.wbr-elabel {
    display: block;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--wbr-text-muted, #94a3b8);
    margin-bottom: 6px;
}
.wbr-req { color: #ef4444; }
.wbr-einput {
    display: block;
    width: 100%;
    padding: 10px 14px;
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    font-size: 13px;
    font-family: var(--wbr-font, inherit);
    color: var(--wbr-text, #e2e8f0);
    box-sizing: border-box;
}
.wbr-einput:focus {
    outline: none;
    border-color: var(--wbr-primary, #6366f1);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
}
.wbr-title-input { font-size: 18px !important; font-weight: 700 !important; }
.wbr-ehelp { font-size: 11px; color: var(--wbr-text-muted, #94a3b8); margin-top: 4px; display: block; }
.wbr-ehelp code { background: rgba(255,255,255,0.1); padding: 1px 4px; border-radius: 4px; font-size: 11px; }

/* Template boxes */
.wbr-template-box {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;
    padding: 18px;
}
.wbr-template-title { font-size: 15px; font-weight: 700; color: #fff; margin: 0 0 10px; }
.wbr-template-vars {
    font-size: 11px;
    color: var(--wbr-text-muted, #94a3b8);
    margin-bottom: 14px;
    line-height: 1.8;
}
.wbr-template-vars code {
    background: rgba(99,102,241,0.2);
    color: #a5b4fc;
    padding: 1px 6px;
    border-radius: 4px;
    margin: 0 2px;
}
.wbr-template-upload { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.wbr-template-current { font-size: 12px; color: #6ee7b7; }

/* WP Editor override in dark context */
#wbr-post-content-wrap .wp-editor-area {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    border-radius: 0 0 6px 6px;
}
</style>

<script>
(function($) {
    'use strict';

    // ── Tab switching ─────────────────────────────────────────────────────────
    $('.wbr-etab').on('click', function() {
        var t = $(this).data('tab');
        $('.wbr-etab').removeClass('wbr-etab-active');
        $(this).addClass('wbr-etab-active');
        $('.wbr-epanel').removeClass('wbr-epanel-active');
        $('.wbr-epanel[data-tab="' + t + '"]').addClass('wbr-epanel-active');
    });

    // ── Media picker (Featured Image) ─────────────────────────────────────────
    var _mediaFrame;
    $('#wbr-set-thumbnail').on('click', function() {
        if (_mediaFrame) { _mediaFrame.open(); return; }
        _mediaFrame = wp.media({ title: 'Pilih Gambar Utama', button: { text: 'Gunakan Gambar Ini' }, multiple: false });
        _mediaFrame.on('select', function() {
            var att = _mediaFrame.state().get('selection').first().toJSON();
            $('#wbr-thumbnail-id').val(att.id);
            var src = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
            $('#wbr-thumb-placeholder').remove();
            if ($('#wbr-thumb-preview').length) {
                $('#wbr-thumb-preview').attr('src', src);
            } else {
                $('#wbr-thumbnail-wrap').prepend('<img src="' + src + '" id="wbr-thumb-preview" style="width:100%;height:160px;object-fit:cover;border-radius:8px;margin-bottom:10px;">');
            }
        });
        _mediaFrame.open();
    });

    $('#wbr-remove-thumbnail').on('click', function() {
        $('#wbr-thumbnail-id').val('');
        $('#wbr-thumb-preview').remove();
        if (!$('#wbr-thumb-placeholder').length) {
            $('#wbr-thumbnail-wrap').prepend('<div id="wbr-thumb-placeholder" style="width:100%;height:160px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;border:2px dashed #e2e8f0;color:#94a3b8;font-size:14px;">Belum ada gambar</div>');
        }
        $(this).remove();
    });

    // ── Save Webinar (Tab 1-3) ────────────────────────────────────────────────
    $('#wbr-save-webinar').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Menyimpan...');

        // Ambil konten dari TinyMCE jika aktif
        var content = '';
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('wbr_post_content')) {
            content = tinyMCE.get('wbr_post_content').getContent();
        } else {
            content = $('#wbr_post_content').val();
        }

        var fd = new FormData();
        fd.append('action', 'wbr_save_webinar');
        fd.append('nonce', wbrAdmin.nonce);
        fd.append('post_id', $('#wbr-post-id').val());
        fd.append('post_title', $('#wbr-post-title').val());
        fd.append('post_excerpt', $('#wbr-post-excerpt').val());
        fd.append('post_content', content);
        fd.append('post_status', $('#wbr-post-status').val());
        fd.append('thumbnail_id', $('#wbr-thumbnail-id').val());
        fd.append('start_datetime', $('#wbr-start-datetime').val());
        fd.append('end_datetime', $('#wbr-end-datetime').val());
        fd.append('zoom_link', $('#wbr-zoom-link').val());
        fd.append('youtube_link', $('#wbr-youtube-link').val());
        fd.append('jam_pelajaran', $('#wbr-jam-pelajaran').val());
        fd.append('cert_number_pattern', $('#wbr-cert-pattern').val());

        // Template files
        var skFile = $('#wbr-sk-template')[0].files[0];
        var petFile = $('#wbr-petikan-template')[0].files[0];
        if (skFile)  fd.append('sk_template', skFile);
        if (petFile) fd.append('petikan_template', petFile);

        $.ajax({
            url: wbrAdmin.ajaxUrl,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    var notice = $('#wbr-save-notice');
                    notice.find('p').text('✅ Webinar berhasil disimpan!');
                    notice.show();
                    setTimeout(function() { notice.hide(); }, 3000);

                    // Update post_id jika create baru
                    if (res.data && res.data.post_id && !$('#wbr-post-id').val()) {
                        $('#wbr-post-id').val(res.data.post_id);
                        // Update URL tanpa reload
                        var newUrl = window.location.href + '&id=' + res.data.post_id;
                        if (window.history && window.history.pushState) {
                            window.history.pushState({}, '', newUrl);
                        }
                    }
                } else {
                    alert('❌ Gagal menyimpan: ' + (res.data || 'Error tidak diketahui'));
                }
            },
            error: function() {
                alert('❌ Error koneksi. Coba lagi.');
            },
            complete: function() {
                $btn.prop('disabled', false).text('💾 Simpan Webinar');
            }
        });
    });

})(jQuery);
</script>
