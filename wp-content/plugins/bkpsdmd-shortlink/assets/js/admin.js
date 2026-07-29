/**
 * BKPSDMD Short Link & QR Code — Admin JavaScript
 * Handles: Generate tab, modal, filter, copy, delete, custom slug, regenerate
 */
/* global bkslData, jQuery */

(function ($) {
    'use strict';

    const { ajaxUrl, nonce, strings } = bkslData;

    // ── State: post_id yang sedang diproses di modal ───────────────────────────
    let _currentPostId  = 0;
    let _currentSlug    = '';

    // ══════════════════════════════════════════════════════════════════════════
    //  HELPER: Clipboard
    // ══════════════════════════════════════════════════════════════════════════
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text).then(() => true).catch(() => false);
        }
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

    // ── Helper: show notice ────────────────────────────────────────────────────
    function showNotice(msg, type, $el) {
        const $n = $el || $('#bksl-admin-notice');
        $n.attr('class', 'bksl-notice ' + type).html(msg).fadeIn(200);
        setTimeout(() => $n.fadeOut(400), 3500);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  MODAL — buka / tutup
    // ══════════════════════════════════════════════════════════════════════════
    function openModal(data, postTitle) {
        const url  = data.short_url;
        const slug = data.slug;
        const qr   = data.qr;

        _currentPostId = data.post_id || _currentPostId;
        _currentSlug   = slug;

        // Isi konten modal
        $('#bksl-modal-post-title').text('📄 ' + postTitle);
        $('#bksl-modal-url').val(url);
        $('#bksl-modal-qr').attr('src', qr);
        $('#bksl-modal-dl-qr').attr({ href: qr, download: 'qr-' + slug + '.png' });
        $('#bksl-modal-slug').val(slug);

        // Share URLs
        const enc   = encodeURIComponent(url);
        const title = encodeURIComponent(postTitle);
        $('#bksl-modal-wa').attr('href', 'https://api.whatsapp.com/send?text=' + title + '%20' + enc);
        $('#bksl-modal-tw').attr('href', 'https://twitter.com/intent/tweet?text=' + title + '&url=' + enc);
        $('#bksl-modal-fb').attr('href', 'https://www.facebook.com/sharer/sharer.php?u=' + enc);

        // Reset slug notice
        $('#bksl-modal-slug-notice').hide();

        $('#bksl-result-modal').fadeIn(200);
        $('body').css('overflow', 'hidden');
    }

    function closeModal() {
        $('#bksl-result-modal').fadeOut(180);
        $('body').css('overflow', '');
    }

    // Tutup modal saat klik overlay atau tombol ✕
    $(document).on('click', '#bksl-modal-overlay, #bksl-modal-close', closeModal);

    // Tutup modal saat tekan Escape
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });

    // ══════════════════════════════════════════════════════════════════════════
    //  TAB GENERATE — Filter input (client-side)
    // ══════════════════════════════════════════════════════════════════════════
    let _filterType = 'all';

    function applyFilter() {
        const keyword = ($('#bksl-filter-input').val() || '').toLowerCase().trim();
        let visible   = 0;

        $('#bksl-post-list .bksl-post-item').each(function () {
            const $item  = $(this);
            const title  = $item.data('title') || '';
            const type   = $item.data('type')  || '';
            const matchK = !keyword || title.includes(keyword);
            const matchT = _filterType === 'all' || type === _filterType;

            if (matchK && matchT) {
                $item.removeClass('hidden');
                visible++;
            } else {
                $item.addClass('hidden');
            }
        });

        $('#bksl-no-result').toggle(visible === 0);
    }

    // Debounce filter input
    let _filterTimer;
    $(document).on('input', '#bksl-filter-input', function () {
        clearTimeout(_filterTimer);
        _filterTimer = setTimeout(applyFilter, 200);
    });

    // Filter type buttons
    $(document).on('click', '.bksl-tab-filter', function () {
        $('.bksl-tab-filter').removeClass('active');
        $(this).addClass('active');
        _filterType = $(this).data('type');
        applyFilter();
    });

    // ══════════════════════════════════════════════════════════════════════════
    //  TAB GENERATE — Klik tombol "✨ Generate" per item
    // ══════════════════════════════════════════════════════════════════════════
    $(document).on('click', '.bksl-btn-generate-item', function () {
        const $btn      = $(this);
        const postId    = $btn.data('post-id');
        const postTitle = $btn.data('post-title');

        $btn.html('<span class="bksl-spinner"></span>').prop('disabled', true);
        _currentPostId = postId;

        $.post(ajaxUrl, {
            action:  'bksl_generate',
            nonce,
            post_id: postId,
        })
        .done(function (res) {
            if (res.success) {
                // Simpan post_id di data agar modal bisa pakai
                res.data.post_id = postId;
                openModal(res.data, postTitle);

                // Hilangkan item dari daftar (sudah punya short link)
                const $item = $('#bksl-item-' + postId);
                $item.css({ transition: 'all 0.3s', opacity: 0, transform: 'translateX(20px)' });
                setTimeout(() => {
                    $item.remove();
                    // Update badge "Belum Dibuat" di header
                    const remaining = $('#bksl-post-list .bksl-post-item:not(.hidden)').length;
                    const $badge = $('.bksl-stat-card:last-child .bksl-stat-num');
                    $badge.text(remaining);
                }, 300);
            } else {
                showNotice('❌ ' + (res.data || strings.error), 'error');
                $btn.html('✨ Generate').prop('disabled', false);
            }
        })
        .fail(function () {
            showNotice('❌ ' + strings.error, 'error');
            $btn.html('✨ Generate').prop('disabled', false);
        });
    });

    // ══════════════════════════════════════════════════════════════════════════
    //  MODAL — Copy short URL
    // ══════════════════════════════════════════════════════════════════════════
    $(document).on('click', '#bksl-modal-copy', function () {
        const url  = $('#bksl-modal-url').val();
        const $btn = $(this);
        copyToClipboard(url).then(ok => {
            if (ok) {
                $btn.html('✅ Disalin!');
                setTimeout(() => $btn.html('📋 Salin'), 2000);
            }
        });
    });

    // ══════════════════════════════════════════════════════════════════════════
    //  MODAL — Generate Slug Baru
    // ══════════════════════════════════════════════════════════════════════════
    $(document).on('click', '#bksl-modal-regen', function () {
        const $btn = $(this);
        $btn.html('<span class="bksl-spinner"></span> Memperbarui...').prop('disabled', true);

        $.post(ajaxUrl, {
            action:  'bksl_regenerate',
            nonce,
            post_id: _currentPostId,
        })
        .done(function (res) {
            if (res.success) {
                res.data.post_id = _currentPostId;
                const postTitle = $('#bksl-modal-post-title').text().replace('📄 ', '');
                openModal(res.data, postTitle);
            } else {
                showNotice('❌ ' + (res.data || strings.error), 'error');
            }
        })
        .fail(function () {
            showNotice('❌ ' + strings.error, 'error');
        })
        .always(function () {
            $btn.html('🔄 Generate Slug Baru').prop('disabled', false);
        });
    });

    // ══════════════════════════════════════════════════════════════════════════
    //  MODAL — Simpan Custom Slug
    // ══════════════════════════════════════════════════════════════════════════
    $(document).on('click', '#bksl-modal-save-slug', function () {
        const $btn  = $(this);
        const slug  = $('#bksl-modal-slug').val().trim();
        const $note = $('#bksl-modal-slug-notice');

        if (!slug) {
            $note.attr('class', 'bksl-notice error').html('Masukkan slug terlebih dahulu.').show();
            return;
        }

        $btn.text('⏳').prop('disabled', true);

        $.post(ajaxUrl, {
            action:  'bksl_save_custom_slug',
            nonce,
            post_id: _currentPostId,
            slug,
        })
        .done(function (res) {
            if (res.success) {
                res.data.post_id = _currentPostId;
                const postTitle = $('#bksl-modal-post-title').text().replace('📄 ', '');
                openModal(res.data, postTitle);
                $note.attr('class', 'bksl-notice success').html('✅ ' + strings.saved).show();
                setTimeout(() => $note.fadeOut(), 3000);
            } else {
                $note.attr('class', 'bksl-notice error').html('❌ ' + (res.data || strings.error)).show();
            }
        })
        .fail(function () {
            $note.attr('class', 'bksl-notice error').html('❌ ' + strings.error).show();
        })
        .always(function () {
            $btn.text('Simpan').prop('disabled', false);
        });
    });

    // ══════════════════════════════════════════════════════════════════════════
    //  TAB KELOLA — Copy button
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

    // ══════════════════════════════════════════════════════════════════════════
    //  TAB KELOLA — Delete button
    // ══════════════════════════════════════════════════════════════════════════
    $(document).on('click', '.bksl-del-btn', function () {
        if (!window.confirm(strings.confirm_del)) return;
        const $btn = $(this);
        const id   = $btn.data('id');
        const $row = $('#bksl-row-' + id);

        $btn.text('⏳').prop('disabled', true);

        $.post(ajaxUrl, { action: 'bksl_delete', nonce, id })
        .done(function (res) {
            if (res.success) {
                $row.css({ transition: 'all 0.3s', opacity: 0, transform: 'translateX(20px)' });
                setTimeout(() => $row.remove(), 300);
                showNotice('🗑 Short link berhasil dihapus.', 'success', $('#bksl-admin-notice'));
            } else {
                showNotice('❌ ' + (res.data || strings.error), 'error', $('#bksl-admin-notice'));
                $btn.text('🗑').prop('disabled', false);
            }
        })
        .fail(function () {
            showNotice('❌ ' + strings.error, 'error', $('#bksl-admin-notice'));
            $btn.text('🗑').prop('disabled', false);
        });
    });

    // ══════════════════════════════════════════════════════════════════════════
    //  META BOX — Copy button (editor post/page)
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

    // ══════════════════════════════════════════════════════════════════════════
    //  META BOX — Regenerate Slug
    // ══════════════════════════════════════════════════════════════════════════
    function updateMetaBox(data) {
        $('#bksl-short-url').val(data.short_url);
        $('#bksl-qr-img').attr('src', data.qr);
        $('#bksl-download-qr').attr({ href: data.qr, download: 'qr-' + data.slug + '.png' });
        $('#bksl-custom-slug').val(data.slug);
        $('.bksl-click-count span').text('👆 ' + data.click_count + ' klik');
        const enc   = encodeURIComponent(data.short_url);
        const title = encodeURIComponent(document.title.split('‹')[0].trim());
        $('.bksl-wa').attr('href', 'https://api.whatsapp.com/send?text=' + title + '%20' + enc);
        $('.bksl-tw').attr('href', 'https://twitter.com/intent/tweet?text=' + title + '&url=' + enc);
        $('.bksl-fb').attr('href', 'https://www.facebook.com/sharer/sharer.php?u=' + enc);
    }

    $(document).on('click', '#bksl-regen-btn', function () {
        const $btn   = $(this);
        const postId = $('#bksl-metabox').data('post-id');
        $btn.html('<span class="bksl-spinner"></span> Memperbarui...').prop('disabled', true);
        $.post(ajaxUrl, { action: 'bksl_regenerate', nonce, post_id: postId })
        .done(res => {
            if (res.success) { updateMetaBox(res.data); showNotice('🔄 ' + strings.regenerated, 'success'); }
            else showNotice('❌ ' + (res.data || strings.error), 'error');
        })
        .fail(() => showNotice('❌ ' + strings.error, 'error'))
        .always(() => $btn.html('🔄 Generate Slug Baru').prop('disabled', false));
    });

    // ══════════════════════════════════════════════════════════════════════════
    //  META BOX — Save Custom Slug
    // ══════════════════════════════════════════════════════════════════════════
    $(document).on('click', '#bksl-save-slug', function () {
        const $btn   = $(this);
        const postId = $('#bksl-metabox').data('post-id');
        const slug   = $('#bksl-custom-slug').val().trim();
        if (!slug) { showNotice('❌ Masukkan slug.', 'error'); return; }
        $btn.text('⏳').prop('disabled', true);
        $.post(ajaxUrl, { action: 'bksl_save_custom_slug', nonce, post_id: postId, slug })
        .done(res => {
            if (res.success) { updateMetaBox(res.data); showNotice('✅ ' + strings.saved, 'success'); }
            else showNotice('❌ ' + (res.data || strings.error), 'error');
        })
        .fail(() => showNotice('❌ ' + strings.error, 'error'))
        .always(() => $btn.text('Simpan').prop('disabled', false));
    });

    // ══════════════════════════════════════════════════════════════════════════
    //  META BOX — Generate (belum punya link)
    // ══════════════════════════════════════════════════════════════════════════
    $(document).on('click', '#bksl-generate-btn', function () {
        const $btn   = $(this);
        const postId = $('#bksl-metabox').data('post-id');
        $btn.html('<span class="bksl-spinner"></span> ' + strings.generating).prop('disabled', true);
        $.post(ajaxUrl, { action: 'bksl_generate', nonce, post_id: postId })
        .done(res => {
            if (res.success) window.location.reload();
            else { showNotice('❌ ' + (res.data || strings.error), 'error'); $btn.html('✨ Buat Short Link').prop('disabled', false); }
        })
        .fail(() => { showNotice('❌ ' + strings.error, 'error'); $btn.html('✨ Buat Short Link').prop('disabled', false); });
    });

})(jQuery);
