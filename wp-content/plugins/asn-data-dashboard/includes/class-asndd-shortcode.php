<?php
/**
 * ASN Data Dashboard Shortcode Class.
 *
 * @package ASN_Data_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ASNDD_Shortcode
 */
class ASNDD_Shortcode {

	/**
	 * Instance counter for unique element IDs on page.
	 *
	 * @var int
	 */
	private static $instance_count = 0;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'asn_dashboard', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render [asn_dashboard] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'periode' => '',
			),
			$atts,
			'asn_dashboard'
		);

		$all_periods       = ASNDD_DB::get_all_periods();
		$requested_periode = sanitize_text_field( $atts['periode'] );

		if ( ! empty( $requested_periode ) && preg_match( '/^\d{4}-\d{2}$/', $requested_periode ) ) {
			$row = ASNDD_DB::get_data( $requested_periode );
		} else {
			$row = ASNDD_DB::get_latest_data();
		}

		$current_periode = $row ? $row->periode : ( ! empty( $requested_periode ) ? $requested_periode : ( ! empty( $all_periods ) ? $all_periods[0] : date( 'Y-m' ) ) );
		$data            = $row ? $row->parsed_data : ASNDD_Schema::get_empty_data();

		self::$instance_count++;
		$instance_id = 'asndd-dashboard-' . self::$instance_count;

		// Enqueue scripts & styles for frontend
		$this->enqueue_assets( $instance_id, $current_periode, $data, $all_periods );

		ob_start();
		include ASNDD_PLUGIN_DIR . 'templates/shortcode-view.php';
		return ob_get_clean();
	}

	/**
	 * Enqueue Chart.js and frontend dashboard assets.
	 *
	 * @param string $instance_id Unique instance ID.
	 * @param string $periode     Current period.
	 * @param array  $data        Data array.
	 * @param array  $all_periods List of all periods.
	 */
	private function enqueue_assets( $instance_id, $periode, $data, $all_periods = array() ) {
		// Chart.js CDN
		wp_enqueue_script(
			'chart-js',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
			array(),
			'4.4.0',
			true
		);

		// Plugin frontend CSS
		wp_enqueue_style(
			'asndd-dashboard-css',
			ASNDD_PLUGIN_URL . 'assets/css/dashboard.css',
			array(),
			filemtime( ASNDD_PLUGIN_DIR . 'assets/css/dashboard.css' )
		);

		// Plugin frontend JS
		wp_enqueue_script(
			'asndd-dashboard-js',
			ASNDD_PLUGIN_URL . 'assets/js/dashboard.js',
			array( 'jquery', 'chart-js' ),
			filemtime( ASNDD_PLUGIN_DIR . 'assets/js/dashboard.js' ),
			true
		);

		// Pass data to JS
		wp_localize_script(
			'asndd-dashboard-js',
			'asnddDashboardData',
			array(
				'instanceId' => $instance_id,
				'periode'    => $periode,
				'periods'    => $all_periods,
				'restUrl'    => esc_url_raw( rest_url( 'asn-dashboard/v1/data' ) ),
				'data'       => $data,
				'schema'     => array(
					'pnsJabatan'    => ASNDD_Schema::get_pns_jabatan(),
					'pnsPangkat'    => ASNDD_Schema::get_pns_pangkat(),
					'pendidikan'    => ASNDD_Schema::get_pendidikan_levels(),
					'pppkJabatan'   => ASNDD_Schema::get_pppk_jabatan(),
					'pppkGolongan'  => ASNDD_Schema::get_pppk_golongan(),
					'pppkPwJabatan' => ASNDD_Schema::get_pppk_pw_jabatan(),
				),
			)
		);
	}

}
