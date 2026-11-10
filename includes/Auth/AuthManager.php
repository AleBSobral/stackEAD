<?php
namespace StackEAD\Auth;

use StackEAD\Admin\WhiteLabelSettings;
use StackEAD\Roles\AccessControl;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AuthManager - Enterprise White Label Authentication Controller.
 *
 * Controls custom /login routing, intercepts standard wp-login.php requests,
 * executes AJAX logins with security checks, and ensures no end-user ever sees
 * the default WordPress login screen.
 */
class AuthManager {

	/**
	 * Initialize authentication hooks.
	 */
	public static function init(): void {
		// 1. Intercept template redirect for /login and /login/
		add_action( 'template_redirect', array( __CLASS__, 'handle_login_redirect' ), 0 );

		// 2. Intercept wp-login.php accesses
		add_action( 'login_init', array( __CLASS__, 'intercept_wp_login' ) );

		// 3. Style native wp-login as fallback (e.g. for logout confirmation screens)
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'enqueue_whitelabel_login_styles' ) );
		add_filter( 'login_headerurl', array( __CLASS__, 'get_login_logo_url' ) );
		add_filter( 'login_headertext', array( __CLASS__, 'get_login_logo_text' ) );
		add_filter( 'login_redirect', array( __CLASS__, 'filter_login_redirect' ), 20, 3 );
	}

	/**
	 * Renders custom White Label login page on /login requests.
	 */
	public static function handle_login_redirect(): void {
		$login_path            = untrailingslashit( (string) wp_parse_url( home_url( '/login' ), PHP_URL_PATH ) );
		$plataforma_login_path = untrailingslashit( (string) wp_parse_url( home_url( '/plataforma-login' ), PHP_URL_PATH ) );
		$current_path          = untrailingslashit( strtok( $_SERVER['REQUEST_URI'] ?? '', '?' ) );

		$is_login_path = (
			$current_path === $login_path ||
			$current_path === $plataforma_login_path ||
			'/login' === $current_path ||
			'/plataforma-login' === $current_path ||
			'1' === (string) get_query_var( 'stackead_login' )
		);

		if ( ! $is_login_path ) {
			return;
		}

		// If user is already authenticated
		if ( is_user_logged_in() ) {
			$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/plataforma' );
			$current_user = wp_get_current_user();

			// Only allow wp-admin redirect if user is an administrator
			if ( str_contains( $redirect_to, 'wp-admin' ) && ! AccessControl::is_administrator( $current_user ) ) {
				$redirect_to = home_url( '/plataforma' );
			}

			wp_safe_redirect( $redirect_to );
			exit;
		}

		LoginTemplate::render();
		exit;
	}

	/**
	 * Intercept direct accesses to wp-login.php and redirect to /login.
	 */
	public static function intercept_wp_login(): void {
		$action = $_REQUEST['action'] ?? '';

		// Allow logout, postpass, or interims to process normally through core
		if ( in_array( $action, array( 'logout', 'postpass', 'confirmaction' ), true ) ) {
			return;
		}

		// Don't intercept if handling a REST request or AJAX
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		$login_url = home_url( '/login' );
		if ( ! empty( $_GET['redirect_to'] ) ) {
			$login_url = add_query_arg( 'redirect_to', urlencode( wp_unslash( $_GET['redirect_to'] ) ), $login_url );
		}
		if ( ! empty( $_GET['loggedout'] ) ) {
			$login_url = add_query_arg( 'loggedout', 'true', $login_url );
		}

		wp_safe_redirect( $login_url );
		exit;
	}

	/**
	 * Fallback styling for wp-login.php if rendered.
	 */
	public static function enqueue_whitelabel_login_styles(): void {
		$settings = WhiteLabelSettings::get_settings();
		$primary  = esc_attr( $settings['primary_color'] );
		$contrast = esc_attr( WhiteLabelSettings::get_contrast_color( $settings['primary_color'] ) );
		$logo     = ! empty( $settings['logo_dark_url'] ) ? esc_url( $settings['logo_dark_url'] ) : esc_url( $settings['logo_url'] );

		?>
		<style id="stackead-wp-login-override">
			body.login {
				background: #090d16 !important;
				color: #e2e8f0 !important;
				font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
				position: relative;
				display: flex;
				align-items: center;
				justify-content: center;
				min-height: 100vh;
			}
			#login {
				background: rgba(15, 23, 42, 0.85) !important;
				backdrop-filter: blur(20px) !important;
				-webkit-backdrop-filter: blur(20px) !important;
				border: 1px solid rgba(255, 255, 255, 0.1) !important;
				border-radius: 16px !important;
				padding: 32px !important;
				box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
				width: 100% !important;
				max-width: 420px !important;
			}
			#login h1 a {
				background-image: url('<?php echo $logo; ?>') !important;
				background-size: contain !important;
				background-position: center !important;
				width: 100% !important;
				height: 60px !important;
				margin-bottom: 24px !important;
			}
			#login form {
				background: transparent !important;
				border: none !important;
				box-shadow: none !important;
				padding: 0 !important;
			}
			#login form .input {
				background: #1e293b !important;
				border: 1px solid #334155 !important;
				color: #f8fafc !important;
				border-radius: 8px !important;
				padding: 12px 14px !important;
			}
			#login form .input:focus {
				border-color: <?php echo $primary; ?> !important;
				box-shadow: 0 0 0 2px <?php echo $primary; ?>33 !important;
			}
			#login label {
				color: #94a3b8 !important;
				font-weight: 500 !important;
			}
			.wp-core-ui .button-primary {
				background: <?php echo $primary; ?> !important;
				border-color: <?php echo $primary; ?> !important;
				color: <?php echo $contrast; ?> !important;
				border-radius: 8px !important;
				font-weight: 700 !important;
				padding: 6px 20px !important;
				width: 100% !important;
				margin-top: 14px !important;
			}
			.login #nav a, .login #backtoblog a {
				color: #94a3b8 !important;
			}
			.login #nav a:hover, .login #backtoblog a:hover {
				color: #f8fafc !important;
			}
		</style>
		<?php
	}

	public static function get_login_logo_url(): string {
		return home_url();
	}

	public static function get_login_logo_text(): string {
		$settings = WhiteLabelSettings::get_settings();
		return $settings['institution_name'];
	}

	/**
	 * Filters login redirect destination.
	 */
	public static function filter_login_redirect( $redirect_to, $request, $user ) {
		if ( ! is_wp_error( $user ) && is_object( $user ) && isset( $user->roles ) ) {
			if ( AccessControl::is_administrator( $user ) ) {
				return ! empty( $redirect_to ) ? $redirect_to : admin_url();
			}
			return home_url( '/plataforma' );
		}
		return $redirect_to;
	}
}
