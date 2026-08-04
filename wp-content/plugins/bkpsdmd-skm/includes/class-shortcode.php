<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BKSKM_Shortcode {

    public static function init() {
        add_shortcode( 'bkpsdmd_skm_form', array( __CLASS__, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
    }

    public static function register_assets() {
        wp_register_style( 'bkskm-frontend-css', BKSKM_URL . 'assets/css/skm-frontend.css', array(), BKSKM_VERSION );
        wp_register_script( 'bkskm-frontend-js', BKSKM_URL . 'assets/js/skm-frontend.js', array( 'jquery' ), BKSKM_VERSION, true );

        wp_localize_script( 'bkskm-frontend-js', 'bkskm_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'bkskm_nonce_action' ),
        ) );
    }

    public static function get_questions() {
        return array(
            1  => array( 'text' => 'Informasi pelayanan tersedia melalui media elektronik maupun nonelektronik', 'type' => 'agree' ),
            2  => array( 'text' => 'Kesesuaian persyaratan dengan informasi yang diberikan', 'type' => 'conform' ),
            3  => array( 'text' => 'Standar dan prosedur layanan diinformasikan dengan jelas', 'type' => 'agree' ),
            4  => array( 'text' => 'Prosedur/Alur layanan mudah dipahami dan dilakukan', 'type' => 'agree' ),
            5  => array( 'text' => 'Layanan diberikan sesuai prosedur tanpa kecurangan', 'type' => 'agree' ),
            6  => array( 'text' => 'Jangka waktu layanan sesuai dengan yang diinformasikan', 'type' => 'conform' ),
            7  => array( 'text' => 'Biaya layanan sesuai dengan yang diinformasikan', 'type' => 'conform' ),
            8  => array( 'text' => 'Tidak ada pungutan liar (pungli) dalam pelayanan', 'type' => 'agree' ),
            9  => array( 'text' => 'Tidak ada percaloan/perantara tidak resmi dalam pelayanan', 'type' => 'agree' ),
            10 => array( 'text' => 'Produk layanan yang diterima sesuai dengan yang dipublikasikan', 'type' => 'conform' ),
            11 => array( 'text' => 'Aplikasi sistem pelayanan merespon kebutuhan dengan cepat (membuka halaman, konten, pencarian informasi, unduh/unggah)', 'type' => 'agree' ),
            12 => array( 'text' => 'Fitur pada aplikasi sistem layanan mudah digunakan', 'type' => 'agree' ),
            13 => array( 'text' => 'Seluruh pengguna layanan dilayani secara adil tanpa diskriminasi', 'type' => 'agree' ),
            14 => array( 'text' => 'Pelayanan diberikan tanpa imbalan uang, barang, atau fasilitas di luar aturan', 'type' => 'agree' ),
            15 => array( 'text' => 'Layanan konsultasi dan pengaduan mudah diakses', 'type' => 'agree' ),
            16 => array( 'text' => 'Sistem layanan online nyaman dan mudah digunakan', 'type' => 'agree' ),
        );
    }

    public static function render_shortcode( $atts ) {
        wp_enqueue_style( 'bkskm-frontend-css' );
        wp_enqueue_script( 'bkskm-frontend-js' );

        $instansi = get_option( 'bkskm_instansi_name', 'BKPSDMD Kabupaten Bangka' );
        $questions = self::get_questions();

        $agree_labels = array(
            1 => 'Sangat tidak setuju',
            2 => 'Tidak setuju',
            3 => 'Setuju',
            4 => 'Sangat setuju',
        );

        $conform_labels = array(
            1 => 'Sangat tidak sesuai',
            2 => 'Tidak sesuai',
            3 => 'Sesuai',
            4 => 'Sangat sesuai',
        );

        ob_start();
        ?>
        <div class="bkskm-container">
            <div class="bkskm-header">
                <div class="bkskm-badge">Formulir Kuesioner Resmi</div>
                <h2>SURVEI KEPUASAN MASYARAKAT (SKM)</h2>
                <p class="bkskm-subheading">Pada Unit Layanan <?php echo esc_html( $instansi ); ?></p>
                <p class="bkskm-desc">Partisipasi Anda sangat berharga untuk meningkatkan kualitas pelayanan publik kami.</p>
            </div>

            <form id="bkskm-survey-form" class="bkskm-form" method="post">
                <!-- Honeypot anti-bot -->
                <div style="display:none;">
                    <input type="text" name="bkskm_website_check" value="" tabindex="-1" autocomplete="off">
                </div>

                <!-- Step Indicator -->
                <div class="bkskm-steps">
                    <div class="bkskm-step-item active" data-step="1">
                        <span class="step-num">1</span>
                        <span class="step-text">Identitas</span>
                    </div>
                    <div class="bkskm-step-line"></div>
                    <div class="bkskm-step-item" data-step="2">
                        <span class="step-num">2</span>
                        <span class="step-text">Penilaian Layanan</span>
                    </div>
                    <div class="bkskm-step-line"></div>
                    <div class="bkskm-step-item" data-step="3">
                        <span class="step-num">3</span>
                        <span class="step-text">Kritik & Saran</span>
                    </div>
                </div>

                <!-- SECTION 1: IDENTITAS RESPONDEN -->
                <div class="bkskm-section bkskm-section-active" id="bkskm-section-1">
                    <h3 class="bkskm-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        IDENTITAS RESPONDEN
                    </h3>

                    <!-- Tanggal Layanan -->
                    <div class="bkskm-field">
                        <label class="bkskm-label" for="tgl_layanan">Tanggal Menerima Layanan <span class="req">*</span></label>
                        <input type="date" id="tgl_layanan" name="tgl_layanan" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" class="bkskm-input" required>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="bkskm-field">
                        <label class="bkskm-label">Jenis Kelamin <span class="req">*</span></label>
                        <div class="bkskm-radio-group grid-2">
                            <label class="bkskm-radio-card">
                                <input type="radio" name="jenis_kelamin" value="Laki-laki" required>
                                <span class="card-content">
                                    <span class="radio-icon">👨</span>
                                    <span>Laki-laki</span>
                                </span>
                            </label>
                            <label class="bkskm-radio-card">
                                <input type="radio" name="jenis_kelamin" value="Perempuan">
                                <span class="card-content">
                                    <span class="radio-icon">👩</span>
                                    <span>Perempuan</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Pendidikan -->
                    <div class="bkskm-field">
                        <label class="bkskm-label">Pendidikan Terakhir <span class="req">*</span></label>
                        <div class="bkskm-radio-group grid-2">
                            <?php
                            $edu_list = array(
                                'Tidak sekolah', 'SD/Sederajat', 'SMP/Sederajat',
                                'SMA/Sederajat', 'D1/D2/D3', 'D4/S1', 'S2', 'S3'
                            );
                            foreach ( $edu_list as $edu ) :
                            ?>
                                <label class="bkskm-radio-pill">
                                    <input type="radio" name="pendidikan" value="<?php echo esc_attr( $edu ); ?>" required>
                                    <span><?php echo esc_html( $edu ); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Usia -->
                    <div class="bkskm-field">
                        <label class="bkskm-label">Kelompok Usia <span class="req">*</span></label>
                        <div class="bkskm-radio-group grid-3">
                            <?php
                            $age_list = array(
                                '< 17 tahun', '17-25 tahun', '26-34 tahun',
                                '35-44 tahun', '45-54 tahun', '55-65 tahun', '>65 tahun'
                            );
                            foreach ( $age_list as $age ) :
                            ?>
                                <label class="bkskm-radio-pill">
                                    <input type="radio" name="usia" value="<?php echo esc_attr( $age ); ?>" required>
                                    <span><?php echo esc_html( $age ); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Pekerjaan -->
                    <div class="bkskm-field">
                        <label class="bkskm-label">Pekerjaan Utama <span class="req">*</span></label>
                        <div class="bkskm-radio-group grid-3">
                            <?php
                            $job_list = array(
                                'ASN', 'TNI', 'POLRI', 'Swasta', 'Wirausaha',
                                'Ibu Rumah Tangga', 'Pelajar/Mahasiswa', 'Petani/Nelayan',
                                'Pekerja Lepas/Freelance', 'Pensiunan', 'Lainnya'
                            );
                            foreach ( $job_list as $job ) :
                            ?>
                                <label class="bkskm-radio-pill">
                                    <input type="radio" name="pekerjaan" value="<?php echo esc_attr( $job ); ?>" required class="bkskm-job-radio">
                                    <span><?php echo esc_html( $job ); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div id="bkskm-pekerjaan-lainnya-wrapper" class="bkskm-subfield" style="display: none; margin-top: 10px;">
                            <input type="text" name="pekerjaan_lainnya" id="pekerjaan_lainnya" class="bkskm-input" placeholder="Tuliskan pekerjaan Anda...">
                        </div>
                    </div>

                    <!-- Disabilitas -->
                    <div class="bkskm-field">
                        <label class="bkskm-label">Apakah Anda merupakan penyandang disabilitas / pendamping penyandang disabilitas? <span class="req">*</span></label>
                        <div class="bkskm-radio-group grid-2">
                            <label class="bkskm-radio-card">
                                <input type="radio" name="is_disabilitas" value="Ya" class="bkskm-disabilitas-radio" required>
                                <span class="card-content">
                                    <span>Ya</span>
                                </span>
                            </label>
                            <label class="bkskm-radio-card">
                                <input type="radio" name="is_disabilitas" value="Tidak" class="bkskm-disabilitas-radio">
                                <span class="card-content">
                                    <span>Tidak</span>
                                </span>
                            </label>
                        </div>

                        <!-- Subfield Disabilitas -->
                        <div id="bkskm-disabilitas-wrapper" class="bkskm-subfield" style="display: none; margin-top: 15px;">
                            <label class="bkskm-label">Jika ya, jenis disabilitas apa yang Anda miliki / dampingi?</label>
                            <div class="bkskm-radio-group grid-2">
                                <?php
                                $dis_list = array( 'Disabilitas Fisik', 'Disabilitas Intelektual', 'Disabilitas Mental', 'Disabilitas Sensorik' );
                                foreach ( $dis_list as $dis ) :
                                ?>
                                    <label class="bkskm-radio-pill">
                                        <input type="radio" name="jenis_disabilitas" value="<?php echo esc_attr( $dis ); ?>">
                                        <span><?php echo esc_html( $dis ); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="bkskm-actions">
                        <div></div>
                        <button type="button" class="bkskm-btn bkskm-btn-next" data-next="2">
                            Lanjut ke Penilaian
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </div>
                </div>

                <!-- SECTION 2: PENDAPAT RESPONDEN TENTANG PELAYANAN -->
                <div class="bkskm-section" id="bkskm-section-2">
                    <h3 class="bkskm-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                        PENDAPAT RESPONDEN TENTANG PELAYANAN
                    </h3>

                    <div class="bkskm-q-list">
                        <?php foreach ( $questions as $q_num => $q_info ) :
                            $labels = ( $q_info['type'] === 'agree' ) ? $agree_labels : $conform_labels;
                        ?>
                            <div class="bkskm-q-card" id="q-card-<?php echo $q_num; ?>">
                                <div class="bkskm-q-header">
                                    <span class="bkskm-q-num"><?php echo $q_num; ?></span>
                                    <div class="bkskm-q-text"><?php echo esc_html( $q_info['text'] ); ?> <span class="req">*</span></div>
                                </div>
                                <div class="bkskm-q-options">
                                    <?php foreach ( $labels as $val => $lbl ) : ?>
                                        <label class="bkskm-likert-opt val-<?php echo $val; ?>">
                                            <input type="radio" name="q<?php echo $q_num; ?>" value="<?php echo $val; ?>" required>
                                            <span class="likert-box">
                                                <span class="val-num"><?php echo $val; ?></span>
                                                <span class="val-label"><?php echo esc_html( $lbl ); ?></span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="bkskm-actions">
                        <button type="button" class="bkskm-btn bkskm-btn-prev" data-prev="1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Kembali
                        </button>
                        <button type="button" class="bkskm-btn bkskm-btn-next" data-next="3">
                            Lanjut ke Kritik & Saran
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </div>
                </div>

                <!-- SECTION 3: KRITIK DAN SARAN -->
                <div class="bkskm-section" id="bkskm-section-3">
                    <h3 class="bkskm-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        KRITIK DAN SARAN
                    </h3>

                    <div class="bkskm-field">
                        <label class="bkskm-label" for="kritik_saran">Masukan, Kritik & Saran Anda untuk Peningkatan Pelayanan Kami:</label>
                        <textarea id="kritik_saran" name="kritik_saran" rows="5" class="bkskm-textarea" placeholder="Tuliskan masukan, kritik, atau saran Anda secara jujur dan membangun di sini..."></textarea>
                    </div>

                    <div class="bkskm-actions">
                        <button type="button" class="bkskm-btn bkskm-btn-prev" data-prev="2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Kembali
                        </button>
                        <button type="submit" id="bkskm-submit-btn" class="bkskm-btn bkskm-btn-submit">
                            <span class="btn-text">Kirim Survei Sekarang</span>
                            <span class="btn-spinner" style="display:none;">
                                <svg class="spinner" viewBox="0 0 50 50"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg>
                            </span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Success Alert Modal / Message -->
            <div id="bkskm-success-message" class="bkskm-success-box" style="display:none;">
                <div class="success-icon">🎉</div>
                <h3>Terima Kasih Atas Partisipasi Anda!</h3>
                <p>Tanggapan survei kepuasan masyarakat Anda telah berhasil tersimpan ke sistem kami.</p>
                <button type="button" onclick="location.reload();" class="bkskm-btn bkskm-btn-reload">Isi Kuesioner Baru</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
