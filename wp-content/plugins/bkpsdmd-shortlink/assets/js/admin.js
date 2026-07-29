/**
 * BKPSDMD Short Link & QR Code — Admin JavaScript
 */
/* global bkslData, jQuery */

(function ($) {
    'use strict';

    const { ajaxUrl, nonce, strings } = bkslData;

    // ── Helper: show notice ────────────────────────────────────────────────────
    function showNotice(msg, type, $wrap) {
        const $n = $wrap || $('#bksl-notice, #bksl-admin-notice').first();
        $n.attr('class', 'bksl-notice ' + type)
          .html(msg)
          .fadeIn(200);
        setTimeout(() => $n.fadeOut(400), 3500);
    }

    // ── Helper: copy to clipboard ─────────────────────────────────────────────
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text).then(() => true);
        }
        // Fallback
        const el = document.createElement('textarea');
        el.value = text;
        el.style.cssText = 'position:fixed;top:-9999px;left:-9999px';
        document.body.appendChild(el);
        el.focus();
        el.select();
        const ok = document.execCommand('copy');
        document.body.removeChild(el);
        return Promise.resolve(ok);
    }

    // ── Helper: update meta box UI after AJAX ──────────────────────────────────
    function updateMetaBox(data) {
        const $box = $('#bksl-metabox');

        // If metabox was in "empty" state, rebuild the short link block
        // (simplest approach: reload meta box via AJAX partial — here we update in-place)
        $('#bksl-short-url').val(data.short_url);
        $('#bksl-qr-img').attr('src', data.qr);
        $('#bksl-download-qr').attr({
            href:     data.qr,
            download: 'qr-' + data.slug + '.png',
        });
        $('#bksl-custom-slug').val(data.slug);
        $('.bksl-click-count span').text('👆 ' + data.click_count + ' klik');

        // Update share links
        const title = encodeURIComponent(document.title.replace(' ‹ ' + document.title.split(' ‹ ').pop(), ''));
        const url   = encodeURIComponent(data.short_url);
        $('.bksl-wa').attr('href', 'https://api.whatsapp.com/send?text=' + title + '%20' + url);
        $('.bksl-tw').attr('href', 'https://twitter.com/intent/tweet?text=' + title + '&url=' + url);
        $('.bksl-fb').attr('href', 'https://www.facebook.com/sharer/sharer.php?u=' + url);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  META BOX — Copy button
    // ══════════════════════════════════════════════════════════════════════════
    $(document).on('click', '#bksl-copy-btn', function () {
        const url = $('#bksl-short-url').val();
        copyToClipboard(url).then(ok => {
            if (ok) {
                showNotice('✅ ' + strings.copied, 'success');
                const $btn = $(this);
                $btn.text('✅');
                setTimeout(() => $btn.text('📋'), 2000);
            } else {
                showNotice('❌ ' + strings.copy_fail, 'error');
            }
        });
    });

    // ── META BOX — Regenerate slug ─────────────────────────────────────────────
    $(document).on('click', '#bksl-regen-btn', function () {
        const $btn   = $(this);
        const postId = $('#bksl-metabox').data('post-id');
        $btn.html('<span class="bksl-spinner"></span> Memperbarui...')
            .prop('disabled', true);

        $.post(ajaxUrl, {
            action:  'bksl_regenerate',
            nonce,
            post_id: postId,
        }).done(res => {
            if (res.success) {
                updateMetaBox(res.data);
                showNotice('🔄 ' + strings.regenerated, 'success');
            } else {
                showNotice('❌ ' + (res.data || strings.error), 'error');
            }
        }).fail(() => {
            showNotice('❌ ' + strings.error, 'error');
        }).always(() => {
            $btn.html('🔄 Generate Slug Baru').prop('disabled', false);
        });
    });

    // ── META BOX — Save custom slug ────────────────────────────────────────────
    $(document).on('click', '#bksl-save-slug', function () {
        const $btn   = $(this);
        const postId = $('#bksl-metabox').data('post-id');
        const slug   = $('#bksl-custom-slug').val().trim();

        if (!slug) {
            showNotice('❌ Masukkan slug terlebih dahulu.', 'error');
            return;
        }

        $btn.text('⏳').prop('disabled', true);

        $.post(ajaxUrl, {
            action:  'bksl_save_custom_slug',
            nonce,
            post_id: postId,
            slug,
        }).done(res => {
            if (res.success) {
                updateMetaBox(res.data);
                showNotice('✅ ' + strings.saved, 'success');
            } else {
                showNotice('❌ ' + (res.data || strings.error), 'error');
            }
        }).fail(() => {
            showNotice('❌ ' + strings.error, 'error');
        }).always(() => {
            $btn.text('Simpan').prop('disabled', false);
        });
    });

    // ── META BOX — Generate button (for posts without link yet) ───────────────
    $(document).on('click', '#bksl-generate-btn', function () {
        const $btn   = $(this);
        const postId = $('#bksl-metabox').data('post-id');
        $btn.html('<span class="bksl-spinner"></span> ' + strings.generating)
            .prop('disabled', true);

        $.post(ajaxUrl, {
            action:  'bksl_generate',
            nonce,
            post_id: postId,
        }).done(res => {
            if (res.success) {
                // Reload page to show full meta box UI
                window.location.reload();
            } else {
                showNotice('❌ ' + (res.data || strings.error), 'error');
                $btn.html('✨ Buat Short Link').prop('disabled', false);
            }
        }).fail(() => {
            showNotice('❌ ' + strings.error, 'error');
            $btn.html('✨ Buat Short Link').prop('disabled', false);
        });
    });

    // ══════════════════════════════════════════════════════════════════════════
    //  ADMIN PAGE — Copy button
    // ══════════════════════════════════════════════════════════════════════════
    $(document).on('click', '.bksl-copy-admin', function () {
        const url  = $(this).data('url');
        const $btn = $(this);
        copyToClipboard(url).then(ok => {
            if (ok) {
                $btn.text('✅');
                setTimeout(() => $btn.text('📋'), 2000);
                showNotice('✅ ' + strings.copied, 'success', $('#bksl-admin-notice'));
            }
        });
    });

    // ── ADMIN PAGE — Delete button ────────────────────────────────────────────
    $(document).on('click', '.bksl-del-btn', function () {
        if (!window.confirm(strings.confirm_del)) return;
        const $btn = $(this);
        const id   = $btn.data('id');
        const $row = $('#bksl-row-' + id);

        $btn.text('⏳').prop('disabled', true);

        $.post(ajaxUrl, {
            action: 'bksl_delete',
            nonce,
            id,
        }).done(res => {
            if (res.success) {
                $row.css('transition', 'all 0.3s')
                    .css({ opacity: 0, transform: 'translateX(20px)' });
                setTimeout(() => $row.remove(), 300);
                showNotice('🗑 Short link berhasil dihapus.', 'success', $('#bksl-admin-notice'));
            } else {
                showNotice('❌ ' + (res.data || strings.error), 'error', $('#bksl-admin-notice'));
                $btn.text('🗑').prop('disabled', false);
            }
        }).fail(() => {
            showNotice('❌ ' + strings.error, 'error', $('#bksl-admin-notice'));
            $btn.text('🗑').prop('disabled', false);
        });
    });

    // ── QR Hover zoom tooltip on admin page ───────────────────────────────────
    // (handled purely by CSS transform scale — no JS needed)

})(jQuery);
