<?php
namespace StackEAD\App;

use StackEAD\RestApi\ApiRouter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Standalone Application Shell for StackEAD (/plataforma).
 * Completely decoupled from wp-admin.
 */
class AppTemplate {

	/**
	 * Render the standalone application.
	 */
	public static function render(): void {
		$is_logged_in = is_user_logged_in();
		if ( ! $is_logged_in ) {
			\StackEAD\Auth\LoginTemplate::render();
			return;
		}

		$user_context = ApiRouter::get_current_user_context()->get_data();
		$settings     = \StackEAD\Admin\WhiteLabelSettings::get_settings();

		$plugin_url = plugin_dir_url( dirname( __DIR__ ) );
		status_header( 200 );
		?>
		<!DOCTYPE html>
		<html lang="pt-BR" data-theme="dark">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo esc_html( sprintf( __( 'Plataforma EAD | %s', 'stackead-core' ), $settings['institution_name'] ) ); ?></title>

			<!-- Modern Fonts -->
			<link rel="preconnect" href="https://fonts.googleapis.com">
			<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
			<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&family=Lexend:wght@400;500;600;700&display=swap" rel="stylesheet">
			
			<!-- Dyslexia Font Support -->
			<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/opendyslexic@1.0.3/dist/opendyslexic.min.css">

			<!-- Lucide Icons -->
			<script src="https://unpkg.com/lucide@latest"></script>

			<?php
			\StackEAD\Admin\WhiteLabelSettings::inject_brand_tokens();
			\StackEAD\Admin\WhiteLabelSettings::inject_favicon();
			?>

			<!-- StackEAD Styles -->
			<link rel="stylesheet" href="<?php echo esc_url( $plugin_url . 'assets/css/stackead-app.css?v=' . filemtime( dirname( __DIR__, 2 ) . '/assets/css/stackead-app.css' ) ); ?>">
			<link rel="stylesheet" href="<?php echo esc_url( $plugin_url . 'assets/css/coga-accessibility.css?v=' . filemtime( dirname( __DIR__, 2 ) . '/assets/css/coga-accessibility.css' ) ); ?>">

			<script>
				window.StackEAD_Config = {
					root: '<?php echo esc_url_raw( rest_url() ); ?>',
					nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
					isLoggedIn: <?php echo $is_logged_in ? 'true' : 'false'; ?>,
					user: <?php echo wp_json_encode( $user_context ); ?>,
					homeUrl: '<?php echo esc_url( home_url() ); ?>',
					loginUrl: '<?php echo esc_url( home_url( '/login' ) ); ?>',
					whitelabel: <?php echo wp_json_encode( $settings ); ?>
				};
			</script>
		</head>
		<body class="<?php echo $is_logged_in ? 'logged-in' : 'logged-out'; ?>">

			<!-- Focus Reading Ruler Overlay (W3C COGA) -->
			<div id="coga-reading-guide" class="coga-reading-guide" style="display: none;"></div>

			<?php if ( ! $is_logged_in ) : ?>
				<!-- Guest / Login Screen -->
				<div class="auth-wrapper">
					<div class="auth-card">
						<div class="brand-logo">
							<?php if ( ! empty( $settings['logo_dark_url'] ) || ! empty( $settings['logo_url'] ) ) : ?>
								<img src="<?php echo esc_url( ! empty( $settings['logo_dark_url'] ) ? $settings['logo_dark_url'] : $settings['logo_url'] ); ?>" alt="<?php echo esc_attr( $settings['institution_name'] ); ?>" style="max-height: 48px; max-width: 180px; object-fit: contain; margin-bottom: 12px;">
							<?php else : ?>
								<div class="logo-icon"><i data-lucide="layers"></i></div>
							<?php endif; ?>
							<span class="logo-text"><?php echo esc_html( $settings['institution_name'] ); ?></span>
						</div>
						<h2><?php esc_html_e( 'Bem-vindo ao Ambiente EAD', 'stackead-core' ); ?></h2>
						<p><?php echo esc_html( sprintf( __( 'Entre com suas credenciais %s para acessar suas formações, trilhas e sessões ao vivo.', 'stackead-core' ), $settings['institution_name'] ) ); ?></p>
						<div class="auth-actions">
							<a href="<?php echo esc_url( home_url( '/login' ) ); ?>" class="btn btn-primary btn-block">
								<i data-lucide="log-in"></i>
								<?php esc_html_e( 'Acessar Minha Conta', 'stackead-core' ); ?>
							</a>
							<a href="<?php echo esc_url( home_url() ); ?>" class="btn btn-secondary btn-block">
								<i data-lucide="arrow-left"></i>
								<?php esc_html_e( 'Voltar ao Site Principal', 'stackead-core' ); ?>
							</a>
						</div>
					</div>
				</div>
			<?php else : ?>
				<!-- Authenticated Standalone Application Layout -->
				<div id="app-container" class="app-layout">
					
					<!-- Mobile Backdrop Overlay -->
					<div id="sidebar-backdrop" class="sidebar-backdrop"></div>

					<!-- Sidebar -->
					<aside id="app-sidebar" class="app-sidebar">
						<div class="sidebar-header">
							<div class="brand-logo">
								<?php
								$sidebar_logo = ! empty( $settings['logo_url'] ) ? $settings['logo_url'] : plugins_url( 'assets/images/artinnova-logo-white.png', dirname( __DIR__ ) );
								?>
								<img src="<?php echo esc_url( $sidebar_logo ); ?>" alt="<?php echo esc_attr( $settings['institution_name'] ); ?>" class="sidebar-brand-img" style="max-height: 44px; max-width: 175px; object-fit: contain; filter: brightness(0) invert(1) drop-shadow(0 2px 8px rgba(0,0,0,0.4));">
							</div>
							<button id="btn-toggle-sidebar" class="btn-icon" title="Recolher Menu">
								<i data-lucide="chevron-left"></i>
							</button>
						</div>

						<!-- Cohort Switcher -->
						<div class="cohort-selector-box">
							<label><i data-lucide="calendar"></i> <?php esc_html_e( 'Turma Selecionada', 'stackead-core' ); ?></label>
							<select id="select-active-cohort">
								<option value=""><?php esc_html_e( 'Carregando turmas...', 'stackead-core' ); ?></option>
							</select>
							<?php
							$can_manage_cohorts = current_user_can( 'manage_options' )
								|| in_array( 'stackead_coordinator', (array) wp_get_current_user()->roles, true )
								|| in_array( 'stackead_master', (array) wp_get_current_user()->roles, true );
							if ( $can_manage_cohorts ) :
							?>
								<button type="button" id="btn-sidebar-create-cohort" class="btn-sidebar-add-cohort" title="<?php esc_attr_e( 'Cadastrar Nova Turma / Coorte', 'stackead-core' ); ?>">
									<i data-lucide="plus-circle"></i>
									<span><?php esc_html_e( 'Nova Turma', 'stackead-core' ); ?></span>
								</button>
							<?php endif; ?>
						</div>

						<!-- Navigation (Dynamically Segregated by Role & Category - Sanfonado / Accordion) -->
						<nav class="sidebar-nav" id="sidebar-accordion-nav">
							<?php
							$nav_sections = ! empty( $user_context['navigation'] ) ? $user_context['navigation'] : array();
							if ( empty( $nav_sections ) && is_user_logged_in() ) {
								$nav_sections = \StackEAD\Roles\AccessControl::get_user_navigation( wp_get_current_user() )['sections'];
							}
							$is_first_nav_item = true;
							$section_icons = array( 'graduation-cap', 'briefcase', 'shield', 'compass' );
							foreach ( $nav_sections as $sec_idx => $section ) :
								$sec_icon = isset( $section_icons[ $sec_idx ] ) ? $section_icons[ $sec_idx ] : 'folder';
							?>
								<div class="nav-accordion-group <?php echo $sec_idx === 0 ? 'is-open' : ''; ?>" data-group-idx="<?php echo esc_attr( $sec_idx ); ?>">
									<button type="button" class="nav-accordion-trigger" data-toggle-group="<?php echo esc_attr( $sec_idx ); ?>">
										<div class="trigger-left">
											<i data-lucide="<?php echo esc_attr( $sec_icon ); ?>" class="group-icon"></i>
											<span class="group-title"><?php echo esc_html( $section['title'] ); ?></span>
										</div>
										<div class="trigger-right">
											<span class="count-badge"><?php echo count( $section['items'] ); ?></span>
											<i data-lucide="chevron-down" class="group-chevron"></i>
										</div>
									</button>
									<ul class="nav-accordion-subitems">
										<?php foreach ( $section['items'] as $item ) : ?>
											<li>
												<button class="nav-link <?php echo $is_first_nav_item ? 'active' : ''; ?>" data-view="<?php echo esc_attr( $item['view'] ); ?>">
													<i data-lucide="<?php echo esc_attr( $item['icon'] ); ?>"></i>
													<span><?php echo esc_html( $item['title'] ); ?></span>
												</button>
											</li>
											<?php $is_first_nav_item = false; ?>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endforeach; ?>
						</nav>

						<!-- Sidebar Footer -->
						<div class="sidebar-footer">
							<div class="user-pill" id="btn-open-user-profile" style="cursor: pointer;" title="Meu Perfil & Avatar">
								<div class="user-avatar" id="sidebar-user-avatar">
									<?php if ( ! empty( $user_context['avatar_url'] ) ) : ?>
										<img src="<?php echo esc_url( $user_context['avatar_url'] ); ?>" alt="<?php echo esc_attr( wp_get_current_user()->display_name ); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
									<?php else : ?>
										<?php echo esc_html( strtoupper( substr( wp_get_current_user()->display_name, 0, 2 ) ) ); ?>
									<?php endif; ?>
								</div>
								<div class="user-meta">
									<span class="user-name"><?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
									<span class="user-role"><?php echo esc_html( ! empty( $user_context['role_label'] ) ? $user_context['role_label'] : $user_context['role'] ); ?></span>
								</div>
								<a href="<?php echo esc_url( wp_logout_url( home_url( '/login?loggedout=true' ) ) ); ?>" class="btn-logout" title="Sair" onclick="event.stopPropagation();">
									<i data-lucide="log-out"></i>
								</a>
							</div>
						</div>
					</aside>

					<!-- Main Content Wrapper -->
					<div class="app-main-wrapper">
						
						<!-- Topbar -->
						<header class="app-topbar">
							<div class="topbar-left">
								<button id="btn-mobile-menu" class="btn-icon mobile-only">
									<i data-lucide="menu"></i>
								</button>
								<div class="breadcrumbs" id="app-breadcrumbs">
									<span class="crumb-parent"><?php esc_html_e( 'Plataforma', 'stackead-core' ); ?></span>
									<span class="crumb-separator">/</span>
									<span class="crumb-current" id="view-title"><?php esc_html_e( 'Meu Painel', 'stackead-core' ); ?></span>
								</div>
							</div>

							<div class="topbar-right">
								<!-- Gamification XP Pill -->
								<div class="gamification-pill" id="topbar-xp-pill" title="Clique para ver suas conquistas">
									<div class="level-badge"><i data-lucide="zap"></i> <span id="topbar-level-num">Nv. 1</span></div>
									<div class="xp-progress-bar">
										<div class="xp-fill" id="topbar-xp-fill" style="width: 20%;"></div>
									</div>
									<span class="xp-text" id="topbar-xp-text">0 XP</span>
								</div>

								<!-- Virtual Coins Wallet Pill -->
								<div class="coin-wallet-pill" id="topbar-coin-pill" title="Carteira de Moedas Virtuais">
									<i data-lucide="coins"></i>
									<span id="topbar-coin-amount">0</span>
									<span id="topbar-coin-symbol">ArtCoins</span>
								</div>

								<!-- Language & Currency Switcher (i18n) -->
								<div class="header-i18n-box">
									<select id="select-app-language" class="i18n-select" title="Selecionar Idioma da Interface">
										<option value="pt-BR">🇧🇷 PT</option>
										<option value="en-US">🇺🇸 EN</option>
										<option value="es-ES">🇪🇸 ES</option>
										<option value="fr-FR">🇫🇷 FR</option>
									</select>
									<select id="select-app-currency" class="i18n-select" title="Moeda Padrão">
										<option value="BRL">R$ BRL</option>
										<option value="USD">$ USD</option>
										<option value="EUR">€ EUR</option>
									</select>
								</div>

								<!-- Dock Acessibilidade Trigger -->
								<button id="btn-toggle-coga" class="btn btn-outline-coga" title="Recursos de Acessibilidade e Apoio Cognitivo (W3C COGA)">
									<i data-lucide="accessibility"></i>
									<span class="desktop-only"><?php esc_html_e( 'Acessibilidade COGA', 'stackead-core' ); ?></span>
								</button>

								<!-- Theme Switcher -->
								<button id="btn-toggle-theme" class="btn-icon" title="Alternar Modo Escuro / Claro">
									<i data-lucide="moon" id="theme-icon"></i>
								</button>

								<?php if ( current_user_can( 'manage_options' ) ) : ?>
									<button type="button" class="btn-icon" title="<?php esc_attr_e( 'Identidade White Label', 'stackead-core' ); ?>" onclick="window.StackEAD_App.navigate('whitelabel-desk');">
										<i data-lucide="sliders"></i>
									</button>
								<?php endif; ?>

								<!-- Site Principal -->
								<a href="<?php echo esc_url( home_url() ); ?>" class="btn-icon" title="Ir para o site principal">
									<i data-lucide="external-link"></i>
								</a>
							</div>
						</header>

						<!-- Dynamic View Container -->
						<main class="app-content-area" id="app-content-root">
							<div class="loading-state">
								<div class="spinner"></div>
								<p><?php esc_html_e( 'Carregando dados da plataforma...', 'stackead-core' ); ?></p>
							</div>
						</main>
					</div>

				</div>

				<!-- Accessibility Dock Modal / Overlay (W3C COGA) -->
				<div id="coga-dock-modal" class="coga-dock-modal" style="display: none;">
					<div class="coga-dock-header">
						<div class="coga-title">
							<i data-lucide="accessibility"></i>
							<h3><?php esc_html_e( 'Recursos de Acessibilidade & Neurodivergência', 'stackead-core' ); ?></h3>
						</div>
						<button id="btn-close-coga" class="btn-icon"><i data-lucide="x"></i></button>
					</div>
					<div class="coga-dock-body">
						<div class="coga-item">
							<div class="coga-item-info">
								<strong><?php esc_html_e( 'Régua de Foco / Guia de Leitura', 'stackead-core' ); ?></strong>
								<span><?php esc_html_e( 'Faixa translúcida guiada pelo mouse para leitura fluida sem salto de linhas.', 'stackead-core' ); ?></span>
							</div>
							<label class="switch">
								<input type="checkbox" id="coga-toggle-ruler">
								<span class="slider"></span>
							</label>
						</div>

						<div class="coga-item">
							<div class="coga-item-info">
								<strong><?php esc_html_e( 'Modo Sem Distrações (Focus Mode)', 'stackead-core' ); ?></strong>
								<span><?php esc_html_e( 'Oculta menus e barras laterais durante o estudo do conteúdo da aula.', 'stackead-core' ); ?></span>
							</div>
							<label class="switch">
								<input type="checkbox" id="coga-toggle-focus">
								<span class="slider"></span>
							</label>
						</div>

						<div class="coga-item">
							<div class="coga-item-info">
								<strong><?php esc_html_e( 'Tipografia Adaptativa', 'stackead-core' ); ?></strong>
								<span><?php esc_html_e( 'Alternar fonte para facilitar leitura e decodificação.', 'stackead-core' ); ?></span>
							</div>
							<div class="coga-font-selector">
								<button class="btn-chip active" data-font="default">Padrão</button>
								<button class="btn-chip" data-font="lexend">Lexend</button>
								<button class="btn-chip" data-font="opendyslexic">OpenDyslexic</button>
							</div>
						</div>

						<div class="coga-item">
							<div class="coga-item-info">
								<strong><?php esc_html_e( 'Espaçamento Ampliado de Texto', 'stackead-core' ); ?></strong>
								<span><?php esc_html_e( 'Aumenta entrelinhas e espaçamento entre palavras.', 'stackead-core' ); ?></span>
							</div>
							<label class="switch">
								<input type="checkbox" id="coga-toggle-spacing">
								<span class="slider"></span>
							</label>
						</div>

						<div class="coga-item">
							<div class="coga-item-info">
								<strong><?php esc_html_e( 'Alto Contraste', 'stackead-core' ); ?></strong>
								<span><?php esc_html_e( 'Máxima distinção entre texto e fundo.', 'stackead-core' ); ?></span>
							</div>
							<label class="switch">
								<input type="checkbox" id="coga-toggle-contrast">
								<span class="slider"></span>
							</label>
						</div>

						<div class="coga-item" style="border-top: 1px solid var(--border); padding-top: 16px;">
							<div class="coga-item-info">
								<strong><?php esc_html_e( 'Solicitar Acomodação Pedagógica Oficial', 'stackead-core' ); ?></strong>
								<span><?php esc_html_e( 'Tempo extra em provas, linguagem simplificada ou tutor de apoio.', 'stackead-core' ); ?></span>
							</div>
							<button id="btn-open-accommodation-request" class="btn btn-secondary btn-sm">
								<?php esc_html_e( 'Fazer Solicitação', 'stackead-core' ); ?>
							</button>
						</div>
					</div>
				</div>

				<!-- Notification Toast Container -->
				<div id="stackead-toast-container" class="toast-container"></div>
			<?php endif; ?>

			<!-- Application Scripts -->
			<script src="<?php echo esc_url( $plugin_url . 'assets/js/stackead-i18n.js?v=' . filemtime( dirname( __DIR__, 2 ) . '/assets/js/stackead-i18n.js' ) ); ?>"></script>
			<script src="<?php echo esc_url( $plugin_url . 'assets/js/coga-dock.js?v=' . filemtime( dirname( __DIR__, 2 ) . '/assets/js/coga-dock.js' ) ); ?>"></script>
			<script src="<?php echo esc_url( $plugin_url . 'assets/js/stackead-app.js?v=' . filemtime( dirname( __DIR__, 2 ) . '/assets/js/stackead-app.js' ) ); ?>"></script>
			<script>
				lucide.createIcons();
			</script>
		</body>
		</html>
		<?php
	}
}
