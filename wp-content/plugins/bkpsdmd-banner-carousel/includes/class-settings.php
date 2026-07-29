<?php
/**
 * Settings API — Pengaturan global carousel
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BKBC_Settings {

    const OPTION_KEY = 'bkbc_carousel_settings';

    public static function defaults() {
        return [
            'animation'  => 'slide',    // fade | slide | zoom
            'duration'   => 600,        // ms
            'autoplay'   => '1',
            'interval'   => 5,          // detik
            'show_dots'  => '1',
            'show_arrows'=> '1',
            'overlay_opacity' => 40,    // 0-100
        ];
    }

    public static function get( $key = null ) {
        $saved    = get_option( self::OPTION_KEY, [] );
        $settings = wp_parse_args( $saved, self::defaults() );
        return $key ? ( $settings[ $key ] ?? null ) : $settings;
    }

    // ── Admin menu ────────────────────────────────────────────────────────────
    public static function register_menu() {
        // Menu utama
        add_menu_page(
            'Banner Carousel',
            'Banner Carousel',
            'manage_options',
            'bkbc-carousel',
            [ __CLASS__, 'render_manage_page' ],
            'dashicons-images-alt2',
            25
        );
        // Submenu: Kelola Slide
        add_submenu_page(
            'bkbc-carousel',
            'Kelola Slide',
            'Kelola Slide',
            'manage_options',
            'bkbc-carousel',
            [ __CLASS__, 'render_manage_page' ]
        );
        // Submenu: Pengaturan
        add_submenu_page(
            'bkbc-carousel',
            'Pengaturan Carousel',
            'Pengaturan',
            'manage_options',
            'bkbc-settings',
            [ __CLASS__, 'render_settings_page' ]
        );
        // Submenu: Tambah Slide (link ke CPT)
        add_submenu_page(
            'bkbc-carousel',
            'Tambah Slide',
            '+ Tambah Slide',
            'manage_options',
            'post-new.php?post_type=banner_slide'
        );
    }

    // ── Settings registration ─────────────────────────────────────────────────
    public static function register_settings() {
        register_setting(
            'bkbc_settings_group',
            self::OPTION_KEY,
            [ __CLASS__, 'sanitize' ]
        );
    }

    public static function sanitize( $input ) {
        $out = self::defaults();
        $animations = [ 'fade', 'slide', 'zoom' ];

        $out['animation']       = in_array( $input['animation'] ?? '', $animations, true ) ? $input['animation'] : 'slide';
        $out['duration']        = max( 100, min( 3000, absint( $input['duration'] ?? 600 ) ) );
        $out['autoplay']        = ! empty( $input['autoplay'] ) ? '1' : '0';
        $out['interval']        = max( 1, min( 30, absint( $input['interval'] ?? 5 ) ) );
        $out['show_dots']       = ! empty( $input['show_dots'] ) ? '1' : '0';
        $out['show_arrows']     = ! empty( $input['show_arrows'] ) ? '1' : '0';
        $out['overlay_opacity'] = max( 0, min( 100, absint( $input['overlay_opacity'] ?? 40 ) ) );

        return $out;
    }

    // ── Settings page render ──────────────────────────────────────────────────
    public static function render_settings_page() {
        $s = self::get();
        ?>
        <div class="wrap bkbc-settings-wrap">
            <div class="bkbc-settings-header">
                <h1>⚙️ Pengaturan Carousel</h1>
                <p>Konfigurasi global animasi, autoplay, dan tampilan carousel banner.</p>
            </div>

            <?php if ( isset( $_GET['settings-updated'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>✅ Pengaturan berhasil disimpan.</p></div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'bkbc_settings_group' ); ?>
                <div class="bkbc-settings-grid">

                    <!-- Animasi -->
                    <div class="bkbc-settings-card">
                        <h2 class="bkbc-card-title">🎬 Animasi Transisi</h2>

                        <div class="bkbc-field">
                            <label>Jenis Animasi</label>
                            <div class="bkbc-anim-options">
                                <?php
                                $anims = [
                                    'slide' => [ 'icon' => '↔', 'label' => 'Slide Horizontal', 'desc' => 'Geser kiri-kanan' ],
                                    'fade'  => [ 'icon' => '✦', 'label' => 'Fade',             'desc' => 'Crossfade halus' ],
                                    'zoom'  => [ 'icon' => '⊕', 'label' => 'Zoom',             'desc' => 'Scale in/out' ],
                                ];
                                foreach ( $anims as $val => $a ) : ?>
                                <label class="bkbc-anim-card <?php echo $s['animation'] === $val ? 'selected' : ''; ?>">
                                    <input type="radio" name="<?php echo self::OPTION_KEY; ?>[animation]"
                                           value="<?php echo $val; ?>"
                                           <?php checked( $s['animation'], $val ); ?>>
                                    <span class="bkbc-anim-icon"><?php echo $a['icon']; ?></span>
                                    <span class="bkbc-anim-label"><?php echo $a['label']; ?></span>
                                    <span class="bkbc-anim-desc"><?php echo $a['desc']; ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="bkbc-field">
                            <label for="bkbc_duration">Durasi Transisi (ms)</label>
                            <div class="bkbc-range-wrap">
                                <input type="range" id="bkbc_duration"
                                       name="<?php echo self::OPTION_KEY; ?>[duration]"
                                       min="100" max="3000" step="100"
                                       value="<?php echo esc_attr( $s['duration'] ); ?>"
                                       oninput="document.getElementById('bkbc_dur_val').textContent=this.value+'ms'">
                                <span class="bkbc-range-val" id="bkbc_dur_val"><?php echo $s['duration']; ?>ms</span>
                            </div>
                        </div>

                        <div class="bkbc-field">
                            <label for="bkbc_overlay">Opacity Overlay Gelap (%)</label>
                            <div class="bkbc-range-wrap">
                                <input type="range" id="bkbc_overlay"
                                       name="<?php echo self::OPTION_KEY; ?>[overlay_opacity]"
                                       min="0" max="90" step="5"
                                       value="<?php echo esc_attr( $s['overlay_opacity'] ); ?>"
                                       oninput="document.getElementById('bkbc_ov_val').textContent=this.value+'%'">
                                <span class="bkbc-range-val" id="bkbc_ov_val"><?php echo $s['overlay_opacity']; ?>%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Autoplay & Navigasi -->
                    <div class="bkbc-settings-card">
                        <h2 class="bkbc-card-title">▶ Autoplay & Navigasi</h2>

                        <div class="bkbc-field bkbc-toggle-field">
                            <div>
                                <label>Autoplay Otomatis</label>
                                <p class="bkbc-hint">Slide berpindah otomatis tanpa interaksi user</p>
                            </div>
                            <label class="bkbc-switch">
                                <input type="checkbox" name="<?php echo self::OPTION_KEY; ?>[autoplay]"
                                       value="1" <?php checked( $s['autoplay'], '1' ); ?>>
                                <span class="bkbc-slider"></span>
                            </label>
                        </div>

                        <div class="bkbc-field">
                            <label for="bkbc_interval">Interval Autoplay (detik)</label>
                            <div class="bkbc-range-wrap">
                                <input type="range" id="bkbc_interval"
                                       name="<?php echo self::OPTION_KEY; ?>[interval]"
                                       min="1" max="30" step="1"
                                       value="<?php echo esc_attr( $s['interval'] ); ?>"
                                       oninput="document.getElementById('bkbc_iv_val').textContent=this.value+'s'">
                                <span class="bkbc-range-val" id="bkbc_iv_val"><?php echo $s['interval']; ?>s</span>
                            </div>
                        </div>

                        <div class="bkbc-field bkbc-toggle-field">
                            <div>
                                <label>Tampilkan Navigation Dots</label>
                                <p class="bkbc-hint">Titik-titik indikator slide di bawah carousel</p>
                            </div>
                            <label class="bkbc-switch">
                                <input type="checkbox" name="<?php echo self::OPTION_KEY; ?>[show_dots]"
                                       value="1" <?php checked( $s['show_dots'], '1' ); ?>>
                                <span class="bkbc-slider"></span>
                            </label>
                        </div>

                        <div class="bkbc-field bkbc-toggle-field">
                            <div>
                                <label>Tampilkan Panah Prev / Next</label>
                                <p class="bkbc-hint">Tombol navigasi manual kiri dan kanan</p>
                            </div>
                            <label class="bkbc-switch">
                                <input type="checkbox" name="<?php echo self::OPTION_KEY; ?>[show_arrows]"
                                       value="1" <?php checked( $s['show_arrows'], '1' ); ?>>
                                <span class="bkbc-slider"></span>
                            </label>
                        </div>
                    </div>

                </div><!-- /grid -->

                <div class="bkbc-settings-footer">
                    <?php submit_button( 'Simpan Pengaturan', 'primary large', 'submit', false ); ?>
                    <a href="?page=bkbc-carousel" class="button button-secondary large">← Kembali ke Kelola Slide</a>
                </div>
            </form>
        </div>
        <?php
    }

    // ── Manage page (delegate) ────────────────────────────────────────────────
    public static function render_manage_page() {
        require_once BKBC_PATH . 'admin/admin-page.php';
        bkbc_render_manage_page();
    }
}
