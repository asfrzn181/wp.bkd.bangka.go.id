<?php
/**
 * ASN Data Dashboard Schema Definition Class.
 *
 * @package ASN_Data_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ASNDD_Schema
 */
class ASNDD_Schema {

	/**
	 * Get PNS/CPNS Jabatan definition list.
	 *
	 * @return array
	 */
	public static function get_pns_jabatan() {
		return array(
			'fungsional_guru'      => __( 'Fungsional Guru', 'asn-data-dashboard' ),
			'fungsional_kesehatan' => __( 'Fungsional Kesehatan', 'asn-data-dashboard' ),
			'fungsional_teknis'    => __( 'Fungsional Teknis', 'asn-data-dashboard' ),
			'pelaksana'            => __( 'Pelaksana', 'asn-data-dashboard' ),
			'pengawas_eselon_4'    => __( 'Pengawas / Eselon IV', 'asn-data-dashboard' ),
			'administrator_eselon_3' => __( 'Administrator / Eselon III', 'asn-data-dashboard' ),
			'jpt_eselon_2'          => __( 'JPT / Eselon II', 'asn-data-dashboard' ),
		);
	}

	/**
	 * Get PNS/CPNS Pangkat list (17 Golongan).
	 *
	 * @return array
	 */
	public static function get_pns_pangkat() {
		return array(
			'ia'   => 'Golongan I/a',
			'ib'   => 'Golongan I/b',
			'ic'   => 'Golongan I/c',
			'id'   => 'Golongan I/d',
			'iia'  => 'Golongan II/a',
			'iib'  => 'Golongan II/b',
			'iic'  => 'Golongan II/c',
			'iid'  => 'Golongan II/d',
			'iiia' => 'Golongan III/a',
			'iiib' => 'Golongan III/b',
			'iiic' => 'Golongan III/c',
			'iiid' => 'Golongan III/d',
			'iva'  => 'Golongan IV/a',
			'ivb'  => 'Golongan IV/b',
			'ivc'  => 'Golongan IV/c',
			'ivd'  => 'Golongan IV/d',
			'ive'  => 'Golongan IV/e',
		);
	}

	/**
	 * Get Pendidikan level list.
	 *
	 * @return array
	 */
	public static function get_pendidikan_levels() {
		return array(
			'sd'      => 'SD',
			'smp'     => 'SMP',
			'sma'     => 'SMA',
			'di'      => 'D.I',
			'dii'     => 'D.II',
			'diii_sm' => 'D.III / SM',
			'div'     => 'D.IV',
			's1'      => 'S.1',
			's2'      => 'S.2',
			's3'      => 'S.3',
		);
	}

	/**
	 * Get PPPK Jabatan list.
	 *
	 * @return array
	 */
	public static function get_pppk_jabatan() {
		return array(
			'fungsional_guru'      => __( 'Fungsional Guru', 'asn-data-dashboard' ),
			'fungsional_kesehatan' => __( 'Fungsional Kesehatan', 'asn-data-dashboard' ),
			'fungsional_teknis'    => __( 'Fungsional Teknis', 'asn-data-dashboard' ),
		);
	}

	/**
	 * Get PPPK Golongan list.
	 *
	 * @return array
	 */
	public static function get_pppk_golongan() {
		return array(
			'v'   => 'Golongan V',
			'vii' => 'Golongan VII',
			'ix'  => 'Golongan IX',
			'x'   => 'Golongan X',
		);
	}

	/**
	 * Get PPPK PW Jabatan list.
	 *
	 * @return array
	 */
	public static function get_pppk_pw_jabatan() {
		return array(
			'fungsional_guru'      => __( 'Fungsional Guru', 'asn-data-dashboard' ),
			'fungsional_kesehatan' => __( 'Fungsional Kesehatan', 'asn-data-dashboard' ),
			'fungsional_teknis'    => __( 'Teknis', 'asn-data-dashboard' ),
		);
	}

	/**
	 * Generate an empty data array structure filled with 0.
	 *
	 * @return array
	 */
	public static function get_empty_data() {
		$data = array(
			'pnsCpns' => array(
				'jabatan' => array(),
				'pangkat' => array(),
				'didik'   => array(),
			),
			'pppk'    => array(
				'jabatan'  => array(),
				'golongan' => array(),
			),
			'pppkPw'  => array(
				'jabatan' => array(),
			),
		);

		foreach ( self::get_pns_jabatan() as $key => $label ) {
			$data['pnsCpns']['jabatan'][ $key ] = array(
				'l' => 0,
				'p' => 0,
			);
		}

		foreach ( self::get_pns_pangkat() as $key => $label ) {
			$data['pnsCpns']['pangkat'][ $key ] = array(
				'l' => 0,
				'p' => 0,
			);
		}

		foreach ( self::get_pendidikan_levels() as $key => $label ) {
			$data['pnsCpns']['didik'][ $key ] = array(
				'struktural' => 0,
				'guru'       => 0,
				'nakes'      => 0,
				'teknis'     => 0,
			);
		}

		foreach ( self::get_pppk_jabatan() as $key => $label ) {
			$data['pppk']['jabatan'][ $key ] = array(
				'l' => 0,
				'p' => 0,
			);
		}

		foreach ( self::get_pppk_golongan() as $key => $label ) {
			$data['pppk']['golongan'][ $key ] = array(
				'l' => 0,
				'p' => 0,
			);
		}

		foreach ( self::get_pendidikan_levels() as $key => $label ) {
			$data['pppk']['didik'][ $key ] = array(
				'guru'   => 0,
				'nakes'  => 0,
				'teknis' => 0,
			);
		}

		foreach ( self::get_pppk_pw_jabatan() as $key => $label ) {
			$data['pppkPw']['jabatan'][ $key ] = array(
				'l' => 0,
				'p' => 0,
			);
		}

		foreach ( self::get_pendidikan_levels() as $key => $label ) {
			$data['pppkPw']['didik'][ $key ] = array(
				'guru'   => 0,
				'nakes'  => 0,
				'teknis' => 0,
			);
		}

		return $data;
	}

	/**
	 * Sanitize raw array/object data to ensure integers and strict structure matching schema.
	 *
	 * @param array|object $raw Raw input data.
	 * @return array Sanitized array.
	 */
	public static function sanitize_data( $raw ) {
		if ( is_string( $raw ) ) {
			$raw = json_decode( $raw, true );
		}
		if ( ! is_array( $raw ) ) {
			return self::get_empty_data();
		}

		$clean = self::get_empty_data();

		// PNS CPNS - Jabatan
		if ( isset( $raw['pnsCpns']['jabatan'] ) && is_array( $raw['pnsCpns']['jabatan'] ) ) {
			foreach ( self::get_pns_jabatan() as $key => $label ) {
				if ( isset( $raw['pnsCpns']['jabatan'][ $key ] ) ) {
					$clean['pnsCpns']['jabatan'][ $key ]['l'] = absint( $raw['pnsCpns']['jabatan'][ $key ]['l'] ?? 0 );
					$clean['pnsCpns']['jabatan'][ $key ]['p'] = absint( $raw['pnsCpns']['jabatan'][ $key ]['p'] ?? 0 );
				}
			}
		}

		// PNS CPNS - Pangkat
		if ( isset( $raw['pnsCpns']['pangkat'] ) && is_array( $raw['pnsCpns']['pangkat'] ) ) {
			foreach ( self::get_pns_pangkat() as $key => $label ) {
				if ( isset( $raw['pnsCpns']['pangkat'][ $key ] ) ) {
					$clean['pnsCpns']['pangkat'][ $key ]['l'] = absint( $raw['pnsCpns']['pangkat'][ $key ]['l'] ?? 0 );
					$clean['pnsCpns']['pangkat'][ $key ]['p'] = absint( $raw['pnsCpns']['pangkat'][ $key ]['p'] ?? 0 );
				}
			}
		}

		// PNS CPNS - Didik
		if ( isset( $raw['pnsCpns']['didik'] ) && is_array( $raw['pnsCpns']['didik'] ) ) {
			foreach ( self::get_pendidikan_levels() as $key => $label ) {
				if ( isset( $raw['pnsCpns']['didik'][ $key ] ) ) {
					$clean['pnsCpns']['didik'][ $key ]['struktural'] = absint( $raw['pnsCpns']['didik'][ $key ]['struktural'] ?? 0 );
					$clean['pnsCpns']['didik'][ $key ]['guru']       = absint( $raw['pnsCpns']['didik'][ $key ]['guru'] ?? 0 );
					$clean['pnsCpns']['didik'][ $key ]['nakes']      = absint( $raw['pnsCpns']['didik'][ $key ]['nakes'] ?? 0 );
					$clean['pnsCpns']['didik'][ $key ]['teknis']     = absint( $raw['pnsCpns']['didik'][ $key ]['teknis'] ?? 0 );
				}
			}
		}

		// PPPK - Jabatan
		if ( isset( $raw['pppk']['jabatan'] ) && is_array( $raw['pppk']['jabatan'] ) ) {
			foreach ( self::get_pppk_jabatan() as $key => $label ) {
				if ( isset( $raw['pppk']['jabatan'][ $key ] ) ) {
					$clean['pppk']['jabatan'][ $key ]['l'] = absint( $raw['pppk']['jabatan'][ $key ]['l'] ?? 0 );
					$clean['pppk']['jabatan'][ $key ]['p'] = absint( $raw['pppk']['jabatan'][ $key ]['p'] ?? 0 );
				}
			}
		}

		// PPPK - Golongan
		if ( isset( $raw['pppk']['golongan'] ) && is_array( $raw['pppk']['golongan'] ) ) {
			foreach ( self::get_pppk_golongan() as $key => $label ) {
				if ( isset( $raw['pppk']['golongan'][ $key ] ) ) {
					$clean['pppk']['golongan'][ $key ]['l'] = absint( $raw['pppk']['golongan'][ $key ]['l'] ?? 0 );
					$clean['pppk']['golongan'][ $key ]['p'] = absint( $raw['pppk']['golongan'][ $key ]['p'] ?? 0 );
				}
			}
		}

		// PPPK - Didik
		if ( isset( $raw['pppk']['didik'] ) && is_array( $raw['pppk']['didik'] ) ) {
			foreach ( self::get_pendidikan_levels() as $key => $label ) {
				if ( isset( $raw['pppk']['didik'][ $key ] ) ) {
					$clean['pppk']['didik'][ $key ]['guru']   = absint( $raw['pppk']['didik'][ $key ]['guru'] ?? 0 );
					$clean['pppk']['didik'][ $key ]['nakes']  = absint( $raw['pppk']['didik'][ $key ]['nakes'] ?? 0 );
					$clean['pppk']['didik'][ $key ]['teknis'] = absint( $raw['pppk']['didik'][ $key ]['teknis'] ?? 0 );
				}
			}
		}

		// PPPK PW - Jabatan
		if ( isset( $raw['pppkPw']['jabatan'] ) && is_array( $raw['pppkPw']['jabatan'] ) ) {
			foreach ( self::get_pppk_pw_jabatan() as $key => $label ) {
				if ( isset( $raw['pppkPw']['jabatan'][ $key ] ) ) {
					$clean['pppkPw']['jabatan'][ $key ]['l'] = absint( $raw['pppkPw']['jabatan'][ $key ]['l'] ?? 0 );
					$clean['pppkPw']['jabatan'][ $key ]['p'] = absint( $raw['pppkPw']['jabatan'][ $key ]['p'] ?? 0 );
				}
			}
		}

		// PPPK PW - Didik
		if ( isset( $raw['pppkPw']['didik'] ) && is_array( $raw['pppkPw']['didik'] ) ) {
			foreach ( self::get_pendidikan_levels() as $key => $label ) {
				if ( isset( $raw['pppkPw']['didik'][ $key ] ) ) {
					$clean['pppkPw']['didik'][ $key ]['guru']   = absint( $raw['pppkPw']['didik'][ $key ]['guru'] ?? 0 );
					$clean['pppkPw']['didik'][ $key ]['nakes']  = absint( $raw['pppkPw']['didik'][ $key ]['nakes'] ?? 0 );
					$clean['pppkPw']['didik'][ $key ]['teknis'] = absint( $raw['pppkPw']['didik'][ $key ]['teknis'] ?? 0 );
				}
			}
		}

		return $clean;
	}


}
