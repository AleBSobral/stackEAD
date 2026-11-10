<?php
namespace StackEAD\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles cohort community discussions, lesson-level Q&A, and voting.
 */
class CommunityService {

	/**
	 * Create a new discussion topic or reply.
	 *
	 * @param int      $cohort_id
	 * @param int      $user_id
	 * @param string   $content
	 * @param string|null $title
	 * @param int      $lesson_id
	 * @param int      $parent_id
	 * @return int|false
	 */
	public static function post_message(
		int $cohort_id,
		int $user_id,
		string $content,
		?string $title = null,
		int $lesson_id = 0,
		int $parent_id = 0
	) {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_discussions';
		$now   = current_time( 'mysql' );

		$inserted = $wpdb->insert(
			$table,
			array(
				'cohort_id'   => $cohort_id,
				'lesson_id'   => $lesson_id,
				'parent_id'   => $parent_id,
				'user_id'     => $user_id,
				'title'       => $title ? sanitize_text_field( $title ) : null,
				'content'     => wp_kses_post( $content ),
				'is_pinned'   => 0,
				'is_resolved' => 0,
				'upvotes'     => 0,
				'created_at'  => $now,
				'updated_at'  => $now,
			),
			array( '%d', '%d', '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Get discussions for a cohort or specific lesson.
	 *
	 * @param int $cohort_id
	 * @param int $lesson_id If 0, gets cohort-wide topics
	 * @param int $limit
	 * @return array
	 */
	public static function get_topics( int $cohort_id, int $lesson_id = 0, int $limit = 30 ): array {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_discussions';

		if ( $lesson_id > 0 ) {
			$topics = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT d.*, u.display_name, u.user_email
					FROM {$table} d
					INNER JOIN {$wpdb->users} u ON d.user_id = u.ID
					WHERE d.cohort_id = %d AND d.lesson_id = %d AND d.parent_id = 0
					ORDER BY d.is_pinned DESC, d.created_at DESC
					LIMIT %d",
					$cohort_id,
					$lesson_id,
					$limit
				)
			);
		} else {
			$topics = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT d.*, u.display_name, u.user_email
					FROM {$table} d
					INNER JOIN {$wpdb->users} u ON d.user_id = u.ID
					WHERE d.cohort_id = %d AND d.parent_id = 0
					ORDER BY d.is_pinned DESC, d.created_at DESC
					LIMIT %d",
					$cohort_id,
					$limit
				)
			);
		}

		if ( empty( $topics ) ) {
			return array();
		}

		// Attach replies count and top replies
		foreach ( $topics as &$topic ) {
			$topic->replies_count = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE parent_id = %d", $topic->id )
			);

			$topic->replies = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT d.*, u.display_name, u.user_email
					FROM {$table} d
					INNER JOIN {$wpdb->users} u ON d.user_id = u.ID
					WHERE d.parent_id = %d
					ORDER BY d.created_at ASC",
					$topic->id
				)
			);
		}

		return $topics;
	}

	/**
	 * Upvote a discussion or reply.
	 *
	 * @param int $discussion_id
	 * @return int New upvote count
	 */
	public static function upvote( int $discussion_id ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_discussions';

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET upvotes = upvotes + 1 WHERE id = %d",
				$discussion_id
			)
		);

		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT upvotes FROM {$table} WHERE id = %d", $discussion_id )
		);
	}

	/**
	 * Toggle resolved status.
	 *
	 * @param int $discussion_id
	 * @return bool
	 */
	public static function toggle_resolved( int $discussion_id ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_discussions';

		$current = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT is_resolved FROM {$table} WHERE id = %d", $discussion_id )
		);

		$new_val = ( 1 === $current ) ? 0 : 1;

		$wpdb->update(
			$table,
			array( 'is_resolved' => $new_val ),
			array( 'id' => $discussion_id ),
			array( '%d' ),
			array( '%d' )
		);

		return (bool) $new_val;
	}
}
