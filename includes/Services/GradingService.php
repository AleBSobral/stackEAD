<?php
namespace StackEAD\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles practical assignment submissions, rubric grading and quiz attempts.
 */
class GradingService {

	/**
	 * Submit practical assignment.
	 *
	 * @param int         $assignment_id
	 * @param int         $user_id
	 * @param int         $cohort_id
	 * @param string|null $content_text
	 * @param string|null $content_url
	 * @param string|null $attachment_ids
	 * @return int|false
	 */
	public static function submit_assignment(
		int $assignment_id,
		int $user_id,
		int $cohort_id,
		?string $content_text,
		?string $content_url,
		?string $attachment_ids = null
	) {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_submissions';
		$now   = current_time( 'mysql' );

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE assignment_id = %d AND user_id = %d",
				$assignment_id,
				$user_id
			)
		);

		if ( $existing ) {
			$wpdb->update(
				$table,
				array(
					'cohort_id'      => $cohort_id,
					'content_text'   => $content_text,
					'content_url'    => $content_url,
					'attachment_ids' => $attachment_ids,
					'status'         => 'submitted',
					'updated_at'     => $now,
				),
				array( 'id' => $existing ),
				array( '%d', '%s', '%s', '%s', '%s', '%s' ),
				array( '%d' )
			);
			return (int) $existing;
		}

		$inserted = $wpdb->insert(
			$table,
			array(
				'assignment_id'  => $assignment_id,
				'user_id'        => $user_id,
				'cohort_id'      => $cohort_id,
				'content_text'   => $content_text,
				'content_url'    => $content_url,
				'attachment_ids' => $attachment_ids,
				'status'         => 'submitted',
				'created_at'     => $now,
				'updated_at'     => $now,
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Grade a submission with rubrics.
	 *
	 * @param int         $submission_id
	 * @param float       $score
	 * @param string      $status approved|revision_requested
	 * @param string|null $feedback
	 * @param array|null  $rubric_scores
	 * @param int         $reviewer_id
	 * @return bool
	 */
	public static function grade_submission(
		int $submission_id,
		float $score,
		string $status,
		?string $feedback,
		?array $rubric_scores,
		int $reviewer_id
	): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_submissions';
		$now   = current_time( 'mysql' );

		$submission = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $submission_id )
		);

		if ( ! $submission ) {
			return false;
		}

		$updated = $wpdb->update(
			$table,
			array(
				'score'         => $score,
				'status'        => $status,
				'feedback'      => $feedback,
				'rubric_scores' => ! empty( $rubric_scores ) ? wp_json_encode( $rubric_scores ) : null,
				'reviewed_by'   => $reviewer_id,
				'reviewed_at'   => $now,
				'updated_at'    => $now,
			),
			array( 'id' => $submission_id ),
			array( '%f', '%s', '%s', '%s', '%d', '%s', '%s' ),
			array( '%d' )
		);

		if ( 'approved' === $status ) {
			do_action( 'stackead_assignment_approved', (int) $submission->user_id, (int) $submission->assignment_id, (int) $submission->cohort_id, $score );
		}

		return false !== $updated;
	}

	/**
	 * Record or update quiz attempt.
	 *
	 * @param int   $quiz_id
	 * @param int   $user_id
	 * @param int   $cohort_id
	 * @param float $score
	 * @param bool  $passed
	 * @param array $answers
	 * @param bool  $is_draft
	 * @return int Attempt ID
	 */
	public static function record_quiz_attempt(
		int $quiz_id,
		int $user_id,
		int $cohort_id,
		float $score,
		bool $passed,
		array $answers,
		bool $is_draft = false
	): int {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_quiz_attempts';
		$now   = current_time( 'mysql' );

		// Get max attempt number
		$last_attempt = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(attempt_number) FROM {$table} WHERE quiz_id = %d AND user_id = %d",
				$quiz_id,
				$user_id
			)
		);

		$attempt_number = $last_attempt > 0 ? $last_attempt + 1 : 1;

		$wpdb->insert(
			$table,
			array(
				'quiz_id'         => $quiz_id,
				'user_id'         => $user_id,
				'cohort_id'       => $cohort_id,
				'score'           => $score,
				'passed'          => $passed ? 1 : 0,
				'attempt_number'  => $attempt_number,
				'answers_payload' => ! $is_draft ? wp_json_encode( $answers ) : null,
				'draft_payload'   => $is_draft ? wp_json_encode( $answers ) : null,
				'is_paused'       => 0,
				'completed_at'    => ! $is_draft ? $now : null,
				'created_at'      => $now,
				'updated_at'      => $now,
			),
			array( '%d', '%d', '%d', '%f', '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s' )
		);

		$id = (int) $wpdb->insert_id;

		if ( ! $is_draft && $passed ) {
			do_action( 'stackead_quiz_passed', $user_id, $quiz_id, $cohort_id, $score );
		}

		return $id;
	}

	/**
	 * Calculate average quiz grade for student in cohort.
	 *
	 * @param int $user_id
	 * @param int $cohort_id
	 * @return float
	 */
	public static function calculate_quiz_average( int $user_id, int $cohort_id ): float {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_quiz_attempts';

		// Get best score for each unique quiz in this cohort
		$scores = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT MAX(score) FROM {$table}
				WHERE user_id = %d AND cohort_id = %d AND completed_at IS NOT NULL
				GROUP BY quiz_id",
				$user_id,
				$cohort_id
			)
		);

		if ( empty( $scores ) ) {
			return 100.0; // If no quizzes required in cohort, requirement passes
		}

		$avg = array_sum( $scores ) / count( $scores );
		return round( (float) $avg, 2 );
	}

	/**
	 * Verify if all required practical assignments for cohort have been approved.
	 *
	 * @param int $user_id
	 * @param int $cohort_id
	 * @return bool
	 */
	public static function are_assignments_approved( int $user_id, int $cohort_id ): bool {
		$assignments = get_posts(
			array(
				'post_type'      => 'stackead_assignment',
				'meta_key'       => '_stackead_cohort_id',
				'meta_value'     => $cohort_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		if ( empty( $assignments ) ) {
			return true; // No mandatory assignments
		}

		global $wpdb;
		$table = $wpdb->prefix . 'stackead_submissions';
		$placeholders = implode( ',', array_fill( 0, count( $assignments ), '%d' ) );

		$approved_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT assignment_id) FROM {$table}
				WHERE user_id = %d AND cohort_id = %d AND assignment_id IN ($placeholders) AND status = 'approved'",
				array_merge( array( $user_id, $cohort_id ), $assignments )
			)
		);

		return $approved_count === count( $assignments );
	}
}
