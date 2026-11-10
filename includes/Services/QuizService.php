<?php
namespace StackEAD\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quiz Engine Service for StackEAD Core.
 * Manages Quiz entities, questions, timing, accommodation extensions, and real server-side grading.
 */
class QuizService {

	/**
	 * Get full Quiz payload.
	 *
	 * @param int  $quiz_id
	 * @param bool $for_student Whether to hide correct answers and explanations.
	 * @param int  $user_id     Optional user ID for COGA accommodation checks.
	 * @return array
	 */
	public static function get_quiz( int $quiz_id, bool $for_student = true, int $user_id = 0 ): array {
		global $wpdb;
		$post = get_post( $quiz_id );
		if ( ! $post || 'stackead_quiz' !== $post->post_type ) {
			// Auto-create if doesn't exist yet
			$post_id = wp_insert_post(
				array(
					'import_id'   => $quiz_id > 0 ? $quiz_id : 0,
					'post_title'  => 'Quiz de Verificação de Aprendizagem',
					'post_type'   => 'stackead_quiz',
					'post_status' => 'publish',
				)
			);
			if ( is_wp_error( $post_id ) ) {
				return array();
			}
			$quiz_id = $post_id;
			$post    = get_post( $quiz_id );
		}

		$passing_score = (float) ( get_post_meta( $quiz_id, '_stackead_passing_score', true ) ?: 70.0 );
		$base_time     = (int) ( get_post_meta( $quiz_id, '_stackead_time_limit_minutes', true ) ?: 15 );
		$is_mandatory  = (bool) get_post_meta( $quiz_id, '_stackead_is_mandatory', true );
		$lesson_id     = (int) get_post_meta( $quiz_id, '_stackead_lesson_id', true );

		// Check student COGA extra time accommodation (+50% time)
		$time_limit = $base_time;
		if ( $user_id && AccommodationService::has_resource( $user_id, 'extra_time' ) ) {
			$time_limit = (int) ceil( $base_time * 1.5 );
		}

		$table = $wpdb->prefix . 'stackead_quiz_questions';
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE quiz_id = %d ORDER BY menu_order ASC, id ASC",
				$quiz_id
			)
		);

		// If no questions exist, seed 3 canonical questions
		if ( empty( $rows ) ) {
			self::seed_default_questions( $quiz_id );
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE quiz_id = %d ORDER BY menu_order ASC, id ASC",
					$quiz_id
				)
			);
		}

		$questions = array();
		foreach ( $rows as $r ) {
			$options = json_decode( $r->options_json, true ) ?: array();
			$q_data  = array(
				'id'            => (int) $r->id,
				'question_text' => $r->question_text,
				'question_type' => $r->question_type,
				'options'       => $options,
				'points'        => (float) $r->points,
			);

			if ( ! $for_student ) {
				$q_data['correct_answer'] = $r->correct_answer;
				$q_data['explanation']    = $r->explanation;
			}

			$questions[] = $q_data;
		}

		return array(
			'id'                 => $quiz_id,
			'lesson_id'          => $lesson_id,
			'title'              => $post->post_title,
			'description'        => $post->post_content,
			'passing_score'      => $passing_score,
			'time_limit_minutes' => $time_limit,
			'base_time_minutes'  => $base_time,
			'is_mandatory'       => $is_mandatory,
			'question_count'     => count( $questions ),
			'questions'          => $questions,
		);
	}

	/**
	 * Grade a student's quiz attempt on the server with mathematical integrity.
	 *
	 * @param int   $quiz_id
	 * @param int   $user_id
	 * @param int   $cohort_id
	 * @param array $student_answers Map of [question_id => selected_option_id]
	 * @return array
	 */
	public static function grade_attempt( int $quiz_id, int $user_id, int $cohort_id, array $student_answers ): array {
		global $wpdb;

		$quiz_post = get_post( $quiz_id );
		if ( ! $quiz_post || 'stackead_quiz' !== $quiz_post->post_type ) {
			return array(
				'success' => false,
				'error'   => 'Quiz não encontrado.',
			);
		}

		$passing_score = (float) ( get_post_meta( $quiz_id, '_stackead_passing_score', true ) ?: 70.0 );
		$lesson_id     = (int) get_post_meta( $quiz_id, '_stackead_lesson_id', true );

		$table = $wpdb->prefix . 'stackead_quiz_questions';
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE quiz_id = %d ORDER BY menu_order ASC, id ASC",
				$quiz_id
			)
		);

		if ( empty( $rows ) ) {
			return array(
				'success' => false,
				'error'   => 'Este quiz não possui questões cadastradas.',
			);
		}

		$total_points   = 0.0;
		$earned_points  = 0.0;
		$correct_count  = 0;
		$breakdown      = array();

		foreach ( $rows as $q ) {
			$q_id     = (int) $q->id;
			$points   = (float) $q->points;
			$total_points += $points;

			$submitted = isset( $student_answers[ $q_id ] ) ? trim( (string) $student_answers[ $q_id ] ) : '';
			$correct   = trim( (string) $q->correct_answer );
			$is_correct = ( strtolower( $submitted ) === strtolower( $correct ) && '' !== $submitted );

			if ( $is_correct ) {
				$earned_points += $points;
				$correct_count++;
			}

			$breakdown[] = array(
				'question_id'    => $q_id,
				'question_text'  => $q->question_text,
				'is_correct'     => $is_correct,
				'submitted'      => $submitted,
				'correct_answer' => $correct,
				'explanation'    => $q->explanation,
				'points_earned'  => $is_correct ? $points : 0.0,
				'points_max'     => $points,
			);
		}

		$score = $total_points > 0 ? round( ( $earned_points / $total_points ) * 100, 1 ) : 0.0;
		$passed = ( $score >= $passing_score );

		// Record in database
		$attempts_table = $wpdb->prefix . 'stackead_quiz_attempts';
		$wpdb->insert(
			$attempts_table,
			array(
				'quiz_id'          => $quiz_id,
				'user_id'          => $user_id,
				'cohort_id'        => $cohort_id,
				'score'            => $score,
				'passed'           => $passed ? 1 : 0,
				'attempt_number'   => 1,
				'answers_payload'  => wp_json_encode( $student_answers ),
				'completed_at'     => current_time( 'mysql' ),
				'created_at'       => current_time( 'mysql' ),
				'updated_at'       => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%f', '%d', '%d', '%s', '%s', '%s', '%s' )
		);
		$attempt_id = $wpdb->insert_id;

		// If passed: award gamification XP and unlock lesson progress
		if ( $passed ) {
			GamificationService::on_quiz_passed( $user_id, $quiz_id, $cohort_id, (float) $score );

			if ( $lesson_id && $cohort_id ) {
				ProgressService::record_progress( $user_id, $lesson_id, $cohort_id, 100.0, 0, true );
			}
		}

		return array(
			'success'         => true,
			'attempt_id'      => $attempt_id,
			'score'           => $score,
			'percentage'      => $score,
			'passed'          => $passed,
			'passing_score'   => $passing_score,
			'earned_points'   => $earned_points,
			'total_points'    => $total_points,
			'correct_count'   => $correct_count,
			'total_questions' => count( $rows ),
			'xp_awarded'      => $passed ? ( ( $score >= 100.0 ) ? 80 : 40 ) : 0,
			'feedback'        => $breakdown,
			'breakdown'       => $breakdown,
		);
	}

	/**
	 * Save or update a Quiz entity with its questions.
	 *
	 * @param array $data
	 * @return int Quiz ID
	 */
	public static function save_quiz( array $data ): int {
		global $wpdb;

		$quiz_id = (int) ( $data['id'] ?? 0 );
		$title   = sanitize_text_field( $data['title'] ?? 'Quiz de Avaliação' );
		$desc    = wp_kses_post( $data['description'] ?? '' );

		if ( $quiz_id > 0 && get_post( $quiz_id ) ) {
			wp_update_post(
				array(
					'ID'           => $quiz_id,
					'post_title'   => $title,
					'post_content' => $desc,
				)
			);
		} else {
			$quiz_id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_content' => $desc,
					'post_type'    => 'stackead_quiz',
					'post_status'  => 'publish',
				)
			);
		}

		if ( is_wp_error( $quiz_id ) ) {
			return 0;
		}

		$passing_score = (float) ( $data['passing_score'] ?? 70.0 );
		$time_limit    = (int) ( $data['time_limit_minutes'] ?? 15 );
		$is_mandatory  = (int) ( $data['is_mandatory'] ?? 1 );
		$lesson_id     = (int) ( $data['lesson_id'] ?? 0 );

		update_post_meta( $quiz_id, '_stackead_passing_score', $passing_score );
		update_post_meta( $quiz_id, '_stackead_time_limit_minutes', $time_limit );
		update_post_meta( $quiz_id, '_stackead_is_mandatory', $is_mandatory );
		if ( $lesson_id ) {
			update_post_meta( $quiz_id, '_stackead_lesson_id', $lesson_id );
			update_post_meta( $lesson_id, '_stackead_quiz_id', $quiz_id );
		}

		// Save questions if provided
		if ( ! empty( $data['questions'] ) && is_array( $data['questions'] ) ) {
			$table = $wpdb->prefix . 'stackead_quiz_questions';
			// Clear old questions
			$wpdb->delete( $table, array( 'quiz_id' => $quiz_id ), array( '%d' ) );

			foreach ( $data['questions'] as $idx => $q ) {
				$q_text    = sanitize_text_field( $q['question_text'] ?? '' );
				$q_type    = sanitize_text_field( $q['question_type'] ?? 'single_choice' );
				$options   = (array) ( $q['options'] ?? array() );
				$correct   = sanitize_text_field( $q['correct_answer'] ?? 'a' );
				$expl      = sanitize_textarea_field( $q['explanation'] ?? '' );
				$points    = (float) ( $q['points'] ?? 10.0 );

				if ( ! empty( $q_text ) ) {
					$wpdb->insert(
						$table,
						array(
							'quiz_id'        => $quiz_id,
							'lesson_id'      => $lesson_id,
							'question_text'  => $q_text,
							'question_type'  => $q_type,
							'options_json'   => wp_json_encode( $options ),
							'correct_answer' => $correct,
							'explanation'    => $expl,
							'points'         => $points,
							'menu_order'     => $idx,
							'created_at'     => current_time( 'mysql' ),
							'updated_at'     => current_time( 'mysql' ),
						),
						array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s' )
					);
				}
			}
		}

		return $quiz_id;
	}

	/**
	 * Seeds 3 default pedagogical questions if a quiz is freshly opened.
	 *
	 * @param int $quiz_id
	 */
	public static function seed_default_questions( int $quiz_id ): void {
		global $wpdb;
		$table = $wpdb->prefix . 'stackead_quiz_questions';

		$default_questions = array(
			array(
				'text'    => '1. Qual a principal vantagem da modularidade pedagógica (Lego Didático) em plataformas educacionais modernas?',
				'options' => array(
					array( 'id' => 'a', 'text' => 'Permite que módulos existam como minicursos independentes ou componham múltiplas trilhas de formação flexíveis.' ),
					array( 'id' => 'b', 'text' => 'Obriga todos os cursos a terem a mesma ementa sem possibilidade de personalização.' ),
					array( 'id' => 'c', 'text' => 'Impede que os alunos tenham acesso a aulas gravadas.' ),
				),
				'correct' => 'a',
				'expl'    => 'A composabilidade tipo Lego permite que a instituição venda módulos avulsos ou componha trilhas completas de acordo com a estratégia pedagógica.',
				'points'  => 10,
			),
			array(
				'text'    => '2. Em uma arquitetura White Label autêntica, como a identidade institucional deve ser gerenciada?',
				'options' => array(
					array( 'id' => 'a', 'text' => 'Inserida diretamente com nomes e imagens fixas no código-fonte do plugin.' ),
					array( 'id' => 'b', 'text' => 'Parametrizada dinamicamente via banco de dados e gerenciada por painel de configurações externo, sem acoplamento à marca.' ),
					array( 'id' => 'c', 'text' => 'Restrita ao envio de arquivos estáticos por FTP a cada novo cliente.' ),
				),
				'correct' => 'b',
				'expl'    => 'O núcleo do LMS deve ser 100% neutro e agnóstico, permitindo customização dinâmica por variáveis de design e metadados institucionais.',
				'points'  => 10,
			),
			array(
				'text'    => '3. De acordo com as diretrizes W3C COGA para neurodivergência, qual acomodação é essencial em provas e avaliações?',
				'options' => array(
					array( 'id' => 'a', 'text' => 'Pausa anti-ansiedade com congelamento do cronômetro e concessão de tempo extra automática para alunos com TDAH/dislexia.' ),
					array( 'id' => 'b', 'text' => 'Eliminação de todo e qualquer cronômetro mesmo em provas certificadoras oficiais.' ),
					array( 'id' => 'c', 'text' => 'Exigência de conclusão em menos de 5 minutos para estimular rapidez sob pressão.' ),
				),
				'correct' => 'a',
				'expl'    => 'A pausa para respiração e o tempo adicional reduzem a ansiedade de desempenho e garantem equidade no processo avaliativo.',
				'points'  => 10,
			),
		);

		foreach ( $default_questions as $idx => $q ) {
			$wpdb->insert(
				$table,
				array(
					'quiz_id'        => $quiz_id,
					'lesson_id'      => 0,
					'question_text'  => $q['text'],
					'question_type'  => 'single_choice',
					'options_json'   => wp_json_encode( $q['options'] ),
					'correct_answer' => $q['correct'],
					'explanation'    => $q['expl'],
					'points'         => $q['points'],
					'menu_order'     => $idx,
					'created_at'     => current_time( 'mysql' ),
					'updated_at'     => current_time( 'mysql' ),
				),
				array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s' )
			);
		}
	}
}
