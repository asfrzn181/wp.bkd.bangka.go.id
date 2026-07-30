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

        // ── Attendance Form Submit (Supports both Token & Walk-in) ────────────
        $('#wbr-attendance-form').on('submit', function (e) {
            e.preventDefault();

            var $form     = $(this);
            var token     = $form.data('token');
            var webinarId = $form.data('webinar-id');
            var isWalkin  = $form.data('is-walkin') == '1';
            var $msg      = $('#wbr-att-msg');
            var $btn      = $('#wbr-att-submit');

            var formData = {};
            $form.serializeArray().forEach(function (item) {
                var match = item.name.match(/form_data\[([^\]]+)\]/);
                if ( match ) {
                    var key = match[1];
                    formData[key] = item.value;
                }
            });

            $msg.hide().removeClass('wbr-alert success error');
            $btn.prop('disabled', true).text('Menyimpan & Membuat Sertifikat...');

            var payload = {
                action:     'wbr_attend',
                token:      token,
                webinar_id: webinarId,
                is_walkin:  isWalkin ? 1 : 0,
                form_data:  formData,
                nonce:      wbrPublic.nonce
            };

            $.post( wbrPublic.ajaxUrl, payload )
            .done(function (res) {
                if ( res.success ) {
                    var msg = 'Berhasil';
                    if (res.data) {
                        msg = typeof res.data === 'string' ? res.data : (res.data.message || msg);
                    }
                    
                    $msg.addClass('wbr-alert success').html( '✅ ' + msg ).show();
                    $form.find('input, textarea, select, button').prop('disabled', true);
                    
                    if ( res.data && typeof res.data === 'object' && res.data.redirect_url ) {
                        setTimeout(function() {
                            window.location.href = res.data.redirect_url;
                        }, 1500);
                    }
                } else {
                    var errMsg = 'Gagal menyimpan absensi.';
                    if (res.data) {
                        errMsg = typeof res.data === 'string' ? res.data : (res.data.message || errMsg);
                    }
                    $msg.addClass('wbr-alert error').html( '⚠️ ' + errMsg ).show();
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
