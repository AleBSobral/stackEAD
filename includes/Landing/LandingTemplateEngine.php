<?php
namespace StackEAD\Landing;

use StackEAD\Admin\WhiteLabelSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LandingTemplateEngine - Standalone Course Landing Page Renderer for StackEAD Core.
 *
 * Renders high-converting, accessible course landing pages based on the ArtInnova Light architecture,
 * independent of the active WordPress theme, wired directly to White Label tokens and CRM lead capture.
 */
class LandingTemplateEngine {

	/**
	 * Initialize landing page hooks.
	 */
	public static function init(): void {
		add_filter( 'template_include', array( __CLASS__, 'filter_template_include' ), 99 );
		add_action( 'init', array( __CLASS__, 'register_landing_rewrites' ) );
	}

	/**
	 * Add rewrite rule for /landing/ preview.
	 */
	public static function register_landing_rewrites(): void {
		add_rewrite_rule( '^landing/?$', 'index.php?stackead_landing=1', 'top' );
		add_filter( 'query_vars', function ( $vars ) {
			$vars[] = 'stackead_landing';
			return $vars;
		} );
	}

	/**
	 * Intercept front page or landing requests and serve the standalone course landing page.
	 *
	 * @param string $template
	 * @return string
	 */
	public static function filter_template_include( string $template ): string {
		// 1. Direct query var /landing/
		if ( (int) get_query_var( 'stackead_landing', 0 ) === 1 ) {
			self::render();
			exit;
		}

		// 2. Front-page when homepage_mode is 'course_landing'
		// If the active theme is already 'artinnova-light' (the full multilingual residency theme), let the theme render unless previewing a specific course.
		$settings = WhiteLabelSettings::get_settings();
		if ( ( is_front_page() || is_home() ) && 'course_landing' === $settings['homepage_mode'] ) {
			if ( 'artinnova-light' === get_stylesheet() && empty( $_GET['preview_course_id'] ) && empty( $_GET['template'] ) ) {
				return $template;
			}
			self::render();
			exit;
		}

		// 3. Single course view
		if ( is_singular( 'stackead_course' ) ) {
			self::render( get_the_ID() );
			exit;
		}

		return $template;
	}

	/**
	 * Render the high-converting standalone landing page.
	 *
	 * @param int|null $course_id
	 */
	public static function render( ?int $course_id = null ): void {
		$settings = WhiteLabelSettings::get_settings();
		$plugin_url = defined( 'STACKEAD_URL' ) ? STACKEAD_URL : plugin_dir_url( dirname( __DIR__ ) );
		$landing_assets_url = $plugin_url . 'templates/landing/artinnova-light/';

		// Course data (fallback to default flagship residency if no course specified)
		if ( ! $course_id && ! empty( $_GET['preview_course_id'] ) ) {
			$course_id = (int) $_GET['preview_course_id'];
		}

		$course = null;
		if ( $course_id ) {
			$course = get_post( $course_id );
		} elseif ( ! empty( $settings['featured_course_id'] ) ) {
			$course = get_post( $settings['featured_course_id'] );
		}

		// Resolve template selection ('artinnova_light' or 'tech_future')
		$active_template = ! empty( $_GET['template'] ) ? sanitize_key( $_GET['template'] ) : ( $course ? BlockManager::get_course_template( $course->ID ) : 'artinnova_light' );

		// Load modular blocks
		$blocks = $course ? BlockManager::get_course_blocks( $course->ID ) : BlockManager::get_default_blocks( 0, $active_template );
		$has_custom_blocks = $course ? (bool) get_post_meta( $course->ID, BlockManager::META_BLOCKS, true ) : false;

		$course_title = $course ? $course->post_title : 'Residência Internacional ArtInnova · Brasil + França';
		$course_subtitle = $course ? get_post_meta( $course->ID, '_stackead_subtitle', true ) : 'Aprenda Artes Midiáticas. Construa pontes transatlânticas. Transforme criatividade em impacto real.';
		$course_price = $course ? (float) get_post_meta( $course->ID, '_stackead_price', true ) : 3000.00;
		$course_workload = $course ? (int) get_post_meta( $course->ID, '_stackead_workload_hours', true ) : 120;

		status_header( 200 );
		?>
		<!DOCTYPE html>
		<html lang="pt-BR">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo esc_html( $course_title . ' · ' . $settings['institution_name'] ); ?></title>
			<meta name="description" content="<?php echo esc_attr( $course_subtitle ); ?>">

			<!-- Google Fonts -->
			<link rel="preconnect" href="https://fonts.googleapis.com">
			<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
			<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

			<!-- Lucide Icons -->
			<script src="https://unpkg.com/lucide@latest"></script>

			<!-- Landing Page Stylesheet -->
			<link rel="stylesheet" href="<?php echo esc_url( $landing_assets_url . 'assets/css/main.css?v=' . STACKEAD_VERSION ); ?>">

			<!-- Dynamic Brand Tokens Injection -->
			<style id="stackead-brand-override">
				:root {
					--primary: <?php echo esc_attr( $settings['primary_color'] ); ?> !important;
					--primary-dark: <?php echo esc_attr( $settings['primary_color'] ); ?> !important;
					--accent: <?php echo esc_attr( $settings['secondary_color'] ); ?> !important;
				}
				.sticky-mobile-bar {
					position: fixed;
					bottom: 0;
					left: 0;
					right: 0;
					background: rgba(255, 255, 255, 0.96);
					backdrop-filter: blur(12px);
					border-top: 1px solid rgba(0, 0, 0, 0.08);
					padding: 12px 20px;
					display: none;
					align-items: center;
					justify-content: space-between;
					z-index: 999;
					box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
				}
				@media (max-width: 768px) {
					.sticky-mobile-bar.visible {
						display: flex;
					}
				}
				.reduced-motion-btn {
					position: fixed;
					bottom: 16px;
					right: 16px;
					background: rgba(15, 23, 42, 0.85);
					color: #fff;
					border: 1px solid rgba(255, 255, 255, 0.2);
					border-radius: 999px;
					padding: 6px 14px;
					font-size: 12px;
					cursor: pointer;
					z-index: 998;
					display: flex;
					align-items: center;
					gap: 6px;
					backdrop-filter: blur(6px);
				}

				/* Template 2: Tech & Future Lab Overrides */
				.theme-tech-future {
					background-color: #07090e !important;
					color: #f8fafc !important;
				}
				.theme-tech-future .landing-header {
					background: rgba(7, 9, 14, 0.95) !important;
					border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
				}
				.theme-tech-future .landing-header a {
					color: #cbd5e1 !important;
				}
				.theme-tech-future .section-metrics {
					background: #0b1120 !important;
					border-color: rgba(255, 255, 255, 0.08) !important;
				}
				.theme-tech-future .section-curriculum,
				.theme-tech-future .section-instructors,
				.theme-tech-future .section-awards,
				.theme-tech-future .section-investment,
				.theme-tech-future .section-faq {
					background: #07090e !important;
					color: #f8fafc !important;
				}
				.theme-tech-future .module-item,
				.theme-tech-future .instructor-card,
				.theme-tech-future .pricing-card,
				.theme-tech-future details {
					background: rgba(15, 23, 42, 0.85) !important;
					border-color: rgba(255, 255, 255, 0.12) !important;
					color: #f8fafc !important;
				}
				.theme-tech-future h1,
				.theme-tech-future h2,
				.theme-tech-future h3,
				.theme-tech-future summary {
					color: #ffffff !important;
				}
				.theme-tech-future p,
				.theme-tech-future .hero-subtitle {
					color: #94a3b8 !important;
				}
			</style>

			<!-- Favicon -->
			<?php if ( ! empty( $settings['favicon_url'] ) ) : ?>
				<link rel="icon" href="<?php echo esc_url( $settings['favicon_url'] ); ?>" sizes="32x32" />
			<?php endif; ?>

			<script>
				window.StackEAD_Landing = {
					restRoot: '<?php echo esc_url_raw( rest_url() ); ?>',
					leadEndpoint: '<?php echo esc_url_raw( rest_url( 'stackead/v1/leads' ) ); ?>',
					nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
					courseId: <?php echo (int) ( $course ? $course->ID : 0 ); ?>,
					whatsappNumber: '<?php echo esc_js( $settings['whatsapp_number'] ); ?>'
				};
			</script>
		</head>
		<body class="landing-course-page <?php echo 'tech_future' === $active_template ? 'theme-tech-future' : 'theme-artinnova-light'; ?>">

			<!-- Accessibility Floating Controller (COGA) -->
			<button id="btn-toggle-motion" class="reduced-motion-btn" title="Pausar ou ativar animações visuais">
				<i data-lucide="eye"></i> <span>Pausar animações</span>
			</button>

			<!-- Header Nav -->
			<header class="landing-header" style="position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0,0,0,0.06); padding: 14px 0;">
				<div class="wrap" style="display: flex; align-items: center; justify-content: space-between; max-width: 1240px; margin: 0 auto; padding: 0 24px;">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
						<?php if ( ! empty( $settings['logo_dark_url'] ) ) : ?>
							<img src="<?php echo esc_url( $settings['logo_dark_url'] ); ?>" alt="<?php echo esc_attr( $settings['institution_name'] ); ?>" style="height: 38px; width: auto; max-width: 180px; object-fit: contain;">
						<?php else : ?>
							<span style="font-size: 22px; font-weight: 800; color: #0f172a;"><?php echo esc_html( $settings['institution_name'] ); ?></span>
						<?php endif; ?>
					</a>

					<nav style="display: flex; align-items: center; gap: 24px;">
						<a href="#experiencia" style="color: #475569; text-decoration: none; font-size: 14px; font-weight: 600;">A Experiência</a>
						<a href="#para-quem" style="color: #475569; text-decoration: none; font-size: 14px; font-weight: 600;">Para Quem</a>
						<a href="#premio" style="color: #475569; text-decoration: none; font-size: 14px; font-weight: 600;">Premiação</a>
						<a href="#investimento" style="color: #475569; text-decoration: none; font-size: 14px; font-weight: 600;">Investimento</a>
						<a href="<?php echo esc_url( home_url( '/plataforma' ) ); ?>" style="color: #4f46e5; font-weight: 700; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 6px;">
							<i data-lucide="log-in" style="width: 16px; height: 16px;"></i> Área do Aluno
						</a>
						<a href="#inscricao" class="btn btn-primary" style="padding: 8px 18px; font-size: 14px; border-radius: 8px;">Garantir Vaga</a>
					</nav>
				</div>
			</header>

			<main id="main-content">
			<?php if ( 'tech_future' === $active_template || $has_custom_blocks ) : ?>
				<?php
				foreach ( $blocks as $block ) {
					echo BlockManager::render_block_html( $block, $active_template, array( 'course' => $course, 'settings' => $settings ) );
				}
				?>
			<?php else : ?>
				<section id="hero" class="hero-section light-hero">
					<canvas id="hero-pixel-canvas" class="hero-pixel-canvas" aria-hidden="true"></canvas>

					<div class="hero-bg">
						<picture>
							<source srcset="<?php echo esc_url( $landing_assets_url . 'assets/images/hero_artinnova_rich_light.webp' ); ?>" type="image/webp">
							<img class="hero-bg-img" fetchpriority="high" src="<?php echo esc_url( $landing_assets_url . 'assets/images/hero_artinnova_rich_light.jpg' ); ?>" alt="Ateliê de artes midiáticas e formação internacional">
						</picture>
						<div class="hero-overlay"></div>
					</div>

					<div class="wrap hero-content">
						<div class="hero-badges-row">
							<div class="hero-floating-badge" id="hero-floating-badge">
								<span class="pulse-dot"></span>
								<span><?php echo esc_html( $course_title ); ?></span>
							</div>
							<div class="hero-sub-badge">
								<span class="badge-star">★</span>
								<span>Super Prêmio: Imersão Internacional na França com Tudo Pago</span>
							</div>
						</div>

						<h1 class="hero-title">
							<span>Sua próxima inovação começa com uma </span>
							<span class="accent-color">ponte.</span>
						</h1>

						<div class="hero-grid">
							<div>
								<p class="hero-lead"><?php echo esc_html( $course_subtitle ); ?></p>
								<div class="hero-actions">
									<a class="btn btn-primary" href="#inscricao">Quero participar &rarr;</a>
									<a class="btn btn-secondary" href="#premio">Conheça o Super Prêmio &darr;</a>
								</div>
								
								<div class="hero-trust-row">
									<div class="trust-item">
										<i data-lucide="shield-check" style="width:20px;height:20px;color:#10b981;"></i>
										<span>Garantia incondicional de 7 dias</span>
									</div>
									<div class="trust-item">
										<i data-lucide="users" style="width:20px;height:20px;color:#6366f1;"></i>
										<span>Turma exclusiva de 60 participantes</span>
									</div>
								</div>
							</div>

							<div class="hero-meta-box">
								<div class="hero-meta-header">
									<div class="hero-meta-title">INSCRIÇÕES ABERTAS · FORMAÇÃO 2026/2027</div>
									<span class="hero-meta-tag">Turma Piloto</span>
								</div>
								<p class="hero-meta-desc">Uma experiência internacional de formação, criação e colaboração entre Campina Grande e Enghien-les-Bains. 60 vagas com ateliês práticos e mentoria direta.</p>
								<div class="hero-meta-price-badge">
									<div class="price-badge-label">Condição de Inscrição</div>
									<div class="price-badge-val">10 &times; R$ 300 no cartão ou à vista com desconto</div>
								</div>
							</div>
						</div>
					</div>
				</section>

				<!-- Metrics Bar -->
				<div class="metrics-bar" role="region" aria-label="Números da Residência">
					<div class="wrap">
						<div class="metrics-grid">
							<div class="metric-item">
								<div class="metric-num-wrap"><span class="metric-number metric-blue">60</span></div>
								<div class="metric-label">vagas internacionais</div>
								<div class="metric-sub">50 Brasil · 10 França</div>
							</div>
							<div class="metric-item">
								<div class="metric-num-wrap"><span class="metric-number metric-green">50</span></div>
								<div class="metric-label">participantes no Brasil</div>
								<div class="metric-sub">40 pagas · 10 sociais</div>
							</div>
							<div class="metric-item">
								<div class="metric-num-wrap"><span class="metric-number metric-yellow">10</span></div>
								<div class="metric-label">participantes na França</div>
								<div class="metric-sub">100% bolsas sociais</div>
							</div>
							<div class="metric-item">
								<div class="metric-num-wrap"><span class="metric-number metric-cyan">4</span></div>
								<div class="metric-label">super prêmios de viagem</div>
								<div class="metric-sub">2 BR para França · 2 FR para Brasil</div>
							</div>
							<div class="metric-item">
								<div class="metric-num-wrap"><span class="metric-number metric-red">1</span></div>
								<div class="metric-label">missão contínua</div>
								<div class="metric-sub">transformar ideias em pontes</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Section 01: Experiência -->
				<section id="experiencia" class="section light-surface">
					<div class="wrap">
						<div class="eyebrow eyebrow-blue">01 — A experiência</div>
						<h2 class="section-title">
							<span>Quatro meses para conectar o que você sabe ao que você ainda pode </span>
							<span class="accent-color">inventar.</span>
						</h2>
						<p class="section-intro">Espaço de formação aplicada, criação de projetos e intercâmbio entre o ecossistema brasileiro de inovação e a cena europeia de Artes Midiáticas.</p>

						<div class="experience-grid">
							<div class="experience-quote-col">
								<div class="editorial-quote-card">
									<div class="quote-text">
										"Não basta dominar ferramentas digitais: é preciso construir projetos com propósito, sensibilidade artística e capacidade de dialogar com redes internacionais."
									</div>
									<div class="quote-author">Diretriz Curatorial · Residência ArtInnova</div>
								</div>

								<div class="experience-media-wrap">
									<picture>
										<source srcset="<?php echo esc_url( $landing_assets_url . 'assets/images/atelier_cocreation.webp' ); ?>" type="image/webp">
										<img loading="lazy" src="<?php echo esc_url( $landing_assets_url . 'assets/images/atelier_cocreation.jpg' ); ?>" alt="Atelier de co-criação internacional de artes midiáticas" class="experience-img">
									</picture>
									<div class="experience-media-caption">Atelier de Co-criação Internacional · Metodologias Interdisciplinares</div>
								</div>
							</div>

							<div class="pillars-col">
								<div class="pillar-card border-accent-blue">
									<div class="pillar-card-header">
										<span class="card-num-badge bg-blue">01</span>
										<h3 class="card-title">Aprender</h3>
									</div>
									<p class="card-desc">Explorar Artes Midiáticas, tecnologias e metodologias de inovação com uma abordagem interdisciplinar e imersiva.</p>
								</div>
								<div class="pillar-card border-accent-green">
									<div class="pillar-card-header">
										<span class="card-num-badge bg-green">02</span>
										<h3 class="card-title">Criar</h3>
									</div>
									<p class="card-desc">Desenvolver uma obra, protótipo ou iniciativa conectada a um desafio que faça sentido para você e seu território.</p>
								</div>
								<div class="pillar-card border-accent-yellow">
									<div class="pillar-card-header">
										<span class="card-num-badge bg-yellow">03</span>
										<h3 class="card-title">Conectar</h3>
									</div>
									<p class="card-desc">Conhecer outros participantes, mestres e redes criativas, ampliando oportunidades de colaboração bilateral Brasil–França.</p>
								</div>
							</div>
						</div>
					</div>
				</section>

				<!-- Section 02: Para Quem -->
				<section id="para-quem" class="section light-bg">
					<div class="wrap two-cols-asym">
						<div>
							<div class="eyebrow eyebrow-green">02 — Para quem</div>
							<h2 class="section-title">
								<span>Para quem não se conforma em apenas </span>
								<span class="accent-color">imaginar o futuro.</span>
							</h2>
							<p class="section-intro">Para pessoas com experiência, curiosidade, sensibilidade artística e tecnológica e vontade real de transformar ideias em iniciativas concretas.</p>
						</div>

						<div class="audience-list">
							<div class="audience-item border-left-blue">
								<span class="audience-item-num num-blue">01</span>
								<span class="audience-item-text">Criadores, artistas, designers e realizadores visuais</span>
							</div>
							<div class="audience-item border-left-green">
								<span class="audience-item-num num-green">02</span>
								<span class="audience-item-text">Empreendedores, tecnólogos e profissionais de inovação</span>
							</div>
							<div class="audience-item border-left-yellow">
								<span class="audience-item-num num-yellow">03</span>
								<span class="audience-item-text">Educadores, pesquisadores e estudantes com vivência profissional</span>
							</div>
							<div class="audience-item border-left-red">
								<span class="audience-item-num num-red">04</span>
								<span class="audience-item-text">Gestores de cultura, tecnologia e desenvolvimento territorial</span>
							</div>
							<div class="audience-item border-left-cyan">
								<span class="audience-item-num num-cyan">05</span>
								<span class="audience-item-text">Profissionais de outras áreas interessados em criar com Artes Midiáticas</span>
							</div>
						</div>
					</div>
				</section>

				<!-- Section 05: Super Premiação Internacional -->
				<section id="premio" class="award-super-section section-dark-pavilion" style="background: #090d16; color: #fff; padding: 80px 0;">
					<div class="wrap">
						<div class="award-header-center" style="text-align: center; max-width: 820px; margin: 0 auto 48px;">
							<div class="award-prestige-badge" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); border-radius: 999px; padding: 6px 16px; font-weight: 700; font-size: 13px; margin-bottom: 20px;">
								<span>🏆</span> Prêmio Internacional de Artes Midiáticas · França & Brasil
							</div>
							<h2 style="font-size: 2.4rem; font-weight: 800; line-height: 1.2; margin-bottom: 20px;">
								Media Arts Innovation Bridges: <span style="background: linear-gradient(90deg, #fbbf24, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">4 Bolsas de Imersão Presencial com Tudo Pago na França e no Brasil</span>
							</h2>
							<p style="color: #94a3b8; font-size: 1.1rem; line-height: 1.6;">
								Os projetos desenvolvidos nos ateliês concorrem a quatro bolsas integrais com passagens aéreas internacionais, alojamento completo, seguro e ateliê técnico no prestigiado Centre des Arts em Enghien-les-Bains (região metropolitana de Paris) e em Campina Grande.
							</p>
						</div>

						<div class="award-heroic-banner" style="border-radius: 16px; overflow: hidden; margin-bottom: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
							<picture>
								<source srcset="<?php echo esc_url( $landing_assets_url . 'assets/images/award_super_banner.webp' ); ?>" type="image/webp">
								<img loading="lazy" src="<?php echo esc_url( $landing_assets_url . 'assets/images/award_super_banner.jpg' ); ?>" alt="Centre des Arts Enghien-les-Bains Paris" style="width: 100%; height: 380px; object-fit: cover; display: block;">
							</picture>
						</div>

						<div class="award-pillars-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
							<div class="award-pillar-card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 24px;">
								<i data-lucide="plane" style="color: #38bdf8; width: 32px; height: 32px; margin-bottom: 12px;"></i>
								<h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 8px;">Passagens & Estadia Pagas</h4>
								<p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.5;">Custeio integral de transporte aéreo internacional, alojamento e seguro para os residentes selecionados.</p>
							</div>
							<div class="award-pillar-card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 24px;">
								<i data-lucide="building-2" style="color: #4ade80; width: 32px; height: 32px; margin-bottom: 12px;"></i>
								<h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 8px;">Centre des Arts (Paris)</h4>
								<p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.5;">Acesso a infraestrutura e ateliês profissionais de criação em artes midiáticas e tecnologias interativas na Europa.</p>
							</div>
							<div class="award-pillar-card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 24px;">
								<i data-lucide="sparkles" style="color: #fbbf24; width: 32px; height: 32px; margin-bottom: 12px;"></i>
								<h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 8px;">Mentoria com Mestres Europeus</h4>
								<p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.5;">Sessões individuais e em equipe com curadores, diretores artísticos e pesquisadores do ecossistema francês.</p>
							</div>
							<div class="award-pillar-card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 24px;">
								<i data-lucide="globe" style="color: #f43f5e; width: 32px; height: 32px; margin-bottom: 12px;"></i>
								<h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 8px;">Visibilidade Internacional</h4>
								<p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.5;">Mostra e difusão do seu projeto em redes culturais internacionais e festivais de artes digitais.</p>
							</div>
						</div>
					</div>
				</section>

				<!-- Section 06: Investimento & Garantia -->
				<section id="investimento" class="section light-bg">
					<div class="wrap">
						<div class="eyebrow eyebrow-blue">06 — Investimento & Segurança</div>
						<h2 class="section-title">
							<span>Uma formação internacional. Um investimento protegido por </span>
							<span class="accent-color">garantias reais.</span>
						</h2>

						<div class="pricing-layout-grid">
							<!-- Matrícula -->
							<div class="card-matricula-brasil">
								<div class="pricing-card-header-banner">
									<span>Matrícula Brasil · Turma 2026/2027</span>
									<span>50 Vagas</span>
								</div>
								<div class="pricing-card-body">
									<div class="price-display">
										<span>10 &times; R$ 300</span>
									</div>
									<div class="price-total">Total: R$ 3.000 · Sem juros no cartão ou com desconto no PIX</div>
									<ul class="pricing-benefits-list">
										<li><span class="check-bullet">✓</span> <span>4 meses de ateliês práticos e formação aplicada</span></li>
										<li><span class="check-bullet">✓</span> <span>Elegibilidade integral às bolsas de imersão presencial na França</span></li>
										<li><span class="check-bullet">✓</span> <span>Acesso direto a mestres, curadores e comunidade internacional</span></li>
										<li><span class="check-bullet">✓</span> <span>Certificação internacional da Residência ArtInnova</span></li>
									</ul>
									<div class="payment-options-box" style="margin-top: 20px; display: flex; flex-direction: column; gap: 8px;">
										<a class="btn btn-primary btn-block" href="#inscricao" style="background: #009ee3; border-color: #009ee3; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px;">
											<span>Pagar com Mercado Pago (Cartão / Pix) &rarr;</span>
										</a>
										<a class="btn btn-secondary btn-block" href="#inscricao" style="background: #ffc439; color: #003087; font-weight: 700; border: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
											<span>Pagar com PayPal (Internacional / Euro / Dólar) &rarr;</span>
										</a>
									</div>
									<div style="margin-top: 14px; font-size: 12px; color: #64748b; text-align: center;">
										✓ Liberação automática de acesso na área do aluno logo após o pagamento.
									</div>
								</div>
							</div>

							<!-- Garantias com Selo Dourado -->
							<div class="card-garantia-transparente">
								<div class="guarantee-seal-header">
									<div class="guarantee-header-text">
										<span class="guarantee-kicker-badge">COMPROMISSO FORMAL ARTINNOVA</span>
										<h3 class="guarantee-main-title">Satisfação Garantida ou Reembolso Integral</h3>
										<p class="guarantee-subtitle">Acreditamos tanto na qualidade dos nossos ateliês que assumimos o compromisso formal com a sua tranquilidade.</p>
									</div>
									<div class="guarantee-seal-media">
										<img src="<?php echo esc_url( $landing_assets_url . 'assets/images/selo-garantia-7-dias.png' ); ?>" alt="Selo Oficial de Garantia 7 Dias" class="seal-gold-img" width="124" height="124">
									</div>
								</div>

								<div class="guarantee-box box-incondicional">
									<div class="guarantee-box-header">
										<span class="badge-guarantee-pill pill-emerald">Garantia 01 · 7 Dias Incondicional</span>
										<span class="guarantee-status-tag">100% Reembolso</span>
									</div>
									<h4 class="guarantee-box-title">Experimente a Primeira Semana sem Risco</h4>
									<p class="guarantee-box-desc">Você tem 7 dias corridos a partir do início para participar dos primeiros encontros. Se por qualquer motivo pessoal ou profissional decidir não continuar, basta nos enviar um e-mail: estornamos 100% do valor pago.</p>
								</div>

								<div class="guarantee-box box-transformacao">
									<div class="guarantee-box-header">
										<span class="badge-guarantee-pill pill-amber">Garantia 02 · Evolução do Conhecimento</span>
										<span class="guarantee-status-tag">Devolução Integral</span>
									</div>
									<h4 class="guarantee-box-title">Compromisso com o Seu Ganho Prático</h4>
									<p class="guarantee-box-desc">Se você comparecer a pelo menos 80% das atividades e comprovar que os conteúdos não agregaram valor para seu percurso artístico, analisamos seu caso para devolução integral da taxa de matrícula.</p>
								</div>
							</div>
						</div>
					</div>
				</section>

				<!-- Section 07: Inscrição / Lead Capture com WhatsApp -->
				<section id="inscricao" class="section light-surface">
					<div class="wrap two-cols-asym">
						<div class="form-intro-col">
							<div class="eyebrow eyebrow-red">07 — Próximo passo</div>
							<h2 class="section-title">
								<span>Entre. Experimente. </span>
								<span class="accent-color">Faça parte.</span>
							</h2>
							<p class="section-intro">Deixe seu contato para receber o edital oficial, cronograma completo das sessões e o acesso prioritário à matrícula com as garantias ativas.</p>

							<div class="form-perks-box">
								<div class="perk-row"><span class="perk-check">✓</span> <span>Acesso prévio ao regulamento e cronograma</span></div>
								<div class="perk-row"><span class="perk-check">✓</span> <span>Prioridade na fila de matrículas antes da abertura pública</span></div>
								<div class="perk-row"><span class="perk-check">✓</span> <span>Proteção com a Dupla Garantia de 7 dias e Transformação</span></div>
							</div>
						</div>

						<div class="form-card">
							<form id="lead-form" novalidate>
								<div class="field">
									<label for="lead_name">Nome completo *</label>
									<input id="lead_name" name="name" type="text" required placeholder="Seu nome completo">
								</div>

								<div class="field">
									<label for="lead_email">E-mail de contato *</label>
									<input id="lead_email" name="email" type="email" required placeholder="seuemail@exemplo.com">
								</div>

								<div class="field">
									<label for="lead_phone">WhatsApp / Telefone *</label>
									<input id="lead_phone" name="phone" type="tel" required placeholder="(99) 99999-9999">
								</div>

								<div class="field">
									<label for="lead_city">Cidade / Estado / País *</label>
									<input id="lead_city" name="city" type="text" required placeholder="Ex: Campina Grande, PB ou Paris, França">
								</div>

								<div class="field">
									<label for="lead_profile">Perfil principal</label>
									<select id="lead_profile" name="profile">
										<option value="">Selecione seu perfil</option>
										<option value="Criador / Artista / Designer">Criador(a) / Artista / Designer</option>
										<option value="Empreendedor / Inovação">Empreendedor(a) / Profissional de Inovação</option>
										<option value="Educador / Pesquisador">Educador(a) / Pesquisador(a)</option>
										<option value="Gestor Cultural">Gestor(a) Cultural ou Tecnológico</option>
										<option value="Outro">Outro perfil interdisciplinar</option>
									</select>
								</div>

								<div class="field field-checkbox">
									<input id="lead_consent" name="consent" type="checkbox" required>
									<label for="lead_consent">Autorizo o uso dos meus dados para contato sobre a formação e concordo com a política de privacidade (LGPD). *</label>
								</div>

								<button class="btn btn-primary" id="btn-submit-lead" type="submit" style="width: 100%;">
									Manifestar Interesse na Turma &rarr;
								</button>

								<div id="form-feedback" style="display:none; margin-top: 16px; padding: 16px; border-radius: 8px; font-size: 14px; text-align: center;"></div>
							</form>
						</div>
					</div>
				</section>

				<!-- Section 08: FAQ -->
				<section id="duvidas" class="section light-bg">
					<div class="wrap">
						<div class="eyebrow eyebrow-green">Dúvidas frequentes</div>
						<h2 class="section-title">
							<span>Antes de construir sua </span>
							<span class="accent-color">ponte.</span>
						</h2>

						<div class="faq-list">
							<div class="faq-item">
								<button class="faq-question">
									<span>Preciso ser artista profissional para participar?</span>
									<span class="faq-icon">+</span>
								</button>
								<div class="faq-answer">
									<p>Não. A residência é voltada para perfis híbridos: criadores, designers, desenvolvedores, pesquisadores e gestores com interesse concreto em criar e colaborar.</p>
								</div>
							</div>
							<div class="faq-item">
								<button class="faq-question">
									<span>Como funciona o Super Prêmio de Viagem Internacional?</span>
									<span class="faq-icon">+</span>
								</button>
								<div class="faq-answer">
									<p>Quatro residentes selecionados (dois do Brasil e dois da França) terão passagens aéreas e hospedagem custeadas para uma imersão de 10 dias no Centre des Arts em Enghien-les-Bains (região de Paris) ou Campina Grande.</p>
								</div>
							</div>
							<div class="faq-item">
								<button class="faq-question">
									<span>Como funciona a Dupla Garantia de 7 dias e de Transformação?</span>
									<span class="faq-icon">+</span>
								</button>
								<div class="faq-answer">
									<p>Você tem 7 dias após o início para desistir e receber 100% do seu dinheiro de volta sem qualquer questionamento. Além disso, se comprovar que a formação não agregou benefícios para você, analisaremos seu caso para reembolso integral.</p>
								</div>
							</div>
						</div>
					</div>
				</section>
			<?php endif; ?>
			</main>

			<!-- Mobile Sticky CTA Bar -->
			<div id="sticky-mobile-bar" class="sticky-mobile-bar">
				<div>
					<div style="font-size: 12px; font-weight: 700; color: #4f46e5; text-transform: uppercase;">Turma 2026/2027</div>
					<div style="font-size: 15px; font-weight: 800; color: #0f172a;">10x de R$ 300</div>
				</div>
				<a href="#inscricao" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px; border-radius: 8px;">Garantir Vaga</a>
			</div>

			<!-- Footer -->
			<footer style="background: #0f172a; color: #94a3b8; padding: 48px 0 32px; font-size: 14px;">
				<div class="wrap" style="max-width: 1240px; margin: 0 auto; padding: 0 24px;">
					<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 24px; padding-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.1);">
						<div>
							<h3 style="color: #fff; font-size: 18px; margin-bottom: 6px;"><?php echo esc_html( $settings['institution_name'] ); ?></h3>
							<p style="margin: 0; font-size: 13px;"><?php echo esc_html( $settings['institution_tagline'] ); ?></p>
						</div>
						<div style="display: flex; gap: 16px;">
							<?php if ( ! empty( $settings['instagram_url'] ) ) : ?>
								<a href="<?php echo esc_url( $settings['instagram_url'] ); ?>" target="_blank" style="color: #94a3b8;"><i data-lucide="instagram"></i></a>
							<?php endif; ?>
							<?php if ( ! empty( $settings['whatsapp_number'] ) ) : ?>
								<a href="https://wa.me/<?php echo esc_attr( $settings['whatsapp_number'] ); ?>" target="_blank" style="color: #94a3b8;"><i data-lucide="phone"></i></a>
							<?php endif; ?>
							<a href="mailto:<?php echo esc_attr( $settings['support_email'] ); ?>" style="color: #94a3b8;"><i data-lucide="mail"></i></a>
						</div>
					</div>
					<div style="margin-top: 24px; text-align: center; font-size: 12px; color: #64748b;">
						&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( $settings['institution_name'] ); ?>. Todos os direitos reservados. Plataforma tecnológica powered by stackEAD Core.
					</div>
				</div>
			</footer>

			<!-- Interactive Scripts -->
			<script>
				document.addEventListener('DOMContentLoaded', () => {
					if (window.lucide) window.lucide.createIcons();

					// FAQ Accordion
					document.querySelectorAll('.faq-question').forEach(btn => {
						btn.addEventListener('click', () => {
							const item = btn.parentElement;
							item.classList.toggle('open');
							const icon = btn.querySelector('.faq-icon');
							if (icon) icon.textContent = item.classList.contains('open') ? '−' : '+';
						});
					});

					// Mobile Sticky Bar
					const stickyBar = document.getElementById('sticky-mobile-bar');
					window.addEventListener('scroll', () => {
						if (window.scrollY > 400) {
							stickyBar.classList.add('visible');
						} else {
							stickyBar.classList.remove('visible');
						}
					});

					// Reduced Motion Toggle (COGA)
					const motionBtn = document.getElementById('btn-toggle-motion');
					let motionPaused = false;
					if (motionBtn) {
						motionBtn.addEventListener('click', () => {
							motionPaused = !motionPaused;
							const canvas = document.getElementById('hero-pixel-canvas');
							if (canvas) canvas.style.display = motionPaused ? 'none' : 'block';
							motionBtn.querySelector('span').textContent = motionPaused ? 'Ativar animações' : 'Pausar animações';
						});
					}

					// Lead Form Submission
					const form = document.getElementById('lead-form');
					const feedback = document.getElementById('form-feedback');
					const submitBtn = document.getElementById('btn-submit-lead');

					if (form) {
						form.addEventListener('submit', async (e) => {
							e.preventDefault();
							feedback.style.display = 'none';

							const name = document.getElementById('lead_name').value.trim();
							const email = document.getElementById('lead_email').value.trim();
							const phone = document.getElementById('lead_phone').value.trim();
							const city = document.getElementById('lead_city').value.trim();
							const profile = document.getElementById('lead_profile').value;
							const consent = document.getElementById('lead_consent').checked;

							if (!name || !email || !phone || !city) {
								feedback.style.display = 'block';
								feedback.style.background = '#fef2f2';
								feedback.style.color = '#dc2626';
								feedback.textContent = 'Por favor, preencha todos os campos obrigatórios (*).';
								return;
							}

							if (!consent) {
								feedback.style.display = 'block';
								feedback.style.background = '#fef2f2';
								feedback.style.color = '#dc2626';
								feedback.textContent = 'É necessário aceitar os termos de consentimento para prosseguir.';
								return;
							}

							submitBtn.disabled = true;
							submitBtn.innerHTML = 'Enviando manifestação...';

							try {
								const res = await fetch(window.StackEAD_Landing.leadEndpoint, {
									method: 'POST',
									headers: {
										'Content-Type': 'application/json',
										'X-WP-Nonce': window.StackEAD_Landing.nonce
									},
									body: JSON.stringify({
										name: name,
										email: email,
										phone: phone,
										city: city,
										profile: profile,
										course_id: window.StackEAD_Landing.courseId
									})
								});

								const data = await res.json();
								if (!res.ok) throw new Error(data.message || 'Erro ao enviar manifestação.');

								feedback.style.display = 'block';
								feedback.style.background = '#f0fdf4';
								feedback.style.color = '#15803d';
								feedback.innerHTML = `
									<strong>🎉 Manifestação recebida com sucesso!</strong>
									<p style="margin: 6px 0 12px;">Entraremos em contato com você via e-mail e WhatsApp com o cronograma detalhado.</p>
									${window.StackEAD_Landing.whatsappNumber ? `
										<a href="https://wa.me/${window.StackEAD_Landing.whatsappNumber}?text=Olá,%20acabei%20de%20me%20inscrever%20na%20turma%20piloto" target="_blank" class="btn btn-primary" style="display:inline-block; padding: 8px 16px; border-radius: 6px; text-decoration:none; font-size:13px;">
											📱 Entrar no Grupo VIP do WhatsApp &rarr;
										</a>
									` : ''}
								`;
								form.reset();
							} catch (err) {
								feedback.style.display = 'block';
								feedback.style.background = '#fef2f2';
								feedback.style.color = '#dc2626';
								feedback.textContent = err.message;
							} finally {
								submitBtn.disabled = false;
								submitBtn.innerHTML = 'Manifestar Interesse na Turma &rarr;';
							}
						});
					}
				});
			</script>
		</body>
		</html>
		<?php
	}
}
