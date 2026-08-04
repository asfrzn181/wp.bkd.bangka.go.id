jQuery(document).ready(function($) {
    'use strict';

    // View Modal Detail
    $(document).on('click', '.bkskm-view-btn', function() {
        var rowData = $(this).data('row');
        if (!rowData) return;

        $('#modal-resp-id').text(rowData.id);

        var html = '<table class="widefat striped" style="border:none;"><tbody>';
        html += '<tr><td style="width:200px;"><strong>Tanggal Layanan</strong></td><td>' + rowData.tgl_layanan + '</td></tr>';
        html += '<tr><td><strong>Jenis Kelamin</strong></td><td>' + rowData.jenis_kelamin + '</td></tr>';
        html += '<tr><td><strong>Pendidikan</strong></td><td>' + rowData.pendidikan + '</td></tr>';
        html += '<tr><td><strong>Usia</strong></td><td>' + rowData.usia + '</td></tr>';
        html += '<tr><td><strong>Pekerjaan</strong></td><td>' + rowData.pekerjaan + (rowData.pekerjaan_lainnya ? ' (' + rowData.pekerjaan_lainnya + ')' : '') + '</td></tr>';
        html += '<tr><td><strong>Disabilitas</strong></td><td>' + rowData.is_disabilitas + (rowData.jenis_disabilitas ? ' (' + rowData.jenis_disabilitas + ')' : '') + '</td></tr>';
        html += '<tr><td><strong>Tanggal Input</strong></td><td>' + rowData.created_at + '</td></tr>';
        html += '<tr><td><strong>IP Address</strong></td><td>' + rowData.ip_address + '</td></tr>';
        html += '</tbody></table>';

        html += '<h3 style="margin-top:20px;">Rincian Penilaian Unsur (1 - 4)</h3>';
        html += '<div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:10px; margin-bottom:20px;">';
        for (var i = 1; i <= 16; i++) {
            html += '<div style="background:#f6f7f7; padding:10px; text-align:center; border-radius:6px; border:1px solid #e2e8f0;">';
            html += '<div style="font-size:0.75rem; color:#646970; font-weight:700;">U' + i + '</div>';
            html += '<div style="font-size:1.2rem; font-weight:800; color:#135e96;">' + rowData['q' + i] + '</div>';
            html += '</div>';
        }
        html += '</div>';

        html += '<h3>Kritik & Saran</h3>';
        html += '<div style="background:#f8fafc; padding:12px 16px; border-radius:8px; border:1px solid #e2e8f0; font-style:italic;">';
        html += rowData.kritik_saran ? $('<div>').text(rowData.kritik_saran).html() : '<em>(Tidak ada kritik & saran)</em>';
        html += '</div>';

        $('#modal-resp-body').html(html);
        $('#bkskm-modal-overlay').fadeIn(200);
    });

    // Close Modal
    $('.bkskm-modal-close, #bkskm-modal-overlay').on('click', function(e) {
        if (e.target === this || $(this).hasClass('bkskm-modal-close')) {
            $('#bkskm-modal-overlay').fadeOut(200);
        }
    });

    // Delete Response
    $(document).on('click', '.bkskm-delete-btn', function() {
        var id = $(this).data('id');
        if (!confirm('Apakah Anda yakin ingin menghapus data responden #' + id + '?')) {
            return;
        }

        var $tr = $('#row-' + id);
        $.post(bkskm_admin.ajax_url, {
            action: 'bkpsdmd_skm_delete_response',
            id: id,
            nonce: bkskm_admin.nonce
        }, function(response) {
            if (response.success) {
                $tr.fadeOut(300, function() { $(this).remove(); });
            } else {
                alert(response.data.message || 'Gagal menghapus data.');
            }
        });
    });

    // Logo Media Uploader
    var logoFrame;
    $('#bkskm-upload-logo-btn').on('click', function(e) {
        e.preventDefault();
        if (logoFrame) {
            logoFrame.open();
            return;
        }
        logoFrame = wp.media({
            title: 'Pilih atau Unggah Logo Instansi',
            button: { text: 'Gunakan Logo Ini' },
            multiple: false
        });

        logoFrame.on('select', function() {
            var attachment = logoFrame.state().get('selection').first().toJSON();
            $('#bkskm_logo_url').val(attachment.url).trigger('change');
        });

        logoFrame.open();
    });

    $('#bkskm_logo_url').on('input change', function() {
        var url = $(this).val();
        if (url) {
            $('#bkskm-logo-preview img').attr('src', url).show();
            $('#bkskm-logo-preview').show();
        } else {
            $('#bkskm-logo-preview').hide();
        }
    });
});
