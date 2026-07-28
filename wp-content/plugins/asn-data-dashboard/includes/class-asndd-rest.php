<?php
/**
 * ASN Data Dashboard REST API Endpoints Class.
 *
 * @package ASN_Data_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ASNDD_REST
 */
class ASNDD_REST {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	private $namespace = 'asn-dashboard/v1';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		// GET List of all available periods
		register_rest_route(
			$this->namespace,
			'/periods',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_periods' ),
				'permission_callback' => '__return_true',
			)
		);

		// GET Latest or Query period data
		register_rest_route(
			$this->namespace,
			'/data',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_data' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'periode' => array(
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function( $param ) {
								return empty( $param ) || preg_match( '/^\d{4}-\d{2}$/', $param );
							},
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_data' ),
					'permission_callback' => array( $this, 'admin_permissions_check' ),
				),
			)
		);

		// GET Specific period data via URL path
		register_rest_route(
			$this->namespace,
			'/data/(?P<periode>\d{4}-\d{2})',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_data_by_period' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Check admin capability for write operations.
	 *
	 * @return bool|WP_Error
	 */
	public function admin_permissions_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Anda tidak memiliki izin untuk menyimpan data ASN.', 'asn-data-dashboard' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * Get list of periods.
	 *
	 * @return WP_REST_Response
	 */
	public function get_periods() {
		$periods = ASNDD_DB::get_all_periods();
		return rest_ensure_response( array( 'periods' => $periods ) );
	}

	/**
	 * Get data for specified query param or latest.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_data( $request ) {
		$periode = $request->get_param( 'periode' );

		if ( ! empty( $periode ) ) {
			$row = ASNDD_DB::get_data( $periode );
		} else {
			$row = ASNDD_DB::get_latest_data();
		}

		if ( ! $row ) {
			return rest_ensure_response(
				array(
					'periode'    => $periode ? $periode : null,
					'data'       => ASNDD_Schema::get_empty_data(),
					'updated_at' => null,
					'found'      => false,
				)
			);
		}

		return rest_ensure_response(
			array(
				'periode'    => $row->periode,
				'data'       => $row->parsed_data,
				'updated_at' => $row->updated_at,
				'found'      => true,
			)
		);
	}

	/**
	 * Get data by path parameter.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_data_by_period( $request ) {
		$periode = $request->get_param( 'periode' );
		$row     = ASNDD_DB::get_data( $periode );

		if ( ! $row ) {
			return new WP_Error(
				'asndd_not_found',
				sprintf( __( 'Data untuk periode %s tidak ditemukan.', 'asn-data-dashboard' ), $periode ),
				array( 'status' => 404 )
			);
		}

		return rest_ensure_response(
			array(
				'periode'    => $row->periode,
				'data'       => $row->parsed_data,
				'updated_at' => $row->updated_at,
				'found'      => true,
			)
		);
	}

	/**
	 * Save data via REST API.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_data( $request ) {
		$body = $request->get_json_params();

		$periode = isset( $body['periode'] ) ? sanitize_text_field( $body['periode'] ) : '';
		if ( ! preg_match( '/^\d{4}-\d{2}$/', $periode ) ) {
			return new WP_Error(
				'asndd_invalid_periode',
				__( 'Format periode harus YYYY-MM.', 'asn-data-dashboard' ),
				array( 'status' => 400 )
			);
		}

		$data   = isset( $body['data'] ) ? $body['data'] : array();
		$saved  = ASNDD_DB::save_data( $periode, $data, get_current_user_id() );

		if ( ! $saved ) {
			return new WP_Error(
				'asndd_save_failed',
				__( 'Gagal menyimpan data.', 'asn-data-dashboard' ),
				array( 'status' => 500 )
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => sprintf( __( 'Data periode %s berhasil disimpan.', 'asn-data-dashboard' ), $periode ),
				'periode' => $periode,
				'data'    => ASNDD_Schema::sanitize_data( $data ),
			)
		);
	}
}
