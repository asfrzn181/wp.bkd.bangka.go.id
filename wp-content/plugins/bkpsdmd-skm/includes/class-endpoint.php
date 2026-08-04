<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BKSKM_Endpoint {

    public static function init() {
        add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
        add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
        add_action( 'template_redirect', array( __CLASS__, 'handle_endpoint_redirect' ) );
    }

    /**
     * Mendapatkan URL survei lengkap.
     */
    public static function get_survey_url() {
        $slug = get_option( 'bkskm_custom_slug', 'survei-skm' );
        $slug = sanitize_title( $slug );
        return home_url( '/' . $slug . '/' );
    }

    /**
     * Mendaftarkan rewrite rules untuk URL custom.
     */
    public static function add_rewrite_rules() {
        $slug = get_option( 'bkskm_custom_slug', 'survei-skm' );
        $slug = sanitize_title( $slug );

        add_rewrite_rule( '^' . $slug . '/?$', 'index.php?bkskm_survey_page=1', 'top' );
        if ( $slug !== 'skm' ) {
            add_rewrite_rule( '^skm/?$', 'index.php?bkskm_survey_page=1', 'top' );
        }
    }

    public static function add_query_vars( $vars ) {
        $vars[] = 'bkskm_survey_page';
        return $vars;
    }

    /**
     * Menangani request URL dan merender halaman survei khusus.
     */
    public static function handle_endpoint_redirect() {
        if ( get_query_var( 'bkskm_survey_page' ) ) {
            self::render_standalone_page();
            exit;
        }
    }

    /**
     * Otomatis membuat Halaman WordPress "Survei Kepuasan Masyarakat (SKM)" saat aktivasi.
     */
    public static function auto_create_page() {
        $slug = get_option( 'bkskm_custom_slug', 'survei-skm' );
        $slug = sanitize_title( $slug );

        $existing_page = get_page_by_path( $slug );
        if ( ! $existing_page ) {
            wp_insert_post( array(
                'post_title'     => 'Survei Kepuasan Masyarakat (SKM)',
                'post_name'      => $slug,
                'post_content'   => '[bkpsdmd_skm_form]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
            ) );
        }

        self::flush_rules();
    }

    public static function flush_rules() {
        self::add_rewrite_rules();
        flush_rewrite_rules();
    }

    /**
     * Render Halaman Standalone Survei SKM
     */
    public static function render_standalone_page() {
        status_header( 200 );
        $instansi = get_option( 'bkskm_instansi_name', 'BKPSDMD Kabupaten Bangka' );
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo( 'charset' ); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Survei Kepuasan Masyarakat (SKM) - <?php echo esc_html( $instansi ); ?></title>
            <?php
            wp_enqueue_style( 'bkskm-frontend-css' );
            wp_enqueue_script( 'bkskm-frontend-js' );
            wp_head();
            ?>
            <style>
                body.bkskm-standalone-body {
                    margin: 0;
                    padding: 30px 15px;
                    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    box-sizing: border-box;
                    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
                }
                .bkskm-standalone-header {
                    text-align: center;
                    color: #ffffff;
                    margin-bottom: 15px;
                }
                .bkskm-standalone-header img {
                    max-height: 80px;
                    width: auto;
                    margin-bottom: 10px;
                }
                .bkskm-standalone-header h1 {
                    font-size: 1.5rem;
                    margin: 0;
                    color: #f8fafc;
                    font-weight: 700;
                }
                .bkskm-standalone-footer {
                    color: #94a3b8;
                    font-size: 0.88rem;
                    text-align: center;
                    margin-top: 25px;
                }
                .bkskm-standalone-footer a {
                    color: #38bdf8;
                    text-decoration: none;
                    font-weight: 600;
                }
                .bkskm-standalone-footer a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body <?php body_class( 'bkskm-standalone-body' ); ?>>
            <div class="bkskm-standalone-header">
                <?php 
                $logo_url = get_option( 'bkskm_logo_url', 'https://bkd.bangka.go.id/wp-content/uploads/2026/07/Logo-Prima-no-bg.png' );
                if ( empty( $logo_url ) && has_custom_logo() ) {
                    $custom_logo_id = get_theme_mod( 'custom_logo' );
                    $logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
                    if ( $logo ) {
                        $logo_url = $logo[0];
                    }
                }
                if ( ! empty( $logo_url ) ) : ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo"></a>
                <?php else : ?>
                    <h1><a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:#fff; text-decoration:none;"><?php bloginfo( 'name' ); ?></a></h1>
                <?php endif; ?>
            </div>

            <?php echo BKSKM_Shortcode::render_shortcode( array() ); ?>

            <div class="bkskm-standalone-footer">
                &copy; <?php echo date( 'Y' ); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $instansi ); ?></a>. Seluruh Hak Cipta Dilindungi.
            </div>

            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
    }
}
