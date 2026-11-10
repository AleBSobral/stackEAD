<?php
namespace StackEAD\Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles scope segregation and data security for Masters and Students.
 */
class ScopeFilter {

	/**
	 * Register scope hooks.
	 */
	public static function init(): void {
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_master_query' ) );
	}

	/**
	 * Restrict Master queries strictly to their assigned cohorts and sessions.
	 *
	 * @param \WP_Query $query
	 */
	public static function filter_master_query( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->post_type, array( 'stackead_cohort', 'stackead_session' ), true ) ) {
			return;
		}

		$user = wp_get_current_user();
		if ( in_array( 'stackead_master', (array) $user->roles, true ) && ! current_user_can( 'manage_options' ) ) {
			$assigned_cohort_ids = self::get_master_cohort_ids( $user->ID );

			if ( 'stackead_cohort' === $screen->post_type ) {
				$query->set( 'post__in', ! empty( $assigned_cohort_ids ) ? $assigned_cohort_ids : array( 0 ) );
			} elseif ( 'stackead_session' === $screen->post_type ) {
				$query->set(
					'meta_query',
					array(
						array(
							'key'     => '_stackead_cohort_id',
							'value'   => ! empty( $assigned_cohort_ids ) ? $assigned_cohort_ids : array( 0 ),
							'compare' => 'IN',
						),
					)
				);
			}
		}
	}

	/**
	 * Retrieve cohort IDs assigned to a specific Master user.
	 * Checks _stackead_master_ids postmeta, parent course _stackead_instructor_ids, and contextual wp_stackead_cohort_members.
	 *
	 * @param int $master_id
	 * @return array
	 */
	public static function get_master_cohort_ids( int $master_id ): array {
		global $wpdb;

		// 1. Direct Cohort Postmeta assignment (_stackead_master_ids)
		$cohorts = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_stackead_master_ids' AND (meta_value = %d OR meta_value LIKE %s OR meta_value LIKE %s OR meta_value LIKE %s)",
				$master_id,
				'%,' . $master_id . ',%',
				$master_id . ',%',
				'%,' . $master_id
			)
		);

		// 2. Parent Course Instructor assignment (_stackead_instructor_ids -> cohorts with _stackead_course_id)
		$instructor_courses = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_stackead_instructor_ids' AND (meta_value = %d OR meta_value LIKE %s OR meta_value LIKE %s OR meta_value LIKE %s)",
				$master_id,
				'%,' . $master_id . ',%',
				$master_id . ',%',
				'%,' . $master_id
			)
		);
		$course_cohorts = array();
		if ( ! empty( $instructor_courses ) ) {
			$course_ids_in  = implode( ',', array_map( 'intval', $instructor_courses ) );
			$course_cohorts = $wpdb->get_col(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_stackead_course_id' AND meta_value IN ({$course_ids_in})"
			);
		}

		// 3. Contextual cohort members table
		$table_members      = $wpdb->prefix . 'stackead_cohort_members';
		$contextual_cohorts = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT cohort_id FROM {$table_members} WHERE user_id = %d AND role IN ('master', 'facilitador', 'tutor', 'professor', 'coordinator') AND status = 'active'",
				$master_id
			)
		);

		$all = array_unique( array_merge(
			! empty( $cohorts ) ? array_map( 'intval', $cohorts ) : array(),
			! empty( $course_cohorts ) ? array_map( 'intval', $course_cohorts ) : array(),
			! empty( $contextual_cohorts ) ? array_map( 'intval', $contextual_cohorts ) : array()
		) );

		return array_values( $all );
	}

	/**
	 * Determine if a user can manage (view submissions, attendance, grade) a specific cohort.
	 * Checks admin/coordinator status, master assignment, and contextual cohort roles.
	 *
	 * @param int $user_id
	 * @param int $cohort_id
	 * @return bool
	 */
	public static function can_user_manage_cohort( int $user_id, int $cohort_id ): bool {
		if ( ! $user_id || ! $cohort_id ) {
			return false;
		}

		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return false;
		}

		$roles = (array) $user->roles;
		if ( in_array( 'stackead_coordinator', $roles, true ) ) {
			return true;
		}

		// Check contextual cohort membership
		global $wpdb;
		$table_members = $wpdb->prefix . 'stackead_cohort_members';
		$contextual_role = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT role FROM {$table_members} WHERE user_id = %d AND cohort_id = %d AND status = 'active'",
				$user_id,
				$cohort_id
			)
		);

		if ( $contextual_role && in_array( strtolower( $contextual_role ), array( 'master', 'facilitador', 'tutor', 'professor', 'coordinator' ), true ) ) {
			return true;
		}

		if ( in_array( 'stackead_master', $roles, true ) ) {
			$assigned = self::get_master_cohort_ids( $user_id );
			return in_array( $cohort_id, $assigned, true );
		}

		return false;
	}

	/**
	 * Determine if a user can access a cohort (as enrolled student, assigned master, coordinator, or admin).
	 *
	 * @param int $user_id
	 * @param int $cohort_id
	 * @return bool
	 */
	public static function can_user_access_cohort( int $user_id, int $cohort_id ): bool {
		if ( self::can_user_manage_cohort( $user_id, $cohort_id ) ) {
			return true;
		}

		// Check contextual cohort members table
		global $wpdb;
		$table_members = $wpdb->prefix . 'stackead_cohort_members';
		$has_active_member = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table_members} WHERE user_id = %d AND cohort_id = %d AND status = 'active'",
				$user_id,
				$cohort_id
			)
		);

		if ( $has_active_member ) {
			return true;
		}

		return \StackEAD\Services\EnrollmentService::is_enrolled( $user_id, $cohort_id );
	}

	/**
	 * Get contextual role of a user in a specific cohort.
	 * Respects explicit cohort memberships first so a Professor can be a Student in another cohort, etc.
	 *
	 * @param int $user_id
	 * @param int $cohort_id
	 * @return string student|master|coordinator|administrator
	 */
	public static function get_user_cohort_role( int $user_id, int $cohort_id ): string {
		global $wpdb;
		$table_members = $wpdb->prefix . 'stackead_cohort_members';
		$role          = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT role FROM {$table_members} WHERE user_id = %d AND cohort_id = %d AND status = 'active'",
				$user_id,
				$cohort_id
			)
		);

		if ( $role ) {
			return strtolower( $role );
		}

		// Check if user is assigned as Master/Instructor on this cohort or its parent course
		$master_cohorts = self::get_master_cohort_ids( $user_id );
		if ( in_array( (int) $cohort_id, $master_cohorts, true ) ) {
			return 'master';
		}

		// If not a master of this cohort, check if enrolled as a student (before falling back to global master role)
		if ( ! user_can( $user_id, 'manage_options' ) ) {
			$user  = get_userdata( $user_id );
			$roles = $user ? (array) $user->roles : array();
			if ( ! in_array( 'stackead_coordinator', $roles, true ) && \StackEAD\Services\EnrollmentService::is_enrolled( $user_id, $cohort_id ) ) {
				return 'student';
			}
		}

		if ( user_can( $user_id, 'manage_options' ) ) {
			return 'administrator';
		}

		$user = get_userdata( $user_id );
		if ( $user ) {
			$roles = (array) $user->roles;
			if ( in_array( 'stackead_coordinator', $roles, true ) ) {
				return 'coordinator';
			}
			if ( in_array( 'stackead_master', $roles, true ) ) {
				return 'master';
			}
		}

		return 'student';
	}
}
