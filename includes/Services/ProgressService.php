<?php
namespace StackEAD\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles lesson progress tracking, sequential locks, and cohort completion percentages.
 */
class ProgressService {

	/**
	 * Update progress for a lesson.
	 *
	 * @param int   $user_id
	 * @param int   $lesson_id
	 * @param int   $cohort_id
	 * @param float $percent
	 * @param int   $seconds_watched
	 * @param bool  $force_complete
	 * @return bool
	 */
	public static function record_progress( int $user_id, int $lesson_id, int $cohort_id, float $percent, int $seconds_watched, bool $force_complete = false ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_progress';
		$now   = current_time( 'mysql' );

		// Check existing
		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, completion_percent, completed_at FROM {$table} WHERE user_id = %d AND lesson_id = %d AND cohort_id = %d",
				$user_id,
				$lesson_id,
				$cohort_id
			)
		);

		$new_percent = max( $percent, $existing ? (float) $existing->completion_percent : 0.0 );
		if ( $force_complete ) {
			$new_percent = 100.0;
		}

		$completed_at = null;
		if ( $new_percent >= 90.0 || $force_complete ) {
			$completed_at = ( $existing && $existing->completed_at ) ? $existing->completed_at : $now;
		}

		$is_first_completion = false;

		if ( $existing ) {
			if ( ! $existing->completed_at && $completed_at ) {
				$is_first_completion = true;
			}

			$wpdb->update(
				$table,
				array(
					'completion_percent' => min( 100.0, $new_percent ),
					'seconds_watched'    => $seconds_watched,
					'completed_at'       => $completed_at,
					'updated_at'         => $now,
				),
				array( 'id' => $existing->id ),
				array( '%f', '%d', '%s', '%s' ),
				array( '%d' )
			);
		} else {
			if ( $completed_at ) {
				$is_first_completion = true;
			}

			$wpdb->insert(
				$table,
				array(
					'user_id'            => $user_id,
					'lesson_id'          => $lesson_id,
					'cohort_id'          => $cohort_id,
					'completion_percent' => min( 100.0, $new_percent ),
					'seconds_watched'    => $seconds_watched,
					'completed_at'       => $completed_at,
					'created_at'         => $now,
					'updated_at'         => $now,
				),
				array( '%d', '%d', '%d', '%f', '%d', '%s', '%s', '%s' )
			);
		}

		// Trigger gamification if newly completed
		if ( $is_first_completion ) {
			do_action( 'stackead_lesson_completed', $user_id, $lesson_id, $cohort_id );
		}

		return true;
	}

	/**
	 * Check if a lesson is locked due to sequential progression rules.
	 *
	 * @param int $user_id
	 * @param int $lesson_id
	 * @param int $cohort_id
	 * @return array ['is_locked' => bool, 'reason' => string]
	 */
	public static function check_sequential_lock( int $user_id, int $lesson_id, int $cohort_id ): array {
		if ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'stackead_teach_cohort' ) ) {
			return array(
				'is_locked' => false,
				'reason'    => '',
			);
		}

		global $wpdb;
		$table = $wpdb->prefix . 'stackead_progress';

		// 1. Explicit prerequisite check on the lesson itself
		$prereq_lesson_id = (int) get_post_meta( $lesson_id, '_stackead_prerequisite_lesson_id', true );
		if ( $prereq_lesson_id > 0 ) {
			$prereq_done = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE user_id = %d AND lesson_id = %d AND cohort_id = %d AND completed_at IS NOT NULL",
					$user_id,
					$prereq_lesson_id,
					$cohort_id
				)
			);
			if ( ! $prereq_done ) {
				return array(
					'is_locked' => true,
					'reason'    => sprintf( __( 'Você precisa concluir a aula de pré-requisito "%s" primeiro.', 'stackead-core' ), get_the_title( $prereq_lesson_id ) ),
				);
			}
		}

		$module_id = wp_get_post_parent_id( $lesson_id );
		if ( ! $module_id ) {
			return array(
				'is_locked' => false,
				'reason'    => '',
			);
		}

		$path_id = wp_get_post_parent_id( $module_id );

		// 2. Inter-module sequential check: If path or cohort enforces sequence, check prior modules
		$path_sequential = $path_id ? get_post_meta( $path_id, '_stackead_sequential_progression', true ) : '';
		if ( 'yes' === $path_sequential || '1' === $path_sequential ) {
			$current_module_post = get_post( $module_id );
			$current_module_order = $current_module_post ? (int) $current_module_post->menu_order : 0;

			// Fetch prior modules in this learning path
			$prior_modules = get_posts(
				array(
					'post_type'      => 'stackead_module',
					'post_parent'    => $path_id,
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				)
			);

			foreach ( $prior_modules as $pm ) {
				if ( (int) $pm->menu_order >= $current_module_order && $pm->ID !== $module_id ) {
					continue;
				}
				if ( $pm->ID === $module_id ) {
					break; // Reached current module
				}

				// Check all lessons of this prior module
				$pm_lessons = get_posts(
					array(
						'post_type'      => 'stackead_lesson',
						'post_parent'    => $pm->ID,
						'posts_per_page' => -1,
						'fields'         => 'ids',
					)
				);

				if ( ! empty( $pm_lessons ) ) {
					$placeholders = implode( ',', array_fill( 0, count( $pm_lessons ), '%d' ) );
					$completed_count = (int) $wpdb->get_var(
						$wpdb->prepare(
							"SELECT COUNT(DISTINCT lesson_id) FROM {$table}
							WHERE user_id = %d AND cohort_id = %d AND lesson_id IN ($placeholders) AND completed_at IS NOT NULL",
							array_merge( array( $user_id, $cohort_id ), $pm_lessons )
						)
					);

					if ( $completed_count < count( $pm_lessons ) ) {
						return array(
							'is_locked' => true,
							'reason'    => sprintf( __( 'Você precisa concluir todas as aulas do módulo "%s" antes de avançar.', 'stackead-core' ), $pm->post_title ),
						);
					}
				}
			}
		}

		// 3. Intra-module sequential lock: check previous lessons in this module
		$is_locked = get_post_meta( $module_id, '_stackead_sequential_lock', true );
		if ( 'yes' === $is_locked || '1' === $is_locked ) {
			$siblings = get_posts(
				array(
					'post_type'      => 'stackead_lesson',
					'post_parent'    => $module_id,
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'fields'         => 'ids',
				)
			);

			foreach ( $siblings as $sibling_id ) {
				if ( (int) $sibling_id === (int) $lesson_id ) {
					break; // Stop at current lesson
				}

				$completed = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT id FROM {$table} WHERE user_id = %d AND lesson_id = %d AND cohort_id = %d AND completed_at IS NOT NULL",
						$user_id,
						$sibling_id,
						$cohort_id
					)
				);

				if ( ! $completed ) {
					$prev_title = get_the_title( $sibling_id );
					return array(
						'is_locked' => true,
						'reason'    => sprintf( __( 'Você precisa concluir a aula "%s" antes de acessar esta aula.', 'stackead-core' ), $prev_title ),
					);
				}
			}
		}

		return array(
			'is_locked' => false,
			'reason'    => '',
		);
	}

	/**
	 * Calculate student's overall progress across all lessons in a cohort.
	 *
	 * @param int $user_id
	 * @param int $cohort_id
	 * @return float Percent from 0.0 to 100.0
	 */
	public static function calculate_cohort_progress( int $user_id, int $cohort_id ): float {
		$path_id = get_post_meta( $cohort_id, '_stackead_path_id', true );
		if ( ! $path_id ) {
			return 0.0;
		}

		// Get all lessons under path
		$modules = get_posts(
			array(
				'post_type'      => 'stackead_module',
				'post_parent'    => $path_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		if ( empty( $modules ) ) {
			return 0.0;
		}

		$lessons = get_posts(
			array(
				'post_type'      => 'stackead_lesson',
				'post_parent__in'=> $modules,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$total = count( $lessons );
		if ( 0 === $total ) {
			return 100.0;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'stackead_progress';
		$placeholders = implode( ',', array_fill( 0, $total, '%d' ) );

		$completed_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT lesson_id) FROM {$table} 
				WHERE user_id = %d AND cohort_id = %d AND lesson_id IN ($placeholders) AND completed_at IS NOT NULL",
				array_merge( array( $user_id, $cohort_id ), $lessons )
			)
		);

		return round( ( $completed_count / $total ) * 100.0, 2 );
	}
}
