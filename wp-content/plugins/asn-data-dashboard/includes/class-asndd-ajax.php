<?php
/**
 * ASN Data Dashboard AJAX Handlers Class.
 *
 * @package ASN_Data_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ASNDD_Ajax
 */
class ASNDD_Ajax {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_asndd_save', array( $this, 'ajax_save' ) );
		add_action( 'wp_ajax_asndd_copy', array( $this, 'ajax_copy' ) );
		add_action( 'wp_ajax_asndd_get', array( $this, 'ajax_get' ) );
	}

	/**
	 * Verify permissions and nonce.
	 */
	private function verify_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Akses ditolak. Anda tidak memiliki izin.', 'asn-data-dashboard' ) ), 403 );
		}

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'asndd_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Sesi tidak valid / nonce expired.', 'asn-data-dashboard' ) ), 400 );
		}
	}

	/**
	 * Handle save data AJAX request.
	 */
	public function ajax_save() {
		$this->verify_request();

		$periode = isset( $_POST['periode'] ) ? sanitize_text_field( wp_unslash( $_POST['periode'] ) ) : '';
		if ( ! preg_match( '/^\d{4}-\d{2}$/', $periode ) ) {
			wp_send_json_error( array( 'message' => __( 'Format periode tidak valid (gunakan YYYY-MM).', 'asn-data-dashboard' ) ) );
		}

		$raw_data = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : array();
		if ( is_string( $raw_data ) ) {
			$raw_data = json_decode( $raw_data, true );
		}

		$result = ASNDD_DB::save_data( $periode, $raw_data, get_current_user_id() );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf( __( 'Data periode %s berhasil disimpan.', 'asn-data-dashboard' ), $periode ),
					'periode' => $periode,
					'data'    => ASNDD_Schema::sanitize_data( $raw_data ),
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'Gagal menyimpan data ke database.', 'asn-data-dashboard' ) ) );
		}
	}

	/**
	 * Handle copy data AJAX request.
	 */
	public function ajax_copy() {
		$this->verify_request();

		$source_periode = isset( $_POST['source_periode'] ) ? sanitize_text_field( wp_unslash( $_POST['source_periode'] ) ) : '';
		$target_periode = isset( $_POST['target_periode'] ) ? sanitize_text_field( wp_unslash( $_POST['target_periode'] ) ) : '';

		if ( ! preg_match( '/^\d{4}-\d{2}$/', $source_periode ) || ! preg_match( '/^\d{4}-\d{2}$/', $target_periode ) ) {
			wp_send_json_error( array( 'message' => __( 'Format periode tidak valid.', 'asn-data-dashboard' ) ) );
		}

		$source = ASNDD_DB::get_data( $source_periode );
		if ( ! $source || empty( $source->parsed_data ) ) {
			wp_send_json_error( array( 'message' => sprintf( __( 'Data periode asal (%s) tidak ditemukan.', 'asn-data-dashboard' ), $source_periode ) ) );
		}

		$result = ASNDD_DB::save_data( $target_periode, $source->parsed_data, get_current_user_id() );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => sprintf( __( 'Berhasil menyalin data dari %s ke %s.', 'asn-data-dashboard' ), $source_periode, $target_periode ),
					'data'    => $source->parsed_data,
					'periode' => $target_periode,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'Gagal menyalin data.', 'asn-data-dashboard' ) ) );
		}
	}

	/**
	 * Handle get data AJAX request.
	 */
	public function ajax_get() {
		$this->verify_request();

		$periode = isset( $_POST['periode'] ) ? sanitize_text_field( wp_unslash( $_POST['periode'] ) ) : '';
		if ( ! preg_match( '/^\d{4}-\d{2}$/', $periode ) ) {
			wp_send_json_error( array( 'message' => __( 'Format periode tidak valid.', 'asn-data-dashboard' ) ) );
		}

		$row = ASNDD_DB::get_data( $periode );
		if ( $row ) {
			wp_send_json_success(
				array(
					'periode'    => $row->periode,
					'data'       => $row->parsed_data,
					'updated_at' => $row->updated_at,
					'exists'     => true,
				)
			);
		} else {
			// Return empty template structure for new period
			wp_send_json_success(
				array(
					'periode'    => $periode,
					'data'       => ASNDD_Schema::get_empty_data(),
					'updated_at' => null,
					'exists'     => false,
				)
			);
		}
	}
}
