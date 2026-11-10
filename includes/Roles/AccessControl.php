<?php
namespace StackEAD\Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AccessControl - Strict Role & Permission Guard for StackEAD Core.
 *
 * Enforces:
 * 1. Admin Bar is ONLY visible to administrators (hidden for students, masters, coordinators, subscribers).
 * 2. /wp-admin is STRICTLY restricted to administrators; all non-admins are intercepted and redirected to /plataforma.
 * 3. Login redirect automatically sends non-administrators to /plataforma.
 * 4. Dynamic navigation structure according to user role and category.
 */
class AccessControl {

	/**
	 * Initialize security hooks.
	 */
	public static function init(): void {
		// 1. Enforce Admin Bar visibility (Admin only)
		add_action( 'init', array( __CLASS__, 'enforce_admin_bar_visibility' ), 9 );
		add_action( 'after_setup_theme', array( __CLASS__, 'enforce_admin_bar_visibility' ), 9 );
		add_filter( 'show_admin_bar', array( __CLASS__, 'filter_show_admin_bar' ), 999 );

		// 2. Lock down /wp-admin for non-administrators
		add_action( 'admin_init', array( __CLASS__, 'restrict_admin_access' ), 1 );

		// 3. Login redirect to /plataforma for non-administrators
		add_filter( 'login_redirect', array( __CLASS__, 'redirect_after_login' ), 10, 3 );
	}

	/**
	 * Check if a given user (or the current user) is a full WordPress administrator.
	 *
	 * @param \WP_User|null $user
	 * @return bool
	 */
	public static function is_administrator( ?\WP_User $user = null ): bool {
		if ( ! $user ) {
			if ( ! is_user_logged_in() ) {
				return false;
			}
			$user = wp_get_current_user();
		}

		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		if ( in_array( 'administrator', (array) $user->roles, true ) ) {
			return true;
		}

		if ( $user->has_cap( 'manage_options' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Enforce that show_admin_bar is strictly disabled for non-administrators.
	 */
	public static function enforce_admin_bar_visibility(): void {
		if ( ! self::is_administrator() ) {
			show_admin_bar( false );
		}
	}

	/**
	 * Filter show_admin_bar output.
	 *
	 * @param bool $show
	 * @return bool
	 */
	public static function filter_show_admin_bar( bool $show ): bool {
		if ( ! self::is_administrator() ) {
			return false;
		}
		return $show;
	}

	/**
	 * Restrict access to /wp-admin:
	 * Only administrators can access any part of wp-admin.
	 * All non-administrators are redirected immediately to /plataforma.
	 */
	public static function restrict_admin_access(): void {
		// Allow AJAX requests (frontend calls, heartbeat, etc.)
		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
			return;
		}
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// Allow WP-Cron executions
		if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
			return;
		}
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		// Allow REST API calls
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		// If user is not logged in, WordPress will handle authentication redirect
		if ( ! is_user_logged_in() ) {
			return;
		}

		// If user is not administrator, block access and redirect to standalone platform
		if ( ! self::is_administrator() ) {
			wp_safe_redirect( home_url( '/plataforma' ) );
			exit;
		}
	}

	/**
	 * Redirect users after login:
	 * Non-administrators go directly to /plataforma.
	 *
	 * @param string           $redirect_to
	 * @param string           $request
	 * @param \WP_User|\WP_Error|null $user
	 * @return string
	 */
	public static function redirect_after_login( $redirect_to, $request, $user ) {
		if ( is_a( $user, 'WP_User' ) ) {
			if ( ! self::is_administrator( $user ) ) {
				return home_url( '/plataforma' );
			}
		}
		return $redirect_to;
	}

	/**
	 * Get human-readable role and category label in Portuguese.
	 * Supports multiple simultaneous roles (e.g. "Administrador • Professor").
	 *
	 * @param \WP_User|null $user
	 * @return string
	 */
	public static function get_user_role_label( ?\WP_User $user = null ): string {
		if ( ! $user ) {
			$user = wp_get_current_user();
		}

		if ( ! $user || ! $user->exists() ) {
			return __( 'Visitante', 'stackead-core' );
		}

		$roles  = (array) $user->roles;
		$labels = array();

		if ( in_array( 'administrator', $roles, true ) ) {
			$labels[] = __( 'Administrador', 'stackead-core' );
		}

		if ( in_array( 'stackead_coordinator', $roles, true ) ) {
			$labels[] = __( 'Coordenador', 'stackead-core' );
		}

		if ( in_array( 'stackead_master', $roles, true ) ) {
			$labels[] = __( 'Professor / Mestre', 'stackead-core' );
		}

		if ( in_array( 'stackead_student', $roles, true ) || in_array( 'subscriber', $roles, true ) ) {
			$labels[] = __( 'Estudante', 'stackead-core' );
		}

		if ( empty( $labels ) ) {
			return __( 'Estudante', 'stackead-core' );
		}

		if ( count( $labels ) === 1 ) {
			if ( in_array( 'administrator', $roles, true ) ) {
				return __( 'Administrador Geral', 'stackead-core' );
			}
			if ( in_array( 'stackead_coordinator', $roles, true ) ) {
				return __( 'Coordenação Pedagógica', 'stackead-core' );
			}
			if ( in_array( 'stackead_master', $roles, true ) ) {
				return __( 'Mestre / Instrutor', 'stackead-core' );
			}
			return __( 'Estudante EAD', 'stackead-core' );
		}

		return implode( ' • ', $labels );
	}

	/**
	 * Get dynamic navigation sections and allowed view keys tailored to user role & category.
	 * Supports multi-role users and contextual cohort memberships.
	 *
	 * @param \WP_User|null $user
	 * @return array{sections: array, allowed_views: array}
	 */
	public static function get_user_navigation( ?\WP_User $user = null ): array {
		if ( ! $user ) {
			$user = wp_get_current_user();
		}

		$is_admin  = self::is_administrator( $user );
		$roles     = $user && $user->exists() ? (array) $user->roles : array();
		$is_coord  = $is_admin || in_array( 'stackead_coordinator', $roles, true );
		$is_master = $is_admin || $is_coord || in_array( 'stackead_master', $roles, true );

		// Also check contextual memberships in wp_stackead_cohort_members so users with contextual roles see the proper desks
		if ( $user && $user->exists() && ( ! $is_coord || ! $is_master ) ) {
			global $wpdb;
			$table_members = $wpdb->prefix . 'stackead_cohort_members';
			static $members_table_exists = null;
			if ( null === $members_table_exists ) {
				$members_table_exists = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_members ) );
			}
			if ( $members_table_exists ) {
				$ctx_roles = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT DISTINCT role FROM {$table_members} WHERE user_id = %d AND status = 'active'",
						$user->ID
					)
				);
				if ( is_array( $ctx_roles ) ) {
					foreach ( $ctx_roles as $cr ) {
						$cr_low = strtolower( (string) $cr );
						if ( 'coordinator' === $cr_low ) {
							$is_coord  = true;
							$is_master = true;
						} elseif ( in_array( $cr_low, array( 'master', 'professor', 'facilitador', 'tutor' ), true ) ) {
							$is_master = true;
						}
					}
				}
			}
		}

		$sections      = array();
		$allowed_views = array( 'dashboard' );

		// 1. Minha Formação / Ambiente Principal
		$main_items = array();

		if ( $is_admin ) {
			$main_title = __( 'Visão Acadêmica & Painel', 'stackead-core' );
		} elseif ( $is_coord ) {
			$main_title = __( 'Coordenação Acadêmica', 'stackead-core' );
		} elseif ( $is_master ) {
			$main_title = __( 'Minhas Turmas & Formação', 'stackead-core' );
		} else {
			$main_title = __( 'Minha Formação', 'stackead-core' );
		}

		$main_items[] = array(
			'view'  => 'dashboard',
			'title' => $is_admin ? __( 'Painel Geral', 'stackead-core' ) : ( $is_master ? __( 'Painel Acadêmico', 'stackead-core' ) : __( 'Meu Painel', 'stackead-core' ) ),
			'icon'  => 'layout-dashboard',
		);

		$main_items[] = array(
			'view'  => 'curriculum',
			'title' => $is_master ? __( 'Conteúdos & Aulas', 'stackead-core' ) : __( 'Trilha & Aulas', 'stackead-core' ),
			'icon'  => 'book-open',
		);
		$allowed_views[] = 'curriculum';
		$allowed_views[] = 'player'; // Video/lesson player view

		$main_items[] = array(
			'view'  => 'sessions',
			'title' => __( 'Sessões Ao Vivo', 'stackead-core' ),
			'icon'  => 'video',
		);
		$allowed_views[] = 'sessions';

		$main_items[] = array(
			'view'  => 'assignments',
			'title' => $is_master ? __( 'Projetos & Entregas', 'stackead-core' ) : __( 'Projetos Práticos', 'stackead-core' ),
			'icon'  => 'file-check',
		);
		$allowed_views[] = 'assignments';

		$main_items[] = array(
			'view'  => 'community',
			'title' => __( 'Fórum da Turma', 'stackead-core' ),
			'icon'  => 'message-square',
		);
		$allowed_views[] = 'community';

		// Gamification & Certificate available to all users (since Masters/Coords/Admins can also be Students in courses)
		$main_items[] = array(
			'view'  => 'gamification',
			'title' => __( 'Conquistas & XP', 'stackead-core' ),
			'icon'  => 'award',
		);
		$allowed_views[] = 'gamification';

		$main_items[] = array(
			'view'  => 'certificate',
			'title' => __( 'Certificação', 'stackead-core' ),
			'icon'  => 'file-badge',
		);
		$allowed_views[] = 'certificate';

		$sections[] = array(
			'title' => $main_title,
			'items' => $main_items,
		);

		// 2. Mesa Docente (Masters, Coordinators, Admins)
		if ( $is_master ) {
			$docente_items = array(
				array(
					'view'  => 'attendance-desk',
					'title' => __( 'Diário de Presença', 'stackead-core' ),
					'icon'  => 'user-check',
				),
				array(
					'view'  => 'grading-desk',
					'title' => __( 'Bancada de Correção', 'stackead-core' ),
					'icon'  => 'clipboard-check',
				),
			);
			$allowed_views[] = 'attendance-desk';
			$allowed_views[] = 'grading-desk';

			$sections[] = array(
				'title' => __( 'Mesa Docente', 'stackead-core' ),
				'items' => $docente_items,
			);
		}

		// 3. Gestão Pedagógica, Inclusão COGA & Finanças (Coordinators & Admins only)
		if ( $is_coord ) {
			$coord_items = array(
				array(
					'view'  => 'users-admin',
					'title' => __( 'Usuários & Membros', 'stackead-core' ),
					'icon'  => 'users',
				),
				array(
					'view'  => 'courses-admin',
					'title' => __( 'Gestão de Cursos', 'stackead-core' ),
					'icon'  => 'folder-plus',
				),
				array(
					'view'  => 'accommodations-desk',
					'title' => __( 'Laudos & COGA', 'stackead-core' ),
					'icon'  => 'heart-handshake',
				),
				array(
					'view'  => 'finance-desk',
					'title' => __( 'Finanças & Planos', 'stackead-core' ),
					'icon'  => 'trending-up',
				),
				array(
					'view'  => 'gamification-admin',
					'title' => __( 'Gestão de Gamificação', 'stackead-core' ),
					'icon'  => 'trophy',
				),
			);
			$allowed_views[] = 'users-admin';
			$allowed_views[] = 'courses-admin';
			$allowed_views[] = 'accommodations-desk';
			$allowed_views[] = 'finance-desk';
			$allowed_views[] = 'gamification-admin';

			$sections[] = array(
				'title' => __( 'Gestão & Coordenação', 'stackead-core' ),
				'items' => $coord_items,
			);
		}

		// 4. Institucional, Notícias & White Label (Administrators only)
		if ( $is_admin ) {
			$admin_special_items = array(
				array(
					'view'  => 'whitelabel-desk',
					'title' => __( 'Identidade White Label', 'stackead-core' ),
					'icon'  => 'sliders',
				),
				array(
					'view'  => 'landing-builder',
					'title' => __( 'Construtor de Landing Page', 'stackead-core' ),
					'icon'  => 'layout',
				),
				array(
					'view'  => 'news-admin',
					'title' => __( 'Mural de Notícias', 'stackead-core' ),
					'icon'  => 'newspaper',
				),
				array(
					'view'  => 'categories-admin',
					'title' => __( 'Gestão de Categorias', 'stackead-core' ),
					'icon'  => 'tag',
				),
				array(
					'view'  => 'leads-admin',
					'title' => __( 'Leads & Inscrições', 'stackead-core' ),
					'icon'  => 'user-plus',
				),
			);
			$allowed_views[] = 'whitelabel-desk';
			$allowed_views[] = 'landing-builder';
			$allowed_views[] = 'news-admin';
			$allowed_views[] = 'categories-admin';
			$allowed_views[] = 'leads-admin';

			$sections[] = array(
				'title' => __( 'Institucional & Marketing', 'stackead-core' ),
				'items' => $admin_special_items,
			);
		}

		return array(
			'sections'      => $sections,
			'allowed_views' => array_values( array_unique( $allowed_views ) ),
		);
	}
}
