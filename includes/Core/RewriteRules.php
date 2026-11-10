<?php
namespace StackEAD\Core;

use StackEAD\App\AppTemplate;
use StackEAD\App\CertificateVerifyTemplate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers rewrite endpoints for the standalone App (/plataforma) and public verification (/certificados).
 */
class RewriteRules {

	/**
	 * Register rewrite hooks.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_template_redirect' ), 1 );
	}

	/**
	 * Add custom rewrite rules.
	 */
	public static function add_rewrite_rules(): void {
		add_rewrite_rule( '^plataforma/?$', 'index.php?stackead_app=1', 'top' );
		add_rewrite_rule( '^plataforma/([^/]+)/?$', 'index.php?stackead_app=1&stackead_subpage=$matches[1]', 'top' );
		add_rewrite_rule( '^certificados/([^/]+)/?$', 'index.php?stackead_cert_code=$matches[1]', 'top' );
		add_rewrite_rule( '^login/?$', 'index.php?stackead_login=1', 'top' );
		add_rewrite_rule( '^plataforma-login/?$', 'index.php?stackead_login=1', 'top' );
	}

	/**
	 * Register query variables.
	 *
	 * @param array $vars
	 * @return array
	 */
	public static function add_query_vars( array $vars ): array {
		$vars[] = 'stackead_app';
		$vars[] = 'stackead_subpage';
		$vars[] = 'stackead_cert_code';
		$vars[] = 'stackead_login';
		return $vars;
	}

	/**
	 * Intercept requests and render standalone application templates.
	 */
	public static function handle_template_redirect(): void {
		$is_app    = get_query_var( 'stackead_app' );
		$cert_code = get_query_var( 'stackead_cert_code' );
		$is_login  = get_query_var( 'stackead_login' );

		if ( $is_login || $is_app || ! empty( $cert_code ) ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			nocache_headers();
			if ( ! headers_sent() ) {
				header( 'X-LiteSpeed-Cache-Control: no-cache' );
			}
			do_action( 'litespeed_control_set_nocache', 'StackEAD dynamic route' );
		}

		if ( $is_login ) {
			\StackEAD\Auth\LoginTemplate::render();
			exit;
		}

		if ( $is_app ) {
			AppTemplate::render();
			exit;
		}

		if ( ! empty( $cert_code ) ) {
			CertificateVerifyTemplate::render( sanitize_text_field( $cert_code ) );
			exit;
		}
	}
}
