<?php
namespace StackEAD\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles professional WP-Admin Meta Boxes for Courses, Cohorts, Lessons, Sessions and Assignments.
 * Provides rich tabs: Overview & Ementa, Instructors, Certification Rules, Curriculum, Pricing, and W3C COGA.
 */
class AdminMetaBoxes {

	/**
	 * Initialize meta boxes hooks.
	 */
	public static function init(): void {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_styles' ) );
	}

	/**
	 * Register meta boxes for EAD post types.
	 */
	public static function register_meta_boxes(): void {
		// 1. Course Meta Box
		add_meta_box(
			'stackead_course_settings',
			__( '⚙️ Configurações & Estrutura do Curso (stackEAD Pro)', 'stackead-core' ),
			array( __CLASS__, 'render_course_meta_box' ),
			'stackead_course',
			'normal',
			'high'
		);

		// 2. Cohort Meta Box
		add_meta_box(
			'stackead_cohort_settings',
			__( '👥 Configurações da Turma / Coorte', 'stackead-core' ),
			array( __CLASS__, 'render_cohort_meta_box' ),
			'stackead_cohort',
			'normal',
			'high'
		);

		// 3. Lesson Meta Box
		add_meta_box(
			'stackead_lesson_settings',
			__( '📖 Conteúdo Multimídia & Acessibilidade COGA da Aula', 'stackead-core' ),
			array( __CLASS__, 'render_lesson_meta_box' ),
			'stackead_lesson',
			'normal',
			'high'
		);

		// 4. Session Meta Box
		add_meta_box(
			'stackead_session_settings',
			__( '🔴 Configurações da Sessão Ao Vivo / Meet', 'stackead-core' ),
			array( __CLASS__, 'render_session_meta_box' ),
			'stackead_session',
			'normal',
			'high'
		);

		// 5. Assignment Meta Box
		add_meta_box(
			'stackead_assignment_settings',
			__( '📝 Parâmetros de Avaliação & Rúbricas da Atividade', 'stackead-core' ),
			array( __CLASS__, 'render_assignment_meta_box' ),
			'stackead_assignment',
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue admin styling for tabs and fields.
	 */
	public static function enqueue_admin_styles( $hook ): void {
		global $post_type;
		$supported = array( 'stackead_course', 'stackead_cohort', 'stackead_lesson', 'stackead_session', 'stackead_assignment' );
		if ( ! in_array( $post_type, $supported, true ) ) {
			return;
		}

		wp_add_inline_style(
			'wp-admin',
			'
			.stackead-meta-container { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; margin-top: 10px; }
			.stackead-tabs-nav { display: flex; border-bottom: 2px solid #e2e8f0; gap: 4px; margin-bottom: 24px; background: #f8fafc; padding: 6px 6px 0; border-radius: 8px 8px 0 0; }
			.stackead-tab-btn { background: none; border: none; padding: 12px 18px; font-size: 13px; font-weight: 600; color: #64748b; cursor: pointer; border-radius: 6px 6px 0 0; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease; }
			.stackead-tab-btn:hover { color: #1e293b; background: #e2e8f0; }
			.stackead-tab-btn.active { color: #4338ca; background: #ffffff; border-bottom: 2px solid #4338ca; font-weight: 700; box-shadow: 0 -2px 6px rgba(0,0,0,0.03); }
			.stackead-tab-pane { display: none; padding: 6px 12px 20px; }
			.stackead-tab-pane.active { display: block; animation: stackeadFade 0.2s ease-in-out; }
			@keyframes stackeadFade { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
			.stackead-form-group { margin-bottom: 20px; }
			.stackead-form-group label { display: block; font-weight: 600; font-size: 13px; color: #1e293b; margin-bottom: 6px; }
			.stackead-form-group .description { font-size: 12px; color: #64748b; margin-top: 4px; }
			.stackead-form-group input[type="text"], .stackead-form-group input[type="number"], .stackead-form-group select, .stackead-form-group textarea { width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 14px; background: #fff; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04); }
			.stackead-form-group input:focus, .stackead-form-group select:focus, .stackead-form-group textarea:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 2px rgba(99,102,241,0.2); }
			.stackead-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
			.stackead-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
			.stackead-badge-tag { display: inline-block; background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
			.stackead-coga-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 14px 18px; border-radius: 4px; margin-bottom: 20px; }
			.stackead-coga-box h4 { margin: 0 0 6px; font-size: 14px; color: #1e3a8a; }
			.stackead-coga-box p { margin: 0; font-size: 12px; color: #3b82f6; }
			'
		);

		wp_add_inline_script(
			'wp-admin',
			'
			document.addEventListener("DOMContentLoaded", function() {
				document.querySelectorAll(".stackead-tab-btn").forEach(function(btn) {
					btn.addEventListener("click", function(e) {
						e.preventDefault();
						var tabId = this.getAttribute("data-tab");
						var container = this.closest(".stackead-meta-container");
						container.querySelectorAll(".stackead-tab-btn").forEach(function(b) { b.classList.remove("active"); });
						container.querySelectorAll(".stackead-tab-pane").forEach(function(p) { p.classList.remove("active"); });
						this.classList.add("active");
						var pane = container.querySelector("#" + tabId);
						if (pane) pane.classList.add("active");
					});
				});
			});
			'
		);
	}

	/**
	 * Renders Course Meta Box.
	 */
	public static function render_course_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'stackead_save_course_meta', 'stackead_course_meta_nonce' );

		$subtitle            = get_post_meta( $post->ID, '_stackead_subtitle', true );
		$workload            = get_post_meta( $post->ID, '_stackead_workload_hours', true );
		$price_type          = get_post_meta( $post->ID, '_stackead_price_type', true );
		$price               = get_post_meta( $post->ID, '_stackead_price', true );
		$sale_price          = get_post_meta( $post->ID, '_stackead_sale_price', true );
		$syllabus            = get_post_meta( $post->ID, '_stackead_syllabus', true );
		$learning_goals      = get_post_meta( $post->ID, '_stackead_learning_goals', true );
		$prerequisites       = get_post_meta( $post->ID, '_stackead_prerequisites', true );
		$path_id             = get_post_meta( $post->ID, '_stackead_path_id', true );
		$instructor_ids      = get_post_meta( $post->ID, '_stackead_instructor_ids', true );
		$min_attendance      = get_post_meta( $post->ID, '_stackead_min_attendance', true );
		$min_quiz            = get_post_meta( $post->ID, '_stackead_min_quiz', true );
		$simplified_summary  = get_post_meta( $post->ID, '_stackead_simplified_summary', true );
		$default_extra_time  = get_post_meta( $post->ID, '_stackead_default_extra_time', true );

		if ( empty( $workload ) ) {
			$workload = 40;
		}
		if ( empty( $price_type ) ) {
			$price_type = 'free';
		}
		if ( empty( $min_attendance ) ) {
			$min_attendance = 75;
		}
		if ( empty( $min_quiz ) ) {
			$min_quiz = 70;
		}

		$instructors = get_users(
			array(
				'role__in' => array( 'administrator', 'stackead_master', 'stackead_coordinator' ),
				'orderby'  => 'display_name',
			)
		);

		$paths = get_posts(
			array(
				'post_type'      => 'stackead_path',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$assigned_instructors = is_array( $instructor_ids ) ? $instructor_ids : ( $instructor_ids ? explode( ',', (string) $instructor_ids ) : array() );
		?>
		<div class="stackead-meta-container">
			<!-- Tabs Navigation -->
			<div class="stackead-tabs-nav">
				<button class="stackead-tab-btn active" data-tab="tab-course-overview">
					📋 <?php esc_html_e( 'Visão Geral & Ementa', 'stackead-core' ); ?>
				</button>
				<button class="stackead-tab-btn" data-tab="tab-course-instructors">
					👨‍🏫 <?php esc_html_e( 'Corpo Docente / Professores', 'stackead-core' ); ?>
				</button>
				<button class="stackead-tab-btn" data-tab="tab-course-curriculum">
					📚 <?php esc_html_e( 'Estrutura Curricular & Trilha', 'stackead-core' ); ?>
				</button>
				<button class="stackead-tab-btn" data-tab="tab-course-certification">
					⏱️ <?php esc_html_e( 'Carga Horária & Certificado', 'stackead-core' ); ?>
				</button>
				<button class="stackead-tab-btn" data-tab="tab-course-pricing">
					💰 <?php esc_html_e( 'Preço & Matrícula', 'stackead-core' ); ?>
				</button>
				<button class="stackead-tab-btn" data-tab="tab-course-coga">
					♿ <?php esc_html_e( 'Acessibilidade COGA', 'stackead-core' ); ?>
				</button>
			</div>

			<!-- Tab 1: Overview & Ementa -->
			<div id="tab-course-overview" class="stackead-tab-pane active">
				<div class="stackead-form-group">
					<label for="stackead_subtitle"><?php esc_html_e( 'Subtítulo / Chamada de Destaque', 'stackead-core' ); ?></label>
					<input type="text" id="stackead_subtitle" name="stackead_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" placeholder="<?php esc_attr_e( 'Ex.: Formação imersiva em novas mídias e curadoria de arte digital', 'stackead-core' ); ?>">
					<p class="description"><?php esc_html_e( 'Frase de impacto exibida no topo do curso e no catálogo.', 'stackead-core' ); ?></p>
				</div>

				<div class="stackead-form-group">
					<label for="stackead_syllabus"><?php esc_html_e( 'Ementa Completa / Conteúdo Programático', 'stackead-core' ); ?></label>
					<textarea id="stackead_syllabus" name="stackead_syllabus" rows="6" placeholder="<?php esc_attr_e( 'Descreva os módulos, os temas abordados e a sequência didática deste curso...', 'stackead-core' ); ?>"><?php echo esc_textarea( $syllabus ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Ementa curricular detalhada para orientação do estudante.', 'stackead-core' ); ?></p>
				</div>

				<div class="stackead-grid-2">
					<div class="stackead-form-group">
						<label for="stackead_learning_goals"><?php esc_html_e( 'Objetivos de Aprendizagem (O que o aluno aprenderá)', 'stackead-core' ); ?></label>
						<textarea id="stackead_learning_goals" name="stackead_learning_goals" rows="4" placeholder="<?php esc_attr_e( '- Dominar conceitos de arte generativa&#10;- Criar projetos imersivos 3D&#10;- Elaborar portfólio profissional', 'stackead-core' ); ?>"><?php echo esc_textarea( $learning_goals ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Insira um objetivo por linha.', 'stackead-core' ); ?></p>
					</div>
					<div class="stackead-form-group">
						<label for="stackead_prerequisites"><?php esc_html_e( 'Pré-requisitos e Requisitos Técnicos', 'stackead-core' ); ?></label>
						<textarea id="stackead_prerequisites" name="stackead_prerequisites" rows="4" placeholder="<?php esc_attr_e( 'Conhecimentos básicos de artes visuais, computador com navegador moderno e acesso à internet.', 'stackead-core' ); ?>"><?php echo esc_textarea( $prerequisites ); ?></textarea>
						<p class="description"><?php esc_html_e( 'O que o aluno precisa saber ou ter antes de iniciar.', 'stackead-core' ); ?></p>
					</div>
				</div>
			</div>

			<!-- Tab 2: Instructors -->
			<div id="tab-course-instructors" class="stackead-tab-pane">
				<div class="stackead-form-group">
					<label><?php esc_html_e( 'Professores / Facilitadores Responsáveis (Mestres)', 'stackead-core' ); ?></label>
					<p class="description"><?php esc_html_e( 'Selecione os professores responsáveis por conduzir este curso e suas turmas.', 'stackead-core' ); ?></p>
					<div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; max-height: 200px; overflow-y: auto; margin-top: 8px;">
						<?php if ( ! empty( $instructors ) ) : ?>
							<?php foreach ( $instructors as $inst ) : ?>
								<?php $checked = in_array( (string) $inst->ID, $assigned_instructors, true ) ? 'checked' : ''; ?>
								<label style="display: flex; align-items: center; gap: 8px; font-weight: normal; margin-bottom: 6px; cursor: pointer;">
									<input type="checkbox" name="stackead_instructor_ids[]" value="<?php echo esc_attr( $inst->ID ); ?>" <?php echo $checked; ?>>
									<strong><?php echo esc_html( $inst->display_name ); ?></strong>
									<span style="color: #64748b; font-size: 11px;">(<?php echo esc_html( $inst->user_email ); ?> - <?php echo esc_html( implode( ', ', $inst->roles ) ); ?>)</span>
								</label>
							<?php endforeach; ?>
						<?php else : ?>
							<p style="color: #64748b; margin: 0;"><?php esc_html_e( 'Nenhum usuário com papel de professor ou mestre encontrado.', 'stackead-core' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Tab 3: Curriculum Structure -->
			<div id="tab-course-curriculum" class="stackead-tab-pane">
				<div class="stackead-form-group">
					<label for="stackead_path_id"><?php esc_html_e( 'Trilha Didática Vinculada', 'stackead-core' ); ?></label>
					<select id="stackead_path_id" name="stackead_path_id">
						<option value=""><?php esc_html_e( '-- Selecione uma Trilha Didática --', 'stackead-core' ); ?></option>
						<?php foreach ( $paths as $p ) : ?>
							<option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( $path_id, $p->ID ); ?>>
								<?php echo esc_html( $p->post_title ); ?> (ID: <?php echo esc_html( $p->ID ); ?>)
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php esc_html_e( 'A trilha didática contém a árvore de Módulos e Aulas deste curso.', 'stackead-core' ); ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=stackead_path' ) ); ?>" target="_blank"><?php esc_html_e( 'Criar nova trilha &rarr;', 'stackead-core' ); ?></a>
					</p>
				</div>
			</div>

			<!-- Tab 4: Certification -->
			<div id="tab-course-certification" class="stackead-tab-pane">
				<div class="stackead-grid-3">
					<div class="stackead-form-group">
						<label for="stackead_workload_hours"><?php esc_html_e( 'Carga Horária Total (Horas)', 'stackead-core' ); ?></label>
						<input type="number" id="stackead_workload_hours" name="stackead_workload_hours" value="<?php echo esc_attr( $workload ); ?>" min="1" max="1000">
						<p class="description"><?php esc_html_e( 'Carga horária expressa no certificado emitido.', 'stackead-core' ); ?></p>
					</div>
					<div class="stackead-form-group">
						<label for="stackead_min_attendance"><?php esc_html_e( 'Frequência Mínima ao Vivo (%)', 'stackead-core' ); ?></label>
						<input type="number" id="stackead_min_attendance" name="stackead_min_attendance" value="<?php echo esc_attr( $min_attendance ); ?>" min="0" max="100">
						<p class="description"><?php esc_html_e( 'Ex.: 75% de presença nas sessões síncronas.', 'stackead-core' ); ?></p>
					</div>
					<div class="stackead-form-group">
						<label for="stackead_min_quiz"><?php esc_html_e( 'Média Mínima nos Quizzes (%)', 'stackead-core' ); ?></label>
						<input type="number" id="stackead_min_quiz" name="stackead_min_quiz" value="<?php echo esc_attr( $min_quiz ); ?>" min="0" max="100">
						<p class="description"><?php esc_html_e( 'Ex.: 70% de aproveitamento nos testes.', 'stackead-core' ); ?></p>
					</div>
				</div>
			</div>

			<!-- Tab 5: Pricing -->
			<div id="tab-course-pricing" class="stackead-tab-pane">
				<div class="stackead-grid-3">
					<div class="stackead-form-group">
						<label for="stackead_price_type"><?php esc_html_e( 'Tipo de Cobrança', 'stackead-core' ); ?></label>
						<select id="stackead_price_type" name="stackead_price_type">
							<option value="free" <?php selected( $price_type, 'free' ); ?>><?php esc_html_e( 'Gratuito', 'stackead-core' ); ?></option>
							<option value="paid" <?php selected( $price_type, 'paid' ); ?>><?php esc_html_e( 'Pagamento Único (R$)', 'stackead-core' ); ?></option>
							<option value="subscription" <?php selected( $price_type, 'subscription' ); ?>><?php esc_html_e( 'Assinatura / Plano Mensal', 'stackead-core' ); ?></option>
						</select>
					</div>
					<div class="stackead-form-group">
						<label for="stackead_price"><?php esc_html_e( 'Preço Regular (R$)', 'stackead-core' ); ?></label>
						<input type="number" step="0.01" id="stackead_price" name="stackead_price" value="<?php echo esc_attr( $price ); ?>" placeholder="0.00">
					</div>
					<div class="stackead-form-group">
						<label for="stackead_sale_price"><?php esc_html_e( 'Preço Promocional (R$)', 'stackead-core' ); ?></label>
						<input type="number" step="0.01" id="stackead_sale_price" name="stackead_sale_price" value="<?php echo esc_attr( $sale_price ); ?>" placeholder="0.00">
					</div>
				</div>
			</div>

			<!-- Tab 6: W3C COGA Accessibility -->
			<div id="tab-course-coga" class="stackead-tab-pane">
				<div class="stackead-coga-box">
					<h4>♿ <?php esc_html_e( 'Diretrizes W3C COGA para Neurodivergentes', 'stackead-core' ); ?></h4>
					<p><?php esc_html_e( 'Forneça alternativas inclusivas para alunos com TDAH, dislexia, ansiedade e sobrecarga cognitiva.', 'stackead-core' ); ?></p>
				</div>
				<div class="stackead-form-group">
					<label for="stackead_simplified_summary"><?php esc_html_e( 'Resumo em Linguagem Simples (Plain Language)', 'stackead-core' ); ?></label>
					<textarea id="stackead_simplified_summary" name="stackead_simplified_summary" rows="3" placeholder="<?php esc_attr_e( 'Texto direto, sem jargões complexos, explicando o que é o curso em frases curtas...', 'stackead-core' ); ?>"><?php echo esc_textarea( $simplified_summary ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Será exibido quando o aluno ativar a chave de "Linguagem Simples" no dock de acessibilidade.', 'stackead-core' ); ?></p>
				</div>
				<div class="stackead-form-group">
					<label style="display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer;">
						<input type="checkbox" name="stackead_default_extra_time" value="yes" <?php checked( $default_extra_time, 'yes' ); ?>>
						<strong><?php esc_html_e( 'Habilitar tempo estendido em quizzes por padrão para este curso', 'stackead-core' ); ?></strong>
					</label>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders Cohort Meta Box.
	 */
	public static function render_cohort_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'stackead_save_cohort_meta', 'stackead_cohort_meta_nonce' );

		$course_id     = get_post_meta( $post->ID, '_stackead_course_id', true );
		$path_id       = get_post_meta( $post->ID, '_stackead_path_id', true );
		$start_date    = get_post_meta( $post->ID, '_stackead_start_date', true );
		$end_date      = get_post_meta( $post->ID, '_stackead_end_date', true );
		$max_students  = get_post_meta( $post->ID, '_stackead_max_students', true );
		$community_url = get_post_meta( $post->ID, '_stackead_community_url', true );
		$master_ids    = get_post_meta( $post->ID, '_stackead_master_ids', true );

		$courses = get_posts(
			array(
				'post_type'      => 'stackead_course',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$paths = get_posts(
			array(
				'post_type'      => 'stackead_path',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$instructors = get_users(
			array(
				'role__in' => array( 'administrator', 'stackead_master', 'stackead_coordinator' ),
				'orderby'  => 'display_name',
			)
		);

		$assigned_masters = is_array( $master_ids ) ? $master_ids : ( $master_ids ? explode( ',', (string) $master_ids ) : array() );
		?>
		<div class="stackead-meta-container">
			<div class="stackead-grid-2">
				<div class="stackead-form-group">
					<label for="stackead_course_id"><?php esc_html_e( 'Curso / Oferta Mãe', 'stackead-core' ); ?></label>
					<select id="stackead_course_id" name="stackead_course_id">
						<option value=""><?php esc_html_e( '-- Selecione o Curso --', 'stackead-core' ); ?></option>
						<?php foreach ( $courses as $c ) : ?>
							<option value="<?php echo esc_attr( $c->ID ); ?>" <?php selected( $course_id, $c->ID ); ?>>
								<?php echo esc_html( $c->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="stackead-form-group">
					<label for="stackead_path_id"><?php esc_html_e( 'Trilha Didática da Turma', 'stackead-core' ); ?></label>
					<select id="stackead_path_id" name="stackead_path_id">
						<option value=""><?php esc_html_e( '-- Selecione a Trilha --', 'stackead-core' ); ?></option>
						<?php foreach ( $paths as $p ) : ?>
							<option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( $path_id, $p->ID ); ?>>
								<?php echo esc_html( $p->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="stackead-grid-3">
				<div class="stackead-form-group">
					<label for="stackead_start_date"><?php esc_html_e( 'Data de Início', 'stackead-core' ); ?></label>
					<input type="date" id="stackead_start_date" name="stackead_start_date" value="<?php echo esc_attr( $start_date ); ?>">
				</div>
				<div class="stackead-form-group">
					<label for="stackead_end_date"><?php esc_html_e( 'Data de Término', 'stackead-core' ); ?></label>
					<input type="date" id="stackead_end_date" name="stackead_end_date" value="<?php echo esc_attr( $end_date ); ?>">
				</div>
				<div class="stackead-form-group">
					<label for="stackead_max_students"><?php esc_html_e( 'Capacidade Máxima de Alunos', 'stackead-core' ); ?></label>
					<input type="number" id="stackead_max_students" name="stackead_max_students" value="<?php echo esc_attr( $max_students ); ?>" placeholder="50">
				</div>
			</div>

			<div class="stackead-form-group">
				<label><?php esc_html_e( 'Facilitadores Atribuídos (Mestres desta Turma)', 'stackead-core' ); ?></label>
				<p class="description"><?php esc_html_e( 'Os mestres selecionados terão acesso restrito exclusivamente ao diário e submissões desta turma.', 'stackead-core' ); ?></p>
				<div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; max-height: 140px; overflow-y: auto;">
					<?php foreach ( $instructors as $inst ) : ?>
						<?php $checked = in_array( (string) $inst->ID, $assigned_masters, true ) ? 'checked' : ''; ?>
						<label style="display: flex; align-items: center; gap: 8px; font-weight: normal; margin-bottom: 4px; cursor: pointer;">
							<input type="checkbox" name="stackead_master_ids[]" value="<?php echo esc_attr( $inst->ID ); ?>" <?php echo $checked; ?>>
							<?php echo esc_html( $inst->display_name ); ?> (<?php echo esc_html( $inst->user_email ); ?>)
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="stackead-form-group">
				<label for="stackead_community_url"><?php esc_html_e( 'Link de Comunidade Externa (WhatsApp, Discord ou Telegram)', 'stackead-core' ); ?></label>
				<input type="text" id="stackead_community_url" name="stackead_community_url" value="<?php echo esc_attr( $community_url ); ?>" placeholder="https://chat.whatsapp.com/...">
			</div>
		</div>
		<?php
	}

	/**
	 * Renders Lesson Meta Box.
	 */
	public static function render_lesson_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'stackead_save_lesson_meta', 'stackead_lesson_meta_nonce' );

		$video_url           = get_post_meta( $post->ID, '_stackead_video_url', true );
		$audio_url           = get_post_meta( $post->ID, '_stackead_audio_url', true );
		$duration            = get_post_meta( $post->ID, '_stackead_duration_minutes', true );
		$simplified_text     = get_post_meta( $post->ID, '_stackead_simplified_text', true );
		$transcription       = get_post_meta( $post->ID, '_stackead_transcription', true );
		$sequential_lock     = get_post_meta( $post->ID, '_stackead_sequential_lock', true );
		$prereq_id           = get_post_meta( $post->ID, '_stackead_prerequisite_lesson_id', true );
		$assignment_id       = get_post_meta( $post->ID, '_stackead_assignment_id', true );
		$quiz_id             = get_post_meta( $post->ID, '_stackead_quiz_id', true );

		$assignments = get_posts( array( 'post_type' => 'stackead_assignment', 'posts_per_page' => -1 ) );
		?>
		<div class="stackead-meta-container">
			<div class="stackead-grid-3">
				<div class="stackead-form-group">
					<label for="stackead_video_url"><?php esc_html_e( 'URL do Vídeo (YouTube, Vimeo, MP4)', 'stackead-core' ); ?></label>
					<input type="text" id="stackead_video_url" name="stackead_video_url" value="<?php echo esc_attr( $video_url ); ?>" placeholder="https://www.youtube.com/watch?v=...">
				</div>
				<div class="stackead-form-group">
					<label for="stackead_audio_url"><?php esc_html_e( 'URL do Áudio / Podcast (Opcional)', 'stackead-core' ); ?></label>
					<input type="text" id="stackead_audio_url" name="stackead_audio_url" value="<?php echo esc_attr( $audio_url ); ?>" placeholder="https://.../aula.mp3">
				</div>
				<div class="stackead-form-group">
					<label for="stackead_duration_minutes"><?php esc_html_e( 'Duração Estimada (Minutos)', 'stackead-core' ); ?></label>
					<input type="number" id="stackead_duration_minutes" name="stackead_duration_minutes" value="<?php echo esc_attr( $duration ); ?>" placeholder="15">
				</div>
			</div>

			<div class="stackead-form-group">
				<label for="stackead_simplified_text">♿ <?php esc_html_e( 'Texto em Linguagem Simplificada (W3C COGA Dual Presentation)', 'stackead-core' ); ?></label>
				<textarea id="stackead_simplified_text" name="stackead_simplified_text" rows="4" placeholder="<?php esc_attr_e( 'Versão acessível e resumida do conteúdo desta aula, com vocabulário direto e frases curtas...', 'stackead-core' ); ?>"><?php echo esc_textarea( $simplified_text ); ?></textarea>
			</div>

			<div class="stackead-form-group">
				<label for="stackead_transcription"><?php esc_html_e( 'Transcrição Integral do Vídeo / Áudio', 'stackead-core' ); ?></label>
				<textarea id="stackead_transcription" name="stackead_transcription" rows="4" placeholder="<?php esc_attr_e( 'Texto transcrito para deficientes auditivos e leitura rápida...', 'stackead-core' ); ?>"><?php echo esc_textarea( $transcription ); ?></textarea>
			</div>

			<div class="stackead-grid-2">
				<div class="stackead-form-group">
					<label for="stackead_assignment_id"><?php esc_html_e( 'Atividade Prática Vinculada', 'stackead-core' ); ?></label>
					<select id="stackead_assignment_id" name="stackead_assignment_id">
						<option value=""><?php esc_html_e( '-- Nenhuma atividade obrigatória --', 'stackead-core' ); ?></option>
						<?php foreach ( $assignments as $asg ) : ?>
							<option value="<?php echo esc_attr( $asg->ID ); ?>" <?php selected( $assignment_id, $asg->ID ); ?>>
								<?php echo esc_html( $asg->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="stackead-form-group">
					<label style="display: flex; align-items: center; gap: 8px; font-weight: normal; margin-top: 24px; cursor: pointer;">
						<input type="checkbox" name="stackead_sequential_lock" value="yes" <?php checked( $sequential_lock, 'yes' ); ?>>
						<strong><?php esc_html_e( 'Exigir conclusão da aula anterior antes desta (Trava Sequencial)', 'stackead-core' ); ?></strong>
					</label>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders Session Meta Box.
	 */
	public static function render_session_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'stackead_save_session_meta', 'stackead_session_meta_nonce' );

		$cohort_id     = get_post_meta( $post->ID, '_stackead_cohort_id', true );
		$session_date  = get_post_meta( $post->ID, '_stackead_session_date', true );
		$meet_url      = get_post_meta( $post->ID, '_stackead_meet_url', true );
		$recording_url = get_post_meta( $post->ID, '_stackead_recording_url', true );

		$cohorts = get_posts( array( 'post_type' => 'stackead_cohort', 'posts_per_page' => -1 ) );
		?>
		<div class="stackead-meta-container">
			<div class="stackead-grid-2">
				<div class="stackead-form-group">
					<label for="stackead_cohort_id"><?php esc_html_e( 'Turma / Coorte Vinculada', 'stackead-core' ); ?></label>
					<select id="stackead_cohort_id" name="stackead_cohort_id">
						<option value=""><?php esc_html_e( '-- Selecione a Turma --', 'stackead-core' ); ?></option>
						<?php foreach ( $cohorts as $co ) : ?>
							<option value="<?php echo esc_attr( $co->ID ); ?>" <?php selected( $cohort_id, $co->ID ); ?>>
								<?php echo esc_html( $co->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="stackead-form-group">
					<label for="stackead_session_date"><?php esc_html_e( 'Data e Horário da Transmissão', 'stackead-core' ); ?></label>
					<input type="datetime-local" id="stackead_session_date" name="stackead_session_date" value="<?php echo esc_attr( $session_date ); ?>">
				</div>
			</div>
			<div class="stackead-grid-2">
				<div class="stackead-form-group">
					<label for="stackead_meet_url"><?php esc_html_e( 'Link da Sala Virtual (Google Meet, Zoom, Teams)', 'stackead-core' ); ?></label>
					<input type="text" id="stackead_meet_url" name="stackead_meet_url" value="<?php echo esc_attr( $meet_url ); ?>" placeholder="https://meet.google.com/...">
				</div>
				<div class="stackead-form-group">
					<label for="stackead_recording_url"><?php esc_html_e( 'Link da Gravação da Aula (Pós-Transmissão)', 'stackead-core' ); ?></label>
					<input type="text" id="stackead_recording_url" name="stackead_recording_url" value="<?php echo esc_attr( $recording_url ); ?>" placeholder="https://vimeo.com/...">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders Assignment Meta Box.
	 */
	public static function render_assignment_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'stackead_save_assignment_meta', 'stackead_assignment_meta_nonce' );

		$cohort_id  = get_post_meta( $post->ID, '_stackead_cohort_id', true );
		$due_date   = get_post_meta( $post->ID, '_stackead_due_date', true );
		$max_score  = get_post_meta( $post->ID, '_stackead_max_score', true );
		if ( empty( $max_score ) ) {
			$max_score = 100;
		}

		$cohorts = get_posts( array( 'post_type' => 'stackead_cohort', 'posts_per_page' => -1 ) );
		?>
		<div class="stackead-meta-container">
			<div class="stackead-grid-3">
				<div class="stackead-form-group">
					<label for="stackead_cohort_id"><?php esc_html_e( 'Turma Vinculada', 'stackead-core' ); ?></label>
					<select id="stackead_cohort_id" name="stackead_cohort_id">
						<option value=""><?php esc_html_e( '-- Selecione a Turma --', 'stackead-core' ); ?></option>
						<?php foreach ( $cohorts as $co ) : ?>
							<option value="<?php echo esc_attr( $co->ID ); ?>" <?php selected( $cohort_id, $co->ID ); ?>>
								<?php echo esc_html( $co->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="stackead-form-group">
					<label for="stackead_due_date"><?php esc_html_e( 'Prazo Limite de Entrega', 'stackead-core' ); ?></label>
					<input type="datetime-local" id="stackead_due_date" name="stackead_due_date" value="<?php echo esc_attr( $due_date ); ?>">
				</div>
				<div class="stackead-form-group">
					<label for="stackead_max_score"><?php esc_html_e( 'Pontuação Máxima', 'stackead-core' ); ?></label>
					<input type="number" id="stackead_max_score" name="stackead_max_score" value="<?php echo esc_attr( $max_score ); ?>">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save meta boxes data.
	 */
	public static function save_meta_boxes( int $post_id, \WP_Post $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// 1. Course save
		if ( 'stackead_course' === $post->post_type && isset( $_POST['stackead_course_meta_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_POST['stackead_course_meta_nonce'], 'stackead_save_course_meta' ) ) {
				return;
			}

			update_post_meta( $post_id, '_stackead_subtitle', sanitize_text_field( $_POST['stackead_subtitle'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_syllabus', sanitize_textarea_field( $_POST['stackead_syllabus'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_learning_goals', sanitize_textarea_field( $_POST['stackead_learning_goals'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_prerequisites', sanitize_textarea_field( $_POST['stackead_prerequisites'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_workload_hours', (int) ( $_POST['stackead_workload_hours'] ?? 40 ) );
			update_post_meta( $post_id, '_stackead_min_attendance', (float) ( $_POST['stackead_min_attendance'] ?? 75 ) );
			update_post_meta( $post_id, '_stackead_min_quiz', (float) ( $_POST['stackead_min_quiz'] ?? 70 ) );
			update_post_meta( $post_id, '_stackead_price_type', sanitize_text_field( $_POST['stackead_price_type'] ?? 'free' ) );
			update_post_meta( $post_id, '_stackead_price', (float) ( $_POST['stackead_price'] ?? 0 ) );
			update_post_meta( $post_id, '_stackead_sale_price', (float) ( $_POST['stackead_sale_price'] ?? 0 ) );
			update_post_meta( $post_id, '_stackead_path_id', (int) ( $_POST['stackead_path_id'] ?? 0 ) );
			update_post_meta( $post_id, '_stackead_simplified_summary', sanitize_textarea_field( $_POST['stackead_simplified_summary'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_default_extra_time', isset( $_POST['stackead_default_extra_time'] ) ? 'yes' : 'no' );

			$instructors = isset( $_POST['stackead_instructor_ids'] ) ? array_map( 'intval', (array) $_POST['stackead_instructor_ids'] ) : array();
			update_post_meta( $post_id, '_stackead_instructor_ids', $instructors );
		}

		// 2. Cohort save
		if ( 'stackead_cohort' === $post->post_type && isset( $_POST['stackead_cohort_meta_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_POST['stackead_cohort_meta_nonce'], 'stackead_save_cohort_meta' ) ) {
				return;
			}

			update_post_meta( $post_id, '_stackead_course_id', (int) ( $_POST['stackead_course_id'] ?? 0 ) );
			update_post_meta( $post_id, '_stackead_path_id', (int) ( $_POST['stackead_path_id'] ?? 0 ) );
			update_post_meta( $post_id, '_stackead_start_date', sanitize_text_field( $_POST['stackead_start_date'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_end_date', sanitize_text_field( $_POST['stackead_end_date'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_max_students', (int) ( $_POST['stackead_max_students'] ?? 0 ) );
			update_post_meta( $post_id, '_stackead_community_url', esc_url_raw( $_POST['stackead_community_url'] ?? '' ) );

			$masters = isset( $_POST['stackead_master_ids'] ) ? array_map( 'intval', (array) $_POST['stackead_master_ids'] ) : array();
			update_post_meta( $post_id, '_stackead_master_ids', implode( ',', $masters ) );
		}

		// 3. Lesson save
		if ( 'stackead_lesson' === $post->post_type && isset( $_POST['stackead_lesson_meta_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_POST['stackead_lesson_meta_nonce'], 'stackead_save_lesson_meta' ) ) {
				return;
			}

			update_post_meta( $post_id, '_stackead_video_url', esc_url_raw( $_POST['stackead_video_url'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_audio_url', esc_url_raw( $_POST['stackead_audio_url'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_duration_minutes', (int) ( $_POST['stackead_duration_minutes'] ?? 0 ) );
			update_post_meta( $post_id, '_stackead_simplified_text', sanitize_textarea_field( $_POST['stackead_simplified_text'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_transcription', sanitize_textarea_field( $_POST['stackead_transcription'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_assignment_id', (int) ( $_POST['stackead_assignment_id'] ?? 0 ) );
			update_post_meta( $post_id, '_stackead_sequential_lock', isset( $_POST['stackead_sequential_lock'] ) ? 'yes' : 'no' );
		}

		// 4. Session save
		if ( 'stackead_session' === $post->post_type && isset( $_POST['stackead_session_meta_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_POST['stackead_session_meta_nonce'], 'stackead_save_session_meta' ) ) {
				return;
			}

			update_post_meta( $post_id, '_stackead_cohort_id', (int) ( $_POST['stackead_cohort_id'] ?? 0 ) );
			update_post_meta( $post_id, '_stackead_session_date', sanitize_text_field( $_POST['stackead_session_date'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_meet_url', esc_url_raw( $_POST['stackead_meet_url'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_recording_url', esc_url_raw( $_POST['stackead_recording_url'] ?? '' ) );
		}

		// 5. Assignment save
		if ( 'stackead_assignment' === $post->post_type && isset( $_POST['stackead_assignment_meta_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_POST['stackead_assignment_meta_nonce'], 'stackead_save_assignment_meta' ) ) {
				return;
			}

			update_post_meta( $post_id, '_stackead_cohort_id', (int) ( $_POST['stackead_cohort_id'] ?? 0 ) );
			update_post_meta( $post_id, '_stackead_due_date', sanitize_text_field( $_POST['stackead_due_date'] ?? '' ) );
			update_post_meta( $post_id, '_stackead_max_score', (float) ( $_POST['stackead_max_score'] ?? 100 ) );
		}
	}
}
