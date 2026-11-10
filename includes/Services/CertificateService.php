<?php
namespace StackEAD\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles certificate approval criteria, SHA-256 generation, issuance and public validation.
 */
class CertificateService {

	/**
	 * Check if a student meets all criteria to graduate and receive certificate.
	 *
	 * @param int $user_id
	 * @param int $cohort_id
	 * @return array ['eligible' => bool, 'progress' => float, 'attendance' => float, 'quiz_avg' => float, 'assignments_ok' => bool, 'reasons' => array]
	 */
	public static function check_eligibility( int $user_id, int $cohort_id ): array {
		$progress       = ProgressService::calculate_cohort_progress( $user_id, $cohort_id );
		$attendance     = AttendanceService::calculate_attendance_percent( $user_id, $cohort_id );
		$quiz_avg       = GradingService::calculate_quiz_average( $user_id, $cohort_id );
		$assignments_ok = GradingService::are_assignments_approved( $user_id, $cohort_id );

		$reasons = array();

		if ( $progress < 100.0 ) {
			$reasons[] = sprintf( __( 'Progresso nas aulas: %.1f%% (Necessário 100%%).', 'stackead-core' ), $progress );
		}
		if ( $attendance < 75.0 ) {
			$reasons[] = sprintf( __( 'Frequência nas sessões síncronas: %.1f%% (Mínimo 75%%).', 'stackead-core' ), $attendance );
		}
		if ( $quiz_avg < 70.0 ) {
			$reasons[] = sprintf( __( 'Média nos quizzes: %.1f%% (Mínimo 70%%).', 'stackead-core' ), $quiz_avg );
		}
		if ( ! $assignments_ok ) {
			$reasons[] = __( 'Todas as atividades práticas obrigatórias devem estar aprovadas.', 'stackead-core' );
		}

		$eligible = empty( $reasons );

		// Check if certificate was already issued
		$existing = get_posts(
			array(
				'post_type'      => 'stackead_certificate',
				'meta_query'     => array(
					array(
						'key'   => '_stackead_user_id',
						'value' => $user_id,
					),
					array(
						'key'   => '_stackead_cohort_id',
						'value' => $cohort_id,
					),
				),
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		$cert_info = null;
		if ( ! empty( $existing ) ) {
			$cert_id = (int) $existing[0];
			$hash    = get_post_meta( $cert_id, '_stackead_hash', true );
			$cert_info = array(
				'cert_id'    => $cert_id,
				'hash'       => $hash,
				'issued_at'  => get_post_meta( $cert_id, '_stackead_issued_at', true ),
				'verify_url' => home_url( '/certificados/' . $hash ),
			);
		}

		return array(
			'eligible'       => $eligible,
			'progress'       => $progress,
			'attendance'     => $attendance,
			'quiz_avg'       => $quiz_avg,
			'assignments_ok' => $assignments_ok,
			'reasons'        => $reasons,
			'issued_cert'    => $cert_info,
		);
	}

	/**
	 * Generate 32-character SHA-256 verification hash.
	 *
	 * @param int $user_id
	 * @param int $cohort_id
	 * @param int $path_id
	 * @return string
	 */
	public static function generate_hash( int $user_id, int $cohort_id, int $path_id ): string {
		$salt    = defined( 'NONCE_SALT' ) ? NONCE_SALT : 'stackead_core_secret_salt';
		$payload = sprintf( '%d|%d|%d|%s|%s', $user_id, $cohort_id, $path_id, $salt, microtime( true ) );

		return strtoupper( substr( hash( 'sha256', $payload ), 0, 32 ) );
	}

	/**
	 * Issue certificate for student in cohort.
	 *
	 * @param int  $user_id
	 * @param int  $cohort_id
	 * @param bool $force_bypass_criteria
	 * @return int|false Certificate Post ID
	 */
	public static function issue_certificate( int $user_id, int $cohort_id, bool $force_bypass_criteria = false ) {
		if ( ! $force_bypass_criteria ) {
			$check = self::check_eligibility( $user_id, $cohort_id );
			if ( ! $check['eligible'] ) {
				return false;
			}
		}

		// Check if already issued
		$existing = get_posts(
			array(
				'post_type'      => 'stackead_certificate',
				'meta_query'     => array(
					array(
						'key'   => '_stackead_user_id',
						'value' => $user_id,
					),
					array(
						'key'   => '_stackead_cohort_id',
						'value' => $cohort_id,
					),
				),
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( ! empty( $existing ) ) {
			return (int) $existing[0];
		}

		$path_id = (int) get_post_meta( $cohort_id, '_stackead_path_id', true );
		$course_id = (int) get_post_meta( $cohort_id, '_stackead_course_id', true );
		$workload = (int) get_post_meta( $course_id, '_stackead_workload_hours', true );
		if ( $workload <= 0 ) {
			$workload = 40;
		}

		$hash = self::generate_hash( $user_id, $cohort_id, $path_id );
		$user = get_userdata( $user_id );
		$title = sprintf( 'Certificado - %s (%s)', $user->display_name, get_the_title( $cohort_id ) );

		$cert_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_type'    => 'stackead_certificate',
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
			)
		);

		if ( $cert_id && ! is_wp_error( $cert_id ) ) {
			update_post_meta( $cert_id, '_stackead_hash', $hash );
			update_post_meta( $cert_id, '_stackead_user_id', $user_id );
			update_post_meta( $cert_id, '_stackead_cohort_id', $cohort_id );
			update_post_meta( $cert_id, '_stackead_path_id', $path_id );
			update_post_meta( $cert_id, '_stackead_course_id', $course_id );
			update_post_meta( $cert_id, '_stackead_workload_hours', $workload );
			update_post_meta( $cert_id, '_stackead_issued_at', current_time( 'mysql' ) );

			// Mark enrollment completed
			global $wpdb;
			$wpdb->update(
				$wpdb->prefix . 'stackead_enrollments',
				array(
					'status'       => 'completed',
					'completed_at' => current_time( 'mysql' ),
				),
				array(
					'user_id'   => $user_id,
					'cohort_id' => $cohort_id,
				)
			);

			do_action( 'stackead_certificate_issued', $cert_id, $user_id, $cohort_id, $hash );

			return $cert_id;
		}

		return false;
	}

	/**
	 * Verify certificate by 32-character hash.
	 *
	 * @param string $hash
	 * @return array|null
	 */
	public static function verify_certificate( string $hash ): ?array {
		$hash = strtoupper( trim( sanitize_text_field( $hash ) ) );

		$certs = get_posts(
			array(
				'post_type'      => 'stackead_certificate',
				'meta_key'       => '_stackead_hash',
				'meta_value'     => $hash,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( empty( $certs ) ) {
			return null;
		}

		$cert_id   = $certs[0];
		$user_id   = (int) get_post_meta( $cert_id, '_stackead_user_id', true );
		$cohort_id = (int) get_post_meta( $cert_id, '_stackead_cohort_id', true );
		$course_id = (int) get_post_meta( $cert_id, '_stackead_course_id', true );
		$workload  = (int) get_post_meta( $cert_id, '_stackead_workload_hours', true );
		$issued_at = get_post_meta( $cert_id, '_stackead_issued_at', true );

		$user = get_userdata( $user_id );

		return array(
			'cert_id'        => $cert_id,
			'hash'           => $hash,
			'student_name'   => $user ? $user->display_name : __( 'Estudante', 'stackead-core' ),
			'course_title'   => $course_id ? get_the_title( $course_id ) : '',
			'cohort_title'   => $cohort_id ? get_the_title( $cohort_id ) : '',
			'workload_hours' => $workload,
			'issued_at'      => $issued_at,
			'verify_url'     => home_url( '/certificados/' . $hash ),
		);
	}
}
