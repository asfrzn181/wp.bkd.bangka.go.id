<?php
/**
 * BKPSDMD Prima functions and definitions
 *
 * @package career-development
 * @since 1.0
 */

if ( ! function_exists( 'CAREER_DEVELOPMENT_SUPPORT' ) ) :
	function CAREER_DEVELOPMENT_SUPPORT() {

		load_theme_textdomain( 'career-development', get_template_directory() . '/languages' );

		// Add support for block styles.
		add_theme_support( 'wp-block-styles' );

		add_theme_support('woocommerce');

		// Enqueue editor styles.
		add_editor_style(get_stylesheet_directory_uri() . '/assets/css/editor-style.css');

		/* Theme Credit link */
		define('CAREER_DEVELOPMENT_BUY_NOW',__('https://www.cretathemes.com/products/career-coaching-wordpress-theme','career-development'));
		define('CAREER_DEVELOPMENT_PRO_DEMO',__('https://pattern.cretathemes.com/career-development-pro/','career-development'));
		define('CAREER_DEVELOPMENT_THEME_DOC',__('https://pattern.cretathemes.com/free-guide/career-development/','career-development'));
		define('CAREER_DEVELOPMENT_PRO_THEME_DOC',__('https://pattern.cretathemes.com/pro-guide/career-development-pro/','career-development'));
		define('CAREER_DEVELOPMENT_SUPPORT',__('https://wordpress.org/support/theme/career-development/','career-development'));
		define('CAREER_DEVELOPMENT_REVIEW',__('https://wordpress.org/support/theme/career-development/reviews/#new-post','career-development'));
		define('CAREER_DEVELOPMENT_PRO_THEME_BUNDLE',__('https://www.cretathemes.com/products/wordpress-theme-bundle','career-development'));
		define('CAREER_DEVELOPMENT_PRO_ALL_THEMES',__('https://www.cretathemes.com/collections/wordpress-block-themes','career-development'));
	}

endif;

add_action( 'after_setup_theme', 'CAREER_DEVELOPMENT_SUPPORT' );

if ( ! function_exists( 'career_development_styles' ) ) :
	function career_development_styles() {
		// Register theme stylesheet.
		$career_development_theme_version = wp_get_theme()->get( 'Version' );

		$career_development_version_string = is_string( $career_development_theme_version ) ? $career_development_theme_version : false;
		wp_enqueue_style(
			'career-development-style',
			get_template_directory_uri() . '/style.css',
			array(),
			$career_development_version_string
		);

		wp_enqueue_style( 'dashicons' );

		wp_enqueue_style( 'animate-css', esc_url(get_template_directory_uri()).'/assets/css/animate.css' );

		wp_enqueue_script( 'jquery-wow', esc_url(get_template_directory_uri()) . '/assets/js/wow.js', array('jquery') );

		wp_style_add_data( 'career-development-style', 'rtl', 'replace' );

		//font-awesome
		wp_enqueue_style( 'fontawesome', get_template_directory_uri() . '/inc/fontawesome/css/all.css'
			, array(), '7.0.0' );

		// Enqueue Custom Script
		wp_enqueue_script(
		    'career-development-custom-script',
		    get_template_directory_uri() . '/assets/js/custom-script.js',
		    array('jquery'),
		    $career_development_version_string,
		    true
		);

		// Mobile navigation & logo responsive fix
		wp_enqueue_style(
		    'career-development-mobile-nav',
		    get_template_directory_uri() . '/assets/css/mobile-nav.css',
		    array('career-development-style'),
		    filemtime( get_template_directory() . '/assets/css/mobile-nav.css' )
		);
	}
endif;

add_action( 'wp_enqueue_scripts', 'career_development_styles' );

/* Enqueue admin-notice-script js */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'appearance_page_career-development') return;

    wp_enqueue_script('admin-notice-script', get_template_directory_uri() . '/get-started/js/admin-notice-script.js', ['jquery'], null, true);
    wp_localize_script('admin-notice-script', 'pluginInstallerData', [
        'ajaxurl'     => admin_url('admin-ajax.php'),
        'nonce'       => wp_create_nonce('install_cretatestimonial_nonce'), // Match this with PHP nonce check
        'redirectUrl' => admin_url('themes.php?page=career-development-guide-page'),
    ]);
});

add_action('wp_ajax_check_creta_testimonial_activation', function () {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    $career_development_plugin_file = 'creta-testimonial-showcase/creta-testimonial-showcase.php';

    if (is_plugin_active($career_development_plugin_file)) {
        wp_send_json_success(['active' => true]);
    } else {
        wp_send_json_success(['active' => false]);
    }
});


// Add block patterns
require get_template_directory() . '/inc/block-patterns.php';

// Blog Grid Filter shortcode + AJAX
require get_template_directory() . '/inc/blog-filter.php';

// Add block styles
require get_template_directory() . '/inc/block-styles.php';

// Block Filters
require get_template_directory() . '/inc/block-filters.php';

// Svg icons
require get_template_directory() . '/inc/icon-function.php';

// TGM
require_once get_template_directory() . '/inc/tgm/tgm.php';

// Customizer
require get_template_directory() . '/inc/customizer.php';

// Get Started.
require get_template_directory() . '/inc/get-started/get-started.php';

// Add Getstart admin notice
function career_development_admin_notice() { 
    global $pagenow;
    $theme_args      = wp_get_theme();
    $meta            = get_option( 'career_development_admin_notice' );
    $name            = $theme_args->__get( 'Name' );
    $current_screen  = get_current_screen();

    if( !$meta ){
	    if( is_network_admin() ){
	        return;
	    }

	    if( ! current_user_can( 'manage_options' ) ){
	        return;
	    } if($current_screen->base != 'appearance_page_career-development-guide-page' && $current_screen->base != 'toplevel_page_cretats-theme-showcase' ) { ?>

	    <div class="notice notice-success dash-notice">
	        	<h1><?php esc_html_e('Hey, Terima kasih telah menggunakan tema BKPSDMD Prima!', 'career-development'); ?></h1>
	        <p> <a href="javascript:void(0);" id="install-activate-button" class="button admin-button info-button get-start-btn">
				   <?php echo __('Navigate Getstart', 'career-development'); ?>
				</a>

				<script type="text/javascript">
				document.getElementById('install-activate-button').addEventListener('click', function () {
				    const career_development_button = this;
				    const career_development_redirectUrl = '<?php echo esc_url(admin_url("themes.php?page=career-development-guide-page")); ?>';
				    // First, check if plugin is already active
				    jQuery.post(ajaxurl, { action: 'check_creta_testimonial_activation' }, function (response) {
				        if (response.success && response.data.active) {
				            // Plugin already active — just redirect
				            window.location.href = career_development_redirectUrl;
				        } else {
				            // Show Installing & Activating only if not already active
				            career_development_button.textContent = 'Navigate Getstart';

				            jQuery.post(ajaxurl, {
				                action: 'install_and_activate_creta_testimonial_plugin',
				                nonce: '<?php echo wp_create_nonce("install_activate_nonce"); ?>'
				            }, function (response) {
				                if (response.success) {
				                    window.location.href = career_development_redirectUrl;
				                } else {
				                    alert('Failed to activate the plugin.');
				                    career_development_button.textContent = 'Try Again';
				                }
				            });
				        }
				    });
				});
				</script>


				<a class="button button-primary site-edit" href="<?php echo esc_url( admin_url( 'site-editor.php' ) ); ?>"><?php esc_html_e('Site Editor', 'career-development'); ?></a>
	        </p>
	        <p class="dismiss-link"><strong><a href="?career_development_admin_notice=1"><?php esc_html_e( 'Dismiss', 'career-development' ); ?></a></strong></p>
	    </div>
	    <?php

	}?>
	    <?php

	}
}

add_action( 'admin_notices', 'career_development_admin_notice' );



if( ! function_exists( 'career_development_update_admin_notice' ) ) :
/**
 * Updating admin notice on dismiss
*/
function career_development_update_admin_notice(){
    if ( isset( $_GET['career_development_admin_notice'] ) && $_GET['career_development_admin_notice'] = '1' ) {
        update_option( 'career_development_admin_notice', true );
    }
}
endif;
add_action( 'admin_init', 'career_development_update_admin_notice' );

//After Switch theme function
add_action('after_switch_theme', 'career_development_getstart_setup_options');
function career_development_getstart_setup_options () {
    update_option('career_development_admin_notice', FALSE );
}