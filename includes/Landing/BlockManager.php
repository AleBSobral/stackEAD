<?php
namespace StackEAD\Landing;

use StackEAD\Admin\WhiteLabelSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BlockManager - Modular Landing Page Block Builder Engine.
 *
 * Provides a lightweight Elementor-style block architecture with 7 drag-and-drop / reorderable
 * blocks and 2 flagship templates: 'artinnova_light' and 'tech_future'.
 */
class BlockManager {

	public const META_BLOCKS   = '_stackead_landing_blocks';
	public const META_TEMPLATE = '_stackead_landing_template';

	public const TEMPLATES = array(
		'artinnova_light' => array(
			'id'          => 'artinnova_light',
			'name'        => 'ArtInnova Light (Editorial & Artes)',
			'description' => 'Design clean, editorial de luxo, ideal para artes, residências e cursos criativos.',
			'theme_mode'  => 'light',
		),
		'tech_future'     => array(
			'id'          => 'tech_future',
			'name'        => 'Tech & Future Lab (Inovação & Código)',
			'description' => 'Design dark cyber, alto contraste, glassmorphism e estética tecnológica.',
			'theme_mode'  => 'dark',
		),
	);

	/**
	 * Available block definitions with default properties.
	 */
	public const BLOCK_SCHEMAS = array(
		'hero'        => array(
			'id'          => 'hero',
			'name'        => 'Hero Header (Abertura)',
			'icon'        => 'layout-template',
			'description' => 'Título principal, chamada de impacto, botão de ação, imagem e badge de urgência.',
		),
		'metrics'     => array(
			'id'          => 'metrics',
			'name'        => 'Barra de Métricas & Autoridade',
			'icon'        => 'bar-chart-2',
			'description' => 'Números de impacto, bolsas concedidas, horas de imersão e alunos impactados.',
		),
		'curriculum'  => array(
			'id'          => 'curriculum',
			'name'        => 'Ementa & Conteúdo Programático',
			'icon'        => 'book-open',
			'description' => 'Módulos de aprendizagem, tópicos detalhados e carga horária.',
		),
		'instructors' => array(
			'id'          => 'instructors',
			'name'        => 'Corpo Docente & Mentores',
			'icon'        => 'users',
			'description' => 'Apresentação dos professores, bios, fotos e credenciais internacionais.',
		),
		'awards'      => array(
			'id'          => 'awards',
			'name'        => 'Super Premiação & Destaques',
			'icon'        => 'award',
			'description' => 'Residência presencial, passagens, hospedagem e benefícios exclusivos.',
		),
		'investment'  => array(
			'id'          => 'investment',
			'name'        => 'Investimento & Dupla Garantia',
			'icon'        => 'shield-check',
			'description' => 'Tabela de preços, opções de parcelamento, selo de garantia de 7 dias e checkout.',
		),
		'faq'         => array(
			'id'          => 'faq',
			'name'        => 'Perguntas Frequentes & Acessibilidade',
			'icon'        => 'help-circle',
			'description' => 'Respostas para dúvidas comuns e compromisso de acessibilidade W3C COGA.',
		),
	);

	/**
	 * Get available templates registry.
	 *
	 * @return array
	 */
	public static function get_available_templates(): array {
		return self::TEMPLATES;
	}

	/**
	 * Get block schemas registry.
	 *
	 * @return array
	 */
	public static function get_block_schemas(): array {
		return self::BLOCK_SCHEMAS;
	}

	/**
	 * Render all active blocks for a course landing page.
	 *
	 * @param int    $course_id
	 * @param string $template
	 * @return string
	 */
	public static function render_course_landing( int $course_id, string $template = '' ): string {
		if ( empty( $template ) ) {
			$template = self::get_course_template( $course_id );
		}
		$blocks = self::get_course_blocks( $course_id );
		$html = '';
		foreach ( $blocks as $block ) {
			if ( ! empty( $block['active'] ) ) {
				$html .= self::render_block_html( $block, $template, array( 'course_id' => $course_id ) );
			}
		}
		return $html;
	}

	/**
	 * Get blocks for a course. If not customized yet, hydrates with template defaults.
	 *
	 * @param int $course_id
	 * @return array
	 */
	public static function get_course_blocks( int $course_id ): array {
		$saved = get_post_meta( $course_id, self::META_BLOCKS, true );
		if ( ! empty( $saved ) && is_array( $saved ) ) {
			return self::sanitize_blocks( $saved );
		}

		$template = self::get_course_template( $course_id );
		return self::get_default_blocks( $course_id, $template );
	}

	/**
	 * Get the active template for a course.
	 *
	 * @param int $course_id
	 * @return string
	 */
	public static function get_course_template( int $course_id ): string {
		$template = get_post_meta( $course_id, self::META_TEMPLATE, true );
		if ( ! empty( $template ) && isset( self::TEMPLATES[ $template ] ) ) {
			return $template;
		}
		return 'artinnova_light';
	}

	/**
	 * Save blocks and template for a course.
	 *
	 * @param int    $course_id
	 * @param array  $blocks
	 * @param string $template
	 * @return array
	 */
	public static function save_course_blocks( int $course_id, array $blocks, string $template = '' ): array {
		$sanitized = self::sanitize_blocks( $blocks );
		update_post_meta( $course_id, self::META_BLOCKS, $sanitized );

		if ( ! empty( $template ) && isset( self::TEMPLATES[ $template ] ) ) {
			update_post_meta( $course_id, self::META_TEMPLATE, $template );
		}

		return array(
			'success'  => true,
			'template' => self::get_course_template( $course_id ),
			'blocks'   => $sanitized,
		);
	}

	/**
	 * Reset blocks for a course to the template default.
	 *
	 * @param int    $course_id
	 * @param string $template
	 * @return array
	 */
	public static function reset_course_blocks( int $course_id, string $template = 'artinnova_light' ): array {
		delete_post_meta( $course_id, self::META_BLOCKS );
		if ( isset( self::TEMPLATES[ $template ] ) ) {
			update_post_meta( $course_id, self::META_TEMPLATE, $template );
		}
		return self::get_course_blocks( $course_id );
	}

	/**
	 * Generate default blocks for a course based on course data.
	 *
	 * @param int    $course_id
	 * @param string $template
	 * @return array
	 */
	public static function get_default_blocks( int $course_id = 0, string $template = 'artinnova_light' ): array {
		$course = $course_id ? get_post( $course_id ) : null;
		$title  = $course ? $course->post_title : 'Residência Internacional ArtInnova · Brasil + França';
		$sub    = $course ? get_post_meta( $course->ID, '_stackead_subtitle', true ) : 'Aprenda Artes Midiáticas. Construa pontes transatlânticas. Transforme criatividade em impacto real.';
		if ( empty( $sub ) ) {
			$sub = 'Formação imersiva em artes midiáticas e tecnologia criativa.';
		}
		$price    = $course ? (float) get_post_meta( $course->ID, '_stackead_price', true ) : 2490.00;
		$workload = $course ? (int) get_post_meta( $course->ID, '_stackead_workload_hours', true ) : 120;
		if ( ! $workload ) {
			$workload = 120;
		}

		$plugin_url = defined( 'STACKEAD_URL' ) ? STACKEAD_URL : plugin_dir_url( dirname( __DIR__ ) );
		$landing_url = $plugin_url . 'templates/landing/artinnova-light/';

		return array(
			array(
				'id'       => 'hero_1',
				'type'     => 'hero',
				'active'   => true,
				'order'    => 1,
				'settings' => array(
					'badge'        => 'Edição Internacional · Brasil + França',
					'headline'     => $title,
					'subtitle'     => $sub,
					'cta_text'     => 'Quero Garantir Minha Vaga',
					'cta_target'   => '#inscricao',
					'image_url'    => $landing_url . 'assets/images/hero_artinnova_rich_light.webp',
					'urgency_text' => 'Inscrições abertas por tempo limitado · Vagas estritamente limitadas',
				),
			),
			array(
				'id'       => 'metrics_1',
				'type'     => 'metrics',
				'active'   => true,
				'order'    => 2,
				'settings' => array(
					'title' => 'Impacto & Reconhecimento Global',
					'items' => array(
						array( 'number' => '85', 'label' => 'Bolsas Concedidas', 'icon' => 'award' ),
						array( 'number' => $workload . 'h', 'label' => 'Horas de Imersão', 'icon' => 'clock' ),
						array( 'number' => '100%', 'label' => 'Online & Acessível COGA', 'icon' => 'globe' ),
						array( 'number' => '+500', 'label' => 'Criativos Conectados', 'icon' => 'users' ),
					),
				),
			),
			array(
				'id'       => 'curriculum_1',
				'type'     => 'curriculum',
				'active'   => true,
				'order'    => 3,
				'settings' => array(
					'title'       => 'Trilha de Formação & Módulos Práticos',
					'description' => 'Uma jornada estruturada do zero à produção artística autoral com acompanhamento docente direto.',
					'modules'     => array(
						array(
							'title'   => 'Módulo 1: Fundamentos de Arte Midiática & Narrativas Contemporâneas',
							'hours'   => '30h',
							'summary' => 'História das artes digitais, poéticas tecnológicas e construção de conceitos.',
						),
						array(
							'title'   => 'Módulo 2: Computação Criativa & Interatividade',
							'hours'   => '40h',
							'summary' => 'Programação visual, sensores, IA generativa para artistas e instalações interativas.',
						),
						array(
							'title'   => 'Módulo 3: Projeto Final & Preparação de Portfólio Internacional',
							'hours'   => '50h',
							'summary' => 'Desenvolvimento orientado da obra autoral e curadoria para festivais na Europa.',
						),
					),
				),
			),
			array(
				'id'       => 'instructors_1',
				'type'     => 'instructors',
				'active'   => true,
				'order'    => 4,
				'settings' => array(
					'title'    => 'Corpo Docente & Mentores Transatlânticos',
					'subtitle' => 'Pesquisadores e artistas premiados atuantes no Brasil, França e Canadá.',
					'items'    => array(
						array(
							'name'      => 'Prof. Dr. Alexandre R.',
							'role'      => 'Coordenador Pedagógico & Artista Midiático',
							'bio'       => 'Doutor em Mídias Digitais com exposições no Brasil e na Europa.',
							'photo_url' => $landing_url . 'assets/images/international_bridge.webp',
						),
						array(
							'name'      => 'Mentoria Francesa · CIIAM Paris',
							'role'      => 'Banca Internacional de Avaliação',
							'bio'       => 'Curadores e especialistas em artes visuais e novas tecnologias em Paris.',
							'photo_url' => $landing_url . 'assets/images/atelier_cocreation.webp',
						),
					),
				),
			),
			array(
				'id'       => 'awards_1',
				'type'     => 'awards',
				'active'   => true,
				'order'    => 5,
				'settings' => array(
					'title'       => 'Super Premiação: Residência Presencial na França',
					'badge'       => 'Exclusivo para os Melhores Projetos',
					'description' => 'Os alunos destaque da turma recebem uma residência presencial com passagens aéreas, estadia em Paris e suporte de produção.',
					'image_url'   => $landing_url . 'assets/images/award_super_banner.webp',
					'benefits'    => array(
						'Passagens aéreas ida e volta Brasil - Paris',
						'Hospedagem e ateliê de criação durante a residência',
						'Exposição oficial na galeria parceira em Paris',
						'Mentoria personalizada com curadores europeus',
					),
				),
			),
			array(
				'id'       => 'investment_1',
				'type'     => 'investment',
				'active'   => true,
				'order'    => 6,
				'settings' => array(
					'title'          => 'Invista no Seu Futuro Criativo',
					'subtitle'       => 'Acesso vitalício à plataforma, materiais, certificado internacional e suporte.',
					'price_display'  => 'R$ ' . number_format( $price > 0 ? $price : 2490, 2, ',', '.' ),
					'installments'   => '12x de R$ ' . number_format( ( $price > 0 ? $price : 2490 ) / 12, 2, ',', '.' ) . ' sem juros',
					'guarantee_days' => 7,
					'guarantee_text' => 'Garantia Incondicional de 7 dias: experimente o curso por uma semana. Se não amar, reembolsamos 100% do valor sem perguntas.',
					'cta_text'       => 'Garantir Vaga na Turma',
				),
			),
			array(
				'id'       => 'faq_1',
				'type'     => 'faq',
				'active'   => true,
				'order'    => 7,
				'settings' => array(
					'title' => 'Perguntas Frequentes & Acessibilidade',
					'items' => array(
						array(
							'question' => 'Preciso de conhecimento prévio em programação ou artes?',
							'answer'   => 'Não! O curso parte dos conceitos fundamentais e possui trilhas adaptadas para iniciantes e profissionais experientes.',
						),
						array(
							'question' => 'Como funciona a acessibilidade para alunos neurodivergentes?',
							'answer'   => 'Nossa plataforma externa foi desenhada sob as diretrizes W3C COGA, incluindo régua de foco para leitura, fontes amigáveis para dislexia, redução de estímulos visuais e suporte pedagógico individualizado.',
						),
						array(
							'question' => 'O certificado é reconhecido internacionalmente?',
							'answer'   => 'Sim, emitido com código QR criptográfico único de validação pública e chancelado pelas instituições parceiras do Brasil e França.',
						),
					),
				),
			),
		);
	}

	/**
	 * Sanitize blocks array.
	 *
	 * @param array $blocks
	 * @return array
	 */
	public static function sanitize_blocks( array $blocks ): array {
		$sanitized = array();

		foreach ( $blocks as $idx => $b ) {
			if ( ! is_array( $b ) || empty( $b['type'] ) ) {
				continue;
			}

			$type = sanitize_key( $b['type'] );
			if ( ! isset( self::BLOCK_SCHEMAS[ $type ] ) ) {
				continue;
			}

			$item = array(
				'id'       => ! empty( $b['id'] ) ? sanitize_key( $b['id'] ) : $type . '_' . ( $idx + 1 ),
				'type'     => $type,
				'active'   => ! isset( $b['active'] ) || (bool) $b['active'],
				'order'    => isset( $b['order'] ) ? (int) $b['order'] : ( $idx + 1 ),
				'settings' => is_array( $b['settings'] ?? null ) ? self::sanitize_block_settings( $type, $b['settings'] ) : array(),
			);

			$sanitized[] = $item;
		}

		// Sort by order
		usort( $sanitized, function ( $a, $b ) {
			return $a['order'] <=> $b['order'];
		} );

		return $sanitized;
	}

	/**
	 * Sanitize settings for specific block types.
	 *
	 * @param string $type
	 * @param array  $settings
	 * @return array
	 */
	protected static function sanitize_block_settings( string $type, array $settings ): array {
		$out = array();

		foreach ( $settings as $k => $v ) {
			if ( is_string( $v ) ) {
				if ( str_contains( $k, 'url' ) || str_contains( $k, 'image' ) ) {
					$out[ $k ] = esc_url_raw( $v );
				} else {
					$out[ $k ] = sanitize_text_field( $v );
				}
			} elseif ( is_numeric( $v ) ) {
				$out[ $k ] = $v;
			} elseif ( is_bool( $v ) ) {
				$out[ $k ] = $v;
			} elseif ( is_array( $v ) ) {
				// Deep sanitize sub arrays (e.g. items, modules, benefits)
				$sub = array();
				foreach ( $v as $sub_k => $sub_v ) {
					if ( is_array( $sub_v ) ) {
						$clean_row = array();
						foreach ( $sub_v as $row_k => $row_val ) {
							$clean_row[ $row_k ] = is_string( $row_val ) ? sanitize_text_field( $row_val ) : $row_val;
						}
						$sub[ $sub_k ] = $clean_row;
					} else {
						$sub[ $sub_k ] = is_string( $sub_v ) ? sanitize_text_field( $sub_v ) : $sub_v;
					}
				}
				$out[ $k ] = $sub;
			}
		}

		return $out;
	}

	/**
	 * Renders an individual block's HTML according to active template.
	 *
	 * @param array  $block
	 * @param string $template
	 * @param array  $context
	 * @return string
	 */
	public static function render_block_html( array $block, string $template = 'artinnova_light', array $context = array() ): string {
		if ( empty( $block['active'] ) ) {
			return '';
		}

		$type = $block['type'];
		$s    = $block['settings'] ?? array();
		$is_dark = ( 'tech_future' === $template );

		ob_start();

		switch ( $type ) {
			case 'hero':
				?>
				<section class="section-hero <?php echo $is_dark ? 'theme-tech-dark' : 'theme-light'; ?>" id="hero">
					<div class="container hero-container" style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 48px; align-items: center; padding: 60px 0;">
						<div class="hero-content">
							<?php if ( ! empty( $s['badge'] ) ) : ?>
								<div class="badge-pill" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(79, 70, 229, 0.1); color: var(--primary); padding: 6px 14px; border-radius: 999px; font-weight: 600; font-size: 0.85rem; margin-bottom: 20px;">
									<i data-lucide="sparkles" style="width: 14px; height: 14px;"></i>
									<span><?php echo esc_html( $s['badge'] ); ?></span>
								</div>
							<?php endif; ?>

							<h1 class="hero-title" style="font-family: 'Space Grotesk', sans-serif; font-size: clamp(2.2rem, 4vw, 3.4rem); font-weight: 800; line-height: 1.15; margin-bottom: 20px; color: var(--text-heading, inherit);">
								<?php echo esc_html( $s['headline'] ?? '' ); ?>
							</h1>

							<p class="hero-subtitle" style="font-size: 1.15rem; line-height: 1.65; color: var(--text-muted, #64748b); margin-bottom: 32px;">
								<?php echo esc_html( $s['subtitle'] ?? '' ); ?>
							</p>

							<div class="hero-cta-group" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
								<a href="<?php echo esc_attr( $s['cta_target'] ?? '#inscricao' ); ?>" class="btn btn-primary btn-hero-cta" style="padding: 16px 32px; font-size: 1.05rem; font-weight: 700; border-radius: 12px; background: var(--primary); color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.35);">
									<span><?php echo esc_html( $s['cta_text'] ?? 'Quero Garantir Minha Vaga' ); ?></span>
									<i data-lucide="arrow-right"></i>
								</a>
							</div>

							<?php if ( ! empty( $s['urgency_text'] ) ) : ?>
								<div class="urgency-note" style="margin-top: 14px; font-size: 0.85rem; color: var(--text-muted, #94a3b8); display: flex; align-items: center; gap: 6px;">
									<i data-lucide="clock" style="width: 14px; height: 14px; color: #f59e0b;"></i>
									<span><?php echo esc_html( $s['urgency_text'] ); ?></span>
								</div>
							<?php endif; ?>
						</div>

						<div class="hero-media">
							<?php if ( ! empty( $s['image_url'] ) ) : ?>
								<div class="hero-media-wrapper" style="position: relative; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
									<img src="<?php echo esc_url( $s['image_url'] ); ?>" alt="<?php echo esc_attr( $s['headline'] ?? '' ); ?>" style="width: 100%; height: auto; display: block; object-fit: cover;">
								</div>
							<?php endif; ?>
						</div>
					</div>
				</section>
				<?php
				break;

			case 'metrics':
				$items = $s['items'] ?? array();
				?>
				<section class="section-metrics" style="padding: 40px 0; border-top: 1px solid rgba(0,0,0,0.06); border-bottom: 1px solid rgba(0,0,0,0.06); background: var(--bg-alt, rgba(0,0,0,0.02));">
					<div class="container">
						<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; text-align: center;">
							<?php foreach ( $items as $it ) : ?>
								<div class="metric-card" style="padding: 16px;">
									<div style="font-family: 'Space Grotesk', sans-serif; font-size: 2.5rem; font-weight: 800; color: var(--primary); margin-bottom: 4px;">
										<?php echo esc_html( $it['number'] ?? '' ); ?>
									</div>
									<div style="font-size: 0.95rem; font-weight: 600; color: var(--text-muted, #475569);">
										<?php echo esc_html( $it['label'] ?? '' ); ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
				<?php
				break;

			case 'curriculum':
				$modules = $s['modules'] ?? array();
				?>
				<section class="section-curriculum" id="ementa" style="padding: 80px 0;">
					<div class="container" style="max-width: 900px; margin: 0 auto;">
						<div style="text-align: center; margin-bottom: 48px;">
							<h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 2.2rem; font-weight: 800; margin-bottom: 12px;">
								<?php echo esc_html( $s['title'] ?? 'Estrutura & Trilha Formativa' ); ?>
							</h2>
							<p style="font-size: 1.05rem; color: var(--text-muted, #64748b);">
								<?php echo esc_html( $s['description'] ?? '' ); ?>
							</p>
						</div>

						<div class="modules-accordion" style="display: flex; flex-direction: column; gap: 16px;">
							<?php foreach ( $modules as $m_idx => $m ) : ?>
								<div class="module-item" style="border: 1px solid var(--border, #e2e8f0); border-radius: 12px; padding: 20px 24px; background: var(--bg-card, #ffffff);">
									<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
										<h3 style="font-size: 1.1rem; font-weight: 700; margin: 0; color: var(--text-heading, #0f172a);">
											<?php echo esc_html( $m['title'] ?? '' ); ?>
										</h3>
										<?php if ( ! empty( $m['hours'] ) ) : ?>
											<span class="badge" style="background: rgba(79, 70, 229, 0.1); color: var(--primary); font-size: 12px; padding: 4px 10px; border-radius: 6px; font-weight: 700;">
												<?php echo esc_html( $m['hours'] ); ?>
											</span>
										<?php endif; ?>
									</div>
									<p style="font-size: 0.95rem; color: var(--text-muted, #64748b); margin: 0; line-height: 1.6;">
										<?php echo esc_html( $m['summary'] ?? '' ); ?>
									</p>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
				<?php
				break;

			case 'instructors':
				$items = $s['items'] ?? array();
				?>
				<section class="section-instructors" id="docentes" style="padding: 80px 0; background: var(--bg-alt, #fafaf9);">
					<div class="container">
						<div style="text-align: center; margin-bottom: 48px;">
							<h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 2.2rem; font-weight: 800; margin-bottom: 12px;">
								<?php echo esc_html( $s['title'] ?? 'Corpo Docente & Mentores' ); ?>
							</h2>
							<p style="font-size: 1.05rem; color: var(--text-muted, #64748b);">
								<?php echo esc_html( $s['subtitle'] ?? '' ); ?>
							</p>
						</div>

						<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px; max-width: 960px; margin: 0 auto;">
							<?php foreach ( $items as $inst ) : ?>
								<div class="instructor-card" style="background: var(--bg-card, #ffffff); border: 1px solid var(--border, #e2e8f0); border-radius: 16px; padding: 28px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
									<?php if ( ! empty( $inst['photo_url'] ) ) : ?>
										<img src="<?php echo esc_url( $inst['photo_url'] ); ?>" alt="<?php echo esc_attr( $inst['name'] ?? '' ); ?>" style="width: 110px; height: 110px; border-radius: 50%; object-fit: cover; margin: 0 auto 16px; border: 3px solid var(--primary);">
									<?php endif; ?>
									<h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 4px;"><?php echo esc_html( $inst['name'] ?? '' ); ?></h3>
									<span style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--primary); margin-bottom: 12px;"><?php echo esc_html( $inst['role'] ?? '' ); ?></span>
									<p style="font-size: 0.9rem; color: var(--text-muted, #64748b); line-height: 1.6; margin: 0;"><?php echo esc_html( $inst['bio'] ?? '' ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
				<?php
				break;

			case 'awards':
				$benefits = $s['benefits'] ?? array();
				?>
				<section class="section-awards" id="premiacao" style="padding: 80px 0;">
					<div class="container" style="max-width: 1040px; margin: 0 auto;">
						<div style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(6, 182, 212, 0.08) 100%); border: 1px solid rgba(79, 70, 229, 0.2); border-radius: 24px; padding: 48px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
							<div>
								<?php if ( ! empty( $s['badge'] ) ) : ?>
									<span class="badge" style="background: var(--primary); color: #fff; padding: 6px 14px; border-radius: 999px; font-weight: 700; font-size: 0.8rem; display: inline-block; margin-bottom: 16px;">
										<?php echo esc_html( $s['badge'] ); ?>
									</span>
								<?php endif; ?>
								<h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 2rem; font-weight: 800; margin-bottom: 16px; line-height: 1.25;">
									<?php echo esc_html( $s['title'] ?? 'Super Premiação Internacional' ); ?>
								</h2>
								<p style="font-size: 1rem; color: var(--text-muted, #475569); line-height: 1.65; margin-bottom: 24px;">
									<?php echo esc_html( $s['description'] ?? '' ); ?>
								</p>
								<ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">
									<?php foreach ( $benefits as $b ) : ?>
										<li style="display: flex; align-items: center; gap: 10px; font-size: 0.95rem; font-weight: 500;">
											<i data-lucide="check-circle" style="width: 18px; height: 18px; color: #10b981; flex-shrink: 0;"></i>
											<span><?php echo esc_html( $b ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
							<div>
								<?php if ( ! empty( $s['image_url'] ) ) : ?>
									<img src="<?php echo esc_url( $s['image_url'] ); ?>" alt="Premiação" style="width: 100%; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); object-fit: cover;">
								<?php endif; ?>
							</div>
						</div>
					</div>
				</section>
				<?php
				break;

			case 'investment':
				$plugin_url = defined( 'STACKEAD_URL' ) ? STACKEAD_URL : plugin_dir_url( dirname( __DIR__ ) );
				?>
				<section class="section-investment" id="inscricao" style="padding: 80px 0; background: var(--bg-alt, #fafaf9);">
					<div class="container" style="max-width: 680px; margin: 0 auto; text-align: center;">
						<h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 2.2rem; font-weight: 800; margin-bottom: 8px;">
							<?php echo esc_html( $s['title'] ?? 'Investimento & Inscrição' ); ?>
						</h2>
						<p style="font-size: 1rem; color: var(--text-muted, #64748b); margin-bottom: 32px;">
							<?php echo esc_html( $s['subtitle'] ?? '' ); ?>
						</p>

						<div class="pricing-card" style="background: var(--bg-card, #ffffff); border: 2px solid var(--primary); border-radius: 20px; padding: 40px 32px; box-shadow: 0 20px 50px rgba(79, 70, 229, 0.12);">
							<div style="font-size: 0.9rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
								Acesso Completo & Formação Integral
							</div>
							<div style="font-family: 'Space Grotesk', sans-serif; font-size: 3rem; font-weight: 800; color: var(--text-heading, #0f172a); margin-bottom: 6px;">
								<?php echo esc_html( $s['price_display'] ?? 'R$ 2.490,00' ); ?>
							</div>
							<div style="font-size: 1rem; color: var(--text-muted, #64748b); margin-bottom: 28px;">
								ou até <?php echo esc_html( $s['installments'] ?? '12x no cartão' ); ?>
							</div>

							<div class="guarantee-box" style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 28px; padding: 14px; background: rgba(16, 185, 129, 0.08); border-radius: 12px; text-align: left;">
								<img src="<?php echo esc_url( $plugin_url . 'templates/landing/artinnova-light/assets/images/selo-garantia-7-dias.png' ); ?>" alt="Garantia" style="width: 50px; height: 50px; flex-shrink: 0; object-fit: contain;">
								<div style="font-size: 0.85rem; color: #065f46; line-height: 1.45;">
									<strong>Dupla Garantia Incondicional de <?php echo (int) ( $s['guarantee_days'] ?? 7 ); ?> Dias:</strong>
									<div><?php echo esc_html( $s['guarantee_text'] ?? 'Se não ficar satisfeito, devolvemos 100% do seu investimento.' ); ?></div>
								</div>
							</div>

							<?php
							$gateways = $s['payment_gateways'] ?? array();
							if ( ! empty( $gateways ) ) : ?>
								<div class="payment-gateways-container" style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
									<div style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
										Escolha sua Forma de Pagamento
									</div>
									<?php foreach ( $gateways as $gw ) : ?>
										<?php if ( ! empty( $gw['code'] ) ) : ?>
											<div class="gateway-embed-wrapper" style="border-radius: 8px; overflow: hidden; margin-top: 4px;">
												<?php echo $gw['code']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											</div>
										<?php else : ?>
											<a href="<?php echo esc_url( $gw['url'] ?? '#manifestacao' ); ?>" class="gateway-btn btn-<?php echo esc_attr( $gw['provider'] ?? 'primary' ); ?>" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 14px 20px; border-radius: 10px; font-weight: 700; text-decoration: none;" target="_blank">
												<span><?php echo esc_html( $gw['label'] ?? 'Pagar com ' . ucfirst( $gw['provider'] ?? 'Cartão' ) ); ?></span>
											</a>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>
							<?php else : ?>
								<a href="#manifestacao" class="btn btn-primary" style="display: block; width: 100%; padding: 18px; font-size: 1.1rem; font-weight: 800; border-radius: 12px; background: var(--primary); color: #fff; text-decoration: none; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.35);">
									<?php echo esc_html( $s['cta_text'] ?? 'Quero Garantir Minha Vaga' ); ?> &rarr;
								</a>
							<?php endif; ?>

							<div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed var(--border, #e2e8f0); font-size: 0.85rem; color: var(--text-muted, #64748b);">
								<i data-lucide="shield-check" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; color: #10b981;"></i>
								Acesso liberado automaticamente e matrícula confirmada após aprovação do pagamento.
							</div>
						</div>
					</div>
				</section>
				<?php
				break;

			case 'faq':
				$items = $s['items'] ?? array();
				?>
				<section class="section-faq" id="faq" style="padding: 80px 0;">
					<div class="container" style="max-width: 800px; margin: 0 auto;">
						<div style="text-align: center; margin-bottom: 40px;">
							<h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 2.2rem; font-weight: 800; margin-bottom: 8px;">
								<?php echo esc_html( $s['title'] ?? 'Dúvidas Frequentes' ); ?>
							</h2>
						</div>

						<div class="faq-list" style="display: flex; flex-direction: column; gap: 14px;">
							<?php foreach ( $items as $f ) : ?>
								<details style="border: 1px solid var(--border, #e2e8f0); border-radius: 12px; padding: 18px 22px; background: var(--bg-card, #ffffff); cursor: pointer;">
									<summary style="font-weight: 700; font-size: 1.05rem; color: var(--text-heading, #0f172a); outline: none;">
										<?php echo esc_html( $f['question'] ?? '' ); ?>
									</summary>
									<p style="margin-top: 12px; font-size: 0.95rem; color: var(--text-muted, #64748b); line-height: 1.6; margin-bottom: 0;">
										<?php echo esc_html( $f['answer'] ?? '' ); ?>
									</p>
								</details>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
				<?php
				break;
		}

		return ob_get_clean();
	}
}
