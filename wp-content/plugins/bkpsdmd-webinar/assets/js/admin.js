/**
 * BKPSDMD Webinar Management — Admin JS
 */
/* global wbrAdmin, jQuery */
(function ($) {
    'use strict';

    $(document).ready(function () {

        // ── Expand/collapse submission data detail in registrants table ───────
        $(document).on('click', '.wbr-data-toggle', function () {
            var id = $(this).data('id');
            var $detail = $('#wbr-data-' + id);
            $detail.slideToggle(150);
            $(this).text( $detail.is(':visible') ? 'Sembunyikan ▲' : 'Lihat semua ▼' );
        });

        // ── Delete Registrant ────────────────────────────────────────────────
        $(document).on('click', '.wbr-del-reg', function () {
            if ( ! confirm( wbrAdmin.strings.confirm_delete ) ) return;
            var id   = $(this).data('id');
            var $row = $('#wbr-reg-row-' + id);

            $.post( wbrAdmin.ajaxUrl, {
                action: 'wbr_delete_registrant',
                id:     id,
                nonce:  wbrAdmin.nonce
            })
            .done(function (res) {
                if ( res.success ) {
                    $row.fadeOut(200, function () { $(this).remove(); });
                } else {
                    alert( res.data || wbrAdmin.strings.error );
                }
            });
        });

        // ── Operator record attendance via scan/token input ──────────────────
        $('#wbr-record-attendance').on('click', function () {
            var token = $('#wbr-scan-token').val().trim();
            if ( ! token ) return;
            var $res = $('#wbr-scan-result');
            $res.text('Menyimpan...').css('color', '#a5b4fc');

            $.post( wbrAdmin.ajaxUrl, {
                action: 'wbr_record_attendance',
                token:  token,
                nonce:  wbrAdmin.nonce
            })
            .done(function (res) {
                if ( res.success ) {
                    $res.text( '✅ ' + res.data ).css('color', '#6ee7b7');
                    $('#wbr-scan-token').val('');
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    $res.text( '❌ ' + res.data ).css('color', '#fca5a5');
                }
            })
            .fail(function () {
                $res.text('❌ Error koneksi').css('color', '#fca5a5');
            });
        });

        // ── Export CSV Registrants ────────────────────────────────────────────
        $('#wbr-export-csv').on('click', function () {
            var webinarId = $(this).data('webinar');
            $.post( wbrAdmin.ajaxUrl, {
                action:     'wbr_export_registrants',
                webinar_id: webinarId,
                nonce:      wbrAdmin.nonce
            })
            .done(function (res) {
                if ( res.success && res.data.csv ) {
                    var blob = new Blob([res.data.csv], { type: 'text/csv;charset=utf-8;' });
                    var link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'registrants-webinar-' + webinarId + '.csv';
                    link.click();
                } else {
                    alert( res.data || 'Gagal export data.' );
                }
            });
        });

        // ── Create SK ────────────────────────────────────────────────────────
        $('#wbr-create-sk').on('click', function () {
            var webinarId = $(this).data('webinar');
            var data = {
                action:           'wbr_create_sk',
                webinar_id:       webinarId,
                sk_number:        $('#sk_number').val().trim(),
                sk_date:          $('#sk_date').val(),
                signing_official: $('#signing_official').val().trim(),
                signing_method:   $('#signing_method').val(),
                nonce:            wbrAdmin.nonce
            };

            $(this).prop('disabled', true).text('Generating...');

            $.post( wbrAdmin.ajaxUrl, data )
            .done(function (res) {
                if ( res.success ) {
                    location.reload();
                } else {
                    alert( res.data || wbrAdmin.strings.error );
                    $('#wbr-create-sk').prop('disabled', false).text('📝 Generate Draft SK');
                }
            });
        });

        // ── Regenerate SK Draft ──────────────────────────────────────────────
        $('#wbr-regen-sk').on('click', function () {
            var id = $(this).data('id');
            var $btn = $(this);
            $btn.prop('disabled', true).text('Regenerating...');

            $.post( wbrAdmin.ajaxUrl, { action: 'wbr_regenerate_sk', sk_id: id, nonce: wbrAdmin.nonce } )
            .done(function (res) {
                if ( res.success ) {
                    alert('Draft SK berhasil diperbarui.');
                    location.reload();
                } else {
                    alert( res.data || wbrAdmin.strings.error );
                    $btn.prop('disabled', false).text('🔄 Regenerate Draft');
                }
            });
        });

        // ── Submit SK for Signing ────────────────────────────────────────────
        $('#wbr-submit-signing').on('click', function () {
            if ( ! confirm( wbrAdmin.strings.confirm_sk ) ) return;
            var id = $(this).data('id');

            $.post( wbrAdmin.ajaxUrl, { action: 'wbr_submit_signing', sk_id: id, nonce: wbrAdmin.nonce } )
            .done(function (res) {
                if ( res.success ) {
                    location.reload();
                } else {
                    alert( res.data || wbrAdmin.strings.error );
                }
            });
        });

        // ── Upload SK Signed File ─────────────────────────────────────────────
        $('#wbr-upload-signed').on('click', function () {
            var id = $(this).data('id');
            var fileInput = document.getElementById('wbr-sk-signed-file');
            if ( ! fileInput || ! fileInput.files.length ) {
                alert('Pilih file terlebih dahulu.');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'wbr_upload_sk_signed');
            formData.append('sk_id', id);
            formData.append('sk_signed_file', fileInput.files[0]);
            formData.append('nonce', wbrAdmin.nonce);

            var $btn = $(this);
            $btn.prop('disabled', true).text('Uploading...');

            $.ajax({
                url:         wbrAdmin.ajaxUrl,
                type:        'POST',
                data:        formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    if ( res.success ) {
                        alert( res.data );
                        location.reload();
                    } else {
                        alert( res.data || 'Gagal upload.' );
                        $btn.prop('disabled', false).text('✅ Upload & Finalkan SK');
                    }
                },
                error: function () {
                    alert('Error upload.');
                    $btn.prop('disabled', false).text('✅ Upload & Finalkan SK');
                }
            });
        });

    });

})(jQuery);
