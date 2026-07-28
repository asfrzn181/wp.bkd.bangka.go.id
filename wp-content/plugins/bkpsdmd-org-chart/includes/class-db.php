<?php
/**
 * Database Class - BKPSDMD Struktur Organisasi
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BKPSDMD_Org_DB {

    /**
     * Nama tabel (tanpa prefix)
     */
    public static function table() {
        global $wpdb;
        return $wpdb->prefix . BKPSDMD_ORG_TABLE;
    }

    /**
     * Buat tabel saat aktivasi plugin
     */
    public static function create_table() {
        global $wpdb;
        $table      = self::table();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            parent_id   BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            jabatan     VARCHAR(255) NOT NULL DEFAULT '',
            nama        VARCHAR(255) NOT NULL DEFAULT '',
            nip         VARCHAR(50)  NOT NULL DEFAULT '',
            foto_url    TEXT         NOT NULL DEFAULT '',
            keterangan  TEXT         NOT NULL DEFAULT '',
            urutan      INT(11)      NOT NULL DEFAULT 0,
            aktif       TINYINT(1)   NOT NULL DEFAULT 1,
            created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY parent_id (parent_id),
            KEY urutan    (urutan)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Hapus tabel saat uninstall
     */
    public static function drop_table() {
        global $wpdb;
        $table = self::table();
        $wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore
    }

    /**
     * Ambil semua data terurut parent-child
     */
    public static function get_all( $aktif_only = false ) {
        global $wpdb;
        $table = self::table();
        $where = $aktif_only ? 'WHERE aktif = 1' : '';
        // phpcs:ignore
        return $wpdb->get_results(
            "SELECT * FROM {$table} {$where} ORDER BY parent_id ASC, urutan ASC, id ASC"
        );
    }

    /**
     * Ambil satu record
     */
    public static function get_one( $id ) {
        global $wpdb;
        $table = self::table();
        // phpcs:ignore
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
    }

    /**
     * Insert record baru
     */
    public static function insert( $data ) {
        global $wpdb;
        $wpdb->insert(
            self::table(),
            array(
                'parent_id'  => intval( $data['parent_id'] ?? 0 ),
                'jabatan'    => sanitize_text_field( $data['jabatan'] ?? '' ),
                'nama'       => sanitize_text_field( $data['nama'] ?? '' ),
                'nip'        => sanitize_text_field( $data['nip'] ?? '' ),
                'foto_url'   => esc_url_raw( $data['foto_url'] ?? '' ),
                'keterangan' => sanitize_textarea_field( $data['keterangan'] ?? '' ),
                'urutan'     => intval( $data['urutan'] ?? 0 ),
                'aktif'      => intval( $data['aktif'] ?? 1 ),
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d' )
        );
        return $wpdb->insert_id;
    }

    /**
     * Update record
     */
    public static function update( $id, $data ) {
        global $wpdb;
        return $wpdb->update(
            self::table(),
            array(
                'parent_id'  => intval( $data['parent_id'] ?? 0 ),
                'jabatan'    => sanitize_text_field( $data['jabatan'] ?? '' ),
                'nama'       => sanitize_text_field( $data['nama'] ?? '' ),
                'nip'        => sanitize_text_field( $data['nip'] ?? '' ),
                'foto_url'   => esc_url_raw( $data['foto_url'] ?? '' ),
                'keterangan' => sanitize_textarea_field( $data['keterangan'] ?? '' ),
                'urutan'     => intval( $data['urutan'] ?? 0 ),
                'aktif'      => intval( $data['aktif'] ?? 1 ),
            ),
            array( 'id' => intval( $id ) ),
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ),
            array( '%d' )
        );
    }

    /**
     * Hapus record
     */
    public static function delete( $id ) {
        global $wpdb;
        // Pindahkan anak ke root jika parent dihapus
        $wpdb->update(
            self::table(),
            array( 'parent_id' => 0 ),
            array( 'parent_id' => intval( $id ) ),
            array( '%d' ),
            array( '%d' )
        );
        return $wpdb->delete( self::table(), array( 'id' => intval( $id ) ), array( '%d' ) );
    }

    /**
     * Bangun pohon hierarki dari flat array
     */
    public static function build_tree( $rows, $parent_id = 0 ) {
        $branch = array();
        foreach ( $rows as $row ) {
            if ( intval( $row->parent_id ) === intval( $parent_id ) ) {
                $children = self::build_tree( $rows, $row->id );
                $row->children = $children;
                $branch[] = $row;
            }
        }
        return $branch;
    }
}
