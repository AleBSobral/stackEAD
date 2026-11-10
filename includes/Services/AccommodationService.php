<?php
namespace StackEAD\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles confidential accommodations for neurodivergent students (W3C COGA).
 */
class AccommodationService {

	/**
	 * Request accommodation.
	 *
	 * @param int      $user_id
	 * @param string   $resource_type extra_time|simplified_language|focus_mode|reading_guide
	 * @param string   $instructions
	 * @param string   $scope_type global|cohort
	 * @param int|null $scope_id
	 * @return int|false
	 */
	public static function request_accommodation(
		int $user_id,
		string $resource_type,
		string $instructions,
		string $scope_type = 'global',
		?int $scope_id = null
	) {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_accommodations';
		$now   = current_time( 'mysql' );

		$inserted = $wpdb->insert(
			$table,
			array(
				'user_id'       => $user_id,
				'scope_type'    => $scope_type,
				'scope_id'      => $scope_id,
				'resource_type' => sanitize_text_field( $resource_type ),
				'instructions'  => sanitize_textarea_field( $instructions ),
				'status'        => 'requested',
				'created_at'    => $now,
				'updated_at'    => $now,
			),
			array( '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Approve or reject accommodation.
	 *
	 * @param int    $id
	 * @param string $status approved|rejected
	 * @param int    $reviewer_id
	 * @return bool
	 */
	public static function review_accommodation( int $id, string $status, int $reviewer_id ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_accommodations';
		$now   = current_time( 'mysql' );

		$updated = $wpdb->update(
			$table,
			array(
				'status'      => $status,
				'approved_by' => $reviewer_id,
				'approved_at' => $now,
				'updated_at'  => $now,
			),
			array( 'id' => $id ),
			array( '%s', '%d', '%s', '%s' ),
			array( '%d' )
		);

		return false !== $updated;
	}

	/**
	 * Get approved accommodations for a student.
	 *
	 * @param int      $user_id
	 * @param int|null $cohort_id
	 * @return array
	 */
	public static function get_student_accommodations( int $user_id, ?int $cohort_id = null ): array {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_accommodations';

		if ( $cohort_id ) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table}
					WHERE user_id = %d AND status = 'approved' AND (scope_type = 'global' OR (scope_type = 'cohort' AND scope_id = %d))",
					$user_id,
					$cohort_id
				)
			);
		} else {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE user_id = %d AND status = 'approved'",
					$user_id
				)
			);
		}

		return ! empty( $results ) ? $results : array();
	}

	/**
	 * Check if a student has a specific approved accommodation resource.
	 *
	 * @param int      $user_id
	 * @param string   $resource_type
	 * @param int|null $cohort_id
	 * @return bool
	 */
	public static function has_resource( int $user_id, string $resource_type, ?int $cohort_id = null ): bool {
		$accommodations = self::get_student_accommodations( $user_id, $cohort_id );
		foreach ( $accommodations as $acc ) {
			if ( $acc->resource_type === $resource_type ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * List all accommodation requests for coordinator view.
	 *
	 * @param string $status requested|approved|rejected|all
	 * @return array
	 */
	public static function list_requests( string $status = 'all' ): array {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_accommodations';

		if ( 'all' === $status ) {
			$results = $wpdb->get_results(
				"SELECT a.*, u.display_name, u.user_email
				FROM {$table} a
				INNER JOIN {$wpdb->users} u ON a.user_id = u.ID
				ORDER BY a.created_at DESC"
			);
		} else {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT a.*, u.display_name, u.user_email
					FROM {$table} a
					INNER JOIN {$wpdb->users} u ON a.user_id = u.ID
					WHERE a.status = %s
					ORDER BY a.created_at DESC",
					$status
				)
			);
		}

		return ! empty( $results ) ? $results : array();
	}
}
