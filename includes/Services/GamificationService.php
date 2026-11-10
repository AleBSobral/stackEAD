<?php
namespace StackEAD\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles gamification logic: XP, badges, levels, streaks and leaderboards.
 */
class GamificationService {

	/**
	 * Initialize gamification event listeners.
	 */
	public static function init(): void {
		add_action( 'stackead_lesson_completed', array( __CLASS__, 'on_lesson_completed' ), 10, 3 );
		add_action( 'stackead_session_attended', array( __CLASS__, 'on_session_attended' ), 10, 3 );
		add_action( 'stackead_quiz_passed', array( __CLASS__, 'on_quiz_passed' ), 10, 4 );
		add_action( 'stackead_assignment_approved', array( __CLASS__, 'on_assignment_approved' ), 10, 4 );
		add_action( 'stackead_certificate_issued', array( __CLASS__, 'on_certificate_issued' ), 10, 4 );
	}

	/**
	 * Award XP points to a user.
	 *
	 * @param int         $user_id
	 * @param int         $points
	 * @param string      $action
	 * @param int|null    $reference_id
	 * @param string|null $description
	 * @return int
	 */
	public static function award_points( int $user_id, int $points, string $action, ?int $reference_id = null, ?string $description = null ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_gamification_points';
		$now   = current_time( 'mysql' );

		$wpdb->insert(
			$table,
			array(
				'user_id'      => $user_id,
				'points'       => $points,
				'point_type'   => 'xp',
				'event_action' => $action,
				'reference_id' => $reference_id,
				'description'  => $description,
				'created_at'   => $now,
			),
			array( '%d', '%d', '%s', '%s', '%d', '%s', '%s' )
		);

		self::check_badge_milestones( $user_id );

		return self::get_user_total_points( $user_id );
	}

	/**
	 * Award a badge to a user if not already earned.
	 *
	 * @param int    $user_id
	 * @param string $badge_key
	 * @param string $title
	 * @param string $description
	 * @param string $icon_svg
	 * @return bool
	 */
	public static function award_badge( int $user_id, string $badge_key, string $title, string $description, string $icon_svg = '' ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_gamification_badges';

		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE user_id = %d AND badge_key = %s",
				$user_id,
				$badge_key
			)
		);

		if ( $exists ) {
			return false;
		}

		$wpdb->insert(
			$table,
			array(
				'user_id'     => $user_id,
				'badge_key'   => $badge_key,
				'title'       => $title,
				'description' => $description,
				'icon_svg'    => $icon_svg,
				'earned_at'   => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		return true;
	}

	/**
	 * Get total points for a user.
	 *
	 * @param int $user_id
	 * @return int
	 */
	public static function get_user_total_points( int $user_id ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_gamification_points';
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(points) FROM {$table} WHERE user_id = %d AND point_type = 'xp'",
				$user_id
			)
		);

		return $total > 0 ? $total : 0;
	}

	/**
	 * Calculate user level based on total XP.
	 *
	 * @param int $user_id
	 * @return array ['level' => int, 'title' => string, 'current_xp' => int, 'next_level_xp' => int, 'progress_percent' => float]
	 */
	public static function get_user_level( int $user_id ): array {
		$total_xp = self::get_user_total_points( $user_id );

		// Level = floor(sqrt(total_xp) / 4) + 1
		$level = (int) floor( sqrt( $total_xp ) / 4 ) + 1;

		$level_titles = array(
			1  => __( 'Aprendiz', 'stackead-core' ),
			2  => __( 'Explorador', 'stackead-core' ),
			3  => __( 'Praticante', 'stackead-core' ),
			4  => __( 'Inovador', 'stackead-core' ),
			5  => __( 'Especialista', 'stackead-core' ),
			6  => __( 'Mestre Criativo', 'stackead-core' ),
			7  => __( 'Visionário', 'stackead-core' ),
			8  => __( 'Pioneiro', 'stackead-core' ),
			9  => __( 'Alquimista Digital', 'stackead-core' ),
			10 => __( 'Lenda ArtInnova', 'stackead-core' ),
		);

		$title = isset( $level_titles[ $level ] ) ? $level_titles[ $level ] : __( 'Lenda ArtInnova', 'stackead-core' );

		// Calculate XP boundaries
		$current_level_min = pow( ( $level - 1 ) * 4, 2 );
		$next_level_min    = pow( $level * 4, 2 );
		$needed            = max( 1, $next_level_min - $current_level_min );
		$gained            = max( 0, $total_xp - $current_level_min );
		$progress          = min( 100.0, round( ( $gained / $needed ) * 100.0, 1 ) );

		return array(
			'level'            => $level,
			'title'            => $title,
			'total_xp'         => $total_xp,
			'next_level_xp'    => (int) $next_level_min,
			'progress_percent' => $progress,
		);
	}

	/**
	 * Get user badges.
	 *
	 * @param int $user_id
	 * @return array
	 */
	public static function get_user_badges( int $user_id ): array {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_gamification_badges';
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE user_id = %d ORDER BY earned_at DESC",
				$user_id
			)
		);

		return ! empty( $results ) ? $results : array();
	}

	/**
	 * Get leaderboard of top students.
	 *
	 * @param int $limit
	 * @return array
	 */
	public static function get_leaderboard( int $limit = 10 ): array {
		global $wpdb;

		$points_table = $wpdb->prefix . 'stackead_gamification_points';
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.user_id, SUM(p.points) as total_xp, u.display_name
				FROM {$points_table} p
				INNER JOIN {$wpdb->users} u ON p.user_id = u.ID
				WHERE p.point_type = 'xp'
				GROUP BY p.user_id
				ORDER BY total_xp DESC
				LIMIT %d",
				$limit
			)
		);

		return ! empty( $results ) ? $results : array();
	}

	/**
	 * Event handler: Lesson completed.
	 */
	public static function on_lesson_completed( int $user_id, int $lesson_id, int $cohort_id ): void {
		self::award_points( $user_id, 25, 'lesson_completed', $lesson_id, __( 'Conclusão de aula', 'stackead-core' ) );
		self::award_badge( $user_id, 'first_lesson', __( 'Primeiro Passo', 'stackead-core' ), __( 'Concluiu a sua primeira aula.', 'stackead-core' ), 'award' );
	}

	/**
	 * Event handler: Session attended.
	 */
	public static function on_session_attended( int $user_id, int $session_id, int $cohort_id ): void {
		self::award_points( $user_id, 50, 'session_attended', $session_id, __( 'Presença em sessão ao vivo', 'stackead-core' ) );
		self::award_badge( $user_id, 'live_presence', __( 'Presença Marcante', 'stackead-core' ), __( 'Participou de uma sessão ao vivo com a turma.', 'stackead-core' ), 'radio' );
	}

	/**
	 * Event handler: Quiz passed.
	 */
	public static function on_quiz_passed( int $user_id, int $quiz_id, int $cohort_id, float $score ): void {
		$points = ( $score >= 100.0 ) ? 80 : 40;
		self::award_points( $user_id, $points, 'quiz_passed', $quiz_id, sprintf( __( 'Aprovação em Quiz (%.0f%%)', 'stackead-core' ), $score ) );
		if ( $score >= 100.0 ) {
			self::award_badge( $user_id, 'quiz_master', __( 'Gabaritou!', 'stackead-core' ), __( 'Obteve 100% de aproveitamento em uma avaliação.', 'stackead-core' ), 'zap' );
		}
	}

	/**
	 * Event handler: Assignment approved.
	 */
	public static function on_assignment_approved( int $user_id, int $assignment_id, int $cohort_id, float $score ): void {
		self::award_points( $user_id, 100, 'assignment_approved', $assignment_id, __( 'Projeto prático aprovado pelo Mestre', 'stackead-core' ) );
		self::award_badge( $user_id, 'practical_creator', __( 'Mão na Massa', 'stackead-core' ), __( 'Entregou um projeto prático aprovado com excelência.', 'stackead-core' ), 'code' );
	}

	/**
	 * Event handler: Certificate issued.
	 */
	public static function on_certificate_issued( int $cert_id, int $user_id, int $cohort_id, string $hash ): void {
		self::award_points( $user_id, 500, 'certificate_issued', $cert_id, __( 'Graduação e emissão de certificado oficial', 'stackead-core' ) );
		self::award_badge( $user_id, 'graduated', __( 'Diplomado', 'stackead-core' ), __( 'Concluiu a formação com sucesso e emitiu seu certificado autêntico.', 'stackead-core' ), 'check-circle' );
	}

	/**
	 * Check milestone badges (e.g. 1000 XP).
	 */
	private static function check_badge_milestones( int $user_id ): void {
		$total = self::get_user_total_points( $user_id );
		if ( $total >= 1000 ) {
			self::award_badge( $user_id, 'xp_1000', __( 'Milenar', 'stackead-core' ), __( 'Alcançou 1.000 XP na plataforma.', 'stackead-core' ), 'flame' );
		}
	}

	/**
	 * Award virtual coins (e.g. ArtCoins) to a user.
	 *
	 * @param int         $user_id
	 * @param int         $coins
	 * @param string      $action
	 * @param int|null    $reference_id
	 * @param string|null $description
	 * @return int Total coins after award
	 */
	public static function award_coins( int $user_id, int $coins, string $action, ?int $reference_id = null, ?string $description = null ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'stackead_gamification_points';
		$now   = current_time( 'mysql' );

		$wpdb->insert(
			$table,
			array(
				'user_id'      => $user_id,
				'points'       => $coins,
				'point_type'   => 'coins',
				'event_action' => $action,
				'reference_id' => $reference_id,
				'description'  => $description ?: sprintf( __( '+%d moedas virtuais', 'stackead-core' ), $coins ),
				'created_at'   => $now,
			),
			array( '%d', '%d', '%s', '%s', '%d', '%s', '%s' )
		);

		return self::get_user_coins( $user_id );
	}

	/**
	 * Get user virtual coins balance.
	 *
	 * @param int $user_id
	 * @return int
	 */
	public static function get_user_coins( int $user_id ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'stackead_gamification_points';

		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(points) FROM {$table} WHERE user_id = %d AND point_type = 'coins'",
				$user_id
			)
		);

		return $total > 0 ? $total : 0;
	}

	/**
	 * Get gamification configuration (Virtual Coin name, symbol, earn rates, and badges).
	 *
	 * @return array
	 */
	public static function get_gamification_config(): array {
		$default = array(
			'coin_name'             => 'ArtCoins',
			'coin_symbol'           => 'AC$',
			'coins_per_lesson'      => 10,
			'coins_per_session'     => 25,
			'coins_per_assignment'  => 50,
			'coins_per_certificate' => 200,
			'xp_per_lesson'         => 20,
			'xp_per_session'        => 50,
			'xp_per_assignment'     => 100,
		);

		$saved = get_option( 'stackead_gamification_config', array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $default );
	}

	/**
	 * Save gamification configuration.
	 *
	 * @param array $config
	 * @return bool
	 */
	public static function save_gamification_config( array $config ): bool {
		$clean = array(
			'coin_name'             => sanitize_text_field( $config['coin_name'] ?? 'ArtCoins' ),
			'coin_symbol'           => sanitize_text_field( $config['coin_symbol'] ?? 'AC$' ),
			'coins_per_lesson'      => max( 0, (int) ( $config['coins_per_lesson'] ?? 10 ) ),
			'coins_per_session'     => max( 0, (int) ( $config['coins_per_session'] ?? 25 ) ),
			'coins_per_assignment'  => max( 0, (int) ( $config['coins_per_assignment'] ?? 50 ) ),
			'coins_per_certificate' => max( 0, (int) ( $config['coins_per_certificate'] ?? 200 ) ),
			'xp_per_lesson'         => max( 0, (int) ( $config['xp_per_lesson'] ?? 20 ) ),
			'xp_per_session'        => max( 0, (int) ( $config['xp_per_session'] ?? 50 ) ),
			'xp_per_assignment'     => max( 0, (int) ( $config['xp_per_assignment'] ?? 100 ) ),
		);

		return update_option( 'stackead_gamification_config', $clean );
	}

	/**
	 * Get standard + custom badges list.
	 *
	 * @return array
	 */
	public static function get_all_badges(): array {
		$standard = array(
			array(
				'key'         => 'first_step',
				'title'       => 'Primeiro Passo',
				'description' => 'Concluiu a primeira aula na plataforma.',
				'icon'        => 'check-circle',
				'xp_reward'   => 50,
				'coin_reward' => 25,
				'is_system'   => true,
			),
			array(
				'key'         => 'live_pioneer',
				'title'       => 'Pioneiro Ao Vivo',
				'description' => 'Marcou presença em uma sessão síncrona com os mestres.',
				'icon'        => 'video',
				'xp_reward'   => 50,
				'coin_reward' => 25,
				'is_system'   => true,
			),
			array(
				'key'         => 'quiz_master',
				'title'       => 'Gabaritou!',
				'description' => 'Obteve 100% de aproveitamento em uma avaliação.',
				'icon'        => 'zap',
				'xp_reward'   => 80,
				'coin_reward' => 40,
				'is_system'   => true,
			),
			array(
				'key'         => 'practical_creator',
				'title'       => 'Mão na Massa',
				'description' => 'Entregou um projeto prático aprovado com excelência.',
				'icon'        => 'code',
				'xp_reward'   => 100,
				'coin_reward' => 50,
				'is_system'   => true,
			),
			array(
				'key'         => 'xp_1000',
				'title'       => 'Milenar',
				'description' => 'Alcançou a marca histórica de 1.000 XP.',
				'icon'        => 'flame',
				'xp_reward'   => 200,
				'coin_reward' => 100,
				'is_system'   => true,
			),
			array(
				'key'         => 'graduated',
				'title'       => 'Diplomado',
				'description' => 'Concluiu a formação com sucesso e emitiu seu certificado autêntico.',
				'icon'        => 'award',
				'xp_reward'   => 500,
				'coin_reward' => 250,
				'is_system'   => true,
			),
		);

		$custom = get_option( 'stackead_custom_badges', array() );
		if ( is_array( $custom ) && ! empty( $custom ) ) {
			return array_merge( $standard, $custom );
		}

		return $standard;
	}

	/**
	 * Save a custom badge.
	 *
	 * @param array $badge
	 * @return array The saved badge
	 */
	public static function save_custom_badge( array $badge ): array {
		$custom = get_option( 'stackead_custom_badges', array() );
		if ( ! is_array( $custom ) ) {
			$custom = array();
		}

		$key = ! empty( $badge['key'] ) ? sanitize_key( $badge['key'] ) : 'badge_' . time();
		$new_badge = array(
			'key'         => $key,
			'title'       => sanitize_text_field( $badge['title'] ?? 'Novo Emblema' ),
			'description' => sanitize_text_field( $badge['description'] ?? '' ),
			'icon'        => sanitize_text_field( $badge['icon'] ?? 'award' ),
			'icon_url'    => esc_url_raw( $badge['icon_url'] ?? '' ),
			'xp_reward'   => (int) ( $badge['xp_reward'] ?? 50 ),
			'coin_reward' => (int) ( $badge['coin_reward'] ?? 25 ),
			'criteria'    => sanitize_text_field( $badge['criteria'] ?? 'manual' ),
			'is_system'   => false,
		);

		// Replace or add
		$found = false;
		foreach ( $custom as $idx => $b ) {
			if ( $b['key'] === $key ) {
				$custom[ $idx ] = $new_badge;
				$found = true;
				break;
			}
		}
		if ( ! $found ) {
			$custom[] = $new_badge;
		}

		update_option( 'stackead_custom_badges', $custom );
		return $new_badge;
	}

	/**
	 * Delete a custom badge.
	 *
	 * @param string $badge_key
	 * @return bool
	 */
	public static function delete_custom_badge( string $badge_key ): bool {
		$custom = get_option( 'stackead_custom_badges', array() );
		if ( ! is_array( $custom ) ) {
			return false;
		}

		$filtered = array_values(
			array_filter(
				$custom,
				function( $b ) use ( $badge_key ) {
					return $b['key'] !== $badge_key;
				}
			)
		);

		return update_option( 'stackead_custom_badges', $filtered );
	}
}
