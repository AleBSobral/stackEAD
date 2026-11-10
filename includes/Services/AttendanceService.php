<?php
namespace StackEAD\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles synchronous sessions attendance recording and percentage calculations.
 */
class AttendanceService {

	/**
	 * Record or update attendance for a single student in a session.
	 *
	 * @param int         $session_id
	 * @param int         $user_id
	 * @param int         $cohort_id
	 * @param string      $status present|absent|excused|late
	 * @param int         $minutes_attended
	 * @param string      $source manual|webhook_zoom|webhook_meet
	 * @param int|null    $recorded_by
	 * @param string|null $note
	 * @return bool
	 */
	public static function record_attendance(
		int $session_id,
		int $user_id,
		int $cohort_id,
		string $status = 'present',
		int $minutes_attended = 0,
		string $source = 'manual',
		?int $recorded_by = null,
		?string $note = null
	): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_attendances';
		$now   = current_time( 'mysql' );

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE session_id = %d AND user_id = %d",
				$session_id,
				$user_id
			)
		);

		if ( $existing ) {
			$wpdb->update(
				$table,
				array(
					'status'           => $status,
					'minutes_attended' => $minutes_attended,
					'source'           => $source,
					'recorded_by'      => $recorded_by ? $recorded_by : get_current_user_id(),
					'note'             => $note,
					'updated_at'       => $now,
				),
				array( 'id' => $existing ),
				array( '%s', '%d', '%s', '%d', '%s', '%s' ),
				array( '%d' )
			);
		} else {
			$wpdb->insert(
				$table,
				array(
					'session_id'       => $session_id,
					'user_id'          => $user_id,
					'cohort_id'        => $cohort_id,
					'status'           => $status,
					'minutes_attended' => $minutes_attended,
					'source'           => $source,
					'recorded_by'      => $recorded_by ? $recorded_by : get_current_user_id(),
					'note'             => $note,
					'created_at'       => $now,
					'updated_at'       => $now,
				),
				array( '%d', '%d', '%d', '%s', '%d', '%s', '%d', '%s', '%s', '%s' )
			);
		}

		if ( in_array( $status, array( 'present', 'late' ), true ) ) {
			do_action( 'stackead_session_attended', $user_id, $session_id, $cohort_id );
		}

		return true;
	}

	/**
	 * Batch record attendance for an entire cohort session.
	 *
	 * @param int   $session_id
	 * @param int   $cohort_id
	 * @param array $attendances Array of ['user_id' => int, 'status' => string, 'minutes' => int, 'note' => string]
	 * @return int Number of records updated/inserted
	 */
	public static function batch_record_attendance( int $session_id, int $cohort_id, array $attendances ): int {
		$count     = 0;
		$master_id = get_current_user_id();

		foreach ( $attendances as $row ) {
			$user_id = isset( $row['user_id'] ) ? (int) $row['user_id'] : 0;
			$status  = isset( $row['status'] ) ? sanitize_text_field( $row['status'] ) : 'present';
			$minutes = isset( $row['minutes'] ) ? (int) $row['minutes'] : 0;
			$note    = isset( $row['note'] ) ? sanitize_textarea_field( $row['note'] ) : null;

			if ( $user_id > 0 ) {
				self::record_attendance( $session_id, $user_id, $cohort_id, $status, $minutes, 'manual', $master_id, $note );
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Calculate student attendance percentage in a cohort.
	 *
	 * @param int $user_id
	 * @param int $cohort_id
	 * @return float Percent from 0.0 to 100.0
	 */
	public static function calculate_attendance_percent( int $user_id, int $cohort_id ): float {
		// Get all sessions linked to this cohort
		$sessions = get_posts(
			array(
				'post_type'      => 'stackead_session',
				'meta_key'       => '_stackead_cohort_id',
				'meta_value'     => $cohort_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$total_sessions = count( $sessions );
		if ( 0 === $total_sessions ) {
			return 100.0; // If no live sessions scheduled, attendance requirement is satisfied
		}

		global $wpdb;
		$table = $wpdb->prefix . 'stackead_attendances';
		$placeholders = implode( ',', array_fill( 0, $total_sessions, '%d' ) );

		$valid_attendances = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT session_id) FROM {$table}
				WHERE user_id = %d AND cohort_id = %d AND session_id IN ($placeholders) AND status IN ('present', 'excused', 'late')",
				array_merge( array( $user_id, $cohort_id ), $sessions )
			)
		);

		return round( ( $valid_attendances / $total_sessions ) * 100.0, 2 );
	}

	/**
	 * Get session attendance sheet.
	 *
	 * @param int $session_id
	 * @param int $cohort_id
	 * @return array
	 */
	public static function get_session_attendance_sheet( int $session_id, int $cohort_id ): array {
		global $wpdb;

		$enrollments_table = $wpdb->prefix . 'stackead_enrollments';
		$attendance_table  = $wpdb->prefix . 'stackead_attendances';

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT e.user_id, u.display_name, u.user_email,
					COALESCE(a.status, 'absent') as status,
					COALESCE(a.minutes_attended, 0) as minutes_attended,
					a.note, a.updated_at
				FROM {$enrollments_table} e
				INNER JOIN {$wpdb->users} u ON e.user_id = u.ID
				LEFT JOIN {$attendance_table} a ON e.user_id = a.user_id AND a.session_id = %d
				WHERE e.cohort_id = %d AND e.status = 'active'
				ORDER BY u.display_name ASC",
				$session_id,
				$cohort_id
			)
		);

		return ! empty( $results ) ? $results : array();
	}
}
