/**
 * BKPSDMD Webinar Management — Inline Form Builder JS
 */
/* global wbrAdmin, jQuery */
(function ($) {
    'use strict';

    $(document).ready(function () {
        // Cek apakah ada tombol tambah field
        if ( ! $('.wbr-add-field').length ) return;

        // Ambil ID webinar dari input hidden jika ada, atau dari data attribute tombol
        function getWebinarId(btn) {
            var inputId = $('#wbr-post-id').val();
            if (inputId && parseInt(inputId) > 0) return parseInt(inputId);
            return $(btn).data('webinar-id') || 0;
        }

        // ── Tabs switching ────────────────────────────────────────────────────
        $('.wbr-fb-tab').on('click', function () {
            var target = $(this).data('form');
            $('.wbr-fb-tab').removeClass('active');
            $(this).addClass('active');
            $('.wbr-fb-panel').removeClass('active');
            $('.wbr-fb-panel[data-form="' + target + '"]').addClass('active');
        });

        // ── Sortable fields ──────────────────────────────────────────────────
        $('.wbr-field-list').sortable({
            handle:      '.wbr-field-drag',
            placeholder: 'wbr-field-placeholder',
            axis:        'y'
        });

        // ── Show/hide options textarea based on type ──────────────────────────
        $(document).on('change', '.wbr-field-type', function () {
            var type = $(this).val();
            var $opts = $(this).closest('.wbr-field-body').find('.wbr-field-options');
            if ( ['radio', 'checkbox', 'select'].indexOf(type) !== -1 ) {
                $opts.slideDown(150);
            } else {
                $opts.slideUp(150);
            }
        });

        // ── Add Field Row ────────────────────────────────────────────────────
        $('.wbr-add-field').on('click', function () {
            var formType = $(this).data('form');
            var $list    = $('#wbr-fields-' + formType);
            $list.find('.wbr-no-fields').remove();

            var randKey = 'field_' + Math.floor(Math.random() * 10000);
            var html = `
                <div class="wbr-field-row" data-key="${randKey}">
                    <div class="wbr-field-drag">☰</div>
                    <div class="wbr-field-body">
                        <div class="wbr-field-main-row">
                            <input type="text" class="wbr-field-label" placeholder="Label field" value="">
                            <input type="text" class="wbr-field-key" placeholder="key (a-z_)" value="${randKey}">
                            <select class="wbr-field-type">
                                <option value="text">text</option>
                                <option value="textarea">textarea</option>
                                <option value="email">email</option>
                                <option value="phone">phone</option>
                                <option value="radio">radio</option>
                                <option value="checkbox">checkbox</option>
                                <option value="select">select</option>
                                <option value="date">date</option>
                                <option value="file_upload">file_upload</option>
                                <option value="number">number</option>
                            </select>
                            <label class="wbr-field-required-label">
                                <input type="checkbox" class="wbr-field-required"> Wajib
                            </label>
                            <label class="wbr-field-identity-label">
                                <input type="checkbox" class="wbr-field-identity"> Identity
                            </label>
                            <button type="button" class="button wbr-remove-field">✕</button>
                        </div>
                        <textarea class="wbr-field-options" placeholder="Opsi (satu per baris) — untuk radio/checkbox/select" style="display:none;"></textarea>
                    </div>
                </div>`;
            $list.append(html);
        });

        // ── Remove Field Row ─────────────────────────────────────────────────
        $(document).on('click', '.wbr-remove-field', function () {
            $(this).closest('.wbr-field-row').fadeOut(150, function () { $(this).remove(); });
        });

        // ── Save Fields via AJAX ─────────────────────────────────────────────
        $('.wbr-save-fields').on('click', function () {
            var formType = $(this).data('form');
            var $list    = $('#wbr-fields-' + formType);
            var fields   = [];

            $list.find('.wbr-field-row').each(function () {
                var $row    = $(this);
                var label   = $row.find('.wbr-field-label').val().trim();
                var key     = $row.find('.wbr-field-key').val().trim() || label.toLowerCase().replace(/[^a-z0-9_]/g, '_');
                var type    = $row.find('.wbr-field-type').val();
                var req     = $row.find('.wbr-field-required').is(':checked') ? 1 : 0;
                var ident   = $row.find('.wbr-field-identity').is(':checked') ? 1 : 0;
                var rawOpts = $row.find('.wbr-field-options').val().trim();
                var opts    = rawOpts ? rawOpts.split('\n').map(function(s){ return s.trim(); }).filter(Boolean) : [];

                if ( label ) {
                    fields.push({
                        label:             label,
                        field_key:         key,
                        field_type:        type,
                        is_required:       req,
                        is_identity_field: ident,
                        options:           opts
                    });
                }
            });

            var $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');

            $.post( wbrAdmin.ajaxUrl, {
                action:     'wbr_save_form_fields',
                webinar_id: getWebinarId($btn),
                form_type:  formType,
                fields:     JSON.stringify(fields),
                nonce:      wbrAdmin.nonce
            })
            .done(function (res) {
                if ( res.success ) {
                    alert( '✅ ' + res.data );
                } else {
                    alert( '❌ ' + (res.data || wbrAdmin.strings.error) );
                }
            })
            .fail(function () {
                alert('❌ Error koneksi.');
            })
            .always(function () {
                $btn.prop('disabled', false).text('💾 Simpan Field');
            });
        });

    });

})(jQuery);
