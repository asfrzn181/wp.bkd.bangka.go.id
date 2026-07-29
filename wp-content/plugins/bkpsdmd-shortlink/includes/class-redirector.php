<?php
/**
 * Redirect handler — tangkap slug 5-char, redirect ke post/page asli
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BKSL_Redirector {

    // ── Register rewrite rule ─────────────────────────────────────────────────
    public static function init() {
        add_action( 'init',              [ __CLASS__, 'add_rewrite_rule' ] );
        add_action( 'template_redirect', [ __CLASS__, 'handle_redirect' ] );
        add_filter( 'query_vars',        [ __CLASS__, 'add_query_vars' ] );
    }

    public static function add_rewrite_rule() {
        // Cocokkan slug 5 karakter alphanum di root
        add_rewrite_rule(
            '^([a-zA-Z0-9]{5})/?$',
            'index.php?bksl_slug=$matches[1]',
            'top'
        );
    }

    public static function add_query_vars( $vars ) {
        $vars[] = 'bksl_slug';
        return $vars;
    }

    public static function handle_redirect() {
        $slug = get_query_var( 'bksl_slug' );
        if ( empty( $slug ) ) {
            return;
        }

        $row = BKSL_DB::get_by_slug( $slug );
        if ( ! $row ) {
            // Slug tidak ditemukan → 404
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            return;
        }

        // Increment klik
        BKSL_DB::increment_click( $row->id );

        // Redirect ke URL post/page
        $url = get_permalink( (int) $row->post_id );
        if ( $url ) {
            wp_redirect( $url, 301 );
            exit;
        }
    }

    // ── Flush rewrite rules ───────────────────────────────────────────────────
    public static function flush_rules() {
        self::add_rewrite_rule();
        flush_rewrite_rules();
    }
}
