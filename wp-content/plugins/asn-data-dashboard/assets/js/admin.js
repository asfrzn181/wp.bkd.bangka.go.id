/**
 * ASN Data Dashboard Admin JavaScript
 */
(function($) {
    'use strict';

    var currentData = asnddAdminData.currentData;
    var currentPeriod = asnddAdminData.currentPeriod;
    var schema = asnddAdminData.schema;
    var charts = {};

    $(document).ready(function() {
        initTabs();
        initModals();
        bindInputEvents();
        bindControlEvents();

        // Initial populate and chart render
        populateForm(currentData);
        recalculateAll();
        initCharts();
    });

    /* ----------------------------------------------------
     * TAB NAVIGATION
     * ---------------------------------------------------- */
    function initTabs() {
        $('.asndd-nav-tabs .nav-tab').on('click', function(e) {
            e.preventDefault();
            var target = $(this).data('tab');

            $('.asndd-nav-tabs .nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            $('.asndd-tab-content').removeClass('asndd-tab-active');
            $('#tab-' + target).addClass('asndd-tab-active');

            // Resize charts if switching to charts tab
            if (target === 'charts') {
                setTimeout(function() {
                    Object.keys(charts).forEach(function(key) {
                        if (charts[key]) {
                            charts[key].resize();
                        }
                    });
                }, 50);
            }
        });
    }

    /* ----------------------------------------------------
     * MODALS
     * ---------------------------------------------------- */
    function initModals() {
        // Open New Period Modal
        $('#asndd-btn-new-period').on('click', function() {
            $('#asndd-modal-new').addClass('asndd-modal-active');
        });

        // Open Copy Modal
        $('#asndd-btn-copy-period').on('click', function() {
            $('#asndd-copy-target-display').val($('#asndd-periode-select').val());
            $('#asndd-modal-copy').addClass('asndd-modal-active');
        });

        // Close Modals
        $('.asndd-modal-close, .asndd-modal-close-btn').on('click', function() {
            $('.asndd-modal').removeClass('asndd-modal-active');
        });

        // Click outside modal content to close
        $('.asndd-modal').on('click', function(e) {
            if ($(e.target).hasClass('asndd-modal')) {
                $('.asndd-modal').removeClass('asndd-modal-active');
            }
        });

        // Confirm New Period
        $('#asndd-btn-confirm-new').on('click', function() {
            var selectedMonth = $('#asndd-new-periode-input').val();
            if (!selectedMonth || !/^\d{4}-\d{2}$/.test(selectedMonth)) {
                showNotice('error', 'Silakan pilih bulan & tahun yang valid.');
                return;
            }

            // Check if option exists in dropdown
            var $select = $('#asndd-periode-select');
            if ($select.find('option[value="' + selectedMonth + '"]').length === 0) {
                $select.append('<option value="' + selectedMonth + '">' + selectedMonth + '</option>');
            }

            $select.val(selectedMonth).trigger('change');
            $('.asndd-modal').removeClass('asndd-modal-active');
        });

        // Confirm Copy
        $('#asndd-btn-confirm-copy').on('click', function() {
            var source = $('#asndd-copy-source-select').val();
            var target = $('#asndd-periode-select').val();

            if (!source || !target) {
                showNotice('error', 'Periode asal dan tujuan harus dipilih.');
                return;
            }

            var $btn = $(this).prop('disabled', true);

            $.ajax({
                url: asnddAdminData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'asndd_copy',
                    nonce: asnddAdminData.nonce,
                    source_periode: source,
                    target_periode: target
                },
                success: function(res) {
                    if (res.success) {
                        currentData = res.data.data;
                        populateForm(currentData);
                        recalculateAll();
                        updateCharts();
                        showNotice('success', res.data.message);
                        $('.asndd-modal').removeClass('asndd-modal-active');
                    } else {
                        showNotice('error', res.data.message || 'Gagal menyalin data.');
                    }
                },
                error: function() {
                    showNotice('error', 'Terjadi kesalahan jaringan.');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    }

    /* ----------------------------------------------------
     * CONTROL EVENTS & AJAX
     * ---------------------------------------------------- */
    function bindControlEvents() {
        // Period Select Change
        $('#asndd-periode-select').on('change', function() {
            var period = $(this).val();
            currentPeriod = period;

            $.ajax({
                url: asnddAdminData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'asndd_get',
                    nonce: asnddAdminData.nonce,
                    periode: period
                },
                success: function(res) {
                    if (res.success) {
                        currentData = res.data.data;
                        populateForm(currentData);
                        recalculateAll();
                        updateCharts();
                        if (res.data.exists) {
                            showNotice('info', 'Data periode ' + period + ' dimuat.');
                        } else {
                            showNotice('info', 'Periode baru ' + period + ' dibuat dengan nilai default (0).');
                        }
                    }
                }
            });
        });

        // Save Button Click
        $('#asndd-btn-save').on('click', function() {
            var $btn = $(this).prop('disabled', true);
            collectFormData();

            $.ajax({
                url: asnddAdminData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'asndd_save',
                    nonce: asnddAdminData.nonce,
                    periode: currentPeriod,
                    data: JSON.stringify(currentData)
                },
                success: function(res) {
                    if (res.success) {
                        showNotice('success', res.data.message);
                    } else {
                        showNotice('error', res.data.message || 'Gagal menyimpan data.');
                    }
                },
                error: function() {
                    showNotice('error', 'Terjadi kesalahan server.');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    }

    /* ----------------------------------------------------
     * FORM & DATA SYNC
     * ---------------------------------------------------- */
    function bindInputEvents() {
        $('.asndd-input-num').on('input change', function() {
            var val = parseInt($(this).val(), 10);
            if (isNaN(val) || val < 0) {
                val = 0;
                $(this).val(0);
            }
            collectFormData();
            recalculateAll();
            updateCharts();
        });
    }

    function populateForm(data) {
        $('.asndd-input-num').each(function() {
            var path = $(this).data('path');
            var val = getNestedValue(data, path, 0);
            $(this).val(val);
        });
    }

    function collectFormData() {
        $('.asndd-input-num').each(function() {
            var path = $(this).data('path');
            var val = parseInt($(this).val(), 10) || 0;
            setNestedValue(currentData, path, val);
        });
    }

    function getNestedValue(obj, path, defaultVal) {
        var parts = path.split('.');
        var curr = obj;
        for (var i = 0; i < parts.length; i++) {
            if (curr === undefined || curr === null) return defaultVal;
            curr = curr[parts[i]];
        }
        return curr !== undefined ? curr : defaultVal;
    }

    function setNestedValue(obj, path, val) {
        var parts = path.split('.');
        var curr = obj;
        for (var i = 0; i < parts.length - 1; i++) {
            if (!curr[parts[i]]) curr[parts[i]] = {};
            curr = curr[parts[i]];
        }
        curr[parts[parts.length - 1]] = val;
    }

    /* ----------------------------------------------------
     * RECALCULATE TOTALS & CARDS
     * ---------------------------------------------------- */
    function recalculateAll() {
        // PNS Jabatan
        var pnsJabL = 0, pnsJabP = 0;
        Object.keys(schema.pnsJabatan).forEach(function(k) {
            var l = currentData.pnsCpns.jabatan[k] ? currentData.pnsCpns.jabatan[k].l : 0;
            var p = currentData.pnsCpns.jabatan[k] ? currentData.pnsCpns.jabatan[k].p : 0;
            $('#tot-pns-jab-' + k).text(l + p);
            pnsJabL += l;
            pnsJabP += p;
        });
        $('#subtot-pns-jab-l').text(pnsJabL);
        $('#subtot-pns-jab-p').text(pnsJabP);
        $('#subtot-pns-jab-all').text(pnsJabL + pnsJabP);

        // PNS Pangkat
        var pnsPktL = 0, pnsPktP = 0;
        Object.keys(schema.pnsPangkat).forEach(function(k) {
            var l = currentData.pnsCpns.pangkat[k] ? currentData.pnsCpns.pangkat[k].l : 0;
            var p = currentData.pnsCpns.pangkat[k] ? currentData.pnsCpns.pangkat[k].p : 0;
            $('#tot-pns-pkt-' + k).text(l + p);
            pnsPktL += l;
            pnsPktP += p;
        });
        $('#subtot-pns-pkt-l').text(pnsPktL);
        $('#subtot-pns-pkt-p').text(pnsPktP);
        $('#subtot-pns-pkt-all').text(pnsPktL + pnsPktP);

        // PNS Pendidikan
        var dStruk = 0, dGuru = 0, dNakes = 0, dTeknis = 0;
        Object.keys(schema.pendidikan).forEach(function(k) {
            var d = currentData.pnsCpns.didik[k] || {};
            var s = d.struktural || 0, g = d.guru || 0, n = d.nakes || 0, t = d.teknis || 0;
            $('#tot-pns-didik-' + k).text(s + g + n + t);
            dStruk += s; dGuru += g; dNakes += n; dTeknis += t;
        });
        $('#subtot-pns-didik-struk').text(dStruk);
        $('#subtot-pns-didik-guru').text(dGuru);
        $('#subtot-pns-didik-nakes').text(dNakes);
        $('#subtot-pns-didik-teknis').text(dTeknis);
        $('#subtot-pns-didik-all').text(dStruk + dGuru + dNakes + dTeknis);

        // PPPK Jabatan
        var pppkJabL = 0, pppkJabP = 0;
        Object.keys(schema.pppkJabatan).forEach(function(k) {
            var l = currentData.pppk.jabatan[k] ? currentData.pppk.jabatan[k].l : 0;
            var p = currentData.pppk.jabatan[k] ? currentData.pppk.jabatan[k].p : 0;
            $('#tot-pppk-jab-' + k).text(l + p);
            pppkJabL += l; pppkJabP += p;
        });
        $('#subtot-pppk-jab-l').text(pppkJabL);
        $('#subtot-pppk-jab-p').text(pppkJabP);
        $('#subtot-pppk-jab-all').text(pppkJabL + pppkJabP);

        // PPPK Golongan
        var pppkGolL = 0, pppkGolP = 0;
        Object.keys(schema.pppkGolongan).forEach(function(k) {
            var l = currentData.pppk.golongan[k] ? currentData.pppk.golongan[k].l : 0;
            var p = currentData.pppk.golongan[k] ? currentData.pppk.golongan[k].p : 0;
            $('#tot-pppk-gol-' + k).text(l + p);
            pppkGolL += l; pppkGolP += p;
        });
        $('#subtot-pppk-gol-l').text(pppkGolL);
        $('#subtot-pppk-gol-p').text(pppkGolP);
        $('#subtot-pppk-gol-all').text(pppkGolL + pppkGolP);

        // PPPK Pendidikan
        var pppkDGuru = 0, pppkDNakes = 0, pppkDTeknis = 0;
        Object.keys(schema.pendidikan).forEach(function(k) {
            var d = (currentData.pppk && currentData.pppk.didik && currentData.pppk.didik[k]) ? currentData.pppk.didik[k] : {};
            var g = d.guru || 0, n = d.nakes || 0, t = d.teknis || 0;
            $('#tot-pppk-didik-' + k).text(g + n + t);
            pppkDGuru += g; pppkDNakes += n; pppkDTeknis += t;
        });
        $('#subtot-pppk-didik-guru').text(pppkDGuru);
        $('#subtot-pppk-didik-nakes').text(pppkDNakes);
        $('#subtot-pppk-didik-teknis').text(pppkDTeknis);
        $('#subtot-pppk-didik-all').text(pppkDGuru + pppkDNakes + pppkDTeknis);


        // PPPK PW Jabatan
        var pwL = 0, pwP = 0;
        Object.keys(schema.pppkPwJabatan).forEach(function(k) {
            var l = currentData.pppkPw.jabatan[k] ? currentData.pppkPw.jabatan[k].l : 0;
            var p = currentData.pppkPw.jabatan[k] ? currentData.pppkPw.jabatan[k].p : 0;
            $('#tot-pppkpw-jab-' + k).text(l + p);
            pwL += l; pwP += p;
        });
        $('#subtot-pppkpw-jab-l').text(pwL);
        $('#subtot-pppkpw-jab-p').text(pwP);
        $('#subtot-pppkpw-jab-all').text(pwL + pwP);

        // PPPK PW Pendidikan
        var pwDGuru = 0, pwDNakes = 0, pwDTeknis = 0;
        Object.keys(schema.pendidikan).forEach(function(k) {
            var d = (currentData.pppkPw && currentData.pppkPw.didik && currentData.pppkPw.didik[k]) ? currentData.pppkPw.didik[k] : {};
            var g = d.guru || 0, n = d.nakes || 0, t = d.teknis || 0;
            $('#tot-pppkpw-didik-' + k).text(g + n + t);
            pwDGuru += g; pwDNakes += n; pwDTeknis += t;
        });
        $('#subtot-pppkpw-didik-guru').text(pwDGuru);
        $('#subtot-pppkpw-didik-nakes').text(pwDNakes);
        $('#subtot-pppkpw-didik-teknis').text(pwDTeknis);
        $('#subtot-pppkpw-didik-all').text(pwDGuru + pwDNakes + pwDTeknis);


        // Metric Summary Cards
        var totalPns = pnsJabL + pnsJabP;
        var totalPppk = pppkJabL + pppkJabP;
        var totalPw = pwL + pwP;
        var grandTotal = totalPns + totalPppk + totalPw;
        var grandL = pnsJabL + pppkJabL + pwL;
        var grandP = pnsJabP + pppkJabP + pwP;

        $('#stat-total-pns').text(numberFormat(totalPns));
        $('#stat-gender-pns').text('L: ' + numberFormat(pnsJabL) + ' | P: ' + numberFormat(pnsJabP));

        $('#stat-total-pppk').text(numberFormat(totalPppk));
        $('#stat-gender-pppk').text('L: ' + numberFormat(pppkJabL) + ' | P: ' + numberFormat(pppkJabP));

        $('#stat-total-pppkpw').text(numberFormat(totalPw));
        $('#stat-gender-pppkpw').text('L: ' + numberFormat(pwL) + ' | P: ' + numberFormat(pwP));

        $('#stat-grand-total').text(numberFormat(grandTotal));
        $('#stat-gender-total').text('L: ' + numberFormat(grandL) + ' | P: ' + numberFormat(grandP));
    }

    /* ----------------------------------------------------
     * CHART.JS VISUALIZATION
     * ---------------------------------------------------- */
    function initCharts() {
        if (typeof Chart === 'undefined') return;

        // Chart 1: Bar Jabatan PNS
        var ctx1 = document.getElementById('chart-pns-jabatan');
        if (ctx1) {
            charts.pnsJabatan = new Chart(ctx1, {
                type: 'bar',
                data: getPnsJabatanChartData(),
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Chart 2: Bar Pangkat PNS
        var ctx2 = document.getElementById('chart-pns-pangkat');
        if (ctx2) {
            charts.pnsPangkat = new Chart(ctx2, {
                type: 'bar',
                data: getPnsPangkatChartData(),
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Chart 3: Donut Pendidikan
        var ctx3 = document.getElementById('chart-pns-pendidikan');
        if (ctx3) {
            charts.pnsPendidikan = new Chart(ctx3, {
                type: 'doughnut',
                data: getPendidikanChartData(),
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Chart 4: Donut Gender Total
        var ctx4 = document.getElementById('chart-gender-total');
        if (ctx4) {
            charts.genderTotal = new Chart(ctx4, {
                type: 'doughnut',
                data: getGenderTotalChartData(),
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Chart 5: Bar Jenis Pegawai
        var ctx5 = document.getElementById('chart-jenis-pegawai');
        if (ctx5) {
            charts.jenisPegawai = new Chart(ctx5, {
                type: 'bar',
                data: getJenisPegawaiChartData(),
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    }

    function updateCharts() {
        if (!charts.pnsJabatan) return;

        charts.pnsJabatan.data = getPnsJabatanChartData();
        charts.pnsJabatan.update();

        charts.pnsPangkat.data = getPnsPangkatChartData();
        charts.pnsPangkat.update();

        charts.pnsPendidikan.data = getPendidikanChartData();
        charts.pnsPendidikan.update();

        charts.genderTotal.data = getGenderTotalChartData();
        charts.genderTotal.update();

        charts.jenisPegawai.data = getJenisPegawaiChartData();
        charts.jenisPegawai.update();
    }

    function getPnsJabatanChartData() {
        var labels = [], dataL = [], dataP = [];
        Object.keys(schema.pnsJabatan).forEach(function(k) {
            labels.push(schema.pnsJabatan[k]);

            var pnsL = currentData.pnsCpns.jabatan[k] ? currentData.pnsCpns.jabatan[k].l : 0;
            var pnsP = currentData.pnsCpns.jabatan[k] ? currentData.pnsCpns.jabatan[k].p : 0;

            var pppkL = (currentData.pppk && currentData.pppk.jabatan && currentData.pppk.jabatan[k]) ? currentData.pppk.jabatan[k].l : 0;
            var pppkP = (currentData.pppk && currentData.pppk.jabatan && currentData.pppk.jabatan[k]) ? currentData.pppk.jabatan[k].p : 0;

            var pwL = (currentData.pppkPw && currentData.pppkPw.jabatan && currentData.pppkPw.jabatan[k]) ? currentData.pppkPw.jabatan[k].l : 0;
            var pwP = (currentData.pppkPw && currentData.pppkPw.jabatan && currentData.pppkPw.jabatan[k]) ? currentData.pppkPw.jabatan[k].p : 0;

            dataL.push(pnsL + pppkL + pwL);
            dataP.push(pnsP + pppkP + pwP);
        });

        return {
            labels: labels,
            datasets: [
                { label: 'Laki-Laki', data: dataL, backgroundColor: '#3b82f6' },
                { label: 'Perempuan', data: dataP, backgroundColor: '#ec4899' }
            ]
        };
    }

    function getPnsPangkatChartData() {
        var labels = [], dataL = [], dataP = [];

        // PNS Golongan (I/a s.d. IV/e)
        Object.keys(schema.pnsPangkat).forEach(function(k) {
            labels.push(schema.pnsPangkat[k].replace('Golongan ', ''));
            dataL.push(currentData.pnsCpns.pangkat[k] ? currentData.pnsCpns.pangkat[k].l : 0);
            dataP.push(currentData.pnsCpns.pangkat[k] ? currentData.pnsCpns.pangkat[k].p : 0);
        });

        // PPPK Golongan (V, VII, IX, X)
        Object.keys(schema.pppkGolongan).forEach(function(k) {
            labels.push('PPPK ' + schema.pppkGolongan[k].replace('Golongan ', ''));
            var gol = (currentData.pppk && currentData.pppk.golongan && currentData.pppk.golongan[k]) ? currentData.pppk.golongan[k] : {};
            dataL.push(gol.l || 0);
            dataP.push(gol.p || 0);
        });

        return {
            labels: labels,
            datasets: [
                { label: 'Laki-Laki', data: dataL, backgroundColor: '#2563eb' },
                { label: 'Perempuan', data: dataP, backgroundColor: '#f43f5e' }
            ]
        };
    }


    function getPendidikanChartData() {
        var labels = [], dataTotal = [];
        Object.keys(schema.pendidikan).forEach(function(k) {
            var dPns = currentData.pnsCpns.didik[k] || {};
            var dPppk = (currentData.pppk && currentData.pppk.didik && currentData.pppk.didik[k]) ? currentData.pppk.didik[k] : {};
            var dPw = (currentData.pppkPw && currentData.pppkPw.didik && currentData.pppkPw.didik[k]) ? currentData.pppkPw.didik[k] : {};
            var tot = (dPns.struktural || 0) + (dPns.guru || 0) + (dPns.nakes || 0) + (dPns.teknis || 0)
                    + (dPppk.guru || 0) + (dPppk.nakes || 0) + (dPppk.teknis || 0)
                    + (dPw.guru || 0) + (dPw.nakes || 0) + (dPw.teknis || 0);
            labels.push(schema.pendidikan[k]);
            dataTotal.push(tot);
        });


        return {
            labels: labels,
            datasets: [{
                data: dataTotal,
                backgroundColor: [
                    '#94a3b8', '#64748b', '#3b82f6', '#0284c7', '#06b6d4',
                    '#10b981', '#84cc16', '#eab308', '#f97316', '#8b5cf6'
                ]
            }]
        };
    }


    function getGenderTotalChartData() {
        var pnsL = 0, pnsP = 0, pppkL = 0, pppkP = 0, pwL = 0, pwP = 0;
        Object.keys(schema.pnsJabatan).forEach(function(k) {
            pnsL += currentData.pnsCpns.jabatan[k] ? currentData.pnsCpns.jabatan[k].l : 0;
            pnsP += currentData.pnsCpns.jabatan[k] ? currentData.pnsCpns.jabatan[k].p : 0;
        });
        Object.keys(schema.pppkJabatan).forEach(function(k) {
            pppkL += currentData.pppk.jabatan[k] ? currentData.pppk.jabatan[k].l : 0;
            pppkP += currentData.pppk.jabatan[k] ? currentData.pppk.jabatan[k].p : 0;
        });
        Object.keys(schema.pppkPwJabatan).forEach(function(k) {
            pwL += currentData.pppkPw.jabatan[k] ? currentData.pppkPw.jabatan[k].l : 0;
            pwP += currentData.pppkPw.jabatan[k] ? currentData.pppkPw.jabatan[k].p : 0;
        });

        return {
            labels: ['Laki-Laki', 'Perempuan'],
            datasets: [{
                data: [pnsL + pppkL + pwL, pnsP + pppkP + pwP],
                backgroundColor: ['#3b82f6', '#ec4899']
            }]
        };
    }

    function getJenisPegawaiChartData() {
        var pnsTot = 0, pppkTot = 0, pwTot = 0;
        Object.keys(schema.pnsJabatan).forEach(function(k) {
            var j = currentData.pnsCpns.jabatan[k] || {};
            pnsTot += (j.l || 0) + (j.p || 0);
        });
        Object.keys(schema.pppkJabatan).forEach(function(k) {
            var j = currentData.pppk.jabatan[k] || {};
            pppkTot += (j.l || 0) + (j.p || 0);
        });
        Object.keys(schema.pppkPwJabatan).forEach(function(k) {
            var j = currentData.pppkPw.jabatan[k] || {};
            pwTot += (j.l || 0) + (j.p || 0);
        });

        return {
            labels: ['PNS & CPNS', 'PPPK', 'PPPK-PW'],
            datasets: [{
                label: 'Jumlah Pegawai',
                data: [pnsTot, pppkTot, pwTot],
                backgroundColor: ['#2563eb', '#10b981', '#f59e0b']
            }]
        };
    }

    /* ----------------------------------------------------
     * HELPER FUNCTIONS
     * ---------------------------------------------------- */
    function numberFormat(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function showNotice(type, message) {
        var cssClass = 'notice-info';
        if (type === 'success') cssClass = 'notice-success';
        if (type === 'error') cssClass = 'notice-error';

        var html = '<div class="notice ' + cssClass + ' is-dismissible"><p>' + message + '</p></div>';
        $('#asndd-notice-container').html(html);

        setTimeout(function() {
            $('#asndd-notice-container').empty();
        }, 5000);
    }

})(jQuery);
