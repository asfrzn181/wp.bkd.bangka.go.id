<?php
/**
 * ASN Data Dashboard Database Layer.
 *
 * @package ASN_Data_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ASNDD_DB
 */
class ASNDD_DB {

	/**
	 * Get full table name with WordPress prefix.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . ASNDD_TABLE_NAME;
	}

	/**
	 * Create custom table using dbDelta.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			periode varchar(7) NOT NULL,
			data longtext NOT NULL,
			updated_by bigint(20) unsigned NOT NULL DEFAULT 0,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY periode (periode)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Drop table (used on uninstall).
	 */
	public static function drop_table() {
		global $wpdb;
		$table_name = self::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
	}

	/**
	 * Get record for a specific period.
	 *
	 * @param string $periode YYYY-MM format.
	 * @return object|null Row object or null.
	 */
	public static function get_data( $periode ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE periode = %s LIMIT 1",
				$periode
			)
		);

		if ( $row && ! empty( $row->data ) ) {
			$row->parsed_data = ASNDD_Schema::sanitize_data( $row->data );
		}

		return $row;
	}

	/**
	 * Get latest record by period.
	 *
	 * @return object|null
	 */
	public static function get_latest_data() {
		global $wpdb;
		$table_name = self::get_table_name();

		$row = $wpdb->get_row(
			"SELECT * FROM {$table_name} ORDER BY periode DESC LIMIT 1"
		);

		if ( $row && ! empty( $row->data ) ) {
			$row->parsed_data = ASNDD_Schema::sanitize_data( $row->data );
		}

		return $row;
	}

	/**
	 * Get list of all available periods (ORDER BY periode DESC).
	 *
	 * @return array List of period strings (e.g. ['2026-06', '2026-05']).
	 */
	public static function get_all_periods() {
		global $wpdb;
		$table_name = self::get_table_name();

		$results = $wpdb->get_col(
			"SELECT periode FROM {$table_name} ORDER BY periode DESC"
		);

		return $results ? $results : array();
	}

	/**
	 * Save or Update (upsert) data for a period.
	 *
	 * @param string       $periode Format YYYY-MM.
	 * @param array|string $data    Array or JSON string.
	 * @param int          $user_id User ID performing save.
	 * @return bool Success status.
	 */
	public static function save_data( $periode, $data, $user_id = 0 ) {
		global $wpdb;
		$table_name = self::get_table_name();

		// Sanitize data first
		$sanitized_array = ASNDD_Schema::sanitize_data( $data );
		$json_data       = wp_json_encode( $sanitized_array );

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$existing = self::get_data( $periode );

		if ( $existing ) {
			$updated = $wpdb->update(
				$table_name,
				array(
					'data'       => $json_data,
					'updated_by' => $user_id,
					'updated_at' => current_time( 'mysql' ),
				),
				array( 'periode' => $periode ),
				array( '%s', '%d', '%s' ),
				array( '%s' )
			);
			return false !== $updated;
		} else {
			$inserted = $wpdb->insert(
				$table_name,
				array(
					'periode'    => $periode,
					'data'       => $json_data,
					'updated_by' => $user_id,
					'updated_at' => current_time( 'mysql' ),
				),
				array( '%s', '%s', '%d', '%s' )
			);
			return false !== $inserted;
		}
	}

	/**
	 * Copy data from one period to another.
	 *
	 * @param string $source_periode Source YYYY-MM.
	 * @param string $target_periode Target YYYY-MM.
	 * @param int    $user_id User ID.
	 * @return bool
	 */
	public static function copy_data( $source_periode, $target_periode, $user_id = 0 ) {
		$source = self::get_data( $source_periode );
		if ( ! $source || empty( $source->data ) ) {
			return false;
		}

		return self::save_data( $target_periode, $source->data, $user_id );
	}
}
