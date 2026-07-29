/**
 * BKPSDMD Banner Carousel — Admin JS
 * Handles: drag-and-drop reorder, toggle aktif, copy shortcode
 */
/* global bkbcAdmin, jQuery */
(function ($) {
    'use strict';

    // ── Drag-and-drop reorder ─────────────────────────────────────────────────
    var $sortable = $('#bkbc-sortable');
    if ( $sortable.length ) {
        $sortable.sortable({
            handle:      '.bkbc-drag-handle',
            placeholder: 'bkbc-slide-row ui-sortable-placeholder',
            axis:        'y',
            tolerance:   'pointer',
            update: function () {
                var order = [];
                $sortable.find('.bkbc-slide-row').each(function () {
                    order.push( $(this).data('id') );
                });
                $.post( bkbcAdmin.ajaxUrl, {
                    action: 'bkbc_reorder',
                    nonce:  bkbcAdmin.reorderNonce,
                    order:  JSON.stringify( order ),
                })
                .done(function (res) {
                    if ( res.success ) {
                        showNotice( '✅ Urutan slide berhasil disimpan.', 'success' );
                    }
                })
                .fail(function () {
                    showNotice( '❌ Gagal menyimpan urutan.', 'error' );
                });
            },
        });
    }

    // ── Toggle aktif / nonaktif ────────────────────────────────────────────────
    $(document).on('click', '.bkbc-btn-toggle', function () {
        var $btn   = $(this);
        var postId = $btn.data('id');

        $btn.text('⏳').prop('disabled', true);

        $.post( bkbcAdmin.ajaxUrl, {
            action:  'bkbc_toggle',
            nonce:   bkbcAdmin.toggleNonce,
            post_id: postId,
        })
        .done(function (res) {
            if ( res.success ) {
                var isActive = res.data.active === '1';
                var $row     = $btn.closest('.bkbc-slide-row');

                $row.toggleClass( 'is-active',   isActive );
                $row.toggleClass( 'is-inactive', ! isActive );

                $btn.text( isActive ? 'Nonaktifkan' : 'Aktifkan' ).prop('disabled', false);
                $btn.data('active', res.data.active);

                // Update badge
                var $badge = $row.find('.bkbc-status-badge:first-child');
                $badge
                    .attr('class', 'bkbc-status-badge ' + ( isActive ? 'active' : 'inactive' ))
                    .text( isActive ? '✅ Aktif' : '⏸ Nonaktif' );

                showNotice( isActive ? '✅ Slide diaktifkan.' : '⏸ Slide dinonaktifkan.', 'success' );
            }
        })
        .fail(function () {
            showNotice( '❌ Gagal mengubah status.', 'error' );
            $btn.text( $btn.data('active') === '1' ? 'Nonaktifkan' : 'Aktifkan' ).prop('disabled', false);
        });
    });

    // ── Salin shortcode ────────────────────────────────────────────────────────
    $(document).on('click', '.bkbc-copy-sc', function () {
        var $btn = $(this);
        var sc   = $btn.data('sc');
        if ( navigator.clipboard ) {
            navigator.clipboard.writeText(sc).then(function () {
                $btn.text('✅ Disalin!');
                setTimeout(function () { $btn.text('Salin'); }, 2000);
            });
        } else {
            var el = document.createElement('textarea');
            el.value = sc;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            $btn.text('✅ Disalin!');
            setTimeout(function () { $btn.text('Salin'); }, 2000);
        }
    });

    // ── Animasi card selection (Settings page) ─────────────────────────────────
    $(document).on('change', '.bkbc-anim-card input', function () {
        $('.bkbc-anim-card').removeClass('selected');
        $(this).closest('.bkbc-anim-card').addClass('selected');
    });

    // ── Helper: notice ─────────────────────────────────────────────────────────
    function showNotice( msg, type ) {
        var $n = $('#bkbc-notice');
        if ( ! $n.length ) return;
        $n.attr('class', 'bkbc-notice ' + type).html(msg).fadeIn(200);
        clearTimeout( $n.data('timer') );
        $n.data('timer', setTimeout(function () { $n.fadeOut(400); }, 3500));
    }

})(jQuery);
