<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BKSKM_DB {

    /**
     * Nama tabel database lengkap dengan prefix.
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . BKSKM_TABLE_RESPONSES;
    }

    /**
     * Membuat atau memperbarui tabel database.
     */
    public static function create_tables() {
        global $wpdb;
        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            tgl_layanan date NOT NULL,
            jenis_kelamin varchar(50) NOT NULL,
            pendidikan varchar(100) NOT NULL,
            usia varchar(50) NOT NULL,
            pekerjaan varchar(100) NOT NULL,
            pekerjaan_lainnya varchar(255) DEFAULT '',
            is_disabilitas varchar(10) NOT NULL DEFAULT 'Tidak',
            jenis_disabilitas varchar(100) DEFAULT '',
            q1 tinyint(2) NOT NULL,
            q2 tinyint(2) NOT NULL,
            q3 tinyint(2) NOT NULL,
            q4 tinyint(2) NOT NULL,
            q5 tinyint(2) NOT NULL,
            q6 tinyint(2) NOT NULL,
            q7 tinyint(2) NOT NULL,
            q8 tinyint(2) NOT NULL,
            q9 tinyint(2) NOT NULL,
            q10 tinyint(2) NOT NULL,
            q11 tinyint(2) NOT NULL,
            q12 tinyint(2) NOT NULL,
            q13 tinyint(2) NOT NULL,
            q14 tinyint(2) NOT NULL,
            q15 tinyint(2) NOT NULL,
            q16 tinyint(2) NOT NULL,
            kritik_saran text DEFAULT '',
            ip_address varchar(100) DEFAULT '',
            user_agent text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Menyimpan tanggapan kuesioner baru.
     */
    public static function insert_response( $data ) {
        global $wpdb;
        $table_name = self::get_table_name();

        $inserted = $wpdb->insert(
            $table_name,
            array(
                'tgl_layanan'       => sanitize_text_field( $data['tgl_layanan'] ),
                'jenis_kelamin'     => sanitize_text_field( $data['jenis_kelamin'] ),
                'pendidikan'        => sanitize_text_field( $data['pendidikan'] ),
                'usia'              => sanitize_text_field( $data['usia'] ),
                'pekerjaan'         => sanitize_text_field( $data['pekerjaan'] ),
                'pekerjaan_lainnya' => isset( $data['pekerjaan_lainnya'] ) ? sanitize_text_field( $data['pekerjaan_lainnya'] ) : '',
                'is_disabilitas'    => sanitize_text_field( $data['is_disabilitas'] ),
                'jenis_disabilitas' => isset( $data['jenis_disabilitas'] ) ? sanitize_text_field( $data['jenis_disabilitas'] ) : '',
                'q1'                => intval( $data['q1'] ),
                'q2'                => intval( $data['q2'] ),
                'q3'                => intval( $data['q3'] ),
                'q4'                => intval( $data['q4'] ),
                'q5'                => intval( $data['q5'] ),
                'q6'                => intval( $data['q6'] ),
                'q7'                => intval( $data['q7'] ),
                'q8'                => intval( $data['q8'] ),
                'q9'                => intval( $data['q9'] ),
                'q10'               => intval( $data['q10'] ),
                'q11'               => intval( $data['q11'] ),
                'q12'               => intval( $data['q12'] ),
                'q13'               => intval( $data['q13'] ),
                'q14'               => intval( $data['q14'] ),
                'q15'               => intval( $data['q15'] ),
                'q16'               => intval( $data['q16'] ),
                'kritik_saran'      => sanitize_textarea_field( $data['kritik_saran'] ),
                'ip_address'        => self::get_client_ip(),
                'user_agent'        => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ), 0, 500 ) : '',
                'created_at'        => current_time( 'mysql' ),
            ),
            array(
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d',
                '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d',
                '%s', '%s', '%s', '%s'
            )
        );

        if ( $inserted === false ) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Menghitung statistik dan IKM berdasarkan rentang tanggal.
     */
    public static function get_stats( $start_date = '', $end_date = '' ) {
        global $wpdb;
        $table_name = self::get_table_name();

        $where = "WHERE 1=1";
        $params = array();

        if ( ! empty( $start_date ) ) {
            $where .= " AND tgl_layanan >= %s";
            $params[] = $start_date;
        }
        if ( ! empty( $end_date ) ) {
            $where .= " AND tgl_layanan <= %s";
            $params[] = $end_date;
        }

        $query = "SELECT COUNT(*) as total_respondents,
            AVG(q1) as avg_q1, AVG(q2) as avg_q2, AVG(q3) as avg_q3, AVG(q4) as avg_q4,
            AVG(q5) as avg_q5, AVG(q6) as avg_q6, AVG(q7) as avg_q7, AVG(q8) as avg_q8,
            AVG(q9) as avg_q9, AVG(q10) as avg_q10, AVG(q11) as avg_q11, AVG(q12) as avg_q12,
            AVG(q13) as avg_q13, AVG(q14) as avg_q14, AVG(q15) as avg_q15, AVG(q16) as avg_q16
            FROM $table_name $where";

        if ( ! empty( $params ) ) {
            $stats = $wpdb->get_row( $wpdb->prepare( $query, $params ), ARRAY_A );
        } else {
            $stats = $wpdb->get_row( $query, ARRAY_A );
        }

        $total = intval( $stats['total_respondents'] ?? 0 );

        $questions_avg = array();
        $total_avg_sum = 0;
        for ( $i = 1; $i <= 16; $i++ ) {
            $val = floatval( $stats["avg_q{$i}"] ?? 0 );
            $questions_avg[$i] = round( $val, 2 );
            $total_avg_sum += $val;
        }

        $avg_unsur = $total > 0 ? ( $total_avg_sum / 16 ) : 0;
        $ikm_val = round( $avg_unsur * 25, 2 ); // Formula PermenPANRB: Rata2 Unsur * 25

        // Determination of Mutu & Kinerja
        $mutu = 'D';
        $kinerja = 'Sangat Kurang';
        $color = '#ef4444'; // Red

        if ( $ikm_val >= 88.31 ) {
            $mutu = 'A';
            $kinerja = 'Sangat Baik';
            $color = '#10b981'; // Green
        } elseif ( $ikm_val >= 76.61 ) {
            $mutu = 'B';
            $kinerja = 'Baik';
            $color = '#3b82f6'; // Blue
        } elseif ( $ikm_val >= 65.00 ) {
            $mutu = 'C';
            $kinerja = 'Kurang Baik';
            $color = '#f59e0b'; // Amber
        }

        // Demographics breakdown
        $demographics = self::get_demographics_breakdown( $where, $params );

        return array(
            'total_respondents' => $total,
            'ikm_score'         => $ikm_val,
            'avg_unsur'         => round( $avg_unsur, 2 ),
            'mutu'              => $mutu,
            'kinerja'           => $kinerja,
            'color'             => $color,
            'questions_avg'     => $questions_avg,
            'demographics'      => $demographics,
        );
    }

    /**
     * Breakdown demografi responden
     */
    private static function get_demographics_breakdown( $where_clause, $params ) {
        global $wpdb;
        $table = self::get_table_name();

        $get_counts = function( $column ) use ( $wpdb, $table, $where_clause, $params ) {
            $sql = "SELECT $column as label, COUNT(*) as count FROM $table $where_clause GROUP BY $column";
            if ( ! empty( $params ) ) {
                return $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
            }
            return $wpdb->get_results( $sql, ARRAY_A );
        };

        return array(
            'gender'      => $get_counts( 'jenis_kelamin' ),
            'education'   => $get_counts( 'pendidikan' ),
            'age'         => $get_counts( 'usia' ),
            'profession'  => $get_counts( 'pekerjaan' ),
            'disability'  => $get_counts( 'is_disabilitas' ),
        );
    }

    /**
     * Mendapatkan daftar tanggapan dengan pagination & search
     */
    public static function get_responses( $limit = 20, $offset = 0, $search = '', $start_date = '', $end_date = '' ) {
        global $wpdb;
        $table_name = self::get_table_name();

        $where = "WHERE 1=1";
        $params = array();

        if ( ! empty( $search ) ) {
            $where .= " AND (jenis_kelamin LIKE %s OR pendidikan LIKE %s OR pekerjaan LIKE %s OR kritik_saran LIKE %s)";
            $s = '%' . $wpdb->esc_like( $search ) . '%';
            $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
        }

        if ( ! empty( $start_date ) ) {
            $where .= " AND tgl_layanan >= %s";
            $params[] = $start_date;
        }
        if ( ! empty( $end_date ) ) {
            $where .= " AND tgl_layanan <= %s";
            $params[] = $end_date;
        }

        $sql = "SELECT * FROM $table_name $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = intval( $limit );
        $params[] = intval( $offset );

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
    }

    /**
     * Hitung total baris untuk pagination
     */
    public static function count_responses( $search = '', $start_date = '', $end_date = '' ) {
        global $wpdb;
        $table_name = self::get_table_name();

        $where = "WHERE 1=1";
        $params = array();

        if ( ! empty( $search ) ) {
            $where .= " AND (jenis_kelamin LIKE %s OR pendidikan LIKE %s OR pekerjaan LIKE %s OR kritik_saran LIKE %s)";
            $s = '%' . $wpdb->esc_like( $search ) . '%';
            $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
        }

        if ( ! empty( $start_date ) ) {
            $where .= " AND tgl_layanan >= %s";
            $params[] = $start_date;
        }
        if ( ! empty( $end_date ) ) {
            $where .= " AND tgl_layanan <= %s";
            $params[] = $end_date;
        }

        $sql = "SELECT COUNT(*) FROM $table_name $where";
        if ( ! empty( $params ) ) {
            return intval( $wpdb->get_var( $wpdb->prepare( $sql, $params ) ) );
        }
        return intval( $wpdb->get_var( $sql ) );
    }

    /**
     * Hapus tanggapan berdasarkan ID
     */
    public static function delete_response( $id ) {
        global $wpdb;
        return $wpdb->delete( self::get_table_name(), array( 'id' => intval( $id ) ), array( '%d' ) );
    }

    /**
     * Mendapatkan IP address pengguna
     */
    public static function get_client_ip() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ips = explode( ',', sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            $ip = trim( $ips[0] );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
        }
        return substr( $ip, 0, 45 );
    }
}
