<?php
/**
 * Database handler — tabel wp_bkpsdmd_shortlinks
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BKSL_DB {

    // ── Create table ─────────────────────────────────────────────────────────
    public static function create_table() {
        global $wpdb;
        $table   = $wpdb->prefix . BKSL_TABLE;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id     BIGINT(20) UNSIGNED NOT NULL,
            slug        VARCHAR(10)         NOT NULL,
            click_count INT(11)             NOT NULL DEFAULT 0,
            created_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY   slug    (slug),
            UNIQUE KEY   post_id (post_id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    // ── Drop table ────────────────────────────────────────────────────────────
    public static function drop_table() {
        global $wpdb;
        $table = $wpdb->prefix . BKSL_TABLE;
        $wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore
    }

    // ── Insert ────────────────────────────────────────────────────────────────
    public static function insert( $post_id, $slug ) {
        global $wpdb;
        return $wpdb->insert(
            $wpdb->prefix . BKSL_TABLE,
            [
                'post_id' => absint( $post_id ),
                'slug'    => sanitize_text_field( $slug ),
            ],
            [ '%d', '%s' ]
        );
    }

    // ── Update slug ───────────────────────────────────────────────────────────
    public static function update_slug( $post_id, $new_slug ) {
        global $wpdb;
        return $wpdb->update(
            $wpdb->prefix . BKSL_TABLE,
            [ 'slug' => sanitize_text_field( $new_slug ) ],
            [ 'post_id' => absint( $post_id ) ],
            [ '%s' ],
            [ '%d' ]
        );
    }

    // ── Delete by post_id ─────────────────────────────────────────────────────
    public static function delete_by_post_id( $post_id ) {
        global $wpdb;
        return $wpdb->delete(
            $wpdb->prefix . BKSL_TABLE,
            [ 'post_id' => absint( $post_id ) ],
            [ '%d' ]
        );
    }

    // ── Delete by id ──────────────────────────────────────────────────────────
    public static function delete_by_id( $id ) {
        global $wpdb;
        return $wpdb->delete(
            $wpdb->prefix . BKSL_TABLE,
            [ 'id' => absint( $id ) ],
            [ '%d' ]
        );
    }

    // ── Get by post_id ────────────────────────────────────────────────────────
    public static function get_by_post_id( $post_id ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}" . BKSL_TABLE . " WHERE post_id = %d LIMIT 1",
                absint( $post_id )
            )
        );
    }

    // ── Get by slug ───────────────────────────────────────────────────────────
    public static function get_by_slug( $slug ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}" . BKSL_TABLE . " WHERE slug = %s LIMIT 1",
                sanitize_text_field( $slug )
            )
        );
    }

    // ── Increment click ───────────────────────────────────────────────────────
    public static function increment_click( $id ) {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}" . BKSL_TABLE . " SET click_count = click_count + 1 WHERE id = %d",
                absint( $id )
            )
        );
    }

    // ── Get all (paginated) ───────────────────────────────────────────────────
    public static function get_all( $limit = 20, $offset = 0, $search = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . BKSL_TABLE;
        $where = '';
        if ( $search ) {
            $like  = '%' . $wpdb->esc_like( $search ) . '%';
            $where = $wpdb->prepare( "WHERE sl.slug LIKE %s OR p.post_title LIKE %s", $like, $like );
        }
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT sl.*, p.post_title, p.post_type, p.post_status
                 FROM {$table} sl
                 LEFT JOIN {$wpdb->posts} p ON p.ID = sl.post_id
                 {$where}
                 ORDER BY sl.created_at DESC
                 LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );
    }

    // ── Count total rows ──────────────────────────────────────────────────────
    public static function count_all( $search = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . BKSL_TABLE;
        if ( $search ) {
            $like = '%' . $wpdb->esc_like( $search ) . '%';
            return (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table} sl LEFT JOIN {$wpdb->posts} p ON p.ID = sl.post_id
                     WHERE sl.slug LIKE %s OR p.post_title LIKE %s",
                    $like, $like
                )
            );
        }
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    }

    // ── Generate unique 5-char alphanum slug ──────────────────────────────────
    public static function generate_unique_slug() {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len   = BKSL_SLUG_LEN;
        do {
            $slug = '';
            for ( $i = 0; $i < $len; $i++ ) {
                $slug .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
            }
        } while ( self::get_by_slug( $slug ) );
        return $slug;
    }
}
