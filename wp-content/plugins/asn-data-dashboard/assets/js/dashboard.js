/**
 * ASN Data Dashboard Frontend Read-Only JavaScript
 * Visualizations & Interactive Category Filtering
 */
(function($) {
    'use strict';

    var chartInstances = {};

    var categoryLabels = {
        'total':  'Total ASN',
        'pns':    'PNS & CPNS',
        'pppk':   'PPPK',
        'pppkpw': 'PPPK-PW'
    };

    $(document).ready(function() {
        // Find all dashboard instances on the page
        $('.asndd-frontend-wrap').each(function() {
            var $wrap = $(this);
            var instId = $wrap.attr('id');
            if (!instId) return;

            // Read inline JSON config
            var $configScript = $wrap.find('script.asndd-config');
            var config = {};
            if ($configScript.length) {
                try {
                    config = JSON.parse($configScript.html());
                } catch (e) {
                    console.error('ASNDD: Error parsing inline JSON config', e);
                }
            }

            // Fallback to global asnddDashboardData if inline script missing
            if (!config.data && typeof asnddDashboardData !== 'undefined') {
                config = asnddDashboardData;
            }

            if (!config.data || !config.schema) return;

            // Store active category (default: 'total')
            config.activeCat = 'total';
            $wrap.data('asndd-config', config);

            // Card Category Click Handler
            $wrap.find('.asndd-fe-card[data-cat]').off('click').on('click', function() {
                var cat = $(this).data('cat');
                if (!cat) return;

                config.activeCat = cat;
                $wrap.data('asndd-config', config);

                // Update Active Card UI
                $wrap.find('.asndd-fe-card[data-cat]').removeClass('asndd-fe-card-active');
                $(this).addClass('asndd-fe-card-active');

                // Update Section Header Title
                $wrap.find('.asndd-fe-cat-title-text').text('Visualisasi Grafik: ' + (categoryLabels[cat] || 'ASN'));

                // Update Charts
                if (typeof Chart !== 'undefined') {
                    updateFrontendCharts(instId, config.data, config.schema, cat);
                }
            });

            // Init Dynamic Period Selector
            $wrap.find('.asndd-fe-period-select').off('change').on('change', function() {
                var selectedPeriod = $(this).val();
                if (!selectedPeriod) return;

                var restUrl = config.restUrl || (typeof asnddDashboardData !== 'undefined' ? asnddDashboardData.restUrl : '');
                if (!restUrl) return;

                $wrap.css('opacity', '0.6');

                $.ajax({
                    url: restUrl,
                    type: 'GET',
                    data: { periode: selectedPeriod },
                    success: function(res) {
                        if (res && res.data) {
                            config.data = res.data;
                            $wrap.data('asndd-config', config);
                            calculateFrontendTotals(instId, config.data, config.schema);
                            updateFrontendCharts(instId, config.data, config.schema, config.activeCat);
                        }
                    },
                    complete: function() {
                        $wrap.css('opacity', '1');
                    }
                });
            });

            // Calculate Totals & Render Charts initially
            calculateFrontendTotals(instId, config.data, config.schema);

            if (typeof Chart !== 'undefined') {
                initFrontendCharts(instId, config.data, config.schema, config.activeCat);
            }
        });
    });

    function calculateFrontendTotals(instId, data, schema) {
        var pnsL = 0, pnsP = 0, pppkL = 0, pppkP = 0, pwL = 0, pwP = 0;

        Object.keys(schema.pnsJabatan).forEach(function(k) {
            pnsL += data.pnsCpns.jabatan[k] ? (data.pnsCpns.jabatan[k].l || 0) : 0;
            pnsP += data.pnsCpns.jabatan[k] ? (data.pnsCpns.jabatan[k].p || 0) : 0;
        });

        Object.keys(schema.pppkJabatan).forEach(function(k) {
            pppkL += data.pppk.jabatan[k] ? (data.pppk.jabatan[k].l || 0) : 0;
            pppkP += data.pppk.jabatan[k] ? (data.pppk.jabatan[k].p || 0) : 0;
        });

        Object.keys(schema.pppkPwJabatan).forEach(function(k) {
            pwL += data.pppkPw.jabatan[k] ? (data.pppkPw.jabatan[k].l || 0) : 0;
            pwP += data.pppkPw.jabatan[k] ? (data.pppkPw.jabatan[k].p || 0) : 0;
        });

        var totPns = pnsL + pnsP;
        var totPppk = pppkL + pppkP;
        var totPw = pwL + pwP;
        var grandTotal = totPns + totPppk + totPw;

        $('#' + instId + '-val-pns').text(numberFormat(totPns));
        $('#' + instId + '-sub-pns').text('L: ' + numberFormat(pnsL) + ' | P: ' + numberFormat(pnsP));

        $('#' + instId + '-val-pppk').text(numberFormat(totPppk));
        $('#' + instId + '-sub-pppk').text('L: ' + numberFormat(pppkL) + ' | P: ' + numberFormat(pppkP));

        $('#' + instId + '-val-pppkpw').text(numberFormat(totPw));
        $('#' + instId + '-sub-pppkpw').text('L: ' + numberFormat(pwL) + ' | P: ' + numberFormat(pwP));

        $('#' + instId + '-val-total').text(numberFormat(grandTotal));
        $('#' + instId + '-sub-total').text('L: ' + numberFormat(pnsL + pppkL + pwL) + ' | P: ' + numberFormat(pnsP + pppkP + pwP));
    }

    function buildChartDataSets(data, schema, activeCat) {
        activeCat = activeCat || 'total';

        // -------------------------------------------------------------------
        // CHART 1: Jabatan per Gender
        // -------------------------------------------------------------------
        var labels1 = [], dataL1 = [], dataP1 = [];
        if (activeCat === 'pns') {
            Object.keys(schema.pnsJabatan).forEach(function(k) {
                labels1.push(schema.pnsJabatan[k]);
                dataL1.push(data.pnsCpns.jabatan[k] ? data.pnsCpns.jabatan[k].l : 0);
                dataP1.push(data.pnsCpns.jabatan[k] ? data.pnsCpns.jabatan[k].p : 0);
            });
        } else if (activeCat === 'pppk') {
            Object.keys(schema.pppkJabatan).forEach(function(k) {
                labels1.push(schema.pppkJabatan[k]);
                dataL1.push(data.pppk.jabatan[k] ? data.pppk.jabatan[k].l : 0);
                dataP1.push(data.pppk.jabatan[k] ? data.pppk.jabatan[k].p : 0);
            });
        } else if (activeCat === 'pppkpw') {
            Object.keys(schema.pppkPwJabatan).forEach(function(k) {
                labels1.push(schema.pppkPwJabatan[k]);
                dataL1.push(data.pppkPw.jabatan[k] ? data.pppkPw.jabatan[k].l : 0);
                dataP1.push(data.pppkPw.jabatan[k] ? data.pppkPw.jabatan[k].p : 0);
            });
        } else {
            // Total
            Object.keys(schema.pnsJabatan).forEach(function(k) {
                labels1.push(schema.pnsJabatan[k]);
                var pnsL = data.pnsCpns.jabatan[k] ? data.pnsCpns.jabatan[k].l : 0;
                var pnsP = data.pnsCpns.jabatan[k] ? data.pnsCpns.jabatan[k].p : 0;
                var pppkL = (data.pppk && data.pppk.jabatan && data.pppk.jabatan[k]) ? data.pppk.jabatan[k].l : 0;
                var pppkP = (data.pppk && data.pppk.jabatan && data.pppk.jabatan[k]) ? data.pppk.jabatan[k].p : 0;
                var pwL = (data.pppkPw && data.pppkPw.jabatan && data.pppkPw.jabatan[k]) ? data.pppkPw.jabatan[k].l : 0;
                var pwP = (data.pppkPw && data.pppkPw.jabatan && data.pppkPw.jabatan[k]) ? data.pppkPw.jabatan[k].p : 0;
                dataL1.push(pnsL + pppkL + pwL);
                dataP1.push(pnsP + pppkP + pwP);
            });
        }

        // -------------------------------------------------------------------
        // CHART 2: Pangkat & Golongan per Gender
        // -------------------------------------------------------------------
        var labels2 = [], dataL2 = [], dataP2 = [];
        if (activeCat === 'pns') {
            Object.keys(schema.pnsPangkat).forEach(function(k) {
                labels2.push(schema.pnsPangkat[k].replace('Golongan ', ''));
                dataL2.push(data.pnsCpns.pangkat[k] ? data.pnsCpns.pangkat[k].l : 0);
                dataP2.push(data.pnsCpns.pangkat[k] ? data.pnsCpns.pangkat[k].p : 0);
            });
        } else if (activeCat === 'pppk') {
            Object.keys(schema.pppkGolongan).forEach(function(k) {
                labels2.push(schema.pppkGolongan[k]);
                var gol = (data.pppk && data.pppk.golongan && data.pppk.golongan[k]) ? data.pppk.golongan[k] : {};
                dataL2.push(gol.l || 0);
                dataP2.push(gol.p || 0);
            });
        } else if (activeCat === 'pppkpw') {
            Object.keys(schema.pppkPwJabatan).forEach(function(k) {
                labels2.push(schema.pppkPwJabatan[k]);
                var jab = (data.pppkPw && data.pppkPw.jabatan && data.pppkPw.jabatan[k]) ? data.pppkPw.jabatan[k] : {};
                dataL2.push(jab.l || 0);
                dataP2.push(jab.p || 0);
            });
        } else {
            // Total
            Object.keys(schema.pnsPangkat).forEach(function(k) {
                labels2.push(schema.pnsPangkat[k].replace('Golongan ', ''));
                dataL2.push(data.pnsCpns.pangkat[k] ? data.pnsCpns.pangkat[k].l : 0);
                dataP2.push(data.pnsCpns.pangkat[k] ? data.pnsCpns.pangkat[k].p : 0);
            });
            Object.keys(schema.pppkGolongan).forEach(function(k) {
                labels2.push('PPPK ' + schema.pppkGolongan[k].replace('Golongan ', ''));
                var gol = (data.pppk && data.pppk.golongan && data.pppk.golongan[k]) ? data.pppk.golongan[k] : {};
                dataL2.push(gol.l || 0);
                dataP2.push(gol.p || 0);
            });
        }

        // -------------------------------------------------------------------
        // CHART 3: Pendidikan
        // -------------------------------------------------------------------
        var labels3 = [], dataTot3 = [];
        Object.keys(schema.pendidikan).forEach(function(k) {
            labels3.push(schema.pendidikan[k]);

            var dPns = data.pnsCpns.didik[k] || {};
            var dPppk = (data.pppk && data.pppk.didik && data.pppk.didik[k]) ? data.pppk.didik[k] : {};
            var dPw = (data.pppkPw && data.pppkPw.didik && data.pppkPw.didik[k]) ? data.pppkPw.didik[k] : {};

            if (activeCat === 'pns') {
                dataTot3.push((dPns.struktural || 0) + (dPns.guru || 0) + (dPns.nakes || 0) + (dPns.teknis || 0));
            } else if (activeCat === 'pppk') {
                dataTot3.push((dPppk.guru || 0) + (dPppk.nakes || 0) + (dPppk.teknis || 0));
            } else if (activeCat === 'pppkpw') {
                dataTot3.push((dPw.guru || 0) + (dPw.nakes || 0) + (dPw.teknis || 0));
            } else {
                dataTot3.push((dPns.struktural || 0) + (dPns.guru || 0) + (dPns.nakes || 0) + (dPns.teknis || 0)
                            + (dPppk.guru || 0) + (dPppk.nakes || 0) + (dPppk.teknis || 0)
                            + (dPw.guru || 0) + (dPw.nakes || 0) + (dPw.teknis || 0));
            }
        });

        // -------------------------------------------------------------------
        // CHART 4: Gender L vs P
        // -------------------------------------------------------------------
        var pL = 0, pP = 0;
        if (activeCat === 'pns') {
            Object.keys(schema.pnsJabatan).forEach(function(k) {
                pL += data.pnsCpns.jabatan[k] ? data.pnsCpns.jabatan[k].l : 0;
                pP += data.pnsCpns.jabatan[k] ? data.pnsCpns.jabatan[k].p : 0;
            });
        } else if (activeCat === 'pppk') {
            Object.keys(schema.pppkJabatan).forEach(function(k) {
                pL += data.pppk.jabatan[k] ? data.pppk.jabatan[k].l : 0;
                pP += data.pppk.jabatan[k] ? data.pppk.jabatan[k].p : 0;
            });
        } else if (activeCat === 'pppkpw') {
            Object.keys(schema.pppkPwJabatan).forEach(function(k) {
                pL += data.pppkPw.jabatan[k] ? data.pppkPw.jabatan[k].l : 0;
                pP += data.pppkPw.jabatan[k] ? data.pppkPw.jabatan[k].p : 0;
            });
        } else {
            Object.keys(schema.pnsJabatan).forEach(function(k) {
                pL += data.pnsCpns.jabatan[k] ? data.pnsCpns.jabatan[k].l : 0;
                pP += data.pnsCpns.jabatan[k] ? data.pnsCpns.jabatan[k].p : 0;
            });
            Object.keys(schema.pppkJabatan).forEach(function(k) {
                pL += data.pppk.jabatan[k] ? data.pppk.jabatan[k].l : 0;
                pP += data.pppk.jabatan[k] ? data.pppk.jabatan[k].p : 0;
            });
            Object.keys(schema.pppkPwJabatan).forEach(function(k) {
                pL += data.pppkPw.jabatan[k] ? data.pppkPw.jabatan[k].l : 0;
                pP += data.pppkPw.jabatan[k] ? data.pppkPw.jabatan[k].p : 0;
            });
        }

        // -------------------------------------------------------------------
        // CHART 5: Komposisi Jenis Pegawai / Distribusi Jabatan
        // -------------------------------------------------------------------
        var labels5 = [], data5 = [], bgColors5 = [];
        if (activeCat === 'pns') {
            Object.keys(schema.pnsJabatan).forEach(function(k) {
                labels5.push(schema.pnsJabatan[k]);
                var j = data.pnsCpns.jabatan[k] || {};
                data5.push((j.l || 0) + (j.p || 0));
            });
            bgColors5 = ['#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe', '#dbeafe', '#eff6ff'];
        } else if (activeCat === 'pppk') {
            Object.keys(schema.pppkJabatan).forEach(function(k) {
                labels5.push(schema.pppkJabatan[k]);
                var j = data.pppk.jabatan[k] || {};
                data5.push((j.l || 0) + (j.p || 0));
            });
            bgColors5 = ['#10b981', '#34d399', '#6ee7b7'];
        } else if (activeCat === 'pppkpw') {
            Object.keys(schema.pppkPwJabatan).forEach(function(k) {
                labels5.push(schema.pppkPwJabatan[k]);
                var j = data.pppkPw.jabatan[k] || {};
                data5.push((j.l || 0) + (j.p || 0));
            });
            bgColors5 = ['#f59e0b', '#fbbf24', '#fde68a'];
        } else {
            // Total comparison
            var pnsTot = 0, pppkTot = 0, pwTot = 0;
            Object.keys(schema.pnsJabatan).forEach(function(k) {
                var j = data.pnsCpns.jabatan[k] || {};
                pnsTot += (j.l || 0) + (j.p || 0);
            });
            Object.keys(schema.pppkJabatan).forEach(function(k) {
                var j = data.pppk.jabatan[k] || {};
                pppkTot += (j.l || 0) + (j.p || 0);
            });
            Object.keys(schema.pppkPwJabatan).forEach(function(k) {
                var j = data.pppkPw.jabatan[k] || {};
                pwTot += (j.l || 0) + (j.p || 0);
            });

            labels5 = ['PNS & CPNS', 'PPPK', 'PPPK-PW'];
            data5 = [pnsTot, pppkTot, pwTot];
            bgColors5 = ['#2563eb', '#10b981', '#f59e0b'];
        }

        return {
            c1: { labels: labels1, dataL: dataL1, dataP: dataP1 },
            c2: { labels: labels2, dataL: dataL2, dataP: dataP2 },
            c3: { labels: labels3, dataTotal: dataTot3 },
            c4: { labels: ['Laki-Laki', 'Perempuan'], data: [pL, pP] },
            c5: { labels: labels5, data: data5, colors: bgColors5 }
        };
    }

    function initFrontendCharts(instId, data, schema, activeCat) {
        var sets = buildChartDataSets(data, schema, activeCat);
        chartInstances[instId] = {};

        // Chart 1
        var c1 = document.getElementById(instId + '-chart-pns-jabatan');
        if (c1) {
            chartInstances[instId].c1 = new Chart(c1, {
                type: 'bar',
                data: {
                    labels: sets.c1.labels,
                    datasets: [
                        { label: 'Laki-Laki', data: sets.c1.dataL, backgroundColor: '#3b82f6' },
                        { label: 'Perempuan', data: sets.c1.dataP, backgroundColor: '#ec4899' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Chart 2
        var c2 = document.getElementById(instId + '-chart-pns-pangkat');
        if (c2) {
            chartInstances[instId].c2 = new Chart(c2, {
                type: 'bar',
                data: {
                    labels: sets.c2.labels,
                    datasets: [
                        { label: 'Laki-Laki', data: sets.c2.dataL, backgroundColor: '#2563eb' },
                        { label: 'Perempuan', data: sets.c2.dataP, backgroundColor: '#f43f5e' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Chart 3
        var c3 = document.getElementById(instId + '-chart-pns-pendidikan');
        if (c3) {
            chartInstances[instId].c3 = new Chart(c3, {
                type: 'doughnut',
                data: {
                    labels: sets.c3.labels,
                    datasets: [{
                        data: sets.c3.dataTotal,
                        backgroundColor: [
                            '#94a3b8', '#64748b', '#3b82f6', '#0284c7', '#06b6d4',
                            '#10b981', '#84cc16', '#eab308', '#f97316', '#8b5cf6'
                        ]
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Chart 4
        var c4 = document.getElementById(instId + '-chart-gender-total');
        if (c4) {
            chartInstances[instId].c4 = new Chart(c4, {
                type: 'doughnut',
                data: {
                    labels: sets.c4.labels,
                    datasets: [{
                        data: sets.c4.data,
                        backgroundColor: ['#3b82f6', '#ec4899']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Chart 5
        var c5 = document.getElementById(instId + '-chart-jenis-pegawai');
        if (c5) {
            chartInstances[instId].c5 = new Chart(c5, {
                type: 'bar',
                data: {
                    labels: sets.c5.labels,
                    datasets: [{
                        label: 'Jumlah Pegawai',
                        data: sets.c5.data,
                        backgroundColor: sets.c5.colors
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    }

    function updateFrontendCharts(instId, data, schema, activeCat) {
        if (!chartInstances[instId]) return;
        var sets = buildChartDataSets(data, schema, activeCat);

        if (chartInstances[instId].c1) {
            chartInstances[instId].c1.data.labels = sets.c1.labels;
            chartInstances[instId].c1.data.datasets[0].data = sets.c1.dataL;
            chartInstances[instId].c1.data.datasets[1].data = sets.c1.dataP;
            chartInstances[instId].c1.update();
        }
        if (chartInstances[instId].c2) {
            chartInstances[instId].c2.data.labels = sets.c2.labels;
            chartInstances[instId].c2.data.datasets[0].data = sets.c2.dataL;
            chartInstances[instId].c2.data.datasets[1].data = sets.c2.dataP;
            chartInstances[instId].c2.update();
        }
        if (chartInstances[instId].c3) {
            chartInstances[instId].c3.data.labels = sets.c3.labels;
            chartInstances[instId].c3.data.datasets[0].data = sets.c3.dataTotal;
            chartInstances[instId].c3.update();
        }
        if (chartInstances[instId].c4) {
            chartInstances[instId].c4.data.datasets[0].data = sets.c4.data;
            chartInstances[instId].c4.update();
        }
        if (chartInstances[instId].c5) {
            chartInstances[instId].c5.data.labels = sets.c5.labels;
            chartInstances[instId].c5.data.datasets[0].data = sets.c5.data;
            chartInstances[instId].c5.data.datasets[0].backgroundColor = sets.c5.colors;
            chartInstances[instId].c5.update();
        }
    }

    function numberFormat(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

})(jQuery);
