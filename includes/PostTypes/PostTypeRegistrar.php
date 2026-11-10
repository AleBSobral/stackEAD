<?php
namespace StackEAD\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all CPTs and Taxonomies for StackEAD Core.
 */
class PostTypeRegistrar {

	/**
	 * Initialize post types and taxonomies.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_all' ) );
		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'disable_block_editor_for_ead' ), 10, 2 );
	}

	/**
	 * Disable Gutenberg block editor for EAD post types to prioritize rich LMS meta box controls.
	 *
	 * @param bool   $use_block_editor
	 * @param string $post_type
	 * @return bool
	 */
	public static function disable_block_editor_for_ead( bool $use_block_editor, string $post_type ): bool {
		if ( 0 === strpos( $post_type, 'stackead_' ) ) {
			return false;
		}
		return $use_block_editor;
	}

	/**
	 * Register all CPTs and Taxonomies.
	 */
	public static function register_all(): void {
		self::register_taxonomies();
		self::register_cpts();
	}

	/**
	 * Register taxonomies.
	 */
	private static function register_taxonomies(): void {
		// 1. Categorias Temáticas
		register_taxonomy(
			'stackead_category',
			array( 'stackead_course', 'stackead_path' ),
			array(
				'labels'            => array(
					'name'          => __( 'Categorias EAD', 'stackead-core' ),
					'singular_name' => __( 'Categoria EAD', 'stackead-core' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'ead-categoria' ),
			)
		);

		// 2. Modalidades (Online, Híbrido, Presencial)
		register_taxonomy(
			'stackead_modality',
			array( 'stackead_course', 'stackead_cohort' ),
			array(
				'labels'            => array(
					'name'          => __( 'Modalidades', 'stackead-core' ),
					'singular_name' => __( 'Modalidade', 'stackead-core' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
			)
		);

		// 3. Nível / Dificuldade (Iniciante, Intermediário, Avançado)
		register_taxonomy(
			'stackead_difficulty',
			array( 'stackead_course', 'stackead_path', 'stackead_lesson' ),
			array(
				'labels'            => array(
					'name'          => __( 'Níveis de Dificuldade', 'stackead-core' ),
					'singular_name' => __( 'Nível', 'stackead-core' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
			)
		);
		// 4. Categorias de Notícias & Comunicados
		register_taxonomy(
			'stackead_news_cat',
			array( 'stackead_news' ),
			array(
				'labels'            => array(
					'name'          => __( 'Categorias de Notícias', 'stackead-core' ),
					'singular_name' => __( 'Categoria de Notícia', 'stackead-core' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'noticias-categoria' ),
			)
		);
	}

	/**
	 * Register all Custom Post Types.
	 */
	private static function register_cpts(): void {
		// 1. Cursos / Ofertas Formativas
		register_post_type(
			'stackead_course',
			array(
				'labels'       => array(
					'name'          => __( 'Cursos / Ofertas', 'stackead-core' ),
					'singular_name' => __( 'Curso', 'stackead-core' ),
					'add_new_item'  => __( 'Adicionar Novo Curso', 'stackead-core' ),
				),
				'public'       => true,
				'has_archive'  => true,
				'show_ui'      => true,
				'show_in_menu' => 'stackead-dashboard',
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'rewrite'      => array( 'slug' => 'cursos' ),
			)
		);

		// 2. Turmas / Coortes
		register_post_type(
			'stackead_cohort',
			array(
				'labels'       => array(
					'name'          => __( 'Turmas / Coortes', 'stackead-core' ),
					'singular_name' => __( 'Turma', 'stackead-core' ),
					'add_new_item'  => __( 'Adicionar Nova Turma', 'stackead-core' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'stackead-dashboard',
				'show_in_rest' => false,
				'supports'     => array( 'title' ),
			)
		);

		// 3. Trilhas de Conhecimento
		register_post_type(
			'stackead_path',
			array(
				'labels'       => array(
					'name'          => __( 'Trilhas Didáticas', 'stackead-core' ),
					'singular_name' => __( 'Trilha', 'stackead-core' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'stackead-dashboard',
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			)
		);

		// 4. Módulos Temáticos (com Trava Sequencial)
		register_post_type(
			'stackead_module',
			array(
				'labels'       => array(
					'name'          => __( 'Módulos', 'stackead-core' ),
					'singular_name' => __( 'Módulo', 'stackead-core' ),
				),
				'public'       => false,
				'hierarchical' => true,
				'show_ui'      => true,
				'show_in_menu' => 'stackead-dashboard',
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor', 'page-attributes' ),
			)
		);

		// 5. Aulas Atômicas
		register_post_type(
			'stackead_lesson',
			array(
				'labels'       => array(
					'name'          => __( 'Aulas', 'stackead-core' ),
					'singular_name' => __( 'Aula', 'stackead-core' ),
				),
				'public'       => false,
				'hierarchical' => true,
				'show_ui'      => true,
				'show_in_menu' => 'stackead-dashboard',
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor', 'page-attributes' ),
			)
		);

		// 6. Sessões Síncronas (Ao Vivo)
		register_post_type(
			'stackead_session',
			array(
				'labels'       => array(
					'name'          => __( 'Sessões Ao Vivo', 'stackead-core' ),
					'singular_name' => __( 'Sessão Ao Vivo', 'stackead-core' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'stackead-dashboard',
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor' ),
			)
		);

		// 7. Atividades Práticas (com Rúbricas)
		register_post_type(
			'stackead_assignment',
			array(
				'labels'       => array(
					'name'          => __( 'Atividades Práticas', 'stackead-core' ),
					'singular_name' => __( 'Atividade', 'stackead-core' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'stackead-dashboard',
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor' ),
			)
		);

		// 8. Certificados
		register_post_type(
			'stackead_certificate',
			array(
				'labels'       => array(
					'name'          => __( 'Certificados Emitidos', 'stackead-core' ),
					'singular_name' => __( 'Certificado', 'stackead-core' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'stackead-dashboard',
				'show_in_rest' => false,
				'supports'     => array( 'title' ),
			)
		);

		// 9. Notícias & Comunicados
		register_post_type(
			'stackead_news',
			array(
				'labels'       => array(
					'name'          => __( 'Notícias & Avisos', 'stackead-core' ),
					'singular_name' => __( 'Notícia', 'stackead-core' ),
					'add_new_item'  => __( 'Adicionar Nova Notícia', 'stackead-core' ),
				),
				'public'       => true,
				'has_archive'  => true,
				'show_ui'      => true,
				'show_in_menu' => 'stackead-dashboard',
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
				'rewrite'      => array( 'slug' => 'noticias' ),
			)
		);

		// 10. Quizzes & Avaliações
		register_post_type(
			'stackead_quiz',
			array(
				'labels'       => array(
					'name'          => __( 'Quizzes & Avaliações', 'stackead-core' ),
					'singular_name' => __( 'Quiz', 'stackead-core' ),
					'add_new_item'  => __( 'Cadastrar Novo Quiz', 'stackead-core' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'stackead-dashboard',
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor' ),
			)
		);
	}
}
