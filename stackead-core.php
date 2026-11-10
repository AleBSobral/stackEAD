<?php
/**
 * Plugin Name: StackEAD Core
 * Plugin URI:  https://artinnova.org/stackead
 * Description: Ecossistema EAD Monolítico de Alta Performance com Acessibilidade Cognitiva (W3C COGA), Gamificação Nativa, Fóruns por Turma e Aplicação Externa Standalone.
 * Version:     1.0.0
 * Author:      ArtInnova Tech Team
 * Author URI:  https://artinnova.org
 * Text Domain: stackead-core
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Constants
define( 'STACKEAD_VERSION', '1.0.0' );
define( 'STACKEAD_FILE', __FILE__ );
define( 'STACKEAD_PATH', plugin_dir_path( __FILE__ ) );
define( 'STACKEAD_URL', plugin_dir_url( __FILE__ ) );

// 2. Load Autoloader
require_once STACKEAD_PATH . 'includes/Core/Autoloader.php';
\StackEAD\Core\Autoloader::register();

// 3. Activation & Deactivation Hooks
register_activation_hook( __FILE__, function () {
	// 1. Database Relational Tables
	\StackEAD\Core\Database::migrate();

	// 2. Roles & Capabilities
	\StackEAD\Roles\RoleManager::register_roles();

	// 3. Rewrite Rules
	\StackEAD\Core\RewriteRules::add_rewrite_rules();

	// Flush rules
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );

// 4. Initialize Core Modules on Plugins Loaded
add_action( 'plugins_loaded', function () {
	// Post Types & Taxonomies
	\StackEAD\PostTypes\PostTypeRegistrar::init();

	// Role Scoping & Master Isolation
	\StackEAD\Roles\ScopeFilter::init();

	// REST API Endpoints
	\StackEAD\RestApi\ApiRouter::init();

	// Standalone SPA Routing (/plataforma & /certificados)
	\StackEAD\Core\RewriteRules::init();

	// Gamification Event Listeners
	\StackEAD\Services\GamificationService::init();

	// WP-Admin Rich Meta Boxes & Course Builder
	\StackEAD\Admin\AdminMetaBoxes::init();

	// Strict Role & Access Control (Admin-only wp-admin & admin bar, role-based navigation)
	\StackEAD\Roles\AccessControl::init();

	// White Label Branding & Institutional Settings
	\StackEAD\Admin\WhiteLabelSettings::init();

	// Image Optimization Pipeline & Avatar (WebP <150KB)
	\StackEAD\Services\ImageOptimizer::init();

	// White Label Authentication Manager (/login interceptor)
	\StackEAD\Auth\AuthManager::init();

	// Standalone Course Landing Page Engine (ArtInnova Light)
	\StackEAD\Landing\LandingTemplateEngine::init();

	// Bidirectional Bridge with CIIAM (Sync & Formations)
	\StackEAD\Sync\CiiamBridge::init();
} );

// 5. WP-Admin Bridge Menu & Platform Launchers
add_action( 'admin_menu', function () {
	add_menu_page(
		__( 'stackEAD', 'stackead-core' ),
		__( 'stackEAD', 'stackead-core' ),
		'read',
		'stackead-dashboard',
		function () {
			?>
			<div class="wrap" style="max-width: 860px; margin-top: 30px;">
				<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 36px; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
					<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
						<span style="font-size: 32px;">🎓</span>
						<h1 style="font-size: 26px; font-weight: 800; color: #1e1b4b; margin: 0;">Ecossistema stackEAD Core</h1>
					</div>
					<p style="font-size: 15px; color: #475569; line-height: 1.7; margin-bottom: 24px;">
						O <strong>stackEAD</strong> opera como uma aplicação desacoplada e independente do painel tradicional do WordPress, rodando diretamente na rota externa <code>/plataforma</code> com dock cognitivo W3C COGA, sala de aula imersiva, sistema de fóruns e gestão de notas.
					</p>
					<div style="display: flex; gap: 16px; flex-wrap: wrap;">
						<a href="<?php echo esc_url( home_url( '/plataforma' ) ); ?>" target="_blank" class="button button-primary button-hero" style="background: #4f46e5; border-color: #4f46e5; border-radius: 8px; font-weight: 700;">
							🚀 Abrir Plataforma EAD (/plataforma) &rarr;
						</a>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=stackead_course' ) ); ?>" class="button button-secondary button-hero" style="border-radius: 8px;">
							➕ Criar Novo Curso com Ementa
						</a>
						<a href="<?php echo esc_url( home_url( '/certificados/DEMO-TEST' ) ); ?>" target="_blank" class="button button-secondary button-hero" style="border-radius: 8px;">
							🔍 Testar Validador de Certificados
						</a>
					</div>
				</div>
			</div>
			<?php
		},
		'dashicons-welcome-learn-more',
		3
	);

	// Direct Platform Launcher Submenu
	add_submenu_page(
		'stackead-dashboard',
		__( 'Abrir Plataforma EAD', 'stackead-core' ),
		'<span style="color:#6366f1; font-weight:700;">🚀 Abrir Plataforma</span>',
		'read',
		'stackead-open-platform',
		function () {
			?>
			<script>
				window.open('<?php echo esc_url( home_url( '/plataforma' ) ); ?>', '_blank');
				window.location.href = '<?php echo esc_url( admin_url( 'edit.php?post_type=stackead_course' ) ); ?>';
			</script>
			<div class="wrap">
				<p><?php esc_html_e( 'Abrindo a Plataforma EAD em nova aba...', 'stackead-core' ); ?> <a href="<?php echo esc_url( home_url( '/plataforma' ) ); ?>" target="_blank"><?php esc_html_e( 'Clique aqui caso não abra automaticamente.', 'stackead-core' ); ?></a></p>
			</div>
			<?php
		},
		0
	);
} );

// 6. Admin Bar Quick Launcher
add_action( 'admin_bar_menu', function ( $admin_bar ) {
	$admin_bar->add_node(
		array(
			'id'    => 'stackead_app_launcher',
			'title' => '<span class="ab-icon dashicons dashicons-welcome-learn-more" style="top:2px;"></span> <span style="font-weight:700; color:#818cf8;">🎓 Plataforma EAD</span>',
			'href'  => home_url( '/plataforma' ),
			'meta'  => array(
				'target' => '_blank',
				'title'  => __( 'Abrir Plataforma do Aluno e Professor (/plataforma)', 'stackead-core' ),
			),
		)
	);
}, 90 );

// 7. Global Admin Notice on EAD screens
add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen || ( false === strpos( $screen->post_type, 'stackead_' ) && false === strpos( $screen->id, 'stackead' ) ) ) {
		return;
	}
	?>
	<div class="notice notice-info" style="display:flex; align-items:center; justify-content:space-between; border-left:4px solid #6366f1; padding:12px 18px; margin:15px 0; background:#f8fafc; border-radius:6px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
		<div style="display:flex; align-items:center; gap:12px;">
			<span style="font-size:24px;">🎓</span>
			<div>
				<strong style="color:#1e1b4b; font-size:14px;"><?php esc_html_e( 'Ambiente de Aprendizagem stackEAD:', 'stackead-core' ); ?></strong>
				<p style="margin:2px 0 0; color:#475569; font-size:13px;"><?php esc_html_e( 'O painel do aluno, sala de aula imersiva, diário dos mestres e dock COGA rodam na aplicação externa desacoplada.', 'stackead-core' ); ?></p>
			</div>
		</div>
		<a href="<?php echo esc_url( home_url( '/plataforma' ) ); ?>" target="_blank" class="button button-primary" style="background:#4f46e5; border-color:#4f46e5; font-weight:700; height:34px; line-height:32px; border-radius:6px; display:flex; align-items:center; gap:6px;">
			<?php esc_html_e( '🚀 Acessar Plataforma EAD (/plataforma)', 'stackead-core' ); ?> &rarr;
		</a>
	</div>
	<?php
} );
