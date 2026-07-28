<?php

require get_template_directory() . '/inc/tgm/class-tgm-plugin-activation.php';
/**
 * Recommended plugins.
 */
function career_development_register_recommended_plugins() {
	$plugins = array(
		array(
			'name'             => __( 'Video Popup Block by WPZOOM', 'career-development' ),
			'slug'             => 'wpzoom-video-popup-block',
			'source'           => '',
			'required'         => false,
			'force_activation' => false,
		),
		array(
			'name'             => __( 'Creta Testimonial Showcase', 'career-development' ),
			'slug'             => 'creta-testimonial-showcase',
			'source'           => '',
			'required'         => false,
			'force_activation' => false,
		)
	
	);
	$config = array();
	tgmpa( $plugins, $config );
}
add_action( 'tgmpa_register', 'career_development_register_recommended_plugins' );


//Creta Testimonial Showcase plugin activation
add_action('wp_ajax_install_and_activate_creta_testimonial_plugin', 'install_and_activate_creta_testimonial_plugin');

function install_and_activate_creta_testimonial_plugin() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'install_activate_nonce')) {
        wp_send_json_error(['message' => 'Nonce verification failed.']);
    }

    // Include necessary WordPress files
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    include_once ABSPATH . 'wp-admin/includes/file.php';
    include_once ABSPATH . 'wp-admin/includes/misc.php';
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

    $career_development_upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());

    // Define required plugins
   $plugins = [
    [
        'slug'     => 'creta-testimonial-showcase',
        'file'     => 'creta-testimonial-showcase/creta-testimonial-showcase.php',
        'url'      => 'https://downloads.wordpress.org/plugin/creta-testimonial-showcase.latest-stable.zip',
    ]
];

    $career_development_installed_plugins = get_plugins();

    foreach ($plugins as $plugin) {
        // Install if not present
        if (!isset($career_development_installed_plugins[$plugin['file']])) {
            $career_development_install_result = $career_development_upgrader->install($plugin['url']);
            if (is_wp_error($career_development_install_result)) {
                wp_send_json_error(['message' => "Failed to install {$plugin['slug']}"]);
            }
        }

        // Activate if not active
        if (!is_plugin_active($plugin['file'])) {
            $career_development_activate_result = activate_plugin($plugin['file']);
            if (is_wp_error($career_development_activate_result)) {
                wp_send_json_error([
                    'message' => "Failed to activate {$plugin['slug']}",
                    'error'   => $career_development_activate_result->get_error_message(),
                ]);
            }
        }
    }

    // Success response
    wp_send_json_success(['message' => 'Creta Testimonial Showcase plugin is activated successfully.']);
}
