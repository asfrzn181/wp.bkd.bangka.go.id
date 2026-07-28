/**
 * Blog Grid Filter - blog-filter.js
 * AJAX filtering — query hanya berjalan saat tombol Cari diklik / Enter
 * Tema: career-development / BKPSDMD Bangka
 */
(function ($) {
    'use strict';

    var filterState = {
        keyword:  '',
        category: '',
        dateFrom: '',
        dateTo:   '',
        page:     1,
        isLoading: false,
    };

    // ---- Init ----
    function initBlogFilter() {
        var $wrap = $('.bkpsdmd-blog-filter-wrap');
        if (!$wrap.length) return;

        initDatePickers($wrap);
        initSelect2($wrap);
        bindEvents($wrap);
    }

    // ---- Init Flatpickr ----
    function initDatePickers($wrap) {
        var commonOpts = {
            dateFormat: 'd/m/Y',
            locale: (typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.id) ? 'id' : 'default',
            allowInput: false,
            disableMobile: true,
            monthSelectorType: 'static',
        };

        var fpFrom = flatpickr($wrap.find('.bkpsdmd-date-from')[0], $.extend({}, commonOpts, {
            placeholder: 'Dari tanggal',
            onReady: function (_, __, fp) {
                $(fp.calendarContainer).addClass('bkpsdmd-fp');
            },
            onChange: function (dates, dateStr) {
                filterState.dateFrom = dateStr;
                if (fpTo && dates[0]) {
                    fpTo.set('minDate', dates[0]);
                }
            },
            onClear: function () {
                filterState.dateFrom = '';
                if (fpTo) fpTo.set('minDate', null);
            },
        }));

        var fpTo = flatpickr($wrap.find('.bkpsdmd-date-to')[0], $.extend({}, commonOpts, {
            placeholder: 'Sampai tanggal',
            onReady: function (_, __, fp) {
                $(fp.calendarContainer).addClass('bkpsdmd-fp');
            },
            onChange: function (dates, dateStr) {
                filterState.dateTo = dateStr;
                if (fpFrom && dates[0]) {
                    fpFrom.set('maxDate', dates[0]);
                }
            },
            onClear: function () {
                filterState.dateTo = '';
                if (fpFrom) fpFrom.set('maxDate', null);
            },
        }));

        // Simpan referensi flatpickr di wrap
        $wrap.data('fpFrom', fpFrom);
        $wrap.data('fpTo', fpTo);
    }

    // ---- Init Select2 ----
    function initSelect2($wrap) {
        var $sel = $wrap.find('.bkpsdmd-cat-select');
        if (!$sel.length || typeof $.fn.select2 === 'undefined') return;

        $sel.select2({
            placeholder: '— Semua Kategori —',
            allowClear: true,
            width: '100%',
            dropdownParent: $wrap,
            minimumResultsForSearch: 6,
        });
    }

    // ---- Bind Events ----
    function bindEvents($wrap) {

        // Enter di keyword → trigger Cari
        $wrap.on('keydown', '.bkpsdmd-keyword-input', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                triggerSearch($wrap);
            }
        });

        // Tombol Cari
        $wrap.on('click', '#bkpsdmd-search-btn', function () {
            triggerSearch($wrap);
        });

        // Tombol Reset
        $wrap.on('click', '#bkpsdmd-reset-filter', function () {
            resetFilters($wrap);
        });

        // Pagination
        $wrap.on('click', '.bkpsdmd-page-btn', function () {
            if ($(this).hasClass('active') || filterState.isLoading) return;
            filterState.page = parseInt($(this).data('page'), 10) || 1;
            fetchPosts($wrap);
            $('html, body').animate({
                scrollTop: $wrap.find('.bkpsdmd-posts-grid').offset().top - 100
            }, 350);
        });
    }

    // ---- Trigger search (reset ke halaman 1) ----
    function triggerSearch($wrap) {
        collectFilters($wrap);
        filterState.page = 1;
        fetchPosts($wrap);
    }

    // ---- Kumpulkan nilai filter ----
    function collectFilters($wrap) {
        filterState.keyword  = $.trim($wrap.find('.bkpsdmd-keyword-input').val()) || '';
        filterState.category = $wrap.find('.bkpsdmd-cat-select').val() || '';
        filterState.dateFrom = filterState.dateFrom || $wrap.find('.bkpsdmd-date-from').val() || '';
        filterState.dateTo   = filterState.dateTo   || $wrap.find('.bkpsdmd-date-to').val()   || '';
    }

    // ---- AJAX fetch ----
    function fetchPosts($wrap) {
        if (filterState.isLoading) return;
        if (typeof bkpsdmdFilter === 'undefined') {
            console.error('[BlogFilter] bkpsdmdFilter tidak terdefinisi.');
            return;
        }

        var postsPerPage = parseInt($wrap.data('posts-per-page'), 10) || 6;
        var nonce        = $wrap.data('nonce') || '';

        filterState.isLoading = true;
        setLoadingState($wrap, true);

        $.ajax({
            url:  bkpsdmdFilter.ajaxurl,
            type: 'POST',
            data: {
                action:         bkpsdmdFilter.action,
                nonce:          nonce,
                posts_per_page: postsPerPage,
                paged:          filterState.page,
                keyword:        filterState.keyword,
                category:       filterState.category,
                date_from:      filterState.dateFrom,
                date_to:        filterState.dateTo,
            },
            success: function (response) {
                if (response && response.success) {
                    updateGrid($wrap, response.data);
                    updateResultBar($wrap, response.data.total_posts);
                } else {
                    console.warn('[BlogFilter] Error:', response);
                }
            },
            error: function (xhr, status, err) {
                console.error('[BlogFilter] AJAX error:', status, err);
            },
            complete: function () {
                filterState.isLoading = false;
                setLoadingState($wrap, false);
            }
        });
    }

    // ---- Update grid + pagination ----
    function updateGrid($wrap, data) {
        var $grid       = $wrap.find('#bkpsdmd-posts-grid');
        var $pagination = $wrap.find('#bkpsdmd-pagination');

        $grid.stop(true).fadeOut(120, function () {
            $grid.html(data.html || '').fadeIn(180);
        });
        $pagination.html(data.pagination || '');
    }

    // ---- Result bar ----
    function updateResultBar($wrap, total) {
        var $bar    = $wrap.find('#bkpsdmd-result-bar');
        var $info   = $wrap.find('#bkpsdmd-result-info');
        var $active = $wrap.find('#bkpsdmd-active-filters');
        var tags    = [];

        if (parseInt(total, 10) === 0) {
            $info.html('<span class="bkpsdmd-info-empty">Tidak ada artikel ditemukan</span>');
        } else {
            $info.html('<strong>' + total + '</strong> artikel ditemukan');
        }

        // Tampilkan tag filter aktif
        if (filterState.keyword)  tags.push('<span class="bkpsdmd-ftag">🔍 ' + $('<div>').text(filterState.keyword).html() + '</span>');
        if (filterState.dateFrom) tags.push('<span class="bkpsdmd-ftag">📅 ' + filterState.dateFrom + (filterState.dateTo ? ' – ' + filterState.dateTo : '') + '</span>');
        if (filterState.category) {
            var catText = $wrap.find('.bkpsdmd-cat-select option:selected').text();
            if (catText) tags.push('<span class="bkpsdmd-ftag">🏷️ ' + $('<div>').text(catText).html() + '</span>');
        }

        $active.html(tags.join(''));
        $bar.show();
    }

    // ---- Loading state ----
    function setLoadingState($wrap, isLoading) {
        var $grid    = $wrap.find('#bkpsdmd-posts-grid');
        var $loading = $wrap.find('#bkpsdmd-loading');
        var $btn     = $wrap.find('#bkpsdmd-search-btn');

        if (isLoading) {
            $grid.addClass('is-loading');
            $loading.fadeIn(150);
            $btn.prop('disabled', true).addClass('is-loading');
        } else {
            $grid.removeClass('is-loading');
            $loading.fadeOut(150);
            $btn.prop('disabled', false).removeClass('is-loading');
        }
    }

    // ---- Reset ----
    function resetFilters($wrap) {
        $wrap.find('.bkpsdmd-keyword-input').val('');

        // Reset flatpickr
        var fpFrom = $wrap.data('fpFrom');
        var fpTo   = $wrap.data('fpTo');
        if (fpFrom) { fpFrom.clear(); fpFrom.set('maxDate', null); }
        if (fpTo)   { fpTo.clear();   fpTo.set('minDate', null); }

        // Reset Select2
        var $sel = $wrap.find('.bkpsdmd-cat-select');
        if ($sel.length && typeof $.fn.select2 !== 'undefined') {
            $sel.val('').trigger('change');
        }

        filterState.keyword  = '';
        filterState.category = '';
        filterState.dateFrom = '';
        filterState.dateTo   = '';
        filterState.page     = 1;

        $wrap.find('#bkpsdmd-result-bar').hide();
        fetchPosts($wrap);
    }

    // ---- DOM Ready ----
    $(document).ready(function () {
        initBlogFilter();
    });

})(jQuery);
