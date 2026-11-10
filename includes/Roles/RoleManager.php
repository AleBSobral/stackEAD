<?php
namespace StackEAD\Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages roles and capabilities registration for StackEAD Core.
 */
class RoleManager {

	/**
	 * Register all StackEAD roles and capabilities.
	 */
	public static function register_roles(): void {
		// 1. Aluno / Estudante EAD
		add_role(
			'stackead_student',
			__( 'Estudante EAD', 'stackead-core' ),
			array(
				'read'                       => true,
				'stackead_learn'             => true,
				'stackead_submit_assignment' => true,
				'stackead_take_quiz'         => true,
				'stackead_request_support'   => true,
			)
		);

		// 2. Mestre / Instrutor EAD
		$master_caps = array(
			'read'                       => true,
			'upload_files'               => true,
			'stackead_teach_cohort'      => true,
			'stackead_record_attendance' => true,
			'stackead_grade_assignments' => true,
			'stackead_moderate_forum'    => true,
			'edit_stackead_lessons'      => true,
			'stackead_learn'             => true,
			'stackead_submit_assignment' => true,
			'stackead_take_quiz'         => true,
			'stackead_request_support'   => true,
		);
		add_role(
			'stackead_master',
			__( 'Professor / Mestre EAD', 'stackead-core' ),
			$master_caps
		);
		$master_role = get_role( 'stackead_master' );
		if ( $master_role ) {
			foreach ( $master_caps as $cap => $grant ) {
				if ( $grant ) {
					$master_role->add_cap( $cap );
				}
			}
		}

		// 3. Coordenador Pedagógico EAD
		$coord_caps = array(
			'read'                           => true,
			'upload_files'                   => true,
			'stackead_manage_courses'        => true,
			'stackead_manage_cohorts'        => true,
			'stackead_enroll_students'       => true,
			'stackead_manage_accommodations' => true,
			'stackead_issue_certificates'    => true,
			'stackead_view_reports'          => true,
			'stackead_teach_cohort'          => true,
			'stackead_record_attendance'     => true,
			'stackead_grade_assignments'     => true,
			'stackead_moderate_forum'        => true,
			'stackead_learn'                 => true,
			'stackead_submit_assignment'     => true,
			'stackead_take_quiz'             => true,
			'stackead_request_support'       => true,
			'edit_stackead_courses'          => true,
			'edit_stackead_cohorts'          => true,
			'edit_stackead_paths'            => true,
			'edit_stackead_modules'          => true,
			'edit_stackead_lessons'          => true,
		);
		add_role(
			'stackead_coordinator',
			__( 'Coordenação Pedagógica EAD', 'stackead-core' ),
			$coord_caps
		);
		$coord_role = get_role( 'stackead_coordinator' );
		if ( $coord_role ) {
			foreach ( $coord_caps as $cap => $grant ) {
				if ( $grant ) {
					$coord_role->add_cap( $cap );
				}
			}
		}

		// 4. Conceder permissões totais ao Administrador
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$caps = array(
				'stackead_manage_courses',
				'stackead_manage_cohorts',
				'stackead_enroll_students',
				'stackead_manage_accommodations',
				'stackead_issue_certificates',
				'stackead_view_reports',
				'stackead_teach_cohort',
				'stackead_record_attendance',
				'stackead_grade_assignments',
				'stackead_moderate_forum',
				'stackead_learn',
				'stackead_submit_assignment',
				'stackead_take_quiz',
				'stackead_request_support',
				'stackead_manage_finance',
			);
			foreach ( $caps as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	/**
	 * Assign multiple global roles to a user cleanly, preserving priority order:
	 * administrator > stackead_coordinator > stackead_master > stackead_student.
	 *
	 * @param int   $user_id
	 * @param array $roles
	 * @param bool  $can_assign_admin Whether the actor is allowed to grant/revoke administrator.
	 * @return array Final list of assigned roles.
	 */
	public static function set_user_roles( int $user_id, array $roles, bool $can_assign_admin = false ): array {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return array();
		}

		$priority_order = array(
			'administrator',
			'stackead_coordinator',
			'stackead_master',
			'stackead_student',
			'subscriber',
		);

		$requested = array_map( 'sanitize_text_field', $roles );
		$was_admin = in_array( 'administrator', (array) $user->roles, true );

		// Filter to valid roles in priority order
		$ordered_roles = array();
		foreach ( $priority_order as $valid_role ) {
			if ( in_array( $valid_role, $requested, true ) ) {
				if ( 'administrator' === $valid_role && ! $can_assign_admin && ! $was_admin ) {
					continue;
				}
				$ordered_roles[] = $valid_role;
			}
		}

		// Protect primary admin (ID 1) or existing admin if actor is not admin
		if ( ( 1 === $user_id || ( $was_admin && ! $can_assign_admin ) ) && ! in_array( 'administrator', $ordered_roles, true ) ) {
			array_unshift( $ordered_roles, 'administrator' );
		}

		if ( empty( $ordered_roles ) ) {
			$ordered_roles = array( 'stackead_student' );
		}

		// Remove all current roles
		foreach ( (array) $user->roles as $existing_role ) {
			$user->remove_role( $existing_role );
		}

		// Add all selected roles in priority order
		foreach ( $ordered_roles as $r ) {
			$user->add_role( $r );
		}

		clean_user_cache( $user_id );
		return $ordered_roles;
	}

	/**
	 * Remove roles and capabilities on uninstallation.
	 */
	public static function remove_roles(): void {
		remove_role( 'stackead_student' );
		remove_role( 'stackead_master' );
		remove_role( 'stackead_coordinator' );
	}
}
