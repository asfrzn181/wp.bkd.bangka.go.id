<?php
/**
 * ASN Data Dashboard Admin Class.
 *
 * @package ASN_Data_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ASNDD_Admin
 */
class ASNDD_Admin {

	/**
	 * Menu hook suffix.
	 *
	 * @var string
	 */
	private $hook_suffix = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add admin sidebar menu.
	 */
	public function add_admin_menu() {
		$this->hook_suffix = add_menu_page(
			__( 'ASN Data Dashboard', 'asn-data-dashboard' ),
			__( 'Rekap Data ASN', 'asn-data-dashboard' ),
			'manage_options',
			'asn-data-dashboard',
			array( $this, 'render_admin_page' ),
			'dashicons-chart-bar',
			30
		);
	}

	/**
	 * Enqueue assets only on plugin admin page.
	 *
	 * @param string $hook Page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( $hook !== $this->hook_suffix ) {
			return;
		}

		// Chart.js CDN
		wp_enqueue_script(
			'chart-js',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
			array(),
			'4.4.0',
			true
		);

		// Plugin Admin CSS
		wp_enqueue_style(
			'asndd-admin-css',
			ASNDD_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			filemtime( ASNDD_PLUGIN_DIR . 'assets/css/admin.css' )
		);

		// Plugin Admin JS
		wp_enqueue_script(
			'asndd-admin-js',
			ASNDD_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'chart-js' ),
			filemtime( ASNDD_PLUGIN_DIR . 'assets/js/admin.js' ),
			true
		);

		// Prepare initial data
		$periods        = ASNDD_DB::get_all_periods();
		$current_period = isset( $_GET['periode'] ) ? sanitize_text_field( wp_unslash( $_GET['periode'] ) ) : ( ! empty( $periods ) ? $periods[0] : date( 'Y-m' ) );
		$row            = ASNDD_DB::get_data( $current_period );
		$current_data   = $row ? $row->parsed_data : ASNDD_Schema::get_empty_data();

		wp_localize_script(
			'asndd-admin-js',
			'asnddAdminData',
			array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'asndd_admin_nonce' ),
				'periods'       => $periods,
				'currentPeriod' => $current_period,
				'currentData'   => $current_data,
				'schema'        => array(
					'pnsJabatan'   => ASNDD_Schema::get_pns_jabatan(),
					'pnsPangkat'   => ASNDD_Schema::get_pns_pangkat(),
					'pendidikan'   => ASNDD_Schema::get_pendidikan_levels(),
					'pppkJabatan'  => ASNDD_Schema::get_pppk_jabatan(),
					'pppkGolongan' => ASNDD_Schema::get_pppk_golongan(),
					'pppkPwJabatan' => ASNDD_Schema::get_pppk_pw_jabatan(),
				),
				'emptyData'     => ASNDD_Schema::get_empty_data(),
			)
		);
	}

	/**
	 * Render plugin admin page.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Anda tidak memiliki akses ke halaman ini.', 'asn-data-dashboard' ) );
		}

		$periods        = ASNDD_DB::get_all_periods();
		$current_period = isset( $_GET['periode'] ) ? sanitize_text_field( wp_unslash( $_GET['periode'] ) ) : ( ! empty( $periods ) ? $periods[0] : date( 'Y-m' ) );
		$row            = ASNDD_DB::get_data( $current_period );
		$current_data   = $row ? $row->parsed_data : ASNDD_Schema::get_empty_data();

		include ASNDD_PLUGIN_DIR . 'templates/admin-page.php';
	}
}
