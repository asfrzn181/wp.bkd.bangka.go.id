jQuery(document).ready(function($) {
    'use strict';

    // ── Conditional Fields ───────────────────────────────────────────────────

    // Pekerjaan Lainnya Toggle
    $('input[name="pekerjaan"]').on('change', function() {
        if ($(this).val() === 'Lainnya') {
            $('#bkskm-pekerjaan-lainnya-wrapper').slideDown(200);
            $('#pekerjaan_lainnya').attr('required', true);
        } else {
            $('#bkskm-pekerjaan-lainnya-wrapper').slideUp(200);
            $('#pekerjaan_lainnya').removeAttr('required').val('');
        }
    });

    // Disabilitas Toggle
    $('input[name="is_disabilitas"]').on('change', function() {
        if ($(this).val() === 'Ya') {
            $('#bkskm-disabilitas-wrapper').slideDown(200);
        } else {
            $('#bkskm-disabilitas-wrapper').slideUp(200);
            $('input[name="jenis_disabilitas"]').prop('checked', false);
        }
    });

    // ── Step Navigation & Validation ────────────────────────────────────────

    function scrollToTop() {
        $('html, body').animate({
            scrollTop: $('.bkskm-container').offset().top - 40
        }, 400);
    }

    function updateStepIndicator(stepNum) {
        $('.bkskm-step-item').removeClass('active completed');
        $('.bkskm-step-item').each(function() {
            var itemStep = parseInt($(this).data('step'));
            if (itemStep < stepNum) {
                $(this).addClass('completed');
            } else if (itemStep === stepNum) {
                $(this).addClass('active');
            }
        });
    }

    function validateStep(currentStep) {
        if (currentStep === 1) {
            var tgl = $('#tgl_layanan').val();
            var jk = $('input[name="jenis_kelamin"]:checked').val();
            var edu = $('input[name="pendidikan"]:checked').val();
            var age = $('input[name="usia"]:checked').val();
            var job = $('input[name="pekerjaan"]:checked').val();
            var dis = $('input[name="is_disabilitas"]:checked').val();

            if (!tgl || !jk || !edu || !age || !job || !dis) {
                alert('Mohon isi seluruh data identitas responden yang bertanda bintang (*).');
                return false;
            }

            if (job === 'Lainnya' && !$.trim($('#pekerjaan_lainnya').val())) {
                alert('Mohon tuliskan rincian pekerjaan Anda pada kolom yang disediakan.');
                $('#pekerjaan_lainnya').focus();
                return false;
            }

            if (dis === 'Ya' && !$('input[name="jenis_disabilitas"]:checked').val()) {
                alert('Mohon pilih jenis disabilitas yang Anda miliki / dampingi.');
                return false;
            }
        } else if (currentStep === 2) {
            var missing = [];
            for (var i = 1; i <= 16; i++) {
                if (!$('input[name="q' + i + '"]:checked').val()) {
                    missing.push(i);
                }
            }

            if (missing.length > 0) {
                alert('Mohon berikan penilaian untuk pertanyaan No: ' + missing.join(', '));
                $('#q-card-' + missing[0])[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
        }
        return true;
    }

    // Next Button Click
    $('.bkskm-btn-next').on('click', function() {
        var nextStep = parseInt($(this).data('next'));
        var currentStep = nextStep - 1;

        if (validateStep(currentStep)) {
            $('.bkskm-section').removeClass('bkskm-section-active');
            $('#bkskm-section-' + nextStep).addClass('bkskm-section-active');
            updateStepIndicator(nextStep);
            scrollToTop();
        }
    });

    // Prev Button Click
    $('.bkskm-btn-prev').on('click', function() {
        var prevStep = parseInt($(this).data('prev'));
        $('.bkskm-section').removeClass('bkskm-section-active');
        $('#bkskm-section-' + prevStep).addClass('bkskm-section-active');
        updateStepIndicator(prevStep);
        scrollToTop();
    });

    // ── Form Submission via AJAX ────────────────────────────────────────────

    $('#bkskm-survey-form').on('submit', function(e) {
        e.preventDefault();

        if (!validateStep(1) || !validateStep(2)) {
            return;
        }

        var $btn = $('#bkskm-submit-btn');
        var $btnText = $btn.find('.btn-text');
        var $btnSpinner = $btn.find('.btn-spinner');

        $btn.prop('disabled', true);
        $btnText.css('opacity', '0.5');
        $btnSpinner.show();

        var formData = $(this).serializeArray();
        formData.push({ name: 'action', value: 'bkpsdmd_skm_submit' });
        formData.push({ name: 'nonce', value: bkskm_obj.nonce });

        $.ajax({
            url: bkskm_obj.ajax_url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $btn.prop('disabled', false);
                $btnText.css('opacity', '1');
                $btnSpinner.hide();

                if (response.success) {
                    $('#bkskm-survey-form').slideUp(300, function() {
                        $('#bkskm-success-message').fadeIn(400);
                        scrollToTop();
                    });
                } else {
                    alert(response.data.message || 'Terjadi kesalahan. Silakan coba lagi.');
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                $btnText.css('opacity', '1');
                $btnSpinner.hide();
                alert('Gagal terhubung ke server. Silakan periksa koneksi internet Anda.');
            }
        });
    });
});
