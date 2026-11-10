<?php
namespace StackEAD\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles database tables creation, updates and migrations for StackEAD Core.
 */
class Database {

	public const DB_VERSION = '1.0.1';

	/**
	 * Run migrations on plugin activation or update.
	 */
	public static function migrate(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// 1. Enrollments table
		$sql_enrollments = "CREATE TABLE {$wpdb->prefix}stackead_enrollments (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_id BIGINT UNSIGNED NOT NULL,
			cohort_id BIGINT UNSIGNED NOT NULL,
			path_id BIGINT UNSIGNED NULL,
			status VARCHAR(30) DEFAULT 'active' NOT NULL,
			enrolled_at DATETIME NOT NULL,
			completed_at DATETIME NULL,
			notes TEXT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			UNIQUE KEY uq_user_cohort (user_id, cohort_id),
			KEY idx_cohort_status (cohort_id, status),
			KEY idx_user_status (user_id, status)
		) {$charset_collate};";

		// 2. Progress table
		$sql_progress = "CREATE TABLE {$wpdb->prefix}stackead_progress (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_id BIGINT UNSIGNED NOT NULL,
			lesson_id BIGINT UNSIGNED NOT NULL,
			cohort_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			completion_percent DECIMAL(5,2) DEFAULT 0.00 NOT NULL,
			seconds_watched INT UNSIGNED DEFAULT 0 NOT NULL,
			completed_at DATETIME NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			UNIQUE KEY uq_user_lesson_cohort (user_id, lesson_id, cohort_id),
			KEY idx_user_progress (user_id, completed_at),
			KEY idx_cohort_lesson (cohort_id, lesson_id)
		) {$charset_collate};";

		// 3. Attendances table
		$sql_attendances = "CREATE TABLE {$wpdb->prefix}stackead_attendances (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			session_id BIGINT UNSIGNED NOT NULL,
			user_id BIGINT UNSIGNED NOT NULL,
			cohort_id BIGINT UNSIGNED NOT NULL,
			status VARCHAR(20) DEFAULT 'present' NOT NULL,
			minutes_attended INT UNSIGNED DEFAULT 0 NOT NULL,
			source VARCHAR(30) DEFAULT 'manual' NOT NULL,
			recorded_by BIGINT UNSIGNED NULL,
			note TEXT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			UNIQUE KEY uq_session_user (session_id, user_id),
			KEY idx_user_attendance (user_id, status)
		) {$charset_collate};";

		// 4. Submissions table
		$sql_submissions = "CREATE TABLE {$wpdb->prefix}stackead_submissions (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			assignment_id BIGINT UNSIGNED NOT NULL,
			user_id BIGINT UNSIGNED NOT NULL,
			cohort_id BIGINT UNSIGNED NOT NULL,
			content_text LONGTEXT NULL,
			content_url VARCHAR(500) NULL,
			attachment_ids TEXT NULL,
			status VARCHAR(30) DEFAULT 'submitted' NOT NULL,
			feedback LONGTEXT NULL,
			score DECIMAL(5,2) NULL,
			rubric_scores JSON NULL,
			reviewed_by BIGINT UNSIGNED NULL,
			reviewed_at DATETIME NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			UNIQUE KEY uq_assignment_user_cohort (assignment_id, user_id, cohort_id),
			KEY idx_status_review (status, reviewed_at)
		) {$charset_collate};";

		// 5. Quiz Attempts table
		$sql_quiz_attempts = "CREATE TABLE {$wpdb->prefix}stackead_quiz_attempts (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			quiz_id BIGINT UNSIGNED NOT NULL,
			user_id BIGINT UNSIGNED NOT NULL,
			cohort_id BIGINT UNSIGNED NOT NULL,
			score DECIMAL(5,2) DEFAULT 0.00 NOT NULL,
			passed TINYINT(1) DEFAULT 0 NOT NULL,
			attempt_number INT UNSIGNED DEFAULT 1 NOT NULL,
			answers_payload JSON NULL,
			draft_payload JSON NULL,
			is_paused TINYINT(1) DEFAULT 0 NOT NULL,
			completed_at DATETIME NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			KEY idx_user_quiz (user_id, quiz_id),
			KEY idx_user_cohort_quiz (user_id, cohort_id, quiz_id),
			KEY idx_passed (passed)
		) {$charset_collate};";

		// 6. Accommodations table (COGA / Neurodivergence)
		$sql_accommodations = "CREATE TABLE {$wpdb->prefix}stackead_accommodations (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_id BIGINT UNSIGNED NOT NULL,
			scope_type VARCHAR(50) DEFAULT 'global' NOT NULL,
			scope_id BIGINT UNSIGNED NULL,
			resource_type VARCHAR(80) NOT NULL,
			instructions TEXT NOT NULL,
			status VARCHAR(30) DEFAULT 'requested' NOT NULL,
			approved_by BIGINT UNSIGNED NULL,
			approved_at DATETIME NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			KEY idx_user_status (user_id, status)
		) {$charset_collate};";

		// 7. Gamification Points table
		$sql_gamification_points = "CREATE TABLE {$wpdb->prefix}stackead_gamification_points (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_id BIGINT UNSIGNED NOT NULL,
			points INT NOT NULL,
			point_type VARCHAR(50) DEFAULT 'xp' NOT NULL,
			event_action VARCHAR(80) NOT NULL,
			reference_id BIGINT UNSIGNED NULL,
			description VARCHAR(255) NULL,
			created_at DATETIME NOT NULL,
			KEY idx_user_points (user_id, point_type)
		) {$charset_collate};";

		// 8. Gamification Badges table
		$sql_gamification_badges = "CREATE TABLE {$wpdb->prefix}stackead_gamification_badges (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_id BIGINT UNSIGNED NOT NULL,
			badge_key VARCHAR(80) NOT NULL,
			title VARCHAR(150) NOT NULL,
			description TEXT NULL,
			icon_svg TEXT NULL,
			earned_at DATETIME NOT NULL,
			UNIQUE KEY uq_user_badge (user_id, badge_key)
		) {$charset_collate};";

		// 9. Community Discussions (Fóruns de Turma e Dúvidas de Aula)
		$sql_discussions = "CREATE TABLE {$wpdb->prefix}stackead_discussions (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			parent_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			cohort_id BIGINT UNSIGNED NOT NULL,
			lesson_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			user_id BIGINT UNSIGNED NOT NULL,
			title VARCHAR(255) NULL,
			content LONGTEXT NOT NULL,
			is_pinned TINYINT(1) DEFAULT 0 NOT NULL,
			is_resolved TINYINT(1) DEFAULT 0 NOT NULL,
			upvotes INT DEFAULT 0 NOT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			KEY idx_cohort_lesson (cohort_id, lesson_id),
			KEY idx_parent_disc (parent_id),
			KEY idx_user_disc (user_id)
		) {$charset_collate};";

		// 10. Orders & Subscriptions (Módulo Financeiro)
		$sql_orders = "CREATE TABLE {$wpdb->prefix}stackead_orders (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_id BIGINT UNSIGNED NOT NULL,
			cohort_id BIGINT UNSIGNED NULL,
			plan_key VARCHAR(80) NOT NULL,
			amount DECIMAL(10,2) NOT NULL,
			currency VARCHAR(10) DEFAULT 'BRL' NOT NULL,
			status VARCHAR(30) DEFAULT 'pending' NOT NULL,
			payment_method VARCHAR(50) DEFAULT 'pix' NOT NULL,
			transaction_id VARCHAR(150) NULL,
			expires_at DATETIME NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			KEY idx_user_orders (user_id, status),
			KEY idx_transaction (transaction_id)
		) {$charset_collate};";

		// 11. Leads & Manifestações de Interesse (Landing Page CRM)
		$sql_leads = "CREATE TABLE {$wpdb->prefix}stackead_leads (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			course_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			name VARCHAR(150) NOT NULL,
			email VARCHAR(180) NOT NULL,
			phone VARCHAR(50) NULL,
			city VARCHAR(120) NULL,
			profile VARCHAR(100) NULL,
			referral VARCHAR(150) NULL,
			status VARCHAR(30) DEFAULT 'new' NOT NULL,
			notes TEXT NULL,
			ip_address VARCHAR(45) NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			KEY idx_course_status (course_id, status),
			KEY idx_created_at (created_at)
		) {$charset_collate};";

		// 12. Quiz Questions table
		$sql_quiz_questions = "CREATE TABLE {$wpdb->prefix}stackead_quiz_questions (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			quiz_id BIGINT UNSIGNED NOT NULL,
			lesson_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
			question_text LONGTEXT NOT NULL,
			question_type VARCHAR(40) DEFAULT 'single_choice' NOT NULL,
			options_json LONGTEXT NOT NULL,
			correct_answer VARCHAR(100) NOT NULL,
			explanation TEXT NULL,
			points DECIMAL(5,2) DEFAULT 10.00 NOT NULL,
			menu_order INT DEFAULT 0 NOT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			KEY idx_quiz_questions (quiz_id),
			KEY idx_lesson_questions (lesson_id)
		) {$charset_collate};";

		// 13. Cohort Members (Contextual Roles) table
		$sql_cohort_members = "CREATE TABLE {$wpdb->prefix}stackead_cohort_members (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			cohort_id BIGINT UNSIGNED NOT NULL,
			user_id BIGINT UNSIGNED NOT NULL,
			role VARCHAR(40) DEFAULT 'student' NOT NULL,
			status VARCHAR(30) DEFAULT 'active' NOT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			UNIQUE KEY uq_cohort_user_role (cohort_id, user_id, role),
			KEY idx_user_cohort_role (user_id, role)
		) {$charset_collate};";

		dbDelta( $sql_enrollments );
		dbDelta( $sql_progress );
		dbDelta( $sql_attendances );
		dbDelta( $sql_submissions );
		dbDelta( $sql_quiz_attempts );
		dbDelta( $sql_accommodations );
		dbDelta( $sql_gamification_points );
		dbDelta( $sql_gamification_badges );
		dbDelta( $sql_discussions );
		dbDelta( $sql_orders );
		dbDelta( $sql_leads );
		dbDelta( $sql_quiz_questions );
		dbDelta( $sql_cohort_members );

		// Clean up previous redundant index if exists
		self::cleanup_legacy_indices();

		update_option( 'stackead_db_version', self::DB_VERSION );
	}

	/**
	 * Drops obsolete legacy indexes after dbDelta upgrade.
	 */
	private static function cleanup_legacy_indices(): void {
		global $wpdb;

		$submissions_table = $wpdb->prefix . 'stackead_submissions';
		$indices = $wpdb->get_results( "SHOW INDEX FROM `{$submissions_table}`" );
		if ( ! empty( $indices ) ) {
			foreach ( $indices as $index ) {
				if ( 'uq_assignment_user' === $index->Key_name ) {
					$wpdb->query( "ALTER TABLE `{$submissions_table}` DROP INDEX `uq_assignment_user`" );
					break;
				}
			}
		}
	}
}
