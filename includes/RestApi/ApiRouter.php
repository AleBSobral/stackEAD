<?php
namespace StackEAD\RestApi;

use StackEAD\Services\EnrollmentService;
use StackEAD\Services\ProgressService;
use StackEAD\Services\AttendanceService;
use StackEAD\Services\GradingService;
use StackEAD\Services\CertificateService;
use StackEAD\Services\AccommodationService;
use StackEAD\Services\GamificationService;
use StackEAD\Services\CommunityService;
use StackEAD\Services\FinanceService;
use StackEAD\Services\QuizService;
use StackEAD\Roles\ScopeFilter;
use StackEAD\Roles\AccessControl;
use StackEAD\Admin\WhiteLabelSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Router for StackEAD Core.
 * Endpoints under namespace: stackead/v1
 */
class ApiRouter {

	public const NAMESPACE = 'stackead/v1';

	/**
	 * Register all REST API routes.
	 */
	public static function init(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register routes with callbacks and permission checks.
	 */
	public static function register_routes(): void {
		// 1. Auth & Current User Context
		register_rest_route(
			self::NAMESPACE,
			'/me',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_current_user_context' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		// 2. Catalog & Courses
		register_rest_route(
			self::NAMESPACE,
			'/courses',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_courses' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/courses',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_course' ),
				'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/courses/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_single_course' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'update_course' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'update_course' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_course' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
			)
		);

		// 3. Cohorts (Turmas) Full In-App CRUD
		register_rest_route(
			self::NAMESPACE,
			'/cohorts',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_cohorts' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'create_cohort' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/cohorts/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_single_cohort' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'update_cohort' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'update_cohort' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_cohort' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
			)
		);

		// Modules In-App CRUD
		register_rest_route(
			self::NAMESPACE,
			'/modules',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_module' ),
				'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/modules/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'update_module' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'update_module' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_module' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
			)
		);

		// Lessons In-App CRUD
		register_rest_route(
			self::NAMESPACE,
			'/lessons',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_lesson' ),
				'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/lessons/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_lesson' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'update_lesson' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'update_lesson' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_lesson' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
			)
		);

		// Sessions In-App CRUD
		register_rest_route(
			self::NAMESPACE,
			'/sessions',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_session' ),
				'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/sessions/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_single_session' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'update_session' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'update_session' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_session' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
			)
		);

		// Assignments In-App CRUD
		register_rest_route(
			self::NAMESPACE,
			'/cohorts/(?P<id>\d+)/assignments',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_cohort_assignments' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/assignments',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_assignment' ),
				'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/assignments/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_single_assignment' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'update_assignment' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'update_assignment' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_assignment' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
			)
		);

		// News update/get
		register_rest_route(
			self::NAMESPACE,
			'/news/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_single_news' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'update_news' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'update_news' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
			)
		);

		// Category update/rename
		register_rest_route(
			self::NAMESPACE,
			'/categories/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'update_category' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'update_category' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
			)
		);


		// 4. Cohort Curriculum (Trilha, Módulos e Aulas)
		register_rest_route(
			self::NAMESPACE,
			'/cohorts/(?P<id>\d+)/curriculum',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_cohort_curriculum' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);



		// 6. Record Progress
		register_rest_route(
			self::NAMESPACE,
			'/progress',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'record_progress' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		// 7. Sessions & Attendance
		register_rest_route(
			self::NAMESPACE,
			'/sessions/(?P<id>\d+)/attendance',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_session_attendance' ),
				'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/sessions/(?P<id>\d+)/attendance',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_session_attendance' ),
				'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
			)
		);

		// 8. Assignments & Submissions
		register_rest_route(
			self::NAMESPACE,
			'/submissions',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'submit_assignment' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/submissions/(?P<id>\d+)/grade',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'grade_submission' ),
				'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/cohorts/(?P<id>\d+)/submissions',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_cohort_submissions' ),
				'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
			)
		);

		// 9. Quizzes (Real Engine & Evaluation)
		register_rest_route(
			self::NAMESPACE,
			'/quizzes/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_single_quiz' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_quiz' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/quizzes',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_quizzes' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_quiz' ),
					'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/quizzes/(?P<id>\d+)/submit',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'submit_quiz' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		// 10. Accommodations (COGA)
		register_rest_route(
			self::NAMESPACE,
			'/accommodations',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_accommodations' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/accommodations',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'request_accommodation' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/accommodations/(?P<id>\d+)/review',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'review_accommodation' ),
				'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
			)
		);

		// 11. Gamification
		register_rest_route(
			self::NAMESPACE,
			'/gamification/me',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_my_gamification' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/gamification/leaderboard',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_leaderboard' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		// 12. Community Discussions
		register_rest_route(
			self::NAMESPACE,
			'/cohorts/(?P<id>\d+)/discussions',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_discussions' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/cohorts/(?P<id>\d+)/discussions',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'post_discussion' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/discussions/(?P<id>\d+)/upvote',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'upvote_discussion' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		// 13. Finance & Subscriptions
		register_rest_route(
			self::NAMESPACE,
			'/finance/summary',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_finance_summary' ),
				'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/orders',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_order' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/orders/(?P<id>\d+)/simulate-pay',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'simulate_pay_order' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		// 14. Certificate Eligibility & Issuance
		register_rest_route(
			self::NAMESPACE,
			'/cohorts/(?P<id>\d+)/certificate-status',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_certificate_status' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/cohorts/(?P<id>\d+)/issue-certificate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'issue_certificate' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		// 15. White Label Settings
		register_rest_route(
			self::NAMESPACE,
			'/settings/whitelabel',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_whitelabel_settings' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_whitelabel_settings' ),
					'permission_callback' => array( __CLASS__, 'check_admin' ),
				),
			)
		);

		// 16. Dual Category Management (Courses and News)
		register_rest_route(
			self::NAMESPACE,
			'/categories',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_categories' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'create_category' ),
					'permission_callback' => array( __CLASS__, 'check_admin' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/categories/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'delete_category' ),
				'permission_callback' => array( __CLASS__, 'check_admin' ),
			)
		);

		// 17. News & Announcements
		register_rest_route(
			self::NAMESPACE,
			'/news',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_news_list' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_news' ),
					'permission_callback' => array( __CLASS__, 'check_admin' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/news/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_single_news' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_news' ),
					'permission_callback' => array( __CLASS__, 'check_admin' ),
				),
			)
		);

		// 18. Leads CRM & Landing Capture
		register_rest_route(
			self::NAMESPACE,
			'/leads',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_leads' ),
					'permission_callback' => array( __CLASS__, 'check_admin' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'capture_lead' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/leads/export-csv',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'export_leads_csv' ),
				'permission_callback' => array( __CLASS__, 'check_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/leads/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'delete_lead' ),
				'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
			)
		);

		// 18b. LGPD (Brasil) & GDPR (EU) Data Subject Rights Endpoints
		register_rest_route(
			self::NAMESPACE,
			'/privacy/my-data',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'export_my_personal_data' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/privacy/consent',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_my_privacy_consent' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_my_privacy_consent' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/privacy/request-erasure',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'request_account_erasure' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		// 19. Authentication & Password Recovery
		register_rest_route(
			self::NAMESPACE,
			'/auth/login',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'auth_login' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/auth/forgot-password',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'auth_forgot_password' ),
				'permission_callback' => '__return_true',
			)
		);

		// 20. Media Upload & WebP Optimization Pipeline (Restricted to Master/Coordinator/Admin)
		register_rest_route(
			self::NAMESPACE,
			'/media/upload',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'upload_media' ),
				'permission_callback' => array( __CLASS__, 'check_master_or_admin' ),
			)
		);

		// 21. User Avatar Upload
		register_rest_route(
			self::NAMESPACE,
			'/users/me/avatar',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'update_my_avatar' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		// 22. Modular Landing Page Builder
		register_rest_route(
			self::NAMESPACE,
			'/courses/(?P<id>\d+)/landing-builder',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_course_landing_builder' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_course_landing_builder' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
			)
		);

		
		// 23. Users & Contextual Cohort Members Management
		register_rest_route(
			self::NAMESPACE,
			'/users',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_users_list' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'create_user_admin' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/users/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'update_user_admin' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_user_admin' ),
					'permission_callback' => array( __CLASS__, 'check_admin' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/cohorts/(?P<id>\d+)/members',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_cohort_members_list' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_cohort_member' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/cohorts/(?P<id>\d+)/members/(?P<user_id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'delete_cohort_member' ),
				'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
			)
		);

		// 24. COGA Neurodiversity Preferences Persistence
		register_rest_route(
			self::NAMESPACE,
			'/accommodations/preferences',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_coga_preferences' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_coga_preferences' ),
					'permission_callback' => array( __CLASS__, 'check_logged_in' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/courses/(?P<id>\d+)/landing-builder/reset',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'reset_course_landing_builder' ),
				'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
			)
		);

		// 25. Gamification & Virtual Coins Admin
		register_rest_route(
			self::NAMESPACE,
			'/gamification/admin/settings',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_gamification_admin_settings' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'save_gamification_admin_settings' ),
					'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/gamification/admin/badges',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_gamification_admin_badge' ),
				'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/gamification/admin/badges/(?P<key>[a-zA-Z0-9_-]+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( __CLASS__, 'delete_gamification_admin_badge' ),
				'permission_callback' => array( __CLASS__, 'check_coordinator_or_admin' ),
			)
		);

		// 26. Instant Checkout & Payment Webhook
		register_rest_route(
			self::NAMESPACE,
			'/finance/checkout-instant',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'checkout_instant' ),
				'permission_callback' => array( __CLASS__, 'check_logged_in' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/finance/payment-webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'payment_webhook' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Permission check: Administrator only.
	 */
	public static function check_admin(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Permission check: Logged in.
	 */
	public static function check_logged_in(): bool {
		return is_user_logged_in();
	}

	/**
	 * Permission check: Master, Coordinator or Admin (Global or Contextual).
	 */
	public static function check_master_or_admin(): bool {
		$user = wp_get_current_user();
		if ( ! $user || ! $user->exists() ) {
			return false;
		}
		if ( current_user_can( 'manage_options' )
			|| in_array( 'stackead_master', (array) $user->roles, true )
			|| in_array( 'stackead_coordinator', (array) $user->roles, true ) ) {
			return true;
		}

		global $wpdb;
		$table_members = $wpdb->prefix . 'stackead_cohort_members';
		$has_ctx       = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table_members} WHERE user_id = %d AND role IN ('master', 'coordinator', 'professor', 'facilitador', 'tutor') AND status = 'active' LIMIT 1",
				$user->ID
			)
		);
		return ! empty( $has_ctx );
	}

	/**
	 * Permission check: Coordinator or Admin (Global or Contextual).
	 */
	public static function check_coordinator_or_admin(): bool {
		$user = wp_get_current_user();
		if ( ! $user || ! $user->exists() ) {
			return false;
		}
		if ( current_user_can( 'manage_options' )
			|| in_array( 'stackead_coordinator', (array) $user->roles, true ) ) {
			return true;
		}

		global $wpdb;
		$table_members = $wpdb->prefix . 'stackead_cohort_members';
		$has_ctx       = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table_members} WHERE user_id = %d AND role = 'coordinator' AND status = 'active' LIMIT 1",
				$user->ID
			)
		);
		return ! empty( $has_ctx );
	}

	/**
	 * GET /me - User context (supports multi-role users).
	 */
	public static function get_current_user_context(): \WP_REST_Response {
		$user  = wp_get_current_user();
		$roles = array_values( (array) $user->roles );

		// Determine highest-priority primary role for legacy compatibility
		$priority = array( 'administrator', 'stackead_coordinator', 'stackead_master', 'stackead_student' );
		$role     = ! empty( $roles ) ? $roles[0] : 'stackead_student';
		foreach ( $priority as $p_role ) {
			if ( in_array( $p_role, $roles, true ) ) {
				$role = $p_role;
				break;
			}
		}

		$gamification = GamificationService::get_user_level( $user->ID );
		$badges       = GamificationService::get_user_badges( $user->ID );
		$navigation   = AccessControl::get_user_navigation( $user );
		$role_label   = AccessControl::get_user_role_label( $user );

		return new \WP_REST_Response(
			array(
				'id'            => $user->ID,
				'name'          => $user->display_name,
				'email'         => $user->user_email,
				'avatar_url'    => get_avatar_url( $user->ID, array( 'size' => 128 ) ),
				'role'          => $role,
				'roles'         => $roles,
				'role_label'    => $role_label,
				'is_admin'      => AccessControl::is_administrator( $user ),
				'is_master'     => in_array( 'stackead_master', $roles, true ),
				'is_coord'      => in_array( 'stackead_coordinator', $roles, true ),
				'is_student'    => in_array( 'stackead_student', $roles, true ) || in_array( 'subscriber', $roles, true ),
				'navigation'    => $navigation['sections'],
				'allowed_views' => $navigation['allowed_views'],
				'gamification'  => $gamification,
				'badges'        => $badges,
				'coins'         => GamificationService::get_user_coins( $user->ID ),
				'coin_config'   => GamificationService::get_gamification_config(),
			),
			200
		);
	}

	/**
	 * GET /courses - Public catalog.
	 */
	public static function get_courses(): \WP_REST_Response {
		$posts = get_posts(
			array(
				'post_type'      => 'stackead_course',
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$items = array();
		foreach ( $posts as $p ) {
			$thumb = get_the_post_thumbnail_url( $p->ID, 'large' );
			$items[] = array(
				'id'             => $p->ID,
				'title'          => $p->post_title,
				'status'         => $p->post_status,
				'subtitle'       => get_post_meta( $p->ID, '_stackead_subtitle', true ),
				'syllabus'       => get_post_meta( $p->ID, '_stackead_syllabus', true ),
				'learning_goals' => get_post_meta( $p->ID, '_stackead_learning_goals', true ),
				'prerequisites'  => get_post_meta( $p->ID, '_stackead_prerequisites', true ),
				'excerpt'        => $p->post_excerpt,
				'description'    => $p->post_content,
				'thumbnail'      => $thumb ? $thumb : '',
				'workload'           => (int) get_post_meta( $p->ID, '_stackead_workload_hours', true ) ?: 40,
				'price_type'         => get_post_meta( $p->ID, '_stackead_price_type', true ) ?: 'free',
				'price'              => (float) get_post_meta( $p->ID, '_stackead_price', true ),
				'sale_price'         => (float) get_post_meta( $p->ID, '_stackead_sale_price', true ),
				'simplified_summary' => get_post_meta( $p->ID, '_stackead_simplified_summary', true ),
			);
		}

		return new \WP_REST_Response( $items, 200 );
	}

	/**
	 * POST /courses - Create a new course with syllabus, workload, and settings.
	 */
	public static function create_course( \WP_REST_Request $request ): \WP_REST_Response {
		$title = sanitize_text_field( (string) $request->get_param( 'title' ) );
		if ( empty( $title ) ) {
			return new \WP_REST_Response( array( 'error' => 'O título do curso é obrigatório.' ), 400 );
		}

		$description    = wp_kses_post( (string) $request->get_param( 'description' ) );
		$subtitle       = sanitize_text_field( (string) $request->get_param( 'subtitle' ) );
		$syllabus       = sanitize_textarea_field( (string) $request->get_param( 'syllabus' ) );
		$learning_goals = sanitize_textarea_field( (string) $request->get_param( 'learning_goals' ) );
		$prerequisites  = sanitize_textarea_field( (string) $request->get_param( 'prerequisites' ) );
		$workload       = (int) ( $request->get_param( 'workload_hours' ) ?: 40 );
		$price_type     = sanitize_text_field( (string) ( $request->get_param( 'price_type' ) ?: 'free' ) );
		$price          = (float) ( $request->get_param( 'price' ) ?: 0.0 );
		$coga_summary   = sanitize_textarea_field( (string) $request->get_param( 'simplified_summary' ) );

		$post_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => $description,
				'post_type'    => 'stackead_course',
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
			)
		);

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Falha ao salvar curso.' ), 500 );
		}

		update_post_meta( $post_id, '_stackead_subtitle', $subtitle );
		update_post_meta( $post_id, '_stackead_syllabus', $syllabus );
		update_post_meta( $post_id, '_stackead_learning_goals', $learning_goals );
		update_post_meta( $post_id, '_stackead_prerequisites', $prerequisites );
		update_post_meta( $post_id, '_stackead_workload_hours', $workload );
		update_post_meta( $post_id, '_stackead_price_type', $price_type );
		update_post_meta( $post_id, '_stackead_price', $price );
		update_post_meta( $post_id, '_stackead_simplified_summary', $coga_summary );

		$attachment_id = (int) $request->get_param( 'attachment_id' );
		if ( $attachment_id > 0 ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}

		// Auto create learning path and default cohort
		$path_id = wp_insert_post(
			array(
				'post_title'   => sprintf( 'Trilha: %s', $title ),
				'post_type'    => 'stackead_path',
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
			)
		);
		if ( $path_id ) {
			update_post_meta( $post_id, '_stackead_path_id', $path_id );

			// Create initial cohort
			$cohort_id = wp_insert_post(
				array(
					'post_title'   => sprintf( 'Turma 1 - %s', $title ),
					'post_type'    => 'stackead_cohort',
					'post_status'  => 'publish',
					'post_author'  => get_current_user_id(),
				)
			);
			if ( $cohort_id ) {
				update_post_meta( $cohort_id, '_stackead_course_id', $post_id );
				update_post_meta( $cohort_id, '_stackead_path_id', $path_id );
			}
		}

		return new \WP_REST_Response(
			array(
				'success'   => true,
				'course_id' => $post_id,
				'message'   => 'Curso criado com sucesso!',
			),
			201
		);
	}

	/**
	 * GET /courses/{id} - Get single course details.
	 */
	public static function get_single_course( \WP_REST_Request $request ): \WP_REST_Response {
		$course_id = (int) $request->get_param( 'id' );
		$course = get_post( $course_id );

		if ( ! $course || 'stackead_course' !== $course->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Curso não encontrado.' ), 404 );
		}

		$thumb = get_the_post_thumbnail_url( $course->ID, 'large' );
		$terms = wp_get_post_terms( $course->ID, 'stackead_category', array( 'fields' => 'ids' ) );

		return new \WP_REST_Response(
			array(
				'id'                 => $course->ID,
				'title'              => $course->post_title,
				'status'             => $course->post_status,
				'subtitle'           => get_post_meta( $course->ID, '_stackead_subtitle', true ),
				'syllabus'           => get_post_meta( $course->ID, '_stackead_syllabus', true ),
				'learning_goals'     => get_post_meta( $course->ID, '_stackead_learning_goals', true ),
				'prerequisites'      => get_post_meta( $course->ID, '_stackead_prerequisites', true ),
				'excerpt'            => $course->post_excerpt,
				'description'        => $course->post_content,
				'thumbnail'          => $thumb ? $thumb : '',
				'workload_hours'     => (int) get_post_meta( $course->ID, '_stackead_workload_hours', true ) ?: 40,
				'price_type'         => get_post_meta( $course->ID, '_stackead_price_type', true ) ?: 'free',
				'price'              => (float) get_post_meta( $course->ID, '_stackead_price', true ),
				'sale_price'         => (float) get_post_meta( $course->ID, '_stackead_sale_price', true ),
				'simplified_summary' => get_post_meta( $course->ID, '_stackead_simplified_summary', true ),
				'categories'         => is_array( $terms ) ? $terms : array(),
			),
			200
		);
	}

	/**
	 * POST /courses/{id} - Update an existing course.
	 */
	public static function update_course( \WP_REST_Request $request ): \WP_REST_Response {
		$course_id = (int) $request->get_param( 'id' );
		$course = get_post( $course_id );

		if ( ! $course || 'stackead_course' !== $course->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Curso não encontrado.' ), 404 );
		}

		$params = $request->get_json_params() ?: $request->get_params();

		$title = sanitize_text_field( (string) ( $params['title'] ?? $course->post_title ) );
		if ( empty( $title ) ) {
			return new \WP_REST_Response( array( 'error' => 'O título do curso é obrigatório.' ), 400 );
		}

		$post_data = array(
			'ID'         => $course_id,
			'post_title' => $title,
		);

		if ( isset( $params['description'] ) ) {
			$post_data['post_content'] = wp_kses_post( (string) $params['description'] );
		}
		if ( isset( $params['status'] ) ) {
			$post_data['post_status'] = sanitize_key( $params['status'] );
		}

		wp_update_post( $post_data );

		if ( isset( $params['subtitle'] ) ) {
			update_post_meta( $course_id, '_stackead_subtitle', sanitize_text_field( $params['subtitle'] ) );
		}
		if ( isset( $params['syllabus'] ) ) {
			update_post_meta( $course_id, '_stackead_syllabus', sanitize_textarea_field( $params['syllabus'] ) );
		}
		if ( isset( $params['learning_goals'] ) ) {
			update_post_meta( $course_id, '_stackead_learning_goals', sanitize_textarea_field( $params['learning_goals'] ) );
		}
		if ( isset( $params['prerequisites'] ) ) {
			update_post_meta( $course_id, '_stackead_prerequisites', sanitize_textarea_field( $params['prerequisites'] ) );
		}
		if ( isset( $params['workload_hours'] ) ) {
			update_post_meta( $course_id, '_stackead_workload_hours', (int) $params['workload_hours'] );
		}
		if ( isset( $params['price_type'] ) ) {
			update_post_meta( $course_id, '_stackead_price_type', sanitize_text_field( $params['price_type'] ) );
		}
		if ( isset( $params['price'] ) ) {
			update_post_meta( $course_id, '_stackead_price', (float) $params['price'] );
		}
		if ( isset( $params['sale_price'] ) ) {
			update_post_meta( $course_id, '_stackead_sale_price', (float) $params['sale_price'] );
		}
		if ( isset( $params['simplified_summary'] ) ) {
			update_post_meta( $course_id, '_stackead_simplified_summary', sanitize_textarea_field( $params['simplified_summary'] ) );
		}
		if ( isset( $params['attachment_id'] ) && (int) $params['attachment_id'] > 0 ) {
			set_post_thumbnail( $course_id, (int) $params['attachment_id'] );
		}
		if ( isset( $params['category_ids'] ) && is_array( $params['category_ids'] ) ) {
			wp_set_post_terms( $course_id, array_map( 'intval', $params['category_ids'] ), 'stackead_category' );
		}

		return new \WP_REST_Response(
			array(
				'success'   => true,
				'message'   => 'Curso atualizado com sucesso no painel externo!',
				'course_id' => $course_id,
			),
			200
		);
	}

	/**
	 * DELETE /courses/{id} - Delete course.
	 */
	public static function delete_course( \WP_REST_Request $request ): \WP_REST_Response {
		$course_id = (int) $request->get_param( 'id' );
		$course = get_post( $course_id );

		if ( ! $course || 'stackead_course' !== $course->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Curso não encontrado.' ), 404 );
		}

		$deleted = wp_delete_post( $course_id, true );

		if ( ! $deleted ) {
			return new \WP_REST_Response( array( 'error' => 'Não foi possível excluir o curso.' ), 500 );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Curso excluído com sucesso do catálogo!',
			),
			200
		);
	}

	/**
	 * GET /cohorts - List cohorts based on global and contextual roles.
	 * Combines Master cohorts + Student enrolled cohorts + Contextual member cohorts so multi-role users see all their cohorts.
	 */
	public static function get_cohorts(): \WP_REST_Response {
		global $wpdb;
		$user_id = get_current_user_id();
		$user    = wp_get_current_user();
		$roles   = $user ? (array) $user->roles : array();

		if ( current_user_can( 'manage_options' ) || in_array( 'stackead_coordinator', $roles, true ) ) {
			// Administrators & Global Coordinators see all cohorts
			$cohort_posts = get_posts(
				array(
					'post_type'      => 'stackead_cohort',
					'post_status'    => array( 'publish', 'draft' ),
					'posts_per_page' => -1,
				)
			);
		} else {
			// Combine all cohorts where the user is Master/Instructor, Student, or Contextual Member
			$master_ids  = \StackEAD\Roles\ScopeFilter::get_master_cohort_ids( $user_id );
			$enrollments = EnrollmentService::get_student_cohorts( $user_id );
			$student_ids = ! empty( $enrollments ) ? wp_list_pluck( $enrollments, 'cohort_id' ) : array();

			$table_members = $wpdb->prefix . 'stackead_cohort_members';
			$member_ids    = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT cohort_id FROM {$table_members} WHERE user_id = %d AND status = 'active'",
					$user_id
				)
			);

			$combined_ids = array_values(
				array_unique(
					array_filter(
						array_map(
							'intval',
							array_merge(
								is_array( $master_ids ) ? $master_ids : array(),
								is_array( $student_ids ) ? $student_ids : array(),
								is_array( $member_ids ) ? $member_ids : array()
							)
						)
					)
				)
			);

			$cohort_posts = ! empty( $combined_ids ) ? get_posts(
				array(
					'post_type'      => 'stackead_cohort',
					'post_status'    => array( 'publish', 'draft' ),
					'post__in'       => $combined_ids,
					'posts_per_page' => -1,
				)
			) : array();
		}

		$items = array();
		foreach ( $cohort_posts as $c ) {
			$progress   = ProgressService::calculate_cohort_progress( $user_id, $c->ID );
			$course_id  = (int) get_post_meta( $c->ID, '_stackead_course_id', true );
			$my_role    = \StackEAD\Roles\ScopeFilter::get_user_cohort_role( $user_id, $c->ID );
			$can_manage = \StackEAD\Roles\ScopeFilter::can_user_manage_cohort( $user_id, $c->ID );

			$items[] = array(
				'id'           => $c->ID,
				'title'        => $c->post_title,
				'course_id'    => $course_id,
				'course_title' => $course_id ? get_the_title( $course_id ) : '',
				'start_date'   => get_post_meta( $c->ID, '_stackead_start_date', true ),
				'end_date'     => get_post_meta( $c->ID, '_stackead_end_date', true ),
				'progress'     => $progress,
				'my_role'      => $my_role,
				'can_manage'   => $can_manage,
			);
		}

		return new \WP_REST_Response( $items, 200 );
	}

	/**
	 * GET /cohorts/{id}/curriculum - Curriculum hierarchy (Modules & Lessons).
	 */
	public static function get_cohort_curriculum( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];
		$user_id   = get_current_user_id();

		if ( ! ScopeFilter::can_user_access_cohort( $user_id, $cohort_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Acesso não autorizado para esta turma.' ), 403 );
		}

		$path_id = (int) get_post_meta( $cohort_id, '_stackead_path_id', true );
		if ( ! $path_id || ! get_post( $path_id ) ) {
			// Auto-cura: se não tem trilha ou foi deletada, cria uma automaticamente e associa
			$cohort_post = get_post( $cohort_id );
			$cohort_title = $cohort_post ? $cohort_post->post_title : '#' . $cohort_id;
			$path_id = wp_insert_post(
				array(
					'post_title'  => 'Trilha Didática - ' . $cohort_title,
					'post_type'   => 'stackead_path',
					'post_status' => 'publish',
				)
			);
			if ( ! is_wp_error( $path_id ) ) {
				update_post_meta( $cohort_id, '_stackead_path_id', (int) $path_id );
			}
		}

		$modules = array();
		if ( $path_id && ! is_wp_error( $path_id ) ) {
			$modules = get_posts(
				array(
					'post_type'      => 'stackead_module',
					'post_parent'    => (int) $path_id,
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				)
			);
		}

		// Fallback resiliente: se não encontrou por post_parent, busca módulos vinculados via meta
		if ( empty( $modules ) && $cohort_id ) {
			$meta_modules = get_posts(
				array(
					'post_type'      => 'stackead_module',
					'meta_key'       => '_stackead_cohort_id',
					'meta_value'     => $cohort_id,
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				)
			);
			if ( ! empty( $meta_modules ) ) {
				$modules = $meta_modules;
			}
		}

		global $wpdb;
		$progress_table = $wpdb->prefix . 'stackead_progress';

		$tree = array();
		$previous_module_locked_next = false;
		$previous_module_title = '';
		$sub_table = $wpdb->prefix . 'stackead_submissions';
		$is_staff = AccessControl::is_administrator( wp_get_current_user() ) || in_array( 'stackead_master', (array) wp_get_current_user()->roles, true ) || in_array( 'stackead_coordinator', (array) wp_get_current_user()->roles, true );

		foreach ( $modules as $mod ) {
			$lessons = get_posts(
				array(
					'post_type'      => 'stackead_lesson',
					'post_parent'    => $mod->ID,
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				)
			);

			$all_lessons_done = true;
			$lessons_data = array();
			foreach ( $lessons as $les ) {
				$prog = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT completion_percent, seconds_watched, completed_at FROM {$progress_table}
						WHERE user_id = %d AND lesson_id = %d AND cohort_id = %d",
						$user_id,
						$les->ID,
						$cohort_id
					)
				);

				$lock = ProgressService::check_sequential_lock( $user_id, $les->ID, $cohort_id );
				$is_completed = ( $prog && $prog->completed_at ) ? true : false;
				if ( ! $is_completed ) {
					$all_lessons_done = false;
				}

				// Check linked assignment details
				$asg_id = (int) get_post_meta( $les->ID, '_stackead_assignment_id', true );
				$asg_title = '';
				$asg_prompt = '';
				$asg_sub = null;
				if ( $asg_id && get_post( $asg_id ) ) {
					$asg_post = get_post( $asg_id );
					$asg_title = $asg_post->post_title;
					$asg_prompt = wp_strip_all_tags( $asg_post->post_content );
					$asg_sub_row = $wpdb->get_row(
						$wpdb->prepare(
							"SELECT status, score FROM {$sub_table} WHERE user_id = %d AND assignment_id = %d ORDER BY id DESC LIMIT 1",
							$user_id,
							$asg_id
						)
					);
					if ( $asg_sub_row ) {
						$asg_sub = array(
							'status' => $asg_sub_row->status,
							'score'  => $asg_sub_row->score !== null ? (float) $asg_sub_row->score : null,
						);
					}
				}

				$lesson_locked = ( $previous_module_locked_next && ! $is_staff ) ? true : $lock['is_locked'];
				$lesson_lock_reason = ( $previous_module_locked_next && ! $is_staff ) ? sprintf( 'Bloqueado: Requer aprovação no Projeto Integrador do módulo anterior (%s).', $previous_module_title ) : $lock['reason'];

				$lessons_data[] = array(
					'id'                    => $les->ID,
					'title'                 => $les->post_title,
					'duration_min'          => (int) get_post_meta( $les->ID, '_stackead_duration_minutes', true ),
					'completed'             => $is_completed,
					'completion_percent'    => $prog ? (float) $prog->completion_percent : 0.0,
					'is_locked'             => $lesson_locked,
					'lock_reason'           => $lesson_lock_reason,
					'quiz_id'               => (int) get_post_meta( $les->ID, '_stackead_quiz_id', true ),
					'assignment_id'         => $asg_id,
					'assignment_title'      => $asg_title,
					'assignment_prompt'     => $asg_prompt,
					'assignment_submission' => $asg_sub,
					'video_url'             => get_post_meta( $les->ID, '_stackead_video_url', true ) ?: '',
					'audio_url'             => get_post_meta( $les->ID, '_stackead_audio_url', true ) ?: '',
					'content'               => $les->post_content,
					'simplified_text'       => get_post_meta( $les->ID, '_stackead_simplified_text', true ) ?: '',
					'sequential_lock'       => get_post_meta( $les->ID, '_stackead_sequential_lock', true ) === 'yes',
					'attachments'           => get_post_meta( $les->ID, '_stackead_attachments', true ) ?: array(),
					'links'                 => get_post_meta( $les->ID, '_stackead_links', true ) ?: array(),
				);
			}

			// Check Module Final Project / Projeto Integrador
			$has_final_project = get_post_meta( $mod->ID, '_stackead_has_final_project', true ) === 'yes';
			$final_project_id  = (int) get_post_meta( $mod->ID, '_stackead_final_project_id', true );
			$lock_next_module  = get_post_meta( $mod->ID, '_stackead_lock_next_module', true ) === 'yes';
			$min_score         = (float) ( get_post_meta( $mod->ID, '_stackead_final_project_min_score', true ) ?: 70 );

			if ( ! $final_project_id ) {
				$found_asgs = get_posts(
					array(
						'post_type'      => 'stackead_assignment',
						'meta_query'     => array(
							array(
								'key'   => '_stackead_module_id',
								'value' => $mod->ID,
							),
							array(
								'key'   => '_stackead_is_final_project',
								'value' => '1',
							),
						),
						'posts_per_page' => 1,
					)
				);
				if ( ! empty( $found_asgs ) ) {
					$final_project_id  = $found_asgs[0]->ID;
					$has_final_project = true;
				}
			}

			$final_project_data = null;
			$final_project_approved = false;

			if ( $has_final_project && $final_project_id && get_post( $final_project_id ) ) {
				$fp_post = get_post( $final_project_id );
				$fp_sub  = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM {$sub_table} WHERE user_id = %d AND assignment_id = %d ORDER BY id DESC LIMIT 1",
						$user_id,
						$final_project_id
					)
				);

				$fp_score = ( $fp_sub && $fp_sub->score !== null ) ? (float) $fp_sub->score : null;
				$final_project_approved = ( $fp_sub && ( $fp_sub->status === 'approved' || ( $fp_score !== null && $fp_score >= $min_score ) ) );

				$final_project_data = array(
					'id'               => $final_project_id,
					'title'            => $fp_post->post_title,
					'prompt'           => $fp_post->post_content,
					'min_score'        => $min_score,
					'max_score'        => (float) ( get_post_meta( $final_project_id, '_stackead_max_score', true ) ?: 100 ),
					'due_date'         => get_post_meta( $final_project_id, '_stackead_due_date', true ),
					'is_approved'      => $final_project_approved,
					'lock_next_module' => $lock_next_module,
					'submission'       => $fp_sub ? array(
						'id'           => (int) $fp_sub->id,
						'status'       => $fp_sub->status,
						'score'        => $fp_score,
						'feedback'     => $fp_sub->feedback,
						'submitted_at' => $fp_sub->created_at,
					) : null,
				);
			}

			// Module Locked State
			$module_is_locked = $previous_module_locked_next && ! $is_staff;
			$module_lock_reason = $module_is_locked ? sprintf( 'Bloqueado: Requer aprovação no Projeto Integrador do módulo anterior (%s).', $previous_module_title ) : '';

			// Module Overall Status
			$module_status = 'in_progress';
			if ( $module_is_locked ) {
				$module_status = 'locked';
			} elseif ( $all_lessons_done && ( ! $has_final_project || $final_project_approved ) ) {
				$module_status = 'completed';
			}

			// Determine if next module should be locked
			if ( $has_final_project && $lock_next_module && ! $final_project_approved ) {
				$previous_module_locked_next = true;
				$previous_module_title       = $mod->post_title;
			} else {
				$previous_module_locked_next = false;
				$previous_module_title       = '';
			}

			$tree[] = array(
				'id'                 => $mod->ID,
				'title'              => $mod->post_title,
				'description'        => $mod->post_content,
				'workload_hours'     => (int) get_post_meta( $mod->ID, '_stackead_workload_hours', true ),
				'theme'              => get_post_meta( $mod->ID, '_stackead_theme', true ) ?: '',
				'syllabus'           => get_post_meta( $mod->ID, '_stackead_syllabus', true ) ?: '',
				'is_standalone'      => get_post_meta( $mod->ID, '_stackead_is_standalone', true ) === 'yes',
				'has_final_project'  => $has_final_project,
				'lock_next_module'   => $lock_next_module,
				'final_project'      => $final_project_data,
				'is_locked'          => $module_is_locked,
				'lock_reason'        => $module_lock_reason,
				'status'             => $module_status,
				'lessons'            => $lessons_data,
			);
		}

		// Also get sync sessions
		$sessions = get_posts(
			array(
				'post_type'      => 'stackead_session',
				'meta_key'       => '_stackead_cohort_id',
				'meta_value'     => $cohort_id,
				'posts_per_page' => -1,
			)
		);

		$sessions_data = array();
		foreach ( $sessions as $s ) {
			$sessions_data[] = array(
				'id'           => $s->ID,
				'title'        => $s->post_title,
				'session_date' => get_post_meta( $s->ID, '_stackead_session_date', true ),
				'meet_url'     => get_post_meta( $s->ID, '_stackead_meet_url', true ),
				'recording'    => get_post_meta( $s->ID, '_stackead_recording_url', true ),
			);
		}

		return new \WP_REST_Response(
			array(
				'cohort_id' => $cohort_id,
				'modules'   => $tree,
				'sessions'  => $sessions_data,
			),
			200
		);
	}

	/**
	 * GET /lessons/{id} - Lesson detailed payload with COGA accommodations.
	 */
	public static function get_lesson( \WP_REST_Request $request ): \WP_REST_Response {
		$lesson_id = (int) $request['id'];
		$user_id   = get_current_user_id();

		$post = get_post( $lesson_id );
		if ( ! $post || 'stackead_lesson' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Aula não encontrada.' ), 404 );
		}

		$simplified_text = get_post_meta( $lesson_id, '_stackead_simplified_text', true );
		$video_url       = get_post_meta( $lesson_id, '_stackead_video_url', true );
		$audio_url       = get_post_meta( $lesson_id, '_stackead_audio_url', true );
		$transcription   = get_post_meta( $lesson_id, '_stackead_transcription', true );
		$duration_min    = (int) ( get_post_meta( $lesson_id, '_stackead_duration_minutes', true ) ?: 15 );
		$sequential_lock = get_post_meta( $lesson_id, '_stackead_sequential_lock', true ) === 'yes';
		$assignment_id   = (int) get_post_meta( $lesson_id, '_stackead_assignment_id', true );
		$quiz_id         = (int) get_post_meta( $lesson_id, '_stackead_quiz_id', true );
		$attachments     = get_post_meta( $lesson_id, '_stackead_attachments', true ) ?: array();
		$links           = get_post_meta( $lesson_id, '_stackead_links', true ) ?: array();

		$quiz_data = null;
		if ( $quiz_id && get_post( $quiz_id ) ) {
			$q_post = get_post( $quiz_id );
			$quiz_data = array(
				'id'            => $quiz_id,
				'title'         => $q_post->post_title,
				'passing_score' => (float) ( get_post_meta( $quiz_id, '_stackead_passing_score', true ) ?: 70 ),
			);
		}

		$asg_data = null;
		if ( $assignment_id && get_post( $assignment_id ) ) {
			$a_post = get_post( $assignment_id );
			$asg_data = array(
				'id'        => $assignment_id,
				'title'     => $a_post->post_title,
				'prompt'    => $a_post->post_content,
				'max_score' => (float) ( get_post_meta( $assignment_id, '_stackead_max_score', true ) ?: 100 ),
			);
		}

		// Check student accommodations
		$has_simplified_pref = AccommodationService::has_resource( $user_id, 'simplified_language' );
		$has_extra_time      = AccommodationService::has_resource( $user_id, 'extra_time' );
		$has_focus_mode      = AccommodationService::has_resource( $user_id, 'focus_mode' );

		return new \WP_REST_Response(
			array(
				'id'                 => $post->ID,
				'module_id'          => (int) $post->post_parent,
				'title'              => $post->post_title,
				'content'            => $post->post_content,
				'duration_min'       => $duration_min,
				'sequential_lock'    => $sequential_lock,
				'simplified_text'    => $simplified_text ? wp_kses_post( $simplified_text ) : '',
				'video_url'          => $video_url ? esc_url( $video_url ) : '',
				'audio_url'          => $audio_url ? esc_url( $audio_url ) : '',
				'transcription'      => $transcription ? wp_kses_post( $transcription ) : '',
				'assignment_id'      => $assignment_id,
				'assignment'         => $asg_data,
				'quiz_id'            => $quiz_id,
				'quiz'               => $quiz_data,
				'attachments'        => $attachments,
				'links'              => $links,
				'accommodations'     => array(
					'default_simplified' => $has_simplified_pref,
					'extra_time'         => $has_extra_time,
					'focus_mode'         => $has_focus_mode,
				),
			),
			200
		);
	}

	/**
	 * POST /progress - Update lesson progress.
	 */
	public static function record_progress( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id         = get_current_user_id();
		$lesson_id       = (int) $request->get_param( 'lesson_id' );
		$cohort_id       = (int) $request->get_param( 'cohort_id' );
		$percent         = (float) $request->get_param( 'percent' );
		$seconds_watched = (int) $request->get_param( 'seconds_watched' );
		$force_complete  = (bool) $request->get_param( 'force_complete' );

		if ( ! $lesson_id || ! $cohort_id ) {
			return new \WP_REST_Response( array( 'error' => 'Parâmetros inválidos.' ), 400 );
		}

		if ( ! ScopeFilter::can_user_access_cohort( $user_id, $cohort_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Aluno não matriculado nesta turma.' ), 403 );
		}

		$percent = max( 0.0, min( 100.0, $percent ) );

		ProgressService::record_progress( $user_id, $lesson_id, $cohort_id, $percent, $seconds_watched, $force_complete );
		$new_cohort_progress = ProgressService::calculate_cohort_progress( $user_id, $cohort_id );

		return new \WP_REST_Response(
			array(
				'success'         => true,
				'cohort_progress' => $new_cohort_progress,
			),
			200
		);
	}

	/**
	 * GET /sessions/{id}/attendance - Get session roll call sheet.
	 */
	public static function get_session_attendance( \WP_REST_Request $request ): \WP_REST_Response {
		$session_id = (int) $request['id'];
		$cohort_id  = (int) get_post_meta( $session_id, '_stackead_cohort_id', true );

		if ( ! $cohort_id ) {
			return new \WP_REST_Response( array( 'error' => 'Sessão inválida ou sem turma associada.' ), 404 );
		}

		if ( ! ScopeFilter::can_user_manage_cohort( get_current_user_id(), $cohort_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Acesso negado: você não é o facilitador atribuído a esta turma.' ), 403 );
		}

		$sheet = AttendanceService::get_session_attendance_sheet( $session_id, $cohort_id );
		return new \WP_REST_Response( $sheet, 200 );
	}

	/**
	 * POST /sessions/{id}/attendance - Batch update session attendance.
	 */
	public static function save_session_attendance( \WP_REST_Request $request ): \WP_REST_Response {
		$session_id  = (int) $request['id'];
		$cohort_id   = (int) get_post_meta( $session_id, '_stackead_cohort_id', true );
		$attendances = (array) $request->get_param( 'attendances' );

		if ( ! $cohort_id ) {
			return new \WP_REST_Response( array( 'error' => 'Sessão inválida ou sem turma associada.' ), 404 );
		}

		if ( ! ScopeFilter::can_user_manage_cohort( get_current_user_id(), $cohort_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Acesso negado: você não é o facilitador atribuído a esta turma.' ), 403 );
		}

		$count = AttendanceService::batch_record_attendance( $session_id, $cohort_id, $attendances );
		return new \WP_REST_Response( array( 'success' => true, 'updated_count' => $count ), 200 );
	}

	/**
	 * POST /submissions - Student submits practical assignment.
	 */
	public static function submit_assignment( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id       = get_current_user_id();
		$assignment_id = (int) $request->get_param( 'assignment_id' );
		$cohort_id     = (int) $request->get_param( 'cohort_id' );
		$content_text   = wp_kses_post( (string) $request->get_param( 'content_text' ) );
		$content_url    = esc_url_raw( (string) $request->get_param( 'content_url' ) );
		$attachment_ids = sanitize_text_field( (string) $request->get_param( 'attachment_ids' ) );

		if ( ! $assignment_id || ! $cohort_id ) {
			return new \WP_REST_Response( array( 'error' => 'Parâmetros inválidos.' ), 400 );
		}

		if ( ! ScopeFilter::can_user_access_cohort( $user_id, $cohort_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Aluno não matriculado nesta turma.' ), 403 );
		}

		$id = GradingService::submit_assignment( $assignment_id, $user_id, $cohort_id, $content_text, $content_url, $attachment_ids );
		return new \WP_REST_Response( array( 'success' => (bool) $id, 'submission_id' => $id ), 200 );
	}

	/**
	 * POST /submissions/{id}/grade - Master grades submission with rubrics.
	 */
	public static function grade_submission( \WP_REST_Request $request ): \WP_REST_Response {
		$submission_id = (int) $request['id'];
		$score         = (float) $request->get_param( 'score' );
		$status        = sanitize_text_field( $request->get_param( 'status' ) );
		$feedback      = sanitize_textarea_field( (string) $request->get_param( 'feedback' ) );
		$rubric_scores = (array) $request->get_param( 'rubric_scores' );

		global $wpdb;
		$sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stackead_submissions WHERE id = %d", $submission_id ) );
		if ( ! $sub ) {
			return new \WP_REST_Response( array( 'error' => 'Submissão não encontrada.' ), 404 );
		}

		if ( ! ScopeFilter::can_user_manage_cohort( get_current_user_id(), (int) $sub->cohort_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Acesso negado: você não é o facilitador responsável por esta turma.' ), 403 );
		}

		$score = max( 0.0, min( 100.0, $score ) );

		$success = GradingService::grade_submission( $submission_id, $score, $status, $feedback, $rubric_scores, get_current_user_id() );
		return new \WP_REST_Response( array( 'success' => $success ), 200 );
	}

	/**
	 * GET /cohorts/{id}/submissions - Master views submissions for cohort.
	 */
	public static function get_cohort_submissions( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];

		if ( ! ScopeFilter::can_user_manage_cohort( get_current_user_id(), $cohort_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Acesso negado para esta turma.' ), 403 );
		}

		global $wpdb;

		$table = $wpdb->prefix . 'stackead_submissions';
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s.*, u.display_name, u.user_email, p.post_title as assignment_title
				FROM {$table} s
				INNER JOIN {$wpdb->users} u ON s.user_id = u.ID
				INNER JOIN {$wpdb->posts} p ON s.assignment_id = p.ID
				WHERE s.cohort_id = %d
				ORDER BY s.updated_at DESC",
				$cohort_id
			)
		);

		return new \WP_REST_Response( ! empty( $results ) ? $results : array(), 200 );
	}

	/**
	 * GET /quizzes - List all Quizzes.
	 */
	public static function get_quizzes( \WP_REST_Request $request ): \WP_REST_Response {
		$posts = get_posts(
			array(
				'post_type'      => 'stackead_quiz',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		global $wpdb;
		$table = $wpdb->prefix . 'stackead_quiz_questions';
		$results = array();
		foreach ( $posts as $p ) {
			$q_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE quiz_id = %d", $p->ID ) );
			$results[] = array(
				'id'                 => $p->ID,
				'title'              => $p->post_title,
				'passing_score'      => (float) ( get_post_meta( $p->ID, '_stackead_passing_score', true ) ?: 70 ),
				'time_limit_minutes' => (int) ( get_post_meta( $p->ID, '_stackead_time_limit_minutes', true ) ?: 15 ),
				'questions_count'    => $q_count,
			);
		}
		return new \WP_REST_Response( $results, 200 );
	}

	/**
	 * GET /quizzes/{id} - Get full Quiz payload with questions.
	 */
	public static function get_single_quiz( \WP_REST_Request $request ): \WP_REST_Response {
		$quiz_id  = (int) $request['id'];
		$user_id  = get_current_user_id();
		$is_staff = current_user_can( 'manage_options' )
			|| in_array( 'stackead_master', (array) wp_get_current_user()->roles, true )
			|| in_array( 'stackead_coordinator', (array) wp_get_current_user()->roles, true );

		$quiz = QuizService::get_quiz( $quiz_id, ! $is_staff, $user_id );
		if ( empty( $quiz ) ) {
			return new \WP_REST_Response( array( 'error' => 'Quiz não encontrado.' ), 404 );
		}
		return new \WP_REST_Response( $quiz, 200 );
	}

	/**
	 * POST /quizzes [/{id}] - Create or update Quiz with questions.
	 */
	public static function save_quiz( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params() ?: $request->get_params();
		if ( isset( $request['id'] ) ) {
			$params['id'] = (int) $request['id'];
		}
		$quiz_id = QuizService::save_quiz( $params );
		if ( ! $quiz_id ) {
			return new \WP_REST_Response( array( 'error' => 'Erro ao salvar quiz.' ), 500 );
		}
		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Quiz e questões salvos com sucesso!',
				'id'      => $quiz_id,
			),
			200
		);
	}

	/**
	 * POST /quizzes/{id}/submit - Real Server-Side Quiz Grading.
	 */
	public static function submit_quiz( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id   = get_current_user_id();
		$quiz_id   = (int) $request['id'];
		$params    = $request->get_json_params() ?: $request->get_params();
		$cohort_id = (int) ( $params['cohort_id'] ?? 0 );
		$answers   = (array) ( $params['answers'] ?? array() );

		if ( ! $quiz_id || ! $cohort_id ) {
			return new \WP_REST_Response( array( 'error' => 'Parâmetros inválidos (cohort_id obrigatório).' ), 400 );
		}

		if ( ! ScopeFilter::can_user_access_cohort( $user_id, $cohort_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Aluno não matriculado nesta turma.' ), 403 );
		}

		$result = QuizService::grade_attempt( $quiz_id, $user_id, $cohort_id, $answers );
		if ( empty( $result['success'] ) ) {
			return new \WP_REST_Response( array( 'error' => $result['error'] ?? 'Erro na avaliação do quiz.' ), 400 );
		}

		return new \WP_REST_Response( $result, 200 );
	}

	/**
	 * GET /accommodations - Student or coordinator accommodations.
	 */
	public static function get_accommodations(): \WP_REST_Response {
		$user_id = get_current_user_id();

		if ( current_user_can( 'manage_options' ) || in_array( 'stackead_coordinator', (array) wp_get_current_user()->roles, true ) ) {
			$list = AccommodationService::list_requests();
		} else {
			$list = AccommodationService::get_student_accommodations( $user_id );
		}

		return new \WP_REST_Response( $list, 200 );
	}

	/**
	 * POST /accommodations - Student requests accommodation.
	 */
	public static function request_accommodation( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id       = get_current_user_id();
		$resource_type = sanitize_text_field( $request->get_param( 'resource_type' ) );
		$instructions  = sanitize_textarea_field( $request->get_param( 'instructions' ) );

		$id = AccommodationService::request_accommodation( $user_id, $resource_type, $instructions );
		return new \WP_REST_Response( array( 'success' => (bool) $id, 'request_id' => $id ), 200 );
	}

	/**
	 * POST /accommodations/{id}/review - Coordinator approves/rejects.
	 */
	public static function review_accommodation( \WP_REST_Request $request ): \WP_REST_Response {
		$id     = (int) $request['id'];
		$status = sanitize_text_field( $request->get_param( 'status' ) );

		$success = AccommodationService::review_accommodation( $id, $status, get_current_user_id() );
		return new \WP_REST_Response( array( 'success' => $success ), 200 );
	}

	/**
	 * GET /gamification/me - Current user XP, rank and badges.
	 */
	public static function get_my_gamification(): \WP_REST_Response {
		$user_id = get_current_user_id();
		$level   = GamificationService::get_user_level( $user_id );
		$badges  = GamificationService::get_user_badges( $user_id );

		return new \WP_REST_Response(
			array(
				'level'  => $level,
				'badges' => $badges,
			),
			200
		);
	}

	/**
	 * GET /gamification/leaderboard - Top student ranking.
	 */
	public static function get_leaderboard(): \WP_REST_Response {
		$board = GamificationService::get_leaderboard( 10 );
		return new \WP_REST_Response( $board, 200 );
	}

	/**
	 * GET /cohorts/{id}/discussions - Get forum discussions.
	 */
	public static function get_discussions( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];
		$user_id   = get_current_user_id();

		if ( ! ScopeFilter::can_user_access_cohort( $user_id, $cohort_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Acesso negado para o fórum desta turma.' ), 403 );
		}

		$lesson_id = (int) $request->get_param( 'lesson_id' );

		$topics = CommunityService::get_topics( $cohort_id, $lesson_id );
		return new \WP_REST_Response( $topics, 200 );
	}

	/**
	 * POST /cohorts/{id}/discussions - Post discussion topic or reply.
	 */
	public static function post_discussion( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id  = (int) $request['id'];
		$user_id    = get_current_user_id();

		if ( ! ScopeFilter::can_user_access_cohort( $user_id, $cohort_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'Acesso negado para postar no fórum desta turma.' ), 403 );
		}

		$title      = sanitize_text_field( (string) $request->get_param( 'title' ) );
		$content    = wp_kses_post( (string) $request->get_param( 'content' ) );
		$lesson_id  = (int) $request->get_param( 'lesson_id' );
		$parent_id  = (int) $request->get_param( 'parent_id' );

		$id = CommunityService::post_message( $cohort_id, $user_id, $content, $title, $lesson_id, $parent_id );
		return new \WP_REST_Response( array( 'success' => (bool) $id, 'discussion_id' => $id ), 200 );
	}

	/**
	 * POST /discussions/{id}/upvote - Upvote message.
	 */
	public static function upvote_discussion( \WP_REST_Request $request ): \WP_REST_Response {
		$id      = (int) $request['id'];
		$upvotes = CommunityService::upvote( $id );
		return new \WP_REST_Response( array( 'success' => true, 'upvotes' => $upvotes ), 200 );
	}

	/**
	 * GET /finance/summary - Revenue stats.
	 */
	public static function get_finance_summary(): \WP_REST_Response {
		$summary = FinanceService::get_financial_summary();
		return new \WP_REST_Response( $summary, 200 );
	}

	/**
	 * POST /orders - Create order.
	 */
	public static function create_order( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id        = get_current_user_id();
		$plan_key       = sanitize_text_field( (string) $request->get_param( 'plan_key' ) );
		$amount         = (float) $request->get_param( 'amount' );
		$cohort_id      = (int) $request->get_param( 'cohort_id' );
		$payment_method = sanitize_text_field( (string) $request->get_param( 'payment_method' ) );

		$order_id = FinanceService::create_order( $user_id, $plan_key, $amount, $cohort_id, $payment_method );
		return new \WP_REST_Response( array( 'success' => true, 'order_id' => $order_id ), 200 );
	}

	/**
	 * POST /orders/{id}/simulate-pay - Test pay order (Restricted to Admins/Coordinators or 0-price free orders).
	 */
	public static function simulate_pay_order( \WP_REST_Request $request ): \WP_REST_Response {
		$order_id = (int) $request['id'];
		global $wpdb;

		$order = $wpdb->get_row( $wpdb->prepare( "SELECT user_id, amount FROM {$wpdb->prefix}stackead_orders WHERE id = %d", $order_id ) );
		if ( ! $order ) {
			return new \WP_REST_Response( array( 'error' => 'Pedido não encontrado.' ), 404 );
		}

		$current_uid    = get_current_user_id();
		$is_manager     = self::check_coordinator_or_admin();
		$is_free_order  = ( (float) $order->amount <= 0.0 );

		if ( ! $is_manager && ( (int) $order->user_id !== $current_uid || ! $is_free_order ) ) {
			return new \WP_REST_Response( array( 'error' => 'Apenas administradores/coordenadores podem aprovar manualmente pedidos pagos.' ), 403 );
		}

		$success = FinanceService::complete_order( $order_id );
		return new \WP_REST_Response( array( 'success' => $success ), 200 );
	}

	/**
	 * GET /cohorts/{id}/certificate-status - Cumulative check.
	 */
	public static function get_certificate_status( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];
		$user_id   = get_current_user_id();

		$status = CertificateService::check_eligibility( $user_id, $cohort_id );
		return new \WP_REST_Response( $status, 200 );
	}

	/**
	 * POST /cohorts/{id}/issue-certificate - Issue certificate.
	 */
	public static function issue_certificate( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];
		$current_user = get_current_user_id();
		$target_student = (int) $request->get_param( 'student_id' );
		$force = (bool) $request->get_param( 'force' );

		$can_manage = ScopeFilter::can_user_manage_cohort( $current_user, $cohort_id );
		$user_id = ( $can_manage && $target_student > 0 ) ? $target_student : $current_user;
		$bypass = ( $can_manage && $force );

		$cert_id = CertificateService::issue_certificate( $user_id, $cohort_id, $bypass );
		if ( ! $cert_id ) {
			return new \WP_REST_Response( array( 'error' => 'Requisitos pedagógicos não foram cumpridos.' ), 400 );
		}

		$hash = get_post_meta( $cert_id, '_stackead_hash', true );
		return new \WP_REST_Response(
			array(
				'success'    => true,
				'cert_id'    => $cert_id,
				'hash'       => $hash,
				'verify_url' => home_url( '/certificados/' . $hash ),
			),
			200
		);
	}

	/* ==========================================================================
	   15. White Label Settings Handlers
	   ========================================================================== */

	public static function get_whitelabel_settings(): \WP_REST_Response {
		return new \WP_REST_Response( WhiteLabelSettings::get_settings(), 200 );
	}

	public static function save_whitelabel_settings( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params() ?: $request->get_params();
		$saved  = WhiteLabelSettings::save_settings( $params );
		return new \WP_REST_Response( $saved, 200 );
	}

	/* ==========================================================================
	   16. Dual Category Handlers (Courses & News)
	   ========================================================================== */

	public static function get_categories( \WP_REST_Request $request ): \WP_REST_Response {
		$type     = $request->get_param( 'type' );
		$taxonomy = ( 'news' === $type ) ? 'stackead_news_cat' : 'stackead_category';

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) ) {
			return new \WP_REST_Response( array(), 200 );
		}

		$formatted = array();
		foreach ( $terms as $term ) {
			$formatted[] = array(
				'id'          => $term->term_id,
				'name'        => wp_specialchars_decode( $term->name ),
				'slug'        => $term->slug,
				'description' => $term->description,
				'count'       => (int) $term->count,
				'taxonomy'    => $taxonomy,
				'type'        => ( 'stackead_news_cat' === $taxonomy ) ? 'news' : 'course',
			);
		}

		return new \WP_REST_Response( $formatted, 200 );
	}

	public static function create_category( \WP_REST_Request $request ): \WP_REST_Response {
		$name        = sanitize_text_field( $request['name'] ?? '' );
		$type        = $request['type'] ?? 'course';
		$taxonomy    = ( 'news' === $type ) ? 'stackead_news_cat' : 'stackead_category';
		$slug        = sanitize_title( $request['slug'] ?? $name );
		$description = sanitize_textarea_field( $request['description'] ?? '' );

		if ( empty( $name ) ) {
			return new \WP_REST_Response( array( 'error' => 'O nome da categoria é obrigatório.' ), 400 );
		}

		$result = wp_insert_term(
			$name,
			$taxonomy,
			array(
				'slug'        => $slug,
				'description' => $description,
			)
		);

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( array( 'error' => $result->get_error_message() ), 400 );
		}

		return new \WP_REST_Response(
			array(
				'id'          => $result['term_id'],
				'name'        => $name,
				'slug'        => $slug,
				'description' => $description,
				'taxonomy'    => $taxonomy,
			),
			201
		);
	}

	public static function delete_category( \WP_REST_Request $request ): \WP_REST_Response {
		$id       = (int) $request['id'];
		$taxonomy = $request->get_param( 'taxonomy' ) ?: 'stackead_category';

		$deleted = wp_delete_term( $id, $taxonomy );
		if ( ! $deleted || is_wp_error( $deleted ) ) {
			wp_delete_term( $id, 'stackead_news_cat' );
		}

		return new \WP_REST_Response( array( 'success' => true ), 200 );
	}

	/* ==========================================================================
	   17. News & Announcements Handlers
	   ========================================================================== */

	public static function get_news_list( \WP_REST_Request $request ): \WP_REST_Response {
		$is_admin = current_user_can( 'manage_options' );
		$status   = $is_admin ? array( 'publish', 'draft' ) : array( 'publish' );

		$posts = get_posts(
			array(
				'post_type'      => 'stackead_news',
				'post_status'    => $status,
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$items = array();
		foreach ( $posts as $p ) {
			$terms    = get_the_terms( $p->ID, 'stackead_news_cat' );
			$cat_name = ! empty( $terms ) && ! is_wp_error( $terms ) ? wp_specialchars_decode( $terms[0]->name ) : 'Geral';
			$cat_id   = ! empty( $terms ) && ! is_wp_error( $terms ) ? $terms[0]->term_id : 0;
			$thumb    = get_the_post_thumbnail_url( $p->ID, 'large' ) ?: get_post_meta( $p->ID, '_stackead_news_cover_url', true );

			$items[] = array(
				'id'          => $p->ID,
				'title'       => $p->post_title,
				'content'     => $p->post_content,
				'excerpt'     => $p->post_excerpt,
				'summary'     => get_post_meta( $p->ID, '_stackead_news_summary', true ) ?: $p->post_excerpt,
				'cover_url'   => $thumb ?: '',
				'author_name' => get_post_meta( $p->ID, '_stackead_news_author_name', true ) ?: get_the_author_meta( 'display_name', $p->post_author ),
				'category'    => $cat_name,
				'category_id' => $cat_id,
				'status'      => $p->post_status,
				'date'        => get_the_date( 'd/m/Y', $p->ID ),
				'created_at'  => $p->post_date,
			);
		}

		return new \WP_REST_Response( $items, 200 );
	}

	public static function get_single_news( \WP_REST_Request $request ): \WP_REST_Response {
		$id = (int) $request['id'];
		$p  = get_post( $id );
		if ( ! $p || 'stackead_news' !== $p->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Notícia não encontrada.' ), 404 );
		}

		$terms = get_the_terms( $p->ID, 'stackead_news_cat' );
		$thumb = get_the_post_thumbnail_url( $p->ID, 'large' ) ?: get_post_meta( $p->ID, '_stackead_news_cover_url', true );

		return new \WP_REST_Response(
			array(
				'id'          => $p->ID,
				'title'       => $p->post_title,
				'content'     => $p->post_content,
				'excerpt'     => $p->post_excerpt,
				'summary'     => get_post_meta( $p->ID, '_stackead_news_summary', true ) ?: $p->post_excerpt,
				'cover_url'   => $thumb ?: '',
				'author_name' => get_post_meta( $p->ID, '_stackead_news_author_name', true ) ?: get_the_author_meta( 'display_name', $p->post_author ),
				'category_id' => ! empty( $terms ) && ! is_wp_error( $terms ) ? $terms[0]->term_id : 0,
				'status'      => $p->post_status,
				'date'        => get_the_date( 'd/m/Y', $p->ID ),
			),
			200
		);
	}

	public static function save_news( \WP_REST_Request $request ): \WP_REST_Response {
		$id          = (int) ( $request['id'] ?? 0 );
		$title       = sanitize_text_field( $request['title'] ?? '' );
		$content     = wp_kses_post( $request['content'] ?? '' );
		$summary     = sanitize_textarea_field( $request['summary'] ?? '' );
		$cover_url   = esc_url_raw( $request['cover_url'] ?? '' );
		$author_name = sanitize_text_field( $request['author_name'] ?? wp_get_current_user()->display_name );
		$status      = in_array( $request['status'] ?? 'publish', array( 'publish', 'draft' ), true ) ? $request['status'] : 'publish';
		$category_id = absint( $request['category_id'] ?? 0 );

		if ( empty( $title ) ) {
			return new \WP_REST_Response( array( 'error' => 'O título da notícia é obrigatório.' ), 400 );
		}

		$post_data = array(
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $summary,
			'post_status'  => $status,
			'post_type'    => 'stackead_news',
		);

		if ( $id > 0 ) {
			$post_data['ID'] = $id;
			$news_id         = wp_update_post( $post_data );
		} else {
			$news_id = wp_insert_post( $post_data );
		}

		if ( is_wp_error( $news_id ) || ! $news_id ) {
			return new \WP_REST_Response( array( 'error' => 'Erro ao salvar a notícia.' ), 500 );
		}

		update_post_meta( $news_id, '_stackead_news_summary', $summary );
		update_post_meta( $news_id, '_stackead_news_cover_url', $cover_url );
		update_post_meta( $news_id, '_stackead_news_author_name', $author_name );

		if ( $category_id > 0 ) {
			wp_set_object_terms( $news_id, array( $category_id ), 'stackead_news_cat' );
		}

		return new \WP_REST_Response(
			array(
				'id'      => $news_id,
				'success' => true,
				'title'   => $title,
				'status'  => $status,
			),
			200
		);
	}

	public static function delete_news( \WP_REST_Request $request ): \WP_REST_Response {
		$id = (int) $request['id'];
		wp_delete_post( $id, true );
		return new \WP_REST_Response( array( 'success' => true ), 200 );
	}

	/* ==========================================================================
	   18. CRM Leads & Landing Capture Handlers
	   ========================================================================== */

	/**
	 * Transient-based IP rate limiter for public endpoints (OWASP API4 mitigation).
	 *
	 * @param string $action         Action identifier.
	 * @param int    $max_attempts   Max requests allowed in window.
	 * @param int    $window_seconds Window duration in seconds.
	 * @return bool True if allowed, false if rate-limited.
	 */
	private static function check_rate_limit( string $action, int $max_attempts = 5, int $window_seconds = 600 ): bool {
		$ip  = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
		$key = 'sead_rl_' . md5( $action . '|' . $ip );
		$cnt = (int) get_transient( $key );
		if ( $cnt >= $max_attempts ) {
			return false;
		}
		set_transient( $key, $cnt + 1, $window_seconds );
		return true;
	}

	/**
	 * Sanitize CSV cell value against CSV Formula Injection (=, +, -, @, tab, CR).
	 *
	 * @param mixed $val Cell value.
	 * @return string
	 */
	private static function sanitize_csv_cell( $val ): string {
		$str = (string) $val;
		if ( '' !== $str && in_array( $str[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			return "'" . $str;
		}
		return $str;
	}

	public static function capture_lead( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! self::check_rate_limit( 'capture_lead', 6, 600 ) ) {
			return new \WP_REST_Response( array( 'error' => 'Muitas solicitações em sequência. Aguarde alguns minutos e tente novamente.' ), 429 );
		}

		global $wpdb;

		$name            = trim( str_replace( array( "\r", "\n" ), ' ', sanitize_text_field( $request['name'] ?? '' ) ) );
		$email           = sanitize_email( $request['email'] ?? '' );
		$phone           = sanitize_text_field( $request['phone'] ?? '' );
		$city            = sanitize_text_field( $request['city'] ?? '' );
		$profile         = sanitize_text_field( $request['profile'] ?? '' );
		$referral        = sanitize_text_field( $request['referral'] ?? '' );
		$course_id       = absint( $request['course_id'] ?? 0 );
		$lang            = strtolower( sanitize_text_field( $request['lang'] ?? 'pt' ) );
		if ( ! in_array( $lang, array( 'pt', 'en', 'es', 'fr' ), true ) ) {
			$lang = 'pt';
		}
		$consent_raw     = $request['consent'] ?? null;
		$consent_given   = ( true === $consent_raw || 1 === $consent_raw || '1' === (string) $consent_raw || 'true' === strtolower( (string) $consent_raw ) || 'on' === strtolower( (string) $consent_raw ) );
		$consent_version = sanitize_text_field( $request['consent_version'] ?? 'v2.0-LGPD-GDPR-2026' );
		$ip              = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );

		if ( empty( $name ) || empty( $email ) || ! is_email( $email ) ) {
			return new \WP_REST_Response( array( 'error' => 'Nome e e-mail válido são obrigatórios.' ), 400 );
		}

		if ( ! $consent_given ) {
			return new \WP_REST_Response(
				array(
					'error'   => 'É obrigatório autorizar a Política de Privacidade (LGPD/GDPR) para enviar sua solicitação.',
					'message' => 'É obrigatório autorizar a Política de Privacidade (LGPD/GDPR) para enviar sua solicitação.',
				),
				400
			);
		}

		$table = $wpdb->prefix . 'stackead_leads';
		$now   = current_time( 'mysql' );

		$inserted = $wpdb->insert(
			$table,
			array(
				'course_id'       => $course_id,
				'name'            => $name,
				'email'           => $email,
				'phone'           => $phone,
				'city'            => $city,
				'profile'         => $profile,
				'referral'        => $referral,
				'status'          => 'new',
				'consent_given'   => 1,
				'consent_version' => $consent_version ?: 'v2.0-LGPD-GDPR-2026',
				'lang'            => $lang,
				'ip_address'      => $ip,
				'created_at'      => $now,
				'updated_at'      => $now,
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return new \WP_REST_Response( array( 'error' => 'Erro ao salvar a manifestação.' ), 500 );
		}

		$lead_id = $wpdb->insert_id;

		// Notificação para a coordenação oficial
		$recipient = 'contact@artinnovaresidency.com';
		$subject   = sprintf( '[ArtInnova Residência] Nova Inscrição: %s', $name );
		$body      = "Uma nova manifestação de interesse foi registrada na Residência ArtInnova:\n\n"
				   . "Nome: " . $name . "\n"
				   . "E-mail: " . $email . "\n"
				   . "Telefone/WhatsApp: " . $phone . "\n"
				   . "Cidade/País: " . $city . "\n"
				   . "Perfil: " . $profile . "\n"
				   . "Indicação: " . $referral . "\n"
				   . "Idioma: " . strtoupper( $lang ) . "\n"
				   . "Consentimento LGPD/GDPR: SIM (" . $consent_version . ")\n"
				   . "ID da Inscrição: #" . $lead_id . "\n\n"
				   . "Data/Hora: " . $now . "\n";

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: Residência ArtInnova <contact@artinnovaresidency.com>',
			'Reply-To: ' . $name . ' <' . $email . '>',
		);

		@wp_mail( $recipient, $subject, $body, $headers );

		return new \WP_REST_Response(
			array(
				'success'         => true,
				'lead_id'         => $lead_id,
				'consent_version' => $consent_version,
				'message'         => 'Manifestação registrada com sucesso.',
			),
			201
		);
	}

	public static function get_leads( \WP_REST_Request $request ): \WP_REST_Response {
		global $wpdb;
		$table = $wpdb->prefix . 'stackead_leads';

		$leads = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 200", ARRAY_A );
		if ( empty( $leads ) ) {
			$leads = array();
		}

		return new \WP_REST_Response( $leads, 200 );
	}

	public static function export_leads_csv( \WP_REST_Request $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'stackead_leads';

		$leads = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=leads-stackead-' . date( 'Y-m-d' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );
		fputs( $output, "\xEF\xBB\xBF" );

		fputcsv( $output, array( 'ID', 'Nome', 'Email', 'Telefone', 'Cidade', 'Perfil', 'Origem', 'Idioma', 'Consentimento LGPD/GDPR', 'Versão Política', 'Status', 'Data' ), ';' );

		foreach ( $leads as $lead ) {
			$consent_label = ! empty( $lead['consent_given'] ) ? 'Sim' : 'Não';
			fputcsv(
				$output,
				array(
					self::sanitize_csv_cell( $lead['id'] ),
					self::sanitize_csv_cell( $lead['name'] ),
					self::sanitize_csv_cell( $lead['email'] ),
					self::sanitize_csv_cell( $lead['phone'] ),
					self::sanitize_csv_cell( $lead['city'] ),
					self::sanitize_csv_cell( $lead['profile'] ),
					self::sanitize_csv_cell( $lead['referral'] ),
					self::sanitize_csv_cell( $lead['lang'] ?? 'pt' ),
					self::sanitize_csv_cell( $consent_label ),
					self::sanitize_csv_cell( $lead['consent_version'] ?? 'v2.0-LGPD-GDPR-2026' ),
					self::sanitize_csv_cell( $lead['status'] ),
					self::sanitize_csv_cell( $lead['created_at'] ),
				),
				';'
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * DELETE /leads/{id} - Permanently erase a lead (LGPD Art. 18 VI / GDPR Art. 17 Right to Erasure).
	 */
	public static function delete_lead( \WP_REST_Request $request ): \WP_REST_Response {
		global $wpdb;
		$lead_id = (int) $request['id'];
		$table   = $wpdb->prefix . 'stackead_leads';

		$deleted = $wpdb->delete( $table, array( 'id' => $lead_id ), array( '%d' ) );
		if ( false === $deleted || 0 === $deleted ) {
			return new \WP_REST_Response( array( 'error' => 'Registro não encontrado ou já removido.' ), 404 );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Lead excluído permanentemente em conformidade com LGPD/GDPR.',
			),
			200
		);
	}

	/**
	 * POST /stackead/v1/auth/login - Public AJAX Login (with rate-limiting and Open Redirect protection).
	 */
	public static function auth_login( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! self::check_rate_limit( 'auth_login', 8, 600 ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Muitas tentativas de login. Por segurança, aguarde 10 minutos antes de tentar novamente.',
				),
				429
			);
		}

		$params = $request->get_json_params();
		if ( empty( $params ) ) {
			$params = $request->get_params();
		}

		$username     = sanitize_text_field( $params['user_login'] ?? $params['username'] ?? '' );
		$password     = (string) ( $params['user_password'] ?? $params['password'] ?? '' );
		$remember     = ! empty( $params['remember'] );
		$raw_redirect = esc_url_raw( $params['redirect_to'] ?? home_url( '/plataforma' ) );
		$redirect     = wp_validate_redirect( $raw_redirect, home_url( '/plataforma' ) );

		if ( empty( $username ) || empty( $password ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Por favor, informe seu usuário ou e-mail e a senha de acesso.',
				),
				400
			);
		}

		$creds = array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $remember,
		);

		$user = wp_signon( $creds, is_ssl() );

		if ( is_wp_error( $user ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Credenciais inválidas. Verifique seu usuário e senha.',
				),
				401
			);
		}

		// Security redirect check: only administrator can be sent to wp-admin
		if ( str_contains( $redirect, 'wp-admin' ) && ! AccessControl::is_administrator( $user ) ) {
			$redirect = home_url( '/plataforma' );
		}

		return new \WP_REST_Response(
			array(
				'success'      => true,
				'redirect_url' => $redirect,
				'user'         => array(
					'id'         => $user->ID,
					'name'       => $user->display_name,
					'email'      => $user->user_email,
					'role'       => ! empty( $user->roles ) ? $user->roles[0] : 'stackead_student',
					'roles'      => array_values( (array) $user->roles ),
					'role_label' => AccessControl::get_user_role_label( $user ),
					'is_admin'   => AccessControl::is_administrator( $user ),
					'avatar_url' => get_avatar_url( $user->ID, array( 'size' => 128 ) ),
				),
			),
			200
		);
	}

	/**
	 * POST /stackead/v1/auth/forgot-password - Password recovery request (Rate-limited).
	 */
	public static function auth_forgot_password( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! self::check_rate_limit( 'auth_forgot_pw', 4, 900 ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Muitas solicitações de recuperação de senha. Aguarde alguns minutos e tente novamente.',
				),
				429
			);
		}
		$params = $request->get_json_params();
		if ( empty( $params ) ) {
			$params = $request->get_params();
		}

		$user_login = sanitize_text_field( $params['user_login'] ?? '' );
		if ( empty( $user_login ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Por favor, informe seu e-mail ou nome de usuário.',
				),
				400
			);
		}

		require_once ABSPATH . WPINC . '/pluggable.php';
		retrieve_password( $user_login );

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Se os dados informados pertencerem a uma conta ativa, você receberá um link de redefinição por e-mail em instantes.',
			),
			200
		);
	}

	/**
	 * POST /stackead/v1/media/upload - Image crop and WebP conversion (<150KB).
	 */
	public static function upload_media( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_params();
		$preset = sanitize_text_field( $params['preset'] ?? 'custom' );
		$title  = sanitize_text_field( $params['title'] ?? '' );
		$crop   = $params['crop'] ?? array();

		if ( is_string( $crop ) ) {
			$crop = json_decode( $crop, true ) ?: array();
		}

		$source = null;
		$files = $request->get_file_params();
		if ( ! empty( $files['file'] ) ) {
			$source = $files['file'];
		} elseif ( ! empty( $params['image_base64'] ) ) {
			$source = $params['image_base64'];
		}

		if ( empty( $source ) ) {
			return new \WP_REST_Response(
				array( 'error' => 'Nenhuma imagem foi enviada.' ),
				400
			);
		}

		try {
			$result = \StackEAD\Services\ImageOptimizer::process_and_save( $source, $preset, $crop, $title );
			return new \WP_REST_Response( $result, 201 );
		} catch ( \Throwable $e ) {
			return new \WP_REST_Response(
				array( 'error' => $e->getMessage() ),
				500
			);
		}
	}

	/**
	 * POST /stackead/v1/users/me/avatar - User Avatar 1:1 Crop & WebP upload.
	 */
	public static function update_my_avatar( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id = get_current_user_id();
		$params  = $request->get_params();
		$crop    = $params['crop'] ?? array();
		if ( is_string( $crop ) ) {
			$crop = json_decode( $crop, true ) ?: array();
		}

		$source = null;
		$files  = $request->get_file_params();
		if ( ! empty( $files['file'] ) ) {
			$source = $files['file'];
		} elseif ( ! empty( $params['image_base64'] ) ) {
			$source = $params['image_base64'];
		}

		if ( empty( $source ) ) {
			return new \WP_REST_Response(
				array( 'error' => 'Nenhuma imagem fornecida para o avatar.' ),
				400
			);
		}

		try {
			$result = \StackEAD\Services\ImageOptimizer::save_user_avatar( $user_id, $source, $crop );
			return new \WP_REST_Response( $result, 200 );
		} catch ( \Throwable $e ) {
			return new \WP_REST_Response(
				array( 'error' => $e->getMessage() ),
				500
			);
		}
	}

	/**
	 * GET /courses/{id}/landing-builder - Get builder data, blocks and available templates.
	 */
	public static function get_course_landing_builder( \WP_REST_Request $request ): \WP_REST_Response {
		$course_id = (int) $request->get_param( 'id' );
		$course = get_post( $course_id );
		if ( ! $course || 'stackead_course' !== $course->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Curso não encontrado.' ), 404 );
		}

		$template = \StackEAD\Landing\BlockManager::get_course_template( $course_id );
		$blocks   = \StackEAD\Landing\BlockManager::get_course_blocks( $course_id );

		return new \WP_REST_Response(
			array(
				'course_id'           => $course_id,
				'course_title'        => $course->post_title,
				'template'            => $template,
				'templates_available' => array_values( \StackEAD\Landing\BlockManager::TEMPLATES ),
				'blocks'              => $blocks,
				'schemas'             => \StackEAD\Landing\BlockManager::BLOCK_SCHEMAS,
				'preview_url'         => home_url( '/landing/?preview_course_id=' . $course_id ),
			),
			200
		);
	}

	/**
	 * POST /courses/{id}/landing-builder - Save customized blocks and template.
	 */
	public static function save_course_landing_builder( \WP_REST_Request $request ): \WP_REST_Response {
		$course_id = (int) $request->get_param( 'id' );
		$course = get_post( $course_id );
		if ( ! $course || 'stackead_course' !== $course->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Curso não encontrado.' ), 404 );
		}

		$params   = $request->get_json_params() ?: $request->get_params();
		$blocks   = $params['blocks'] ?? array();
		$template = sanitize_text_field( $params['template'] ?? '' );

		if ( ! is_array( $blocks ) ) {
			return new \WP_REST_Response( array( 'error' => 'Estrutura de blocos inválida.' ), 400 );
		}

		$result = \StackEAD\Landing\BlockManager::save_course_blocks( $course_id, $blocks, $template );

		return new \WP_REST_Response(
			array(
				'success'  => true,
				'message'  => 'Landing page atualizada com sucesso!',
				'template' => $result['template'],
				'blocks'   => $result['blocks'],
			),
			200
		);
	}

	/**
	 * POST /courses/{id}/landing-builder/reset - Reset blocks to default template.
	 */
	public static function reset_course_landing_builder( \WP_REST_Request $request ): \WP_REST_Response {
		$course_id = (int) $request->get_param( 'id' );
		$course = get_post( $course_id );
		if ( ! $course || 'stackead_course' !== $course->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Curso não encontrado.' ), 404 );
		}

		$params   = $request->get_json_params() ?: $request->get_params();
		$template = sanitize_text_field( $params['template'] ?? 'artinnova_light' );

		$blocks = \StackEAD\Landing\BlockManager::reset_course_blocks( $course_id, $template );

		return new \WP_REST_Response(
			array(
				'success'  => true,
				'message'  => 'Blocos restaurados para o padrão com sucesso.',
				'template' => $template,
				'blocks'   => $blocks,
			),
			200
		);
	}

	/* ==========================================================================
	   Full In-App CRUD Controller Methods (Cohorts, Modules, Lessons, Sessions, Assignments)
	   ========================================================================== */

	/**
	 * POST /cohorts - Create a new Cohort.
	 */
	public static function create_cohort( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params() ?: $request->get_params();
		$title = sanitize_text_field( $params['title'] ?? '' );
		if ( empty( $title ) ) {
			return new \WP_REST_Response( array( 'error' => 'O título da turma é obrigatório.' ), 400 );
		}

		$course_id = (int) ( $params['course_id'] ?? 0 );
		$post_id = wp_insert_post(
			array(
				'post_title'  => $title,
				'post_type'   => 'stackead_cohort',
				'post_status' => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) ) {
			return new \WP_REST_Response( array( 'error' => $post_id->get_error_message() ), 500 );
		}

		// Auto-create or link path
		$path_id = 0;
		if ( $course_id ) {
			$course_path = get_post_meta( $course_id, '_stackead_path_id', true );
			if ( $course_path ) {
				$path_id = (int) $course_path;
			}
		}
		if ( ! $path_id ) {
			$path_id = wp_insert_post(
				array(
					'post_title'  => 'Trilha Didática - ' . $title,
					'post_type'   => 'stackead_path',
					'post_status' => 'publish',
				)
			);
		}

		update_post_meta( $post_id, '_stackead_course_id', $course_id );
		update_post_meta( $post_id, '_stackead_path_id', $path_id );
		update_post_meta( $post_id, '_stackead_start_date', sanitize_text_field( $params['start_date'] ?? '' ) );
		update_post_meta( $post_id, '_stackead_end_date', sanitize_text_field( $params['end_date'] ?? '' ) );
		update_post_meta( $post_id, '_stackead_max_students', (int) ( $params['max_students'] ?? 50 ) );
		update_post_meta( $post_id, '_stackead_community_url', esc_url_raw( $params['community_url'] ?? '' ) );

		if ( ! empty( $params['master_ids'] ) ) {
			update_post_meta( $post_id, '_stackead_master_ids', (array) $params['master_ids'] );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Turma criada com sucesso!',
				'id'      => $post_id,
				'title'   => $title,
			),
			201
		);
	}

	/**
	 * GET /cohorts/{id} - Get single Cohort details.
	 */
	public static function get_single_cohort( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];
		$post = get_post( $cohort_id );
		if ( ! $post || 'stackead_cohort' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Turma não encontrada.' ), 404 );
		}

		$course_id = (int) get_post_meta( $cohort_id, '_stackead_course_id', true );
		$course = $course_id ? get_post( $course_id ) : null;

		return new \WP_REST_Response(
			array(
				'id'             => $cohort_id,
				'title'          => $post->post_title,
				'course_id'      => $course_id,
				'course_title'   => $course ? $course->post_title : '',
				'start_date'     => get_post_meta( $cohort_id, '_stackead_start_date', true ),
				'end_date'       => get_post_meta( $cohort_id, '_stackead_end_date', true ),
				'max_students'   => (int) get_post_meta( $cohort_id, '_stackead_max_students', true ),
				'community_url'  => get_post_meta( $cohort_id, '_stackead_community_url', true ),
				'master_ids'     => get_post_meta( $cohort_id, '_stackead_master_ids', true ) ?: array(),
			),
			200
		);
	}

	/**
	 * POST|PUT /cohorts/{id} - Update Cohort.
	 */
	public static function update_cohort( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];
		$post = get_post( $cohort_id );
		if ( ! $post || 'stackead_cohort' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Turma não encontrada.' ), 404 );
		}

		$params = $request->get_json_params() ?: $request->get_params();
		if ( ! empty( $params['title'] ) ) {
			wp_update_post(
				array(
					'ID'         => $cohort_id,
					'post_title' => sanitize_text_field( $params['title'] ),
				)
			);
		}

		if ( isset( $params['course_id'] ) ) {
			update_post_meta( $cohort_id, '_stackead_course_id', (int) $params['course_id'] );
		}
		if ( isset( $params['start_date'] ) ) {
			update_post_meta( $cohort_id, '_stackead_start_date', sanitize_text_field( $params['start_date'] ) );
		}
		if ( isset( $params['end_date'] ) ) {
			update_post_meta( $cohort_id, '_stackead_end_date', sanitize_text_field( $params['end_date'] ) );
		}
		if ( isset( $params['max_students'] ) ) {
			update_post_meta( $cohort_id, '_stackead_max_students', (int) $params['max_students'] );
		}
		if ( isset( $params['community_url'] ) ) {
			update_post_meta( $cohort_id, '_stackead_community_url', esc_url_raw( $params['community_url'] ) );
		}
		if ( isset( $params['master_ids'] ) ) {
			update_post_meta( $cohort_id, '_stackead_master_ids', (array) $params['master_ids'] );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Turma atualizada com sucesso!',
			),
			200
		);
	}

	/**
	 * DELETE /cohorts/{id} - Delete Cohort.
	 */
	public static function delete_cohort( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];
		$post = get_post( $cohort_id );
		if ( ! $post || 'stackead_cohort' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Turma não encontrada.' ), 404 );
		}

		wp_delete_post( $cohort_id, true );
		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Turma excluída com sucesso.' ), 200 );
	}

	/**
	 * POST /modules - Create Module.
	 */
	public static function create_module( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params() ?: $request->get_params();
		$title = sanitize_text_field( $params['title'] ?? '' );
		if ( empty( $title ) ) {
			return new \WP_REST_Response( array( 'error' => 'O título do módulo é obrigatório.' ), 400 );
		}

		$cohort_id = (int) ( $params['cohort_id'] ?? 0 );
		$path_id = 0;
		if ( $cohort_id ) {
			$path_id = (int) get_post_meta( $cohort_id, '_stackead_path_id', true );
			if ( ! $path_id || ! get_post( $path_id ) ) {
				$cohort_post = get_post( $cohort_id );
				$cohort_title = $cohort_post ? $cohort_post->post_title : '#' . $cohort_id;
				$path_id = wp_insert_post(
					array(
						'post_title'  => 'Trilha Didática - ' . $cohort_title,
						'post_type'   => 'stackead_path',
						'post_status' => 'publish',
					)
				);
				if ( ! is_wp_error( $path_id ) ) {
					update_post_meta( $cohort_id, '_stackead_path_id', (int) $path_id );
				}
			}
		}

		$order = (int) ( $params['menu_order'] ?? 0 );
		$mod_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => sanitize_textarea_field( $params['description'] ?? '' ),
				'post_parent'  => ( ! is_wp_error( $path_id ) ? (int) $path_id : 0 ),
				'post_type'    => 'stackead_module',
				'post_status'  => 'publish',
				'menu_order'   => $order,
			)
		);

		if ( is_wp_error( $mod_id ) ) {
			return new \WP_REST_Response( array( 'error' => $mod_id->get_error_message() ), 500 );
		}

		if ( $cohort_id ) {
			update_post_meta( $mod_id, '_stackead_cohort_id', $cohort_id );
		}
		if ( $path_id && ! is_wp_error( $path_id ) ) {
			update_post_meta( $mod_id, '_stackead_path_id', (int) $path_id );
		}
		if ( isset( $params['hours'] ) ) {
			update_post_meta( $mod_id, '_stackead_workload_hours', (int) $params['hours'] );
		}
		if ( isset( $params['workload_hours'] ) ) {
			update_post_meta( $mod_id, '_stackead_workload_hours', (int) $params['workload_hours'] );
		}
		if ( isset( $params['theme'] ) ) {
			update_post_meta( $mod_id, '_stackead_theme', sanitize_text_field( $params['theme'] ) );
		}
		if ( isset( $params['syllabus'] ) ) {
			update_post_meta( $mod_id, '_stackead_syllabus', wp_kses_post( $params['syllabus'] ) );
		}
		if ( isset( $params['is_standalone'] ) ) {
			update_post_meta( $mod_id, '_stackead_is_standalone', ! empty( $params['is_standalone'] ) ? 'yes' : 'no' );
		}
		if ( isset( $params['has_final_project'] ) ) {
			$has_fp = ! empty( $params['has_final_project'] ) && 'no' !== $params['has_final_project'];
			update_post_meta( $mod_id, '_stackead_has_final_project', $has_fp ? 'yes' : 'no' );
			if ( isset( $params['lock_next_module'] ) ) {
				update_post_meta( $mod_id, '_stackead_lock_next_module', ! empty( $params['lock_next_module'] ) && 'no' !== $params['lock_next_module'] ? 'yes' : 'no' );
			}
			if ( isset( $params['final_project_min_score'] ) ) {
				update_post_meta( $mod_id, '_stackead_final_project_min_score', (float) $params['final_project_min_score'] );
			}
			if ( $has_fp && ! empty( $params['final_project_title'] ) ) {
				$fp_asg_id = (int) get_post_meta( $mod_id, '_stackead_final_project_id', true );
				$asg_args = array(
					'post_title'   => sanitize_text_field( $params['final_project_title'] ),
					'post_content' => wp_kses_post( $params['final_project_instructions'] ?? '' ),
					'post_type'    => 'stackead_assignment',
					'post_status'  => 'publish',
				);
				if ( $fp_asg_id && get_post( $fp_asg_id ) ) {
					$asg_args['ID'] = $fp_asg_id;
					wp_update_post( $asg_args );
				} else {
					$fp_asg_id = wp_insert_post( $asg_args );
					update_post_meta( $mod_id, '_stackead_final_project_id', $fp_asg_id );
				}
				if ( $fp_asg_id && ! is_wp_error( $fp_asg_id ) ) {
					update_post_meta( $fp_asg_id, '_stackead_cohort_id', $cohort_id ?: (int) get_post_meta( $mod_id, '_stackead_cohort_id', true ) );
					update_post_meta( $fp_asg_id, '_stackead_module_id', $mod_id );
					update_post_meta( $fp_asg_id, '_stackead_is_final_project', '1' );
					update_post_meta( $fp_asg_id, '_stackead_lock_next_module', ! empty( $params['lock_next_module'] ) ? '1' : '0' );
					update_post_meta( $fp_asg_id, '_stackead_min_score', (float) ( $params['final_project_min_score'] ?? 70 ) );
					update_post_meta( $fp_asg_id, '_stackead_max_score', 100 );
				}
			}
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Módulo criado com sucesso!',
				'id'      => $mod_id,
				'title'   => $title,
			),
			201
		);
	}

	/**
	 * POST|PUT /modules/{id} - Update Module.
	 */
	public static function update_module( \WP_REST_Request $request ): \WP_REST_Response {
		$mod_id = (int) $request['id'];
		$post = get_post( $mod_id );
		if ( ! $post || 'stackead_module' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Módulo não encontrado.' ), 404 );
		}

		$params = $request->get_json_params() ?: $request->get_params();
		$update_args = array( 'ID' => $mod_id );

		if ( ! empty( $params['title'] ) ) {
			$update_args['post_title'] = sanitize_text_field( $params['title'] );
		}
		if ( isset( $params['description'] ) ) {
			$update_args['post_content'] = sanitize_textarea_field( $params['description'] );
		}
		if ( isset( $params['menu_order'] ) ) {
			$update_args['menu_order'] = (int) $params['menu_order'];
		}

		wp_update_post( $update_args );

		if ( isset( $params['hours'] ) || isset( $params['workload_hours'] ) ) {
			$hours = isset( $params['workload_hours'] ) ? (int) $params['workload_hours'] : (int) $params['hours'];
			update_post_meta( $mod_id, '_stackead_workload_hours', $hours );
		}
		if ( isset( $params['theme'] ) ) {
			update_post_meta( $mod_id, '_stackead_theme', sanitize_text_field( $params['theme'] ) );
		}
		if ( isset( $params['syllabus'] ) ) {
			update_post_meta( $mod_id, '_stackead_syllabus', wp_kses_post( $params['syllabus'] ) );
		}
		if ( isset( $params['is_standalone'] ) ) {
			update_post_meta( $mod_id, '_stackead_is_standalone', ! empty( $params['is_standalone'] ) ? 'yes' : 'no' );
		}
		if ( isset( $params['has_final_project'] ) ) {
			$has_fp = ! empty( $params['has_final_project'] ) && 'no' !== $params['has_final_project'];
			update_post_meta( $mod_id, '_stackead_has_final_project', $has_fp ? 'yes' : 'no' );
			if ( isset( $params['lock_next_module'] ) ) {
				update_post_meta( $mod_id, '_stackead_lock_next_module', ! empty( $params['lock_next_module'] ) && 'no' !== $params['lock_next_module'] ? 'yes' : 'no' );
			}
			if ( isset( $params['final_project_min_score'] ) ) {
				update_post_meta( $mod_id, '_stackead_final_project_min_score', (float) $params['final_project_min_score'] );
			}
			if ( $has_fp && ! empty( $params['final_project_title'] ) ) {
				$fp_asg_id = (int) get_post_meta( $mod_id, '_stackead_final_project_id', true );
				$asg_args = array(
					'post_title'   => sanitize_text_field( $params['final_project_title'] ),
					'post_content' => wp_kses_post( $params['final_project_instructions'] ?? '' ),
					'post_type'    => 'stackead_assignment',
					'post_status'  => 'publish',
				);
				if ( $fp_asg_id && get_post( $fp_asg_id ) ) {
					$asg_args['ID'] = $fp_asg_id;
					wp_update_post( $asg_args );
				} else {
					$fp_asg_id = wp_insert_post( $asg_args );
					update_post_meta( $mod_id, '_stackead_final_project_id', $fp_asg_id );
				}
				if ( $fp_asg_id && ! is_wp_error( $fp_asg_id ) ) {
					update_post_meta( $fp_asg_id, '_stackead_cohort_id', $cohort_id ?: (int) get_post_meta( $mod_id, '_stackead_cohort_id', true ) );
					update_post_meta( $fp_asg_id, '_stackead_module_id', $mod_id );
					update_post_meta( $fp_asg_id, '_stackead_is_final_project', '1' );
					update_post_meta( $fp_asg_id, '_stackead_lock_next_module', ! empty( $params['lock_next_module'] ) ? '1' : '0' );
					update_post_meta( $fp_asg_id, '_stackead_min_score', (float) ( $params['final_project_min_score'] ?? 70 ) );
					update_post_meta( $fp_asg_id, '_stackead_max_score', 100 );
				}
			}
		}

		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Módulo atualizado com sucesso!' ), 200 );
	}

	/**
	 * DELETE /modules/{id} - Delete Module.
	 */
	public static function delete_module( \WP_REST_Request $request ): \WP_REST_Response {
		$mod_id = (int) $request['id'];
		$post = get_post( $mod_id );
		if ( ! $post || 'stackead_module' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Módulo não encontrado.' ), 404 );
		}

		wp_delete_post( $mod_id, true );
		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Módulo excluído com sucesso.' ), 200 );
	}

	/**
	 * POST /lessons - Create Lesson.
	 */
	public static function create_lesson( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params() ?: $request->get_params();
		$title = sanitize_text_field( $params['title'] ?? '' );
		if ( empty( $title ) ) {
			return new \WP_REST_Response( array( 'error' => 'O título da aula é obrigatório.' ), 400 );
		}

		$module_id = (int) ( $params['module_id'] ?? 0 );
		$les_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => wp_kses_post( $params['content'] ?? '' ),
				'post_parent'  => $module_id,
				'post_type'    => 'stackead_lesson',
				'post_status'  => 'publish',
				'menu_order'   => (int) ( $params['menu_order'] ?? 0 ),
			)
		);

		if ( is_wp_error( $les_id ) ) {
			return new \WP_REST_Response( array( 'error' => $les_id->get_error_message() ), 500 );
		}

		update_post_meta( $les_id, '_stackead_video_url', esc_url_raw( $params['video_url'] ?? '' ) );
		update_post_meta( $les_id, '_stackead_audio_url', esc_url_raw( $params['audio_url'] ?? '' ) );
		update_post_meta( $les_id, '_stackead_duration_minutes', (int) ( $params['duration_minutes'] ?? 15 ) );
		update_post_meta( $les_id, '_stackead_simplified_text', sanitize_textarea_field( $params['simplified_text'] ?? '' ) );
		update_post_meta( $les_id, '_stackead_transcription', sanitize_textarea_field( $params['transcription'] ?? '' ) );
		update_post_meta( $les_id, '_stackead_sequential_lock', ! empty( $params['sequential_lock'] ) ? 'yes' : 'no' );
		if ( isset( $params['quiz_id'] ) ) {
			$qid = (int) $params['quiz_id'];
			update_post_meta( $les_id, '_stackead_quiz_id', $qid );
			if ( $qid > 0 ) {
				update_post_meta( $qid, '_stackead_lesson_id', $les_id );
			}
		}
		if ( isset( $params['assignment_id'] ) ) {
			$asgid = (int) $params['assignment_id'];
			update_post_meta( $les_id, '_stackead_assignment_id', $asgid );
			if ( $asgid > 0 ) {
				update_post_meta( $asgid, '_stackead_lesson_id', $les_id );
			}
		}
		if ( isset( $params['attachments'] ) ) {
			$raw_att = is_array( $params['attachments'] ) ? $params['attachments'] : json_decode( (string) $params['attachments'], true );
			$clean_att = array();
			if ( is_array( $raw_att ) ) {
				foreach ( $raw_att as $item ) {
					if ( ! empty( $item['name'] ) || ! empty( $item['url'] ) ) {
						$clean_att[] = array(
							'name' => sanitize_text_field( $item['name'] ?? 'Arquivo' ),
							'url'  => esc_url_raw( $item['url'] ?? '' ),
							'size' => sanitize_text_field( $item['size'] ?? '' ),
							'type' => sanitize_text_field( $item['type'] ?? 'file' ),
						);
					}
				}
			}
			update_post_meta( $les_id, '_stackead_attachments', $clean_att );
		}
		if ( isset( $params['links'] ) ) {
			$raw_links = is_array( $params['links'] ) ? $params['links'] : json_decode( (string) $params['links'], true );
			$clean_links = array();
			if ( is_array( $raw_links ) ) {
				foreach ( $raw_links as $item ) {
					if ( ! empty( $item['title'] ) || ! empty( $item['url'] ) ) {
						$clean_links[] = array(
							'title'       => sanitize_text_field( $item['title'] ?? 'Link Útil' ),
							'url'         => esc_url_raw( $item['url'] ?? '' ),
							'description' => sanitize_text_field( $item['description'] ?? '' ),
						);
					}
				}
			}
			update_post_meta( $les_id, '_stackead_links', $clean_links );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Aula cadastrada com sucesso!',
				'id'      => $les_id,
				'title'   => $title,
			),
			201
		);
	}

	/**
	 * POST|PUT /lessons/{id} - Update Lesson.
	 */
	public static function update_lesson( \WP_REST_Request $request ): \WP_REST_Response {
		$les_id = (int) $request['id'];
		$post = get_post( $les_id );
		if ( ! $post || 'stackead_lesson' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Aula não encontrada.' ), 404 );
		}

		$params = $request->get_json_params() ?: $request->get_params();
		$update_args = array( 'ID' => $les_id );

		if ( ! empty( $params['title'] ) ) {
			$update_args['post_title'] = sanitize_text_field( $params['title'] );
		}
		if ( isset( $params['content'] ) ) {
			$update_args['post_content'] = wp_kses_post( $params['content'] );
		}
		if ( isset( $params['module_id'] ) ) {
			$update_args['post_parent'] = (int) $params['module_id'];
		}
		if ( isset( $params['menu_order'] ) ) {
			$update_args['menu_order'] = (int) $params['menu_order'];
		}

		wp_update_post( $update_args );

		if ( isset( $params['video_url'] ) ) {
			update_post_meta( $les_id, '_stackead_video_url', esc_url_raw( $params['video_url'] ) );
		}
		if ( isset( $params['audio_url'] ) ) {
			update_post_meta( $les_id, '_stackead_audio_url', esc_url_raw( $params['audio_url'] ) );
		}
		if ( isset( $params['duration_minutes'] ) ) {
			update_post_meta( $les_id, '_stackead_duration_minutes', (int) $params['duration_minutes'] );
		}
		if ( isset( $params['simplified_text'] ) ) {
			update_post_meta( $les_id, '_stackead_simplified_text', sanitize_textarea_field( $params['simplified_text'] ) );
		}
		if ( isset( $params['transcription'] ) ) {
			update_post_meta( $les_id, '_stackead_transcription', sanitize_textarea_field( $params['transcription'] ) );
		}
		if ( isset( $params['sequential_lock'] ) ) {
			update_post_meta( $les_id, '_stackead_sequential_lock', ! empty( $params['sequential_lock'] ) ? 'yes' : 'no' );
		}
		if ( isset( $params['quiz_id'] ) ) {
			$qid = (int) $params['quiz_id'];
			update_post_meta( $les_id, '_stackead_quiz_id', $qid );
			if ( $qid > 0 ) {
				update_post_meta( $qid, '_stackead_lesson_id', $les_id );
			}
		}
		if ( isset( $params['assignment_id'] ) ) {
			$asgid = (int) $params['assignment_id'];
			update_post_meta( $les_id, '_stackead_assignment_id', $asgid );
			if ( $asgid > 0 ) {
				update_post_meta( $asgid, '_stackead_lesson_id', $les_id );
			}
		}
		if ( isset( $params['attachments'] ) ) {
			$raw_att = is_array( $params['attachments'] ) ? $params['attachments'] : json_decode( (string) $params['attachments'], true );
			$clean_att = array();
			if ( is_array( $raw_att ) ) {
				foreach ( $raw_att as $item ) {
					if ( ! empty( $item['name'] ) || ! empty( $item['url'] ) ) {
						$clean_att[] = array(
							'name' => sanitize_text_field( $item['name'] ?? 'Arquivo' ),
							'url'  => esc_url_raw( $item['url'] ?? '' ),
							'size' => sanitize_text_field( $item['size'] ?? '' ),
							'type' => sanitize_text_field( $item['type'] ?? 'file' ),
						);
					}
				}
			}
			update_post_meta( $les_id, '_stackead_attachments', $clean_att );
		}
		if ( isset( $params['links'] ) ) {
			$raw_links = is_array( $params['links'] ) ? $params['links'] : json_decode( (string) $params['links'], true );
			$clean_links = array();
			if ( is_array( $raw_links ) ) {
				foreach ( $raw_links as $item ) {
					if ( ! empty( $item['title'] ) || ! empty( $item['url'] ) ) {
						$clean_links[] = array(
							'title'       => sanitize_text_field( $item['title'] ?? 'Link Útil' ),
							'url'         => esc_url_raw( $item['url'] ?? '' ),
							'description' => sanitize_text_field( $item['description'] ?? '' ),
						);
					}
				}
			}
			update_post_meta( $les_id, '_stackead_links', $clean_links );
		}

		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Aula atualizada com sucesso!' ), 200 );
	}

	/**
	 * DELETE /lessons/{id} - Delete Lesson.
	 */
	public static function delete_lesson( \WP_REST_Request $request ): \WP_REST_Response {
		$les_id = (int) $request['id'];
		$post = get_post( $les_id );
		if ( ! $post || 'stackead_lesson' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Aula não encontrada.' ), 404 );
		}

		wp_delete_post( $les_id, true );
		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Aula excluída com sucesso.' ), 200 );
	}

	/**
	 * POST /sessions - Create Live Session.
	 */
	public static function create_session( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params() ?: $request->get_params();
		$title = sanitize_text_field( $params['title'] ?? '' );
		if ( empty( $title ) ) {
			return new \WP_REST_Response( array( 'error' => 'O título da sessão é obrigatório.' ), 400 );
		}

		$cohort_id = (int) ( $params['cohort_id'] ?? 0 );
		$session_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => sanitize_textarea_field( $params['description'] ?? '' ),
				'post_type'    => 'stackead_session',
				'post_status'  => 'publish',
			)
		);

		if ( is_wp_error( $session_id ) ) {
			return new \WP_REST_Response( array( 'error' => $session_id->get_error_message() ), 500 );
		}

		update_post_meta( $session_id, '_stackead_cohort_id', $cohort_id );
		update_post_meta( $session_id, '_stackead_session_date', sanitize_text_field( $params['session_date'] ?? '' ) );
		update_post_meta( $session_id, '_stackead_meet_url', esc_url_raw( $params['meet_url'] ?? '' ) );
		update_post_meta( $session_id, '_stackead_recording_url', esc_url_raw( $params['recording_url'] ?? '' ) );

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Sessão ao vivo agendada com sucesso!',
				'id'      => $session_id,
				'title'   => $title,
			),
			201
		);
	}

	/**
	 * GET /sessions/{id} - Get single Session.
	 */
	public static function get_single_session( \WP_REST_Request $request ): \WP_REST_Response {
		$session_id = (int) $request['id'];
		$post = get_post( $session_id );
		if ( ! $post || 'stackead_session' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Sessão não encontrada.' ), 404 );
		}

		return new \WP_REST_Response(
			array(
				'id'            => $session_id,
				'title'         => $post->post_title,
				'description'   => $post->post_content,
				'cohort_id'     => (int) get_post_meta( $session_id, '_stackead_cohort_id', true ),
				'session_date'  => get_post_meta( $session_id, '_stackead_session_date', true ),
				'meet_url'      => get_post_meta( $session_id, '_stackead_meet_url', true ),
				'recording_url' => get_post_meta( $session_id, '_stackead_recording_url', true ),
			),
			200
		);
	}

	/**
	 * POST|PUT /sessions/{id} - Update Live Session.
	 */
	public static function update_session( \WP_REST_Request $request ): \WP_REST_Response {
		$session_id = (int) $request['id'];
		$post = get_post( $session_id );
		if ( ! $post || 'stackead_session' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Sessão não encontrada.' ), 404 );
		}

		$params = $request->get_json_params() ?: $request->get_params();
		$update_args = array( 'ID' => $session_id );

		if ( ! empty( $params['title'] ) ) {
			$update_args['post_title'] = sanitize_text_field( $params['title'] );
		}
		if ( isset( $params['description'] ) ) {
			$update_args['post_content'] = sanitize_textarea_field( $params['description'] );
		}
		wp_update_post( $update_args );

		if ( isset( $params['session_date'] ) ) {
			update_post_meta( $session_id, '_stackead_session_date', sanitize_text_field( $params['session_date'] ) );
		}
		if ( isset( $params['meet_url'] ) ) {
			update_post_meta( $session_id, '_stackead_meet_url', esc_url_raw( $params['meet_url'] ) );
		}
		if ( isset( $params['recording_url'] ) ) {
			update_post_meta( $session_id, '_stackead_recording_url', esc_url_raw( $params['recording_url'] ) );
		}
		if ( isset( $params['cohort_id'] ) ) {
			update_post_meta( $session_id, '_stackead_cohort_id', (int) $params['cohort_id'] );
		}

		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Sessão atualizada com sucesso!' ), 200 );
	}

	/**
	 * DELETE /sessions/{id} - Delete Live Session.
	 */
	public static function delete_session( \WP_REST_Request $request ): \WP_REST_Response {
		$session_id = (int) $request['id'];
		$post = get_post( $session_id );
		if ( ! $post || 'stackead_session' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Sessão não encontrada.' ), 404 );
		}

		wp_delete_post( $session_id, true );
		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Sessão excluída com sucesso.' ), 200 );
	}

	/**
	 * GET /cohorts/{id}/assignments - List assignments for cohort.
	 */
	public static function get_cohort_assignments( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];
		$user_id   = get_current_user_id();

		$assignments = get_posts(
			array(
				'post_type'      => 'stackead_assignment',
				'meta_key'       => '_stackead_cohort_id',
				'meta_value'     => $cohort_id,
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		global $wpdb;
		$sub_table = $wpdb->prefix . 'stackead_submissions';

		$out = array();
		foreach ( $assignments as $asg ) {
			$sub = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$sub_table} WHERE user_id = %d AND assignment_id = %d ORDER BY id DESC LIMIT 1",
					$user_id,
					$asg->ID
				)
			);

			$raw_types   = get_post_meta( $asg->ID, '_stackead_submission_types', true );
			$sub_types   = $raw_types ? ( json_decode( $raw_types, true ) ?: array() ) : array( 'text_essay', 'external_link', 'file_upload' );
			$raw_rubrics = get_post_meta( $asg->ID, '_stackead_rubric_criteria', true );
			$rubrics     = $raw_rubrics ? ( json_decode( $raw_rubrics, true ) ?: array() ) : array();

			$out[] = array(
				'id'               => $asg->ID,
				'title'            => $asg->post_title,
				'module_id'        => (int) get_post_meta( $asg->ID, '_stackead_module_id', true ),
				'lesson_id'        => (int) get_post_meta( $asg->ID, '_stackead_lesson_id', true ),
				'is_final_project' => get_post_meta( $asg->ID, '_stackead_is_final_project', true ) === '1',
				'lock_next_module' => get_post_meta( $asg->ID, '_stackead_lock_next_module', true ) === '1',
				'min_score'        => (float) ( get_post_meta( $asg->ID, '_stackead_min_score', true ) ?: 70 ),
				'prompt'           => $asg->post_content,
				'due_date'         => get_post_meta( $asg->ID, '_stackead_due_date', true ),
				'max_score'        => (float) ( get_post_meta( $asg->ID, '_stackead_max_score', true ) ?: 100 ),
				'submission_types' => $sub_types,
				'rubrics'          => $rubrics,
				'submission'       => $sub ? array(
					'id'             => (int) $sub->id,
					'status'         => $sub->status,
					'score'          => $sub->score !== null ? (float) $sub->score : null,
					'feedback'       => $sub->feedback,
					'content_text'   => $sub->content_text,
					'content_url'    => $sub->content_url,
					'attachment_ids' => $sub->attachment_ids,
					'submitted_at'   => $sub->created_at,
				) : null,
			);
		}

		return new \WP_REST_Response( $out, 200 );
	}

	/**
	 * POST /assignments - Create Assignment.
	 */
	public static function create_assignment( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params() ?: $request->get_params();
		$title = sanitize_text_field( $params['title'] ?? '' );
		if ( empty( $title ) ) {
			return new \WP_REST_Response( array( 'error' => 'O título da atividade é obrigatório.' ), 400 );
		}

		$cohort_id = (int) ( $params['cohort_id'] ?? 0 );
		$asg_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => wp_kses_post( $params['prompt'] ?? '' ),
				'post_type'    => 'stackead_assignment',
				'post_status'  => 'publish',
			)
		);

		if ( is_wp_error( $asg_id ) ) {
			return new \WP_REST_Response( array( 'error' => $asg_id->get_error_message() ), 500 );
		}

		update_post_meta( $asg_id, '_stackead_cohort_id', $cohort_id );
		if ( isset( $params['module_id'] ) ) {
			update_post_meta( $asg_id, '_stackead_module_id', (int) $params['module_id'] );
		}
		if ( isset( $params['lesson_id'] ) ) {
			$les_id = (int) $params['lesson_id'];
			update_post_meta( $asg_id, '_stackead_lesson_id', $les_id );
			if ( $les_id ) {
				update_post_meta( $les_id, '_stackead_assignment_id', $asg_id );
			}
		}
		if ( isset( $params['is_final_project'] ) ) {
			update_post_meta( $asg_id, '_stackead_is_final_project', ! empty( $params['is_final_project'] ) ? '1' : '0' );
		}
		if ( isset( $params['lock_next_module'] ) ) {
			update_post_meta( $asg_id, '_stackead_lock_next_module', ! empty( $params['lock_next_module'] ) ? '1' : '0' );
		}
		if ( isset( $params['min_score'] ) ) {
			update_post_meta( $asg_id, '_stackead_min_score', (float) $params['min_score'] );
		}
		update_post_meta( $asg_id, '_stackead_due_date', sanitize_text_field( $params['due_date'] ?? '' ) );
		update_post_meta( $asg_id, '_stackead_max_score', (float) ( $params['max_score'] ?? 100 ) );

		if ( isset( $params['submission_types'] ) ) {
			$types = is_array( $params['submission_types'] ) ? $params['submission_types'] : array( $params['submission_types'] );
			update_post_meta( $asg_id, '_stackead_submission_types', wp_json_encode( array_values( array_filter( $types ) ) ) );
		}
		if ( isset( $params['rubrics'] ) ) {
			update_post_meta( $asg_id, '_stackead_rubric_criteria', wp_json_encode( (array) $params['rubrics'] ) );
		}
		if ( isset( $params['custom_questions'] ) ) {
			update_post_meta( $asg_id, '_stackead_custom_questions', wp_json_encode( (array) $params['custom_questions'] ) );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Atividade prática cadastrada com sucesso!',
				'id'      => $asg_id,
				'title'   => $title,
			),
			201
		);
	}

	/**
	 * GET /assignments/{id} - Get single Assignment.
	 */
	public static function get_single_assignment( \WP_REST_Request $request ): \WP_REST_Response {
		$asg_id = (int) $request['id'];
		$post = get_post( $asg_id );
		if ( ! $post || 'stackead_assignment' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Atividade não encontrada.' ), 404 );
		}

		return new \WP_REST_Response(
			array(
				'id'        => $asg_id,
				'title'     => $post->post_title,
				'prompt'    => $post->post_content,
				'cohort_id' => (int) get_post_meta( $asg_id, '_stackead_cohort_id', true ),
				'due_date'  => get_post_meta( $asg_id, '_stackead_due_date', true ),
				'max_score' => (float) ( get_post_meta( $asg_id, '_stackead_max_score', true ) ?: 100 ),
			),
			200
		);
	}

	/**
	 * POST|PUT /assignments/{id} - Update Assignment.
	 */
	public static function update_assignment( \WP_REST_Request $request ): \WP_REST_Response {
		$asg_id = (int) $request['id'];
		$post = get_post( $asg_id );
		if ( ! $post || 'stackead_assignment' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Atividade não encontrada.' ), 404 );
		}

		$params = $request->get_json_params() ?: $request->get_params();
		$update_args = array( 'ID' => $asg_id );

		if ( ! empty( $params['title'] ) ) {
			$update_args['post_title'] = sanitize_text_field( $params['title'] );
		}
		if ( isset( $params['prompt'] ) ) {
			$update_args['post_content'] = wp_kses_post( $params['prompt'] );
		}
		wp_update_post( $update_args );

		if ( isset( $params['due_date'] ) ) {
			update_post_meta( $asg_id, '_stackead_due_date', sanitize_text_field( $params['due_date'] ) );
		}
		if ( isset( $params['max_score'] ) ) {
			update_post_meta( $asg_id, '_stackead_max_score', (float) $params['max_score'] );
		}
		if ( isset( $params['cohort_id'] ) ) {
			update_post_meta( $asg_id, '_stackead_cohort_id', (int) $params['cohort_id'] );
		}
		if ( isset( $params['submission_types'] ) ) {
			$types = is_array( $params['submission_types'] ) ? $params['submission_types'] : array( $params['submission_types'] );
			update_post_meta( $asg_id, '_stackead_submission_types', wp_json_encode( array_values( array_filter( $types ) ) ) );
		}
		if ( isset( $params['rubrics'] ) ) {
			update_post_meta( $asg_id, '_stackead_rubric_criteria', wp_json_encode( (array) $params['rubrics'] ) );
		}
		if ( isset( $params['custom_questions'] ) ) {
			update_post_meta( $asg_id, '_stackead_custom_questions', wp_json_encode( (array) $params['custom_questions'] ) );
		}

		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Atividade atualizada com sucesso!' ), 200 );
	}

	/**
	 * DELETE /assignments/{id} - Delete Assignment.
	 */
	public static function delete_assignment( \WP_REST_Request $request ): \WP_REST_Response {
		$asg_id = (int) $request['id'];
		$post = get_post( $asg_id );
		if ( ! $post || 'stackead_assignment' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Atividade não encontrada.' ), 404 );
		}

		wp_delete_post( $asg_id, true );
		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Atividade excluída com sucesso.' ), 200 );
	}

	/**
	 * POST|PUT /news/{id} - Update News.
	 */
	public static function update_news( \WP_REST_Request $request ): \WP_REST_Response {
		$news_id = (int) $request['id'];
		$post = get_post( $news_id );
		if ( ! $post || 'stackead_news' !== $post->post_type ) {
			return new \WP_REST_Response( array( 'error' => 'Notícia não encontrada.' ), 404 );
		}

		$params = $request->get_json_params() ?: $request->get_params();
		$update_args = array( 'ID' => $news_id );

		if ( ! empty( $params['title'] ) ) {
			$update_args['post_title'] = sanitize_text_field( $params['title'] );
		}
		if ( isset( $params['content'] ) ) {
			$update_args['post_content'] = wp_kses_post( $params['content'] );
		}
		if ( isset( $params['summary'] ) ) {
			$clean_summary = sanitize_textarea_field( $params['summary'] );
			$update_args['post_excerpt'] = $clean_summary;
			update_post_meta( $news_id, '_stackead_summary', $clean_summary );
			update_post_meta( $news_id, '_stackead_news_summary', $clean_summary );
		}
		if ( ! empty( $params['status'] ) ) {
			$update_args['post_status'] = sanitize_text_field( $params['status'] );
		}

		wp_update_post( $update_args );

		if ( isset( $params['category_id'] ) ) {
			$cat_id = (int) $params['category_id'];
			if ( $cat_id > 0 ) {
				wp_set_post_terms( $news_id, array( $cat_id ), 'stackead_news_cat' );
			}
		}

		if ( isset( $params['cover_url'] ) ) {
			$cover_raw = (string) $params['cover_url'];
			if ( ! empty( $cover_raw ) && str_starts_with( $cover_raw, 'data:image' ) ) {
				$attach_id = self::upload_base64_image( $cover_raw, 'news-' . $news_id );
				if ( $attach_id ) {
					set_post_thumbnail( $news_id, $attach_id );
					$thumb_url = wp_get_attachment_url( $attach_id );
					if ( $thumb_url ) {
						update_post_meta( $news_id, '_stackead_news_cover_url', esc_url_raw( $thumb_url ) );
					}
				}
			} else {
				update_post_meta( $news_id, '_stackead_news_cover_url', esc_url_raw( $cover_raw ) );
			}
		}

		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Notícia atualizada com sucesso!' ), 200 );
	}

	/**
	 * POST|PUT /categories/{id} - Update/Rename Category.
	 */
	public static function update_category( \WP_REST_Request $request ): \WP_REST_Response {
		$term_id = (int) $request['id'];
		$params  = $request->get_json_params() ?: $request->get_params();
		$name    = sanitize_text_field( $params['name'] ?? '' );
		$tax     = sanitize_text_field( $params['taxonomy'] ?? 'stackead_category' );

		if ( empty( $name ) ) {
			return new \WP_REST_Response( array( 'error' => 'O nome da categoria é obrigatório.' ), 400 );
		}

		$result = wp_update_term( $term_id, $tax, array( 'name' => $name ) );
		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( array( 'error' => $result->get_error_message() ), 400 );
		}

		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Categoria renomeada com sucesso!' ), 200 );
	}

	/* ==========================================================================
	   23. Users & Contextual Cohort Members Handlers (Milestone 4)
	   ========================================================================== */

	/**
	 * Internal helper to upsert a user's contextual membership in a cohort and sync enrollments/meta.
	 *
	 * @param int    $cohort_id Cohort post ID.
	 * @param int    $user_id   WordPress user ID.
	 * @param string $role      student|master|coordinator.
	 * @param string $status    active|inactive.
	 * @return void
	 */
	private static function sync_cohort_member_internal( int $cohort_id, int $user_id, string $role, string $status = 'active' ): void {
		if ( $cohort_id <= 0 || $user_id <= 0 ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'stackead_cohort_members';
		$now   = current_time( 'mysql' );

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE cohort_id = %d AND user_id = %d",
				$cohort_id,
				$user_id
			)
		);

		if ( $existing ) {
			$wpdb->update(
				$table,
				array(
					'role'       => $role,
					'status'     => $status,
					'updated_at' => $now,
				),
				array( 'id' => $existing ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);
		} else {
			$wpdb->insert(
				$table,
				array(
					'cohort_id'  => $cohort_id,
					'user_id'    => $user_id,
					'role'       => $role,
					'status'     => $status,
					'created_at' => $now,
					'updated_at' => $now,
				),
				array( '%d', '%d', '%s', '%s', '%s', '%s' )
			);
		}

		if ( 'active' === $status ) {
			if ( 'student' === $role ) {
				\StackEAD\Services\EnrollmentService::enroll( $user_id, $cohort_id );
			} elseif ( in_array( $role, array( 'master', 'professor', 'facilitador', 'tutor' ), true ) ) {
				$raw_masters = (string) get_post_meta( $cohort_id, '_stackead_master_ids', true );
				$master_ids  = array_filter( array_map( 'intval', explode( ',', $raw_masters ) ) );
				if ( ! in_array( $user_id, $master_ids, true ) ) {
					$master_ids[] = $user_id;
					update_post_meta( $cohort_id, '_stackead_master_ids', implode( ',', $master_ids ) );
				}
			}
		}
	}

	/**
	 * GET /users - List all platform users with global (multi-role) and cohort-specific contextual roles.
	 */
	public static function get_users_list( \WP_REST_Request $request ): \WP_REST_Response {
		global $wpdb;
		$search = sanitize_text_field( (string) $request->get_param( 'search' ) );

		$args = array(
			'number'  => 300,
			'orderby' => 'display_name',
			'order'   => 'ASC',
		);

		if ( ! empty( $search ) ) {
			$args['search']         = '*' . $search . '*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
		}

		$users         = get_users( $args );
		$table_members = $wpdb->prefix . 'stackead_cohort_members';
		$priority      = array( 'administrator', 'stackead_coordinator', 'stackead_master', 'stackead_student' );
		$output        = array();

		foreach ( $users as $u ) {
			$memberships = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT cm.id, cm.cohort_id, cm.role, cm.status, p.post_title as cohort_title
					FROM {$table_members} cm
					INNER JOIN {$wpdb->posts} p ON cm.cohort_id = p.ID
					WHERE cm.user_id = %d
					ORDER BY p.post_title ASC",
					$u->ID
				)
			);

			if ( ! empty( $memberships ) ) {
				foreach ( $memberships as $m ) {
					$c_id            = (int) get_post_meta( (int) $m->cohort_id, '_stackead_course_id', true );
					$m->course_title = $c_id ? get_the_title( $c_id ) : '';
				}
			}

			$user_roles   = array_values( (array) $u->roles );
			$primary_role = ! empty( $user_roles ) ? $user_roles[0] : 'stackead_student';
			foreach ( $priority as $p_role ) {
				if ( in_array( $p_role, $user_roles, true ) ) {
					$primary_role = $p_role;
					break;
				}
			}

			$output[] = array(
				'id'           => $u->ID,
				'name'         => $u->display_name ?: $u->user_login,
				'email'        => $u->user_email,
				'username'     => $u->user_login,
				'avatar_url'   => get_avatar_url( $u->ID, array( 'size' => 96 ) ),
				'roles'        => $user_roles,
				'primary_role' => $primary_role,
				'role_label'   => AccessControl::get_user_role_label( $u ),
				'memberships'  => ! empty( $memberships ) ? $memberships : array(),
			);
		}

		return new \WP_REST_Response( $output, 200 );
	}

	/**
	 * POST /users - Create new user with multiple global roles and optional cohort memberships.
	 */
	public static function create_user_admin( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params() ?: $request->get_params();

		$name        = sanitize_text_field( $params['name'] ?? '' );
		$email       = sanitize_email( $params['email'] ?? '' );
		$username    = sanitize_user( $params['username'] ?? '' );
		$password    = (string) ( $params['password'] ?? '' );
		$global_role = sanitize_text_field( $params['global_role'] ?? 'stackead_student' );
		$cohort_id   = (int) ( $params['cohort_id'] ?? 0 );
		$cohort_role = sanitize_text_field( $params['cohort_role'] ?? 'student' );

		// Parse multi-role array (global_roles) or fallback to single global_role
		$global_roles = array();
		if ( ! empty( $params['global_roles'] ) && is_array( $params['global_roles'] ) ) {
			$global_roles = array_values( array_filter( array_map( 'sanitize_text_field', $params['global_roles'] ) ) );
		} elseif ( ! empty( $global_role ) ) {
			$global_roles = array_values( array_filter( array_map( 'sanitize_text_field', explode( ',', $global_role ) ) ) );
		}
		if ( empty( $global_roles ) ) {
			$global_roles = array( 'stackead_student' );
		}

		if ( empty( $email ) || ! is_email( $email ) ) {
			return new \WP_REST_Response( array( 'error' => 'E-mail institucional inválido.' ), 400 );
		}

		if ( empty( $name ) ) {
			return new \WP_REST_Response( array( 'error' => 'O nome do usuário é obrigatório.' ), 400 );
		}

		if ( email_exists( $email ) ) {
			return new \WP_REST_Response( array( 'error' => 'Este e-mail já está cadastrado no sistema.' ), 400 );
		}

		if ( empty( $username ) ) {
			$parts     = explode( '@', $email );
			$base_user = sanitize_user( $parts[0], true );
			$username  = $base_user;
			$i         = 1;
			while ( username_exists( $username ) ) {
				$username = $base_user . $i++;
			}
		} elseif ( username_exists( $username ) ) {
			return new \WP_REST_Response( array( 'error' => 'Este nome de usuário já está em uso.' ), 400 );
		}

		if ( empty( $password ) ) {
			$password = wp_generate_password( 12, true );
		}

		$initial_role = reset( $global_roles ) ?: 'stackead_student';
		if ( 'administrator' === $initial_role && ! current_user_can( 'manage_options' ) ) {
			$initial_role = 'stackead_student';
		}

		$user_id = wp_insert_user(
			array(
				'user_login'   => $username,
				'user_pass'    => $password,
				'user_email'   => $email,
				'display_name' => $name,
				'role'         => $initial_role,
			)
		);

		if ( is_wp_error( $user_id ) ) {
			return new \WP_REST_Response( array( 'error' => $user_id->get_error_message() ), 400 );
		}

		// Assign all selected global roles simultaneously
		$assigned_roles = \StackEAD\Roles\RoleManager::set_user_roles(
			(int) $user_id,
			$global_roles,
			current_user_can( 'manage_options' )
		);

		// Process multiple contextual cohort memberships (if provided as array)
		if ( ! empty( $params['memberships'] ) && is_array( $params['memberships'] ) ) {
			foreach ( $params['memberships'] as $m_item ) {
				$m_cohort_id = (int) ( $m_item['cohort_id'] ?? 0 );
				$m_role      = sanitize_text_field( $m_item['role'] ?? 'student' );
				if ( $m_cohort_id > 0 ) {
					self::sync_cohort_member_internal( $m_cohort_id, (int) $user_id, $m_role, 'active' );
				}
			}
		} elseif ( $cohort_id > 0 ) {
			// Legacy single cohort_id + cohort_role fallback
			self::sync_cohort_member_internal( $cohort_id, (int) $user_id, $cohort_role, 'active' );
		}

		return new \WP_REST_Response(
			array(
				'success'  => true,
				'user_id'  => $user_id,
				'username' => $username,
				'email'    => $email,
				'roles'    => $assigned_roles,
				'message'  => 'Usuário cadastrado com sucesso!',
			),
			201
		);
	}

	/**
	 * PUT /users/{id} - Update user profile, multiple global roles, cohort memberships or reset password.
	 */
	public static function update_user_admin( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id = (int) $request['id'];
		$params  = $request->get_json_params() ?: $request->get_params();

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new \WP_REST_Response( array( 'error' => 'Usuário não encontrado.' ), 404 );
		}

		$data = array( 'ID' => $user_id );

		if ( isset( $params['name'] ) && ! empty( trim( $params['name'] ) ) ) {
			$data['display_name'] = sanitize_text_field( $params['name'] );
		}

		if ( isset( $params['email'] ) && ! empty( trim( $params['email'] ) ) ) {
			$new_email = sanitize_email( $params['email'] );
			if ( $new_email !== $user->user_email && email_exists( $new_email ) ) {
				return new \WP_REST_Response( array( 'error' => 'E-mail já está em uso por outro usuário.' ), 400 );
			}
			$data['user_email'] = $new_email;
		}

		if ( ! empty( $params['password'] ) ) {
			$data['user_pass'] = (string) $params['password'];
		}

		$updated = wp_update_user( $data );
		if ( is_wp_error( $updated ) ) {
			return new \WP_REST_Response( array( 'error' => $updated->get_error_message() ), 400 );
		}

		// Handle multi-role or single-role updates without wiping secondary roles
		$roles_to_assign = array();
		if ( ! empty( $params['global_roles'] ) && is_array( $params['global_roles'] ) ) {
			$roles_to_assign = array_values( array_filter( array_map( 'sanitize_text_field', $params['global_roles'] ) ) );
		} elseif ( ! empty( $params['global_role'] ) ) {
			$roles_to_assign = array_values( array_filter( array_map( 'sanitize_text_field', explode( ',', (string) $params['global_role'] ) ) ) );
		}

		$assigned_roles = (array) $user->roles;
		if ( ! empty( $roles_to_assign ) ) {
			$can_assign_admin = current_user_can( 'manage_options' );
			// Prevent accidental self-demotion of main administrator (ID 1 or current user if admin)
			if ( ( 1 === $user_id || ( $user_id === get_current_user_id() && $can_assign_admin ) ) && in_array( 'administrator', (array) $user->roles, true ) ) {
				if ( ! in_array( 'administrator', $roles_to_assign, true ) ) {
					$roles_to_assign[] = 'administrator';
				}
			}
			// If a Coordinator edits an existing Admin, preserve administrator role
			if ( ! $can_assign_admin && in_array( 'administrator', (array) $user->roles, true ) ) {
				$roles_to_assign[] = 'administrator';
				$can_assign_admin  = true;
			}

			$assigned_roles = \StackEAD\Roles\RoleManager::set_user_roles( $user_id, $roles_to_assign, $can_assign_admin );
		}

		// Optional inline cohort memberships sync during user update
		if ( ! empty( $params['memberships'] ) && is_array( $params['memberships'] ) ) {
			foreach ( $params['memberships'] as $m_item ) {
				$m_cohort_id = (int) ( $m_item['cohort_id'] ?? 0 );
				$m_role      = sanitize_text_field( $m_item['role'] ?? 'student' );
				if ( $m_cohort_id > 0 ) {
					self::sync_cohort_member_internal( $m_cohort_id, $user_id, $m_role, 'active' );
				}
			}
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'roles'   => $assigned_roles,
				'message' => 'Usuário atualizado com sucesso!',
			),
			200
		);
	}

	/**
	 * DELETE /users/{id} - Delete user safely.
	 */
	public static function delete_user_admin( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id = (int) $request['id'];
		$current = get_current_user_id();

		if ( 1 === $user_id || $current === $user_id ) {
			return new \WP_REST_Response( array( 'error' => 'Não é permitido excluir o administrador principal ou seu próprio usuário.' ), 400 );
		}

		require_once ABSPATH . 'wp-admin/includes/user.php';
		$deleted = wp_delete_user( $user_id, 1 );

		if ( ! $deleted ) {
			return new \WP_REST_Response( array( 'error' => 'Falha ao remover o usuário.' ), 500 );
		}

		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'stackead_cohort_members', array( 'user_id' => $user_id ), array( '%d' ) );
		$wpdb->delete( $wpdb->prefix . 'stackead_enrollments', array( 'user_id' => $user_id ), array( '%d' ) );

		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Usuário excluído com sucesso!' ), 200 );
	}

	/**
	 * GET /cohorts/{id}/members - Get members of a cohort with their roles, attendance and progress.
	 */
	public static function get_cohort_members_list( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];
		global $wpdb;

		$table = $wpdb->prefix . 'stackead_cohort_members';
		$members = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT cm.*, u.display_name, u.user_email, u.user_login
				FROM {$table} cm
				INNER JOIN {$wpdb->users} u ON cm.user_id = u.ID
				WHERE cm.cohort_id = %d
				ORDER BY u.display_name ASC",
				$cohort_id
			)
		);

		$output = array();
		foreach ( (array) $members as $m ) {
			$progress   = \StackEAD\Services\ProgressService::calculate_cohort_progress( (int) $m->user_id, $cohort_id );
			$attendance = \StackEAD\Services\AttendanceService::calculate_attendance_percent( (int) $m->user_id, $cohort_id );

			$output[] = array(
				'id'           => (int) $m->id,
				'user_id'      => (int) $m->user_id,
				'name'         => $m->display_name ?: $m->user_login,
				'email'        => $m->user_email,
				'username'     => $m->user_login,
				'avatar_url'   => get_avatar_url( $m->user_id, array( 'size' => 64 ) ),
				'role'         => $m->role,
				'status'       => $m->status,
				'progress'     => round( $progress, 1 ),
				'attendance'   => round( $attendance, 1 ),
				'joined_at'    => $m->created_at,
			);
		}

		return new \WP_REST_Response( $output, 200 );
	}

	/**
	 * POST /cohorts/{id}/members - Add or update a member in cohort.
	 */
	public static function save_cohort_member( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];
		$params    = $request->get_json_params() ?: $request->get_params();

		$user_id = (int) ( $params['user_id'] ?? 0 );
		$role    = sanitize_text_field( $params['role'] ?? 'student' );
		$status  = sanitize_text_field( $params['status'] ?? 'active' );

		if ( ! $user_id ) {
			return new \WP_REST_Response( array( 'error' => 'ID do usuário é obrigatório.' ), 400 );
		}

		self::sync_cohort_member_internal( $cohort_id, $user_id, $role, $status );

		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Membro vinculado com sucesso!' ), 200 );
	}

	/**
	 * DELETE /cohorts/{id}/members/{user_id} - Remove member from cohort.
	 */
	public static function delete_cohort_member( \WP_REST_Request $request ): \WP_REST_Response {
		$cohort_id = (int) $request['id'];
		$user_id   = (int) $request['user_id'];

		global $wpdb;
		$table = $wpdb->prefix . 'stackead_cohort_members';
		$wpdb->delete(
			$table,
			array(
				'cohort_id' => $cohort_id,
				'user_id'   => $user_id,
			),
			array( '%d', '%d' )
		);

		return new \WP_REST_Response( array( 'success' => true, 'message' => 'Membro desvinculado da turma.' ), 200 );
	}

	/* ==========================================================================
	   24. COGA Neurodiversity Preferences Handlers (Milestone 7)
	   ========================================================================== */

	/**
	 * GET /accommodations/preferences - Retrieve persisted accessibility preferences.
	 */
	public static function get_coga_preferences( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id = get_current_user_id();
		$saved   = get_user_meta( $user_id, '_stackead_coga_preferences', true );

		if ( ! is_array( $saved ) ) {
			$saved = array(
				'dyslexic_font'   => false,
				'reading_guide'   => false,
				'high_contrast'   => false,
				'line_spacing'    => 'normal',
				'sound_dampening' => false,
			);
		}

		return new \WP_REST_Response( $saved, 200 );
	}

	/**
	 * POST /accommodations/preferences - Save accessibility preferences.
	 */
	public static function save_coga_preferences( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id = get_current_user_id();
		$params  = $request->get_json_params() ?: $request->get_params();

		$clean = array(
			'dyslexic_font'   => ! empty( $params['dyslexic_font'] ),
			'reading_guide'   => ! empty( $params['reading_guide'] ),
			'high_contrast'   => ! empty( $params['high_contrast'] ),
			'line_spacing'    => sanitize_text_field( $params['line_spacing'] ?? 'normal' ),
			'sound_dampening' => ! empty( $params['sound_dampening'] ),
		);

		update_user_meta( $user_id, '_stackead_coga_preferences', $clean );

		return new \WP_REST_Response( $clean, 200 );
	}

	/**
	 * POST /finance/checkout-instant - Process immediate payment and enroll user.
	 */
	public static function checkout_instant( \WP_REST_Request $request ): \WP_REST_Response {
		$params    = $request->get_json_params() ?: $request->get_params();
		$user_id   = get_current_user_id();
		$cohort_id = (int) ( $params['cohort_id'] ?? 0 );
		$amount    = (float) ( $params['amount'] ?? 0 );
		$gateway   = sanitize_text_field( $params['gateway'] ?? 'mercadopago' );
		$tx_id     = sanitize_text_field( $params['transaction_id'] ?? '' );

		if ( ! $cohort_id ) {
			return new \WP_REST_Response( array( 'error' => 'Turma não especificada.' ), 400 );
		}

		// Verify course pricing to prevent unauthorized free bypass on paid courses
		$course_id   = (int) get_post_meta( $cohort_id, '_stackead_course_id', true );
		$course_type = $course_id ? (string) get_post_meta( $course_id, '_stackead_price_type', true ) : 'free';
		$course_price = $course_id ? (float) get_post_meta( $course_id, '_stackead_price', true ) : 0.0;

		if ( 'free' !== $course_type && $course_price > 0 && ! self::check_coordinator_or_admin() ) {
			return new \WP_REST_Response( array( 'error' => 'Cursos pagos exigem confirmação do gateway de pagamento ou liberação da coordenação.' ), 403 );
		}

		$result = FinanceService::process_instant_enrollment( $user_id, $cohort_id, $amount, $gateway, $tx_id );
		return new \WP_REST_Response( $result, 200 );
	}

	/**
	 * POST /finance/payment-webhook - Webhook for external payment gateways (Protected by cryptographic secret).
	 */
	public static function payment_webhook( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params() ?: $request->get_params();

		// Ensure webhook secret exists and verify request authenticity
		$expected_secret = (string) get_option( 'stackead_webhook_secret', '' );
		if ( empty( $expected_secret ) ) {
			$expected_secret = wp_generate_password( 32, false );
			update_option( 'stackead_webhook_secret', $expected_secret, false );
		}

		$provided_secret = (string) ( $request->get_header( 'X-StackEAD-Webhook-Secret' ) ?: ( $params['webhook_secret'] ?? '' ) );
		if ( ! current_user_can( 'manage_options' ) && ( empty( $provided_secret ) || ! hash_equals( $expected_secret, $provided_secret ) ) ) {
			return new \WP_REST_Response( array( 'error' => 'Assinatura de webhook inválida ou ausente.' ), 403 );
		}

		$order_id = (int) ( $params['order_id'] ?? 0 );
		$status   = sanitize_text_field( $params['status'] ?? 'completed' );

		if ( $order_id && 'completed' === $status ) {
			FinanceService::complete_order( $order_id );
			return new \WP_REST_Response( array( 'success' => true, 'message' => 'Pedido confirmado e matrícula liberada.' ), 200 );
		}

		return new \WP_REST_Response( array( 'received' => true ), 200 );
	}

	/**
	 * GET /gamification/admin/settings - Get virtual coins & badges settings.
	 */
	public static function get_gamification_admin_settings(): \WP_REST_Response {
		return new \WP_REST_Response(
			array(
				'config' => GamificationService::get_gamification_config(),
				'badges' => GamificationService::get_all_badges(),
			),
			200
		);
	}

	/**
	 * POST /gamification/admin/settings - Save coin rules.
	 */
	public static function save_gamification_admin_settings( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params() ?: $request->get_params();
		GamificationService::save_gamification_config( (array) $params );
		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Configurações de moedas e gamificação salvas com sucesso!',
				'config'  => GamificationService::get_gamification_config(),
			),
			200
		);
	}

	/**
	 * POST /gamification/admin/badges - Save or create custom badge.
	 */
	public static function save_gamification_admin_badge( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params() ?: $request->get_params();
		if ( empty( $params['title'] ) ) {
			return new \WP_REST_Response( array( 'error' => 'O título do emblema é obrigatório.' ), 400 );
		}

		$saved = GamificationService::save_custom_badge( (array) $params );
		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Emblema salvo com sucesso!',
				'badge'   => $saved,
			),
			200
		);
	}

	/**
	 * DELETE /gamification/admin/badges/{key} - Delete custom badge.
	 */
	public static function delete_gamification_admin_badge( \WP_REST_Request $request ): \WP_REST_Response {
		$key = sanitize_key( $request['key'] );
		$success = GamificationService::delete_custom_badge( $key );
		return new \WP_REST_Response(
			array(
				'success' => $success,
				'message' => $success ? 'Emblema removido com sucesso.' : 'Não foi possível remover este emblema.',
			),
			200
		);
	}

	/**
	 * Helper: Mask IP address for LGPD/GDPR audit trails (IPv4 /24 or IPv6 /48 anonymization).
	 */
	private static function mask_ip( string $ip ): string {
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$parts = explode( '.', $ip );
			$parts[3] = '0';
			return implode( '.', $parts );
		}
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			$parts = explode( ':', $ip );
			return implode( ':', array_slice( $parts, 0, 3 ) ) . '::';
		}
		return 'masked';
	}

	/**
	 * GET /privacy/consent - Get logged-in user's granular LGPD/GDPR consent settings.
	 */
	public static function get_my_privacy_consent(): \WP_REST_Response {
		$user_id = get_current_user_id();
		$saved   = get_user_meta( $user_id, '_stackead_privacy_consents', true );

		$defaults = array(
			'essential_academic'      => true,
			'academic_communications' => true,
			'learning_analytics'      => false,
			'international_transfer'  => true,
			'policy_version'          => 'v2.0-LGPD-GDPR-2026',
			'updated_at'              => '',
			'masked_ip'               => '',
		);

		$consents = is_array( $saved ) ? wp_parse_args( $saved, $defaults ) : $defaults;
		$erasure  = get_user_meta( $user_id, '_stackead_erasure_request', true );

		return new \WP_REST_Response(
			array(
				'consents'        => $consents,
				'erasure_request' => is_array( $erasure ) ? $erasure : null,
				'dpo_email'       => 'contact@artinnovaresidency.com',
				'regulations'     => array( 'LGPD (Lei 13.709/2018 - Brasil)', 'GDPR / RGPD (UE 2016/679 - França/UE)' ),
			),
			200
		);
	}

	/**
	 * POST /privacy/consent - Update granular LGPD/GDPR consent preferences.
	 */
	public static function save_my_privacy_consent( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id = get_current_user_id();
		$params  = $request->get_json_params() ?: $request->get_params();
		$ip      = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );

		$consents = array(
			'essential_academic'      => true, // Contract execution / Legal obligation (LGPD Art. 7 V / GDPR Art. 6.1.b)
			'academic_communications' => ! empty( $params['academic_communications'] ),
			'learning_analytics'      => ! empty( $params['learning_analytics'] ),
			'international_transfer'  => ! empty( $params['international_transfer'] ),
			'policy_version'          => 'v2.0-LGPD-GDPR-2026',
			'updated_at'              => current_time( 'mysql' ),
			'masked_ip'               => self::mask_ip( $ip ),
		);

		update_user_meta( $user_id, '_stackead_privacy_consents', $consents );

		return new \WP_REST_Response(
			array(
				'success'  => true,
				'message'  => 'Preferências de privacidade (LGPD/GDPR) registradas com sucesso!',
				'consents' => $consents,
			),
			200
		);
	}

	/**
	 * GET /privacy/my-data - Right of Access & Data Portability JSON export (LGPD Art. 18 II,V / GDPR Arts. 15, 20).
	 */
	public static function export_my_personal_data(): \WP_REST_Response {
		global $wpdb;
		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );

		if ( ! $user ) {
			return new \WP_REST_Response( array( 'error' => 'Usuário não encontrado.' ), 404 );
		}

		$cohort_members_table = $wpdb->prefix . 'stackead_cohort_members';
		$enrollments_table    = $wpdb->prefix . 'stackead_enrollments';
		$progress_table       = $wpdb->prefix . 'stackead_progress';
		$attendances_table    = $wpdb->prefix . 'stackead_attendances';
		$submissions_table    = $wpdb->prefix . 'stackead_submissions';
		$quiz_attempts_table  = $wpdb->prefix . 'stackead_quiz_attempts';
		$points_table         = $wpdb->prefix . 'stackead_gamification_points';
		$badges_table         = $wpdb->prefix . 'stackead_gamification_badges';
		$orders_table         = $wpdb->prefix . 'stackead_orders';
		$leads_table          = $wpdb->prefix . 'stackead_leads';

		$safe_query = static function ( string $sql ) use ( $wpdb ) {
			$res = $wpdb->get_results( $sql, ARRAY_A );
			return is_array( $res ) ? $res : array();
		};

		$consents = get_user_meta( $user_id, '_stackead_privacy_consents', true );
		$erasure  = get_user_meta( $user_id, '_stackead_erasure_request', true );

		$export_package = array(
			'schema'                 => 'ArtInnova-StackEAD-PrivacyExport-v2.0',
			'legal_framework'        => array( 'LGPD (Lei 13.709/2018 - Art. 18 II & V)', 'GDPR (Regulation EU 2016/679 - Arts. 15 & 20)' ),
			'exported_at'            => gmdate( 'c' ),
			'data_controller'        => 'Residência Internacional ArtInnova (Brasil · França)',
			'dpo_contact'            => 'contact@artinnovaresidency.com',
			'account'                => array(
				'id'           => (int) $user->ID,
				'username'     => $user->user_login,
				'display_name' => $user->display_name,
				'first_name'   => $user->first_name,
				'last_name'    => $user->last_name,
				'email'        => $user->user_email,
				'registered'   => $user->user_registered,
				'roles'        => array_values( (array) $user->roles ),
				'avatar_url'   => get_user_meta( $user_id, '_stackead_avatar_url', true ) ?: '',
			),
			'privacy_consents'       => is_array( $consents ) ? $consents : array( 'policy_version' => 'v2.0-LGPD-GDPR-2026', 'essential_academic' => true ),
			'erasure_request_status' => is_array( $erasure ) ? $erasure : null,
			'cohort_memberships'     => $safe_query( $wpdb->prepare( "SELECT cohort_id, role, status, created_at FROM {$cohort_members_table} WHERE user_id = %d", $user_id ) ),
			'enrollments'            => $safe_query( $wpdb->prepare( "SELECT cohort_id, status, enrolled_at, completed_at, created_at FROM {$enrollments_table} WHERE user_id = %d", $user_id ) ),
			'lesson_progress'        => $safe_query( $wpdb->prepare( "SELECT lesson_id, cohort_id, completion_percent, seconds_watched, completed_at, updated_at FROM {$progress_table} WHERE user_id = %d", $user_id ) ),
			'attendances'            => $safe_query( $wpdb->prepare( "SELECT session_id, cohort_id, status, minutes_attended, created_at FROM {$attendances_table} WHERE user_id = %d", $user_id ) ),
			'submissions'            => $safe_query( $wpdb->prepare( "SELECT id, assignment_id, cohort_id, status, score, feedback, created_at FROM {$submissions_table} WHERE user_id = %d", $user_id ) ),
			'quiz_attempts'          => $safe_query( $wpdb->prepare( "SELECT id, quiz_id, cohort_id, score, passed, attempt_number, completed_at FROM {$quiz_attempts_table} WHERE user_id = %d", $user_id ) ),
			'gamification'           => array(
				'points_ledger' => $safe_query( $wpdb->prepare( "SELECT points, point_type, event_action, description, created_at FROM {$points_table} WHERE user_id = %d ORDER BY created_at DESC", $user_id ) ),
				'badges'        => $safe_query( $wpdb->prepare( "SELECT badge_key, title, description, earned_at FROM {$badges_table} WHERE user_id = %d", $user_id ) ),
			),
			'orders'                 => $safe_query( $wpdb->prepare( "SELECT id, cohort_id, plan_key, amount, currency, status, payment_method, created_at FROM {$orders_table} WHERE user_id = %d", $user_id ) ),
			'related_leads'          => $safe_query( $wpdb->prepare( "SELECT id, name, email, city, profile, consent_given, consent_version, lang, created_at FROM {$leads_table} WHERE email = %s", $user->user_email ) ),
		);

		return new \WP_REST_Response( $export_package, 200 );
	}

	/**
	 * POST /privacy/request-erasure - Formal Right to Erasure / Revocation Request (LGPD Art. 18 VI / GDPR Art. 17).
	 */
	public static function request_account_erasure( \WP_REST_Request $request ): \WP_REST_Response {
		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return new \WP_REST_Response( array( 'error' => 'Usuário não encontrado.' ), 404 );
		}

		$params = $request->get_json_params() ?: $request->get_params();
		$reason = sanitize_textarea_field( $params['reason'] ?? '' );
		$scope  = sanitize_text_field( $params['scope'] ?? 'full_erasure_or_anonymization' );
		$ip     = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );

		$protocol_id = 'PRIV-' . gmdate( 'Ymd' ) . '-' . strtoupper( substr( wp_generate_password( 6, false ), 0, 6 ) );
		$record      = array(
			'protocol_id'  => $protocol_id,
			'user_id'      => (int) $user_id,
			'user_email'   => $user->user_email,
			'display_name' => $user->display_name,
			'scope'        => $scope,
			'reason'       => $reason ?: 'Solicitação direta pelo Painel do Titular (LGPD/GDPR).',
			'status'       => 'pending_dpo_review',
			'requested_at' => current_time( 'mysql' ),
			'masked_ip'    => self::mask_ip( $ip ),
		);

		update_user_meta( $user_id, '_stackead_erasure_request', $record );

		$all_requests = get_option( 'stackead_gdpr_erasure_requests', array() );
		if ( ! is_array( $all_requests ) ) {
			$all_requests = array();
		}
		$all_requests[ $protocol_id ] = $record;
		update_option( 'stackead_gdpr_erasure_requests', $all_requests, false );

		$dpo_email = 'contact@artinnovaresidency.com';
		$headers   = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: Residência ArtInnova <contact@artinnovaresidency.com>',
			'Reply-To: ' . $user->display_name . ' <' . $user->user_email . '>',
		);

		$dpo_subject = sprintf( '[LGPD/GDPR DPO] Solicitação de Exclusão/Revogação #%s (%s)', $protocol_id, $user->display_name );
		$dpo_body    = "Nova solicitação formal de Direitos do Titular registrada na plataforma EAD:\n\n"
					 . "Protocolo: {$protocol_id}\n"
					 . "Titular: {$user->display_name} (ID #{$user_id})\n"
					 . "E-mail: {$user->user_email}\n"
					 . "Escopo: {$scope}\n"
					 . "Justificativa: {$record['reason']}\n"
					 . "Data/Hora: {$record['requested_at']}\n\n"
					 . "Prazo legal de atendimento:\n"
					 . "• LGPD (Brasil - Art. 19): até 15 dias.\n"
					 . "• GDPR (União Europeia - Art. 12.3): até 1 mês (30 dias).\n";
		@wp_mail( $dpo_email, $dpo_subject, $dpo_body, $headers );

		$user_subject = sprintf( '[ArtInnova] Protocolo de Privacidade LGPD/GDPR #%s', $protocol_id );
		$user_body    = "Olá, {$user->display_name},\n\n"
					  . "Confirmamos o recebimento da sua solicitação de exercício de direitos de titularidade de dados pessoais (LGPD Lei nº 13.709/2018 / GDPR Regulamento UE 2016/679).\n\n"
					  . "• Número de Protocolo: {$protocol_id}\n"
					  . "• Data do Registro: {$record['requested_at']}\n"
					  . "• Encarregado pelo Tratamento de Dados (DPO): contact@artinnovaresidency.com\n\n"
					  . "Nossa equipe de proteção de dados analisará e processará sua solicitação dentro dos prazos legais (15 dias LGPD / 30 dias GDPR).\n\n"
					  . "Atenciosamente,\n"
					  . "Residência Internacional ArtInnova\n";
		@wp_mail( $user->user_email, $user_subject, $user_body, array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: Residência ArtInnova <contact@artinnovaresidency.com>',
			'Reply-To: DPO ArtInnova <contact@artinnovaresidency.com>',
		) );

		return new \WP_REST_Response(
			array(
				'success'     => true,
				'protocol_id' => $protocol_id,
				'record'      => $record,
				'message'     => "Solicitação registrada sob o protocolo {$protocol_id} e encaminhada ao Encarregado de Dados (DPO).",
			),
			200
		);
	}
}
