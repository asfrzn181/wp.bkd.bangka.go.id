/**
 * BKPSDMD Webinar Management — Public JS
 * Handles AJAX submit for Registration Form & Attendance Form
 */
/* global wbrPublic, jQuery */
(function ($) {
    'use strict';

    $(document).ready(function () {

        // ── Registration Form Submit ──────────────────────────────────────────
        $('#wbr-registration-form').on('submit', function (e) {
            e.preventDefault();

            var $form     = $(this);
            var webinarId = $form.data('webinar-id');
            var $msg      = $('#wbr-reg-msg');
            var $btn      = $('#wbr-reg-submit');

            // Gather form data
            var formData = {};
            $form.serializeArray().forEach(function (item) {
                // Extract field key from form_data[key] or form_data[key][]
                var match = item.name.match(/form_data\[([^\]]+)\]/);
                if ( match ) {
                    var key = match[1];
                    if ( item.name.indexOf('[]') !== -1 ) {
                        if ( ! formData[key] ) formData[key] = [];
                        formData[key].push( item.value );
                    } else {
                        formData[key] = item.value;
                    }
                }
            });

            $msg.hide().removeClass('wbr-alert success error');
            $btn.prop('disabled', true).text('Mengirim pendaftaran...');

            $.post( wbrPublic.ajaxUrl, {
                action:     'wbr_register',
                webinar_id: webinarId,
                form_data:  formData,
                nonce:      wbrPublic.nonce
            })
            .done(function (res) {
                if ( res.success ) {
                    $msg.addClass('wbr-alert success').html( '🎉 ' + res.data ).show();
                    $form.find('input[type="text"], input[type="email"], textarea').val('');
                } else {
                    $msg.addClass('wbr-alert error').html( '⚠️ ' + (res.data || 'Gagal mendaftar.') ).show();
                }
            })
            .fail(function () {
                $msg.addClass('wbr-alert error').html('⚠️ Error koneksi. Silakan coba lagi.').show();
            })
            .always(function () {
                $btn.prop('disabled', false).text('🎓 Kirim Pendaftaran');
            });
        });

        // ── Attendance Form Submit ────────────────────────────────────────────
        $('#wbr-attendance-form').on('submit', function (e) {
            e.preventDefault();

            var $form = $(this);
            var token = $form.data('token');
            var $msg  = $('#wbr-att-msg');
            var $btn  = $('#wbr-att-submit');

            var formData = {};
            $form.serializeArray().forEach(function (item) {
                var match = item.name.match(/form_data\[([^\]]+)\]/);
                if ( match ) {
                    var key = match[1];
                    formData[key] = item.value;
                }
            });

            $msg.hide().removeClass('wbr-alert success error');
            $btn.prop('disabled', true).text('Menyimpan kehadiran...');

            $.post( wbrPublic.ajaxUrl, {
                action:    'wbr_attend',
                token:     token,
                form_data: formData,
                nonce:     wbrPublic.nonce
            })
            .done(function (res) {
                if ( res.success ) {
                    $msg.addClass('wbr-alert success').html( '✅ ' + res.data ).show();
                    $form.find('input, textarea, button').prop('disabled', true);
                } else {
                    $msg.addClass('wbr-alert error').html( '⚠️ ' + (res.data || 'Gagal menyimpan absensi.') ).show();
                    $btn.prop('disabled', false).text('✅ Simpan Kehadiran Saya');
                }
            })
            .fail(function () {
                $msg.addClass('wbr-alert error').html('⚠️ Error koneksi. Silakan coba lagi.').show();
                $btn.prop('disabled', false).text('✅ Simpan Kehadiran Saya');
            });
        });

    });

})(jQuery);
