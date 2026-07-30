<?php
/**
 * Database — buat & upgrade 5 tabel custom webinar
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_DB {

    public static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // ── 1. webinar_meta ─────────────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}webinar_meta (
            post_id             BIGINT(20) UNSIGNED NOT NULL,
            start_datetime      DATETIME            NOT NULL,
            end_datetime        DATETIME            NOT NULL,
            zoom_link           VARCHAR(500)        NOT NULL DEFAULT '',
            youtube_link        VARCHAR(500)        NOT NULL DEFAULT '',
            cert_number_pattern VARCHAR(255)        NOT NULL DEFAULT '',
            sk_template_file    VARCHAR(500)        NOT NULL DEFAULT '',
            petikan_template_file VARCHAR(500)      NOT NULL DEFAULT '',
            PRIMARY KEY (post_id),
            INDEX idx_start (start_datetime),
            INDEX idx_end   (end_datetime)
        ) $charset;" );

        // ── 2. webinar_form_field ────────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}webinar_form_field (
            id               BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            webinar_id       BIGINT(20) UNSIGNED NOT NULL,
            form_type        VARCHAR(20)         NOT NULL DEFAULT 'registration',
            field_key        VARCHAR(100)        NOT NULL,
            label            VARCHAR(255)        NOT NULL,
            field_type       VARCHAR(50)         NOT NULL DEFAULT 'text',
            options          LONGTEXT,
            is_required      TINYINT(1)          NOT NULL DEFAULT 0,
            is_identity_field TINYINT(1)         NOT NULL DEFAULT 0,
            sort_order       INT(11)             NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            INDEX idx_webinar_form (webinar_id, form_type, sort_order)
        ) $charset;" );

        // ── 3. webinar_registrant ────────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}webinar_registrant (
            id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            webinar_id      BIGINT(20) UNSIGNED NOT NULL,
            unique_token    VARCHAR(64)         NOT NULL,
            email           VARCHAR(191)        NOT NULL,
            submission_data LONGTEXT            NOT NULL,
            registered_at   DATETIME            NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uk_token (unique_token),
            INDEX idx_webinar (webinar_id),
            INDEX idx_email   (webinar_id, email)
        ) $charset;" );

        // ── 4. webinar_attendance ────────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}webinar_attendance (
            id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            webinar_id      BIGINT(20) UNSIGNED NOT NULL,
            registrant_id   BIGINT(20) UNSIGNED NOT NULL,
            submission_data LONGTEXT            NOT NULL,
            attended_at     DATETIME            NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uk_registrant (webinar_id, registrant_id),
            INDEX idx_webinar (webinar_id)
        ) $charset;" );

        // ── 5. webinar_sk ────────────────────────────────────────────────────
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}webinar_sk (
            id                BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            webinar_id        BIGINT(20) UNSIGNED NOT NULL,
            sk_number         VARCHAR(255)        NOT NULL DEFAULT '',
            sk_date           DATE,
            signing_official  VARCHAR(255)        NOT NULL DEFAULT '',
            sk_draft_file     VARCHAR(500)        NOT NULL DEFAULT '',
            signing_method    VARCHAR(30)         NOT NULL DEFAULT 'wet_signature',
            sk_signed_file    VARCHAR(500)        NOT NULL DEFAULT '',
            status            VARCHAR(30)         NOT NULL DEFAULT 'draft',
            created_at        DATETIME            NOT NULL,
            updated_at        DATETIME            NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uk_webinar (webinar_id)
        ) $charset;" );

        // ── 6. webinar_certificate ───────────────────────────────────────────
        // sk_id adalah NULLABLE — sertifikat bisa terbit sebelum SK dibuat
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}webinar_certificate (
            id                   BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            webinar_id           BIGINT(20) UNSIGNED NOT NULL,
            sk_id                BIGINT(20) UNSIGNED,
            attendance_id        BIGINT(20) UNSIGNED NOT NULL,
            petikan_number       VARCHAR(255)        NOT NULL DEFAULT '',
            holder_name          VARCHAR(255)        NOT NULL DEFAULT '',
            holder_email         VARCHAR(191)        NOT NULL DEFAULT '',
            file_path_pdf        VARCHAR(500)        NOT NULL DEFAULT '',
            qr_verification_hash VARCHAR(64)         NOT NULL,
            status               VARCHAR(20)         NOT NULL DEFAULT 'active',
            revoked_at           DATETIME,
            revoked_by           BIGINT(20) UNSIGNED,
            revoke_reason        TEXT,
            generated_at         DATETIME            NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uk_hash (qr_verification_hash),
            UNIQUE KEY uk_att (attendance_id),
            INDEX idx_webinar (webinar_id),
            INDEX idx_sk (sk_id),
            INDEX idx_status (status)
        ) $charset;" );
    }

    // ── Helper: get one row ──────────────────────────────────────────────────
    public static function get_row( $table, $where_col, $where_val, $format = '%d' ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$table} WHERE {$where_col} = {$format} LIMIT 1", $where_val )
        );
    }

    // ── Helper: get results ───────────────────────────────────────────────────
    public static function get_results( $sql, $args = [] ) {
        global $wpdb;
        return $args ? $wpdb->get_results( $wpdb->prepare( $sql, $args ) ) : $wpdb->get_results( $sql );
    }
}
