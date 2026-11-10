<?php
namespace StackEAD\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles student cohort enrollments and access permissions.
 */
class EnrollmentService {

	/**
	 * Enroll a student into a cohort.
	 *
	 * @param int      $user_id
	 * @param int      $cohort_id
	 * @param int|null $path_id
	 * @param string   $status
	 * @return int|false
	 */
	public static function enroll( int $user_id, int $cohort_id, ?int $path_id = null, string $status = 'active' ) {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_enrollments';
		$now   = current_time( 'mysql' );

		// Check if already enrolled
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE user_id = %d AND cohort_id = %d",
				$user_id,
				$cohort_id
			)
		);

		if ( $existing ) {
			$wpdb->update(
				$table,
				array(
					'status'     => $status,
					'path_id'    => $path_id,
					'updated_at' => $now,
				),
				array( 'id' => $existing ),
				array( '%s', '%d', '%s' ),
				array( '%d' )
			);
			return (int) $existing;
		}

		$inserted = $wpdb->insert(
			$table,
			array(
				'user_id'     => $user_id,
				'cohort_id'   => $cohort_id,
				'path_id'     => $path_id,
				'status'      => $status,
				'enrolled_at' => $now,
				'created_at'  => $now,
				'updated_at'  => $now,
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Check if a user has an active enrollment in a cohort.
	 *
	 * @param int $user_id
	 * @param int $cohort_id
	 * @return bool
	 */
	public static function is_enrolled( int $user_id, int $cohort_id ): bool {
		global $wpdb;

		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		$table = $wpdb->prefix . 'stackead_enrollments';
		$status = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT status FROM {$table} WHERE user_id = %d AND cohort_id = %d",
				$user_id,
				$cohort_id
			)
		);

		return 'active' === $status;
	}

	/**
	 * Get all active cohorts for a student.
	 *
	 * @param int $user_id
	 * @return array
	 */
	public static function get_student_cohorts( int $user_id ): array {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_enrollments';
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT e.*, p.post_title as cohort_title FROM {$table} e
				INNER JOIN {$wpdb->posts} p ON e.cohort_id = p.ID
				WHERE e.user_id = %d AND e.status = 'active'
				ORDER BY e.enrolled_at DESC",
				$user_id
			)
		);

		return ! empty( $results ) ? $results : array();
	}

	/**
	 * Get all enrolled students for a specific cohort.
	 *
	 * @param int $cohort_id
	 * @return array
	 */
	public static function get_cohort_students( int $cohort_id ): array {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_enrollments';
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT e.*, u.display_name, u.user_email FROM {$table} e
				INNER JOIN {$wpdb->users} u ON e.user_id = u.ID
				WHERE e.cohort_id = %d
				ORDER BY u.display_name ASC",
				$cohort_id
			)
		);

		return ! empty( $results ) ? $results : array();
	}
}
