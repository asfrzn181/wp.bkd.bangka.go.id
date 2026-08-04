<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BKSKM_Admin {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
    }

    public static function register_settings() {
        register_setting( 'bkskm_settings_group', 'bkskm_instansi_name' );
        register_setting( 'bkskm_settings_group', 'bkskm_logo_url' );
        register_setting( 'bkskm_settings_group', 'bkskm_custom_slug', array(
            'type'              => 'string',
            'sanitize_callback' => function( $val ) {
                $slug = sanitize_title( $val );
                if ( empty( $slug ) ) {
                    $slug = 'survei-skm';
                }
                return $slug;
            }
        ) );

        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
            BKSKM_Endpoint::flush_rules();
        }
    }

    public static function add_admin_menu() {
        add_menu_page(
            'Survei Kepuasan Masyarakat',
            'Survei SKM',
            'manage_options',
            'bkpsdmd-skm',
            array( __CLASS__, 'render_analytics_page' ),
            'dashicons-chart-bar',
            30
        );

        add_submenu_page(
            'bkpsdmd-skm',
            'Analitik & IKM',
            'Analitik & IKM',
            'manage_options',
            'bkpsdmd-skm',
            array( __CLASS__, 'render_analytics_page' )
        );

        add_submenu_page(
            'bkpsdmd-skm',
            'Data Responden',
            'Data Responden',
            'manage_options',
            'bkpsdmd-skm-data',
            array( __CLASS__, 'render_data_page' )
        );

        add_submenu_page(
            'bkpsdmd-skm',
            'Pengaturan',
            'Pengaturan',
            'manage_options',
            'bkpsdmd-skm-settings',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    public static function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'bkpsdmd-skm' ) === false ) {
            return;
        }

        wp_enqueue_style( 'bkskm-admin-css', BKSKM_URL . 'assets/css/skm-admin.css', array(), BKSKM_VERSION );

        wp_enqueue_script( 'bkskm-admin-js', BKSKM_URL . 'assets/js/skm-admin.js', array( 'jquery' ), BKSKM_VERSION, true );
        wp_localize_script( 'bkskm-admin-js', 'bkskm_admin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'bkskm_admin_nonce' ),
        ) );
    }

    /**
     * Halaman Dashboard Analytics & IKM
     */
    public static function render_analytics_page() {
        $start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
        $end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';

        $stats = BKSKM_DB::get_stats( $start_date, $end_date );
        $questions = BKSKM_Shortcode::get_questions();
        $instansi = get_option( 'bkskm_instansi_name', 'BKPSDMD Kabupaten Bangka' );
        ?>
        <div class="wrap bkskm-admin-wrap">
            <div class="bkskm-admin-header">
                <div>
                    <h1>Survei Kepuasan Masyarakat (SKM)</h1>
                    <p class="bkskm-admin-sub">Unit Layanan: <strong><?php echo esc_html( $instansi ); ?></strong> (PermenPANRB No. 14 Tahun 2017)</p>
                </div>
                <div class="bkskm-admin-actions">
                    <a href="<?php echo esc_url( BKSKM_Endpoint::get_survey_url() ); ?>" target="_blank" class="button button-secondary button-large">
                        <span class="dashicons dashicons-external" style="line-height:28px;"></span> Buka Halaman Survei
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=bkpsdmd_skm_export_csv&start_date=' . $start_date . '&end_date=' . $end_date ) ); ?>" class="button button-primary button-large">
                        <span class="dashicons dashicons-download" style="line-height:28px;"></span> Unduh CSV Tanggapan
                    </a>
                </div>
            </div>

            <!-- Filter Tanggal -->
            <div class="bkskm-admin-card bkskm-filter-card">
                <form method="get" action="">
                    <input type="hidden" name="page" value="bkpsdmd-skm">
                    <div class="bkskm-filter-flex">
                        <div class="filter-item">
                            <label>Dari Tanggal:</label>
                            <input type="date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>" class="regular-text">
                        </div>
                        <div class="filter-item">
                            <label>Sampai Tanggal:</label>
                            <input type="date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>" class="regular-text">
                        </div>
                        <div class="filter-item btn-item">
                            <button type="submit" class="button button-secondary">Filter Data</button>
                            <?php if ( ! empty( $start_date ) || ! empty( $end_date ) ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=bkpsdmd-skm' ) ); ?>" class="button">Reset</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Metric Stats Grid -->
            <div class="bkskm-metrics-grid">
                <!-- Score Card -->
                <div class="bkskm-metric-card main-score" style="border-left-color: <?php echo esc_attr( $stats['color'] ); ?>;">
                    <div class="metric-title">NILAI INDEKS SKM (IKM)</div>
                    <div class="metric-value" style="color: <?php echo esc_attr( $stats['color'] ); ?>;"><?php echo esc_html( number_format( $stats['ikm_score'], 2 ) ); ?></div>
                    <div class="metric-subtitle">
                        Rata-Rata Unsur: <strong><?php echo esc_html( $stats['avg_unsur'] ); ?> / 4.00</strong>
                    </div>
                </div>

                <!-- Mutu Card -->
                <div class="bkskm-metric-card">
                    <div class="metric-title">MUTU PELAYANAN</div>
                    <div class="metric-badge" style="background-color: <?php echo esc_attr( $stats['color'] ); ?>;">
                        MUTU <?php echo esc_html( $stats['mutu'] ); ?>
                    </div>
                    <div class="metric-subtitle">Kinerja: <strong><?php echo esc_html( $stats['kinerja'] ); ?></strong></div>
                </div>

                <!-- Responden Card -->
                <div class="bkskm-metric-card">
                    <div class="metric-title">TOTAL RESPONDEN</div>
                    <div class="metric-value"><?php echo esc_html( number_format( $stats['total_respondents'] ) ); ?></div>
                    <div class="metric-subtitle">Orang Terdata</div>
                </div>
            </div>

            <!-- Breakdown Per Unsur (Q1 - Q16) -->
            <div class="bkskm-admin-card">
                <div class="bkskm-card-header">
                    <h2>Breakdown Nilai Rata-Rata Per Unsur Layanan (Skala 1.00 - 4.00)</h2>
                    <span class="bkskm-hint">Diurutkan berdasarkan unsur U1 s.d U16 (PermenPANRB No. 14 Tahun 2017)</span>
                </div>
                <div class="bkskm-unsur-grid">
                    <?php foreach ( $questions as $q_num => $q_info ) :
                        $avg = $stats['questions_avg'][$q_num] ?? 0;
                        $pct = ( $avg / 4.00 ) * 100;

                        // Unsur Mutu Color
                        $u_color = '#ef4444'; // Red
                        $u_mutu = 'D';
                        if ( $avg >= 3.5325 ) {
                            $u_color = '#10b981'; // Green
                            $u_mutu = 'A';
                        } elseif ( $avg >= 3.0644 ) {
                            $u_color = '#2563eb'; // Blue
                            $u_mutu = 'B';
                        } elseif ( $avg >= 2.6000 ) {
                            $u_color = '#f59e0b'; // Amber
                            $u_mutu = 'C';
                        }
                    ?>
                        <div class="unsur-item">
                            <div class="unsur-top">
                                <span class="unsur-badge">U<?php echo $q_num; ?></span>
                                <div class="unsur-score-wrap">
                                    <span class="unsur-mutu-tag" style="background-color: <?php echo esc_attr( $u_color ); ?>;">Mutu <?php echo $u_mutu; ?></span>
                                    <span class="unsur-val" style="color: <?php echo esc_attr( $u_color ); ?>;"><?php echo esc_html( number_format( $avg, 2 ) ); ?> <small>/ 4.00</small></span>
                                </div>
                            </div>
                            <div class="unsur-title"><?php echo esc_html( $q_info['text'] ); ?></div>
                            <div class="unsur-bottom">
                                <div class="unsur-bar-bg">
                                    <div class="unsur-bar-fill" style="width: <?php echo esc_attr( $pct ); ?>%; background-color: <?php echo esc_attr( $u_color ); ?>;"></div>
                                </div>
                                <span class="unsur-pct"><?php echo esc_html( number_format( $pct, 1 ) ); ?>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Demographics Charts / Tables -->
            <div class="bkskm-demo-grid">
                <!-- Gender -->
                <div class="bkskm-admin-card">
                    <h3>Jenis Kelamin</h3>
                    <ul class="bkskm-demo-list">
                        <?php foreach ( $stats['demographics']['gender'] as $item ) : ?>
                            <li>
                                <span><?php echo esc_html( $item['label'] ); ?></span>
                                <strong><?php echo esc_html( $item['count'] ); ?> orang</strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Education -->
                <div class="bkskm-admin-card">
                    <h3>Tingkat Pendidikan</h3>
                    <ul class="bkskm-demo-list">
                        <?php foreach ( $stats['demographics']['education'] as $item ) : ?>
                            <li>
                                <span><?php echo esc_html( $item['label'] ); ?></span>
                                <strong><?php echo esc_html( $item['count'] ); ?> orang</strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Age -->
                <div class="bkskm-admin-card">
                    <h3>Kelompok Usia</h3>
                    <ul class="bkskm-demo-list">
                        <?php foreach ( $stats['demographics']['age'] as $item ) : ?>
                            <li>
                                <span><?php echo esc_html( $item['label'] ); ?></span>
                                <strong><?php echo esc_html( $item['count'] ); ?> orang</strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Disability -->
                <div class="bkskm-admin-card">
                    <h3>Penyandang Disabilitas</h3>
                    <ul class="bkskm-demo-list">
                        <?php foreach ( $stats['demographics']['disability'] as $item ) : ?>
                            <li>
                                <span><?php echo esc_html( $item['label'] ); ?></span>
                                <strong><?php echo esc_html( $item['count'] ); ?> orang</strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Halaman Data Responden (Tabel & Detail)
     */
    public static function render_data_page() {
        $paged      = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
        $start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
        $end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';

        $limit = 15;
        $offset = ( $paged - 1 ) * $limit;

        $responses = BKSKM_DB::get_responses( $limit, $offset, $search, $start_date, $end_date );
        $total_items = BKSKM_DB::count_responses( $search, $start_date, $end_date );
        $total_pages = ceil( $total_items / $limit );
        ?>
        <div class="wrap bkskm-admin-wrap">
            <h1>Data Responden Kuesioner SKM</h1>

            <!-- Search & Filter -->
            <div class="tablenav top">
                <form method="get" action="" class="alignleft actions">
                    <input type="hidden" name="page" value="bkpsdmd-skm-data">
                    <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Cari pekerjaan / kritik..." class="regular-text">
                    <input type="date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>">
                    <input type="date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>">
                    <button type="submit" class="button">Cari Data</button>
                    <?php if ( ! empty( $search ) || ! empty( $start_date ) || ! empty( $end_date ) ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=bkpsdmd-skm-data' ) ); ?>" class="button">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Table Data -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Tgl Layanan</th>
                        <th>Gender</th>
                        <th>Pendidikan</th>
                        <th>Usia</th>
                        <th>Pekerjaan</th>
                        <th>Disabilitas</th>
                        <th>Rata-rata Skor</th>
                        <th>Kritik & Saran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $responses ) ) : ?>
                        <tr>
                            <td colspan="10">Belum ada data responden yang masuk.</td>
                        </tr>
                    <?php else :
                        foreach ( $responses as $row ) :
                            $sum = 0;
                            for ( $i = 1; $i <= 16; $i++ ) {
                                $sum += intval( $row["q{$i}"] );
                            }
                            $avg_score = round( $sum / 16, 2 );
                    ?>
                        <tr id="row-<?php echo $row['id']; ?>">
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td><?php echo esc_html( date( 'd/m/Y', strtotime( $row['tgl_layanan'] ) ) ); ?></td>
                            <td><?php echo esc_html( $row['jenis_kelamin'] ); ?></td>
                            <td><?php echo esc_html( $row['pendidikan'] ); ?></td>
                            <td><?php echo esc_html( $row['usia'] ); ?></td>
                            <td>
                                <?php echo esc_html( $row['pekerjaan'] ); ?>
                                <?php if ( ! empty( $row['pekerjaan_lainnya'] ) ) : ?>
                                    <br><small style="color:#666;">(<?php echo esc_html( $row['pekerjaan_lainnya'] ); ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo esc_html( $row['is_disabilitas'] ); ?>
                                <?php if ( $row['is_disabilitas'] === 'Ya' && ! empty( $row['jenis_disabilitas'] ) ) : ?>
                                    <br><small style="color:#666;"><?php echo esc_html( $row['jenis_disabilitas'] ); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo esc_html( $avg_score ); ?> / 4</strong></td>
                            <td>
                                <?php echo esc_html( wp_trim_words( $row['kritik_saran'], 8, '...' ) ); ?>
                            </td>
                            <td>
                                <button type="button" class="button button-small bkskm-view-btn" data-row='<?php echo esc_attr( json_encode( $row ) ); ?>'>Detail</button>
                                <button type="button" class="button button-small button-link-delete bkskm-delete-btn" data-id="<?php echo $row['id']; ?>">Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ( $total_pages > 1 ) : ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php echo $total_items; ?> item</span>
                        <?php
                        echo paginate_links( array(
                            'base'      => add_query_arg( 'paged', '%#%' ),
                            'format'    => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total'     => $total_pages,
                            'current'   => $paged,
                        ) );
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Detail Modal Dialog -->
        <div id="bkskm-modal-overlay" class="bkskm-modal-overlay" style="display:none;">
            <div class="bkskm-modal">
                <div class="bkskm-modal-header">
                    <h2>Detail Tanggapan Responden #<span id="modal-resp-id"></span></h2>
                    <button type="button" class="bkskm-modal-close">&times;</button>
                </div>
                <div class="bkskm-modal-body" id="modal-resp-body">
                    <!-- Populated by JS -->
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Halaman Pengaturan Plugin
     */
    public static function render_settings_page() {
        $slug = get_option( 'bkskm_custom_slug', 'survei-skm' );
        $url  = BKSKM_Endpoint::get_survey_url();
        ?>
        <div class="wrap bkskm-admin-wrap">
            <h1>Pengaturan Survei SKM</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'bkskm_settings_group' );
                do_settings_sections( 'bkskm_settings_group' );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="bkskm_instansi_name">Nama Unit Layanan / Instansi</label></th>
                        <td>
                            <input type="text" id="bkskm_instansi_name" name="bkskm_instansi_name" value="<?php echo esc_attr( get_option( 'bkskm_instansi_name', 'BKPSDMD Kabupaten Bangka' ) ); ?>" class="large-text">
                            <p class="description">Nama instansi ini akan ditampilkan pada judul kuesioner survei.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bkskm_logo_url">Logo Instansi</label></th>
                        <td>
                            <?php $logo_url = get_option( 'bkskm_logo_url', 'https://bkd.bangka.go.id/wp-content/uploads/2026/07/Logo-Prima-no-bg.png' ); ?>
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                                <input type="url" id="bkskm_logo_url" name="bkskm_logo_url" value="<?php echo esc_attr( $logo_url ); ?>" class="large-text" placeholder="https://...">
                                <button type="button" id="bkskm-upload-logo-btn" class="button button-secondary">Pilih / Unggah Logo</button>
                            </div>
                            <div id="bkskm-logo-preview" style="background:#0f172a; padding:15px; display:inline-block; border-radius:8px;">
                                <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo Preview" style="max-height:60px; display:block;">
                            </div>
                            <p class="description">Logo ini akan ditampilkan di bagian atas halaman survei SKM.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bkskm_custom_slug">URL Slug Halaman Survei</label></th>
                        <td>
                            <input type="text" id="bkskm_custom_slug" name="bkskm_custom_slug" value="<?php echo esc_attr( $slug ); ?>" class="regular-text">
                            <p class="description">Alamat jalur URL khusus untuk mengakses halaman survei (contoh: <code>survei-skm</code> atau <code>skm</code>).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Link Langsung URL Survei</label></th>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <input type="text" id="bkskm_direct_url" value="<?php echo esc_url( $url ); ?>" readonly class="large-text" style="background:#f6f7f7; font-weight:600;">
                                <a href="<?php echo esc_url( $url ); ?>" target="_blank" class="button button-secondary">Buka URL</a>
                            </div>
                            <p class="description">Anda dapat membagikan Link URL ini secara langsung kepada responden tanpa memerlukan shortcode.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button( 'Simpan Pengaturan' ); ?>
            </form>
        </div>
        <?php
    }
}
