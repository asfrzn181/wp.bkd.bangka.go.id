/**
 * admin.js — JavaScript untuk Halaman Admin
 * BKPSDMD Struktur Organisasi
 *
 * Foto URL: disimpan langsung di <input name="foto_url">
 * Media Library mengisi field yang sama.
 * Tidak ada field hidden terpisah.
 */
(function ($) {
    'use strict';

    var DEFAULT_AVATAR = '';

    $(document).ready(function () {

        DEFAULT_AVATAR = $('#bkpsdmd-preview-img').attr('src') || '';

        // =============================================
        //  MEDIA UPLOADER — mengisi #foto_url langsung
        // =============================================
        var mediaUploader;

        $('#bkpsdmd-upload-btn').on('click', function (e) {
            e.preventDefault();
            if ( mediaUploader ) { mediaUploader.open(); return; }

            mediaUploader = wp.media({
                title:    'Pilih Foto Pegawai',
                button:   { text: 'Gunakan Foto Ini' },
                multiple: false,
                library:  { type: 'image' },
            });

            mediaUploader.on('select', function () {
                var url = mediaUploader.state().get('selection').first().toJSON().url;
                setPhoto(url);
            });

            mediaUploader.open();
        });

        // =============================================
        //  PREVIEW FOTO (tombol Preview atau Enter)
        // =============================================

        // Tombol Preview
        $('#bkpsdmd-preview-btn').on('click', function () {
            var url = $.trim($('#foto_url').val());
            if ( url ) {
                updatePreview(url);
                $('#bkpsdmd-remove-foto').show();
            }
        });

        // Enter di field URL → preview
        $('#foto_url').on('keydown', function (e) {
            if ( e.key === 'Enter' ) {
                e.preventDefault();
                $('#bkpsdmd-preview-btn').trigger('click');
            }
        });

        // Auto-preview saat mengetik (debounce 700ms)
        var urlTimer;
        $('#foto_url').on('input', function () {
            var url = $.trim($(this).val());
            clearTimeout(urlTimer);
            if ( ! url ) {
                updatePreview(DEFAULT_AVATAR);
                $('#bkpsdmd-remove-foto').hide();
                return;
            }
            urlTimer = setTimeout(function () { updatePreview(url); }, 700);
        });

        // =============================================
        //  HAPUS FOTO
        // =============================================
        $('#bkpsdmd-remove-foto').on('click', function () {
            $('#foto_url').val('');
            updatePreview(DEFAULT_AVATAR);
            $('#bkpsdmd-preview-card-img').attr('src', DEFAULT_AVATAR);
            $(this).hide();
        });

        // =============================================
        //  LIVE PREVIEW KARTU
        // =============================================
        $('#jabatan').on('input', function () { $('#preview-jabatan').text($(this).val() || 'Nama Jabatan'); });
        $('#nama').on('input',    function () { $('#preview-nama').text($(this).val() || 'Nama Pegawai'); });
        $('#nip').on('input',     function () {
            $('#preview-nip').text($(this).val() ? 'NIP: ' + $(this).val() : '');
        });

        // =============================================
        //  VALIDASI FORM
        // =============================================
        $('#bkpsdmd-org-form').on('submit', function (e) {
            if ( ! $.trim($('#jabatan').val()) ) {
                e.preventDefault();
                $('#jabatan').focus();
                alert('Nama Jabatan wajib diisi!');
                return false;
            }
            // Pastikan preview kartu sudah sinkron sebelum submit
            var url = $.trim($('#foto_url').val());
            if ( url ) { $('#bkpsdmd-remove-foto').show(); }
        });

        // =============================================
        //  HELPERS
        // =============================================

        /**
         * Terapkan URL: isi preview + kartu
         */
        function setPhoto(url) {
            $('#foto_url').val(url);
            updatePreview(url);
            $('#bkpsdmd-preview-card-img').attr('src', url);
            $('#bkpsdmd-remove-foto').show();
        }

        /**
         * Update preview lingkaran saja
         */
        function updatePreview(url) {
            $('#bkpsdmd-preview-img').attr('src', url);
        }

    });

})(jQuery);
