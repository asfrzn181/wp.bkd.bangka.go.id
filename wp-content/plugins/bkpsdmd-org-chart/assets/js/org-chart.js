/**
 * org-chart.js — Frontend JavaScript
 * BKPSDMD Struktur Organisasi
 *
 * Fitur:
 * - Minimize / Maximize node (klik tombol toggle)
 * - Hover foto pegawai (popup)
 * - Zoom In / Out / Reset
 * - Expand All / Collapse All
 * - Drag canvas untuk scroll
 */
(function ($) {
    'use strict';

    var zoomLevel   = 1;
    var ZOOM_STEP   = 0.15;
    var ZOOM_MIN    = 0.4;
    var ZOOM_MAX    = 1.8;
    var $canvas, $popup, popupTimer;

    // =============================================
    //  INIT
    // =============================================
    function init() {
        $canvas = $('#bkpsdmd-org-canvas');
        $popup  = $('#bkpsdmd-photo-popup');

        if ( ! $canvas.length ) return;

        bindToggle();
        bindHoverPopup();
        bindControls();
        bindDragCanvas();
    }

    // =============================================
    //  MINIMIZE / MAXIMIZE
    // =============================================
    function bindToggle() {
        $(document).on('click', '.bkpsdmd-toggle-btn', function (e) {
            e.stopPropagation();
            var $btn  = $(this);
            var $li   = $btn.closest('.bkpsdmd-tree-node');
            var isCollapsed = $li.hasClass('is-collapsed');

            if ( isCollapsed ) {
                // Expand
                $li.removeClass('is-collapsed');
                $btn.find('.bkpsdmd-icon-minus').show();
                $btn.find('.bkpsdmd-icon-plus').hide();
                $btn.attr('title', 'Ciutkan');
                // Animasi fade-in anak
                $li.find('> .bkpsdmd-tree-children').hide().fadeIn(250);
            } else {
                // Collapse
                $li.find('> .bkpsdmd-tree-children').fadeOut(200, function () {
                    $li.addClass('is-collapsed');
                });
                $btn.find('.bkpsdmd-icon-minus').hide();
                $btn.find('.bkpsdmd-icon-plus').show();
                $btn.attr('title', 'Perluas');
            }
        });
    }

    // =============================================
    //  HOVER POPUP FOTO
    // =============================================
    function bindHoverPopup() {
        $(document).on('mouseenter', '.bkpsdmd-node-card', function (e) {
            var $card   = $(this);
            var jabatan = $card.data('jabatan') || '';
            var nama    = $card.data('nama')    || '';
            var nip     = $card.data('nip')     || '';
            var foto    = $card.data('foto')    || '';

            // Update isi popup
            $('#bkpsdmd-popup-img').attr('src', foto).attr('alt', nama);
            $('#bkpsdmd-popup-jabatan').text(jabatan);
            $('#bkpsdmd-popup-nama').text(nama);
            $('#bkpsdmd-popup-nip').text(nip ? 'NIP: ' + nip : '');

            clearTimeout(popupTimer);
            positionPopup(e);
            $popup.addClass('is-visible');
        });

        $(document).on('mousemove', '.bkpsdmd-node-card', function (e) {
            positionPopup(e);
        });

        $(document).on('mouseleave', '.bkpsdmd-node-card', function () {
            popupTimer = setTimeout(function () {
                $popup.removeClass('is-visible');
            }, 120);
        });

        // Jangan tutup kalau masuk ke popup
        $popup.on('mouseenter', function () {
            clearTimeout(popupTimer);
        }).on('mouseleave', function () {
            $popup.removeClass('is-visible');
        });
    }

    function positionPopup(e) {
        var pw = $popup.outerWidth()  || 220;
        var ph = $popup.outerHeight() || 260;
        var vw = $(window).width();
        var vh = $(window).height();
        var mx = e.clientX;
        var my = e.clientY;
        var offset = 16;

        var left = mx + offset;
        var top  = my + offset;

        // Jangan keluar dari viewport
        if ( left + pw > vw - 10 ) left = mx - pw - offset;
        if ( top  + ph > vh - 10 ) top  = my - ph - offset;
        if ( left < 10 ) left = 10;
        if ( top  < 10 ) top  = 10;

        $popup.css({ left: left + 'px', top: top + 'px' });
    }

    // =============================================
    //  KONTROL ZOOM & EXPAND/COLLAPSE ALL
    // =============================================
    function bindControls() {
        // Expand All
        $('#bkpsdmd-expand-all').on('click', function () {
            $('.bkpsdmd-tree-node.is-collapsed').each(function () {
                $(this).removeClass('is-collapsed');
                $(this).find('> .bkpsdmd-tree-children').show();
                $(this).find('.bkpsdmd-icon-minus').show();
                $(this).find('.bkpsdmd-icon-plus').hide();
            });
        });

        // Collapse All (kecuali level 0)
        $('#bkpsdmd-collapse-all').on('click', function () {
            $('.bkpsdmd-tree-node.has-children').not(':has(.bkpsdmd-node-level-0)').each(function () {
                // Cek apakah ini bukan node root
                if ( $(this).find('> .bkpsdmd-node-card .bkpsdmd-node-level-0').length ) return;
                $(this).addClass('is-collapsed');
                $(this).find('> .bkpsdmd-tree-children').hide();
                $(this).find('.bkpsdmd-icon-minus').hide();
                $(this).find('.bkpsdmd-icon-plus').show();
            });
        });

        // Zoom In
        $('#bkpsdmd-zoom-in').on('click', function () {
            zoomLevel = Math.min(zoomLevel + ZOOM_STEP, ZOOM_MAX);
            applyZoom();
        });

        // Zoom Out
        $('#bkpsdmd-zoom-out').on('click', function () {
            zoomLevel = Math.max(zoomLevel - ZOOM_STEP, ZOOM_MIN);
            applyZoom();
        });

        // Zoom Reset
        $('#bkpsdmd-zoom-reset').on('click', function () {
            zoomLevel = 1;
            applyZoom();
        });

        // Zoom dengan scroll mouse di dalam canvas
        $('.bkpsdmd-org-canvas-wrap').on('wheel', function (e) {
            if ( ! e.ctrlKey ) return; // hanya kalau Ctrl + scroll
            e.preventDefault();
            if ( e.originalEvent.deltaY < 0 ) {
                zoomLevel = Math.min(zoomLevel + ZOOM_STEP * 0.5, ZOOM_MAX);
            } else {
                zoomLevel = Math.max(zoomLevel - ZOOM_STEP * 0.5, ZOOM_MIN);
            }
            applyZoom();
        });
    }

    function applyZoom() {
        $canvas.css('transform', 'scale(' + zoomLevel.toFixed(2) + ')');
        // Adjust canvas wrap height berdasarkan zoom
        var originalH = $canvas[0].scrollHeight / zoomLevel;
        // Biarkan scroll natural
    }

    // =============================================
    //  DRAG CANVAS (pan/scroll)
    // =============================================
    function bindDragCanvas() {
        var $wrap  = $('.bkpsdmd-org-canvas-wrap');
        var isDragging = false;
        var startX, startY, scrollLeft, scrollTop;

        $wrap.on('mousedown', function (e) {
            // Jangan drag kalau klik pada kartu/tombol
            if ( $(e.target).closest('.bkpsdmd-toggle-btn, a, button').length ) return;
            isDragging = true;
            startX = e.pageX - $wrap[0].offsetLeft;
            startY = e.pageY - $wrap[0].offsetTop;
            scrollLeft = $wrap[0].scrollLeft;
            scrollTop  = $wrap[0].scrollTop;
            $wrap.css('cursor', 'grabbing');
        });

        $(document).on('mousemove', function (e) {
            if ( ! isDragging ) return;
            e.preventDefault();
            var x    = e.pageX - $wrap[0].offsetLeft;
            var y    = e.pageY - $wrap[0].offsetTop;
            var walkX = (x - startX) * 1.2;
            var walkY = (y - startY) * 1.2;
            $wrap[0].scrollLeft = scrollLeft - walkX;
            $wrap[0].scrollTop  = scrollTop  - walkY;
        });

        $(document).on('mouseup mouseleave', function () {
            isDragging = false;
            $wrap.css('cursor', 'grab');
        });
    }

    // =============================================
    //  DOMReady
    // =============================================
    $(document).ready(function () {
        init();
    });

})(jQuery);
