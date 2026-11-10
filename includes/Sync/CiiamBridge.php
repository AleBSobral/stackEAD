<?php
namespace StackEAD\Sync;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ponte bidirecional entre o CIIAM e o StackEAD Core.
 * Permite importar formações cadastradas no CIIAM para o WordPress e
 * refletir edições feitas no WordPress de volta no CIIAM.
 */
class CiiamBridge {

	/**
	 * Flag de controle de concorrência para evitar loop infinito de sincronização.
	 *
	 * @var bool
	 */
	private static bool $is_syncing = false;

	/**
	 * Inicializa os hooks da ponte CIIAM.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_manual_sync_actions' ) );
		add_action( 'save_post_stackead_course', array( __CLASS__, 'on_save_course' ), 20, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_sync_meta_box' ) );
	}

	/**
	 * Retorna a URL base do CIIAM configurada.
	 *
	 * @return string
	 */
	public static function get_ciiam_base_url(): string {
		$url = get_option( 'stackead_ciiam_api_url', '' );
		if ( empty( $url ) ) {
			// Fallback para ambientes locais comuns do Laragon
			$url = 'http://127.0.0.1:8000';
		}
		return untrailingslashit( $url );
	}

	/**
	 * Registra o submenu "Ponte CIIAM" no painel do stackEAD.
	 */
	public static function register_admin_menu(): void {
		add_submenu_page(
			'stackead-dashboard',
			__( 'Ponte CIIAM & Sincronização', 'stackead-core' ),
			__( 'Ponte CIIAM 🔄', 'stackead-core' ),
			'manage_options',
			'stackead-ciiam-bridge',
			array( __CLASS__, 'render_bridge_page' )
		);
	}

	/**
	 * Registra Meta Box de sincronização na edição do curso stackead_course.
	 */
	public static function register_sync_meta_box(): void {
		add_meta_box(
			'stackead_ciiam_sync_box',
			__( '🔄 Integração & Sincronização CIIAM', 'stackead-core' ),
			array( __CLASS__, 'render_sync_meta_box' ),
			'stackead_course',
			'side',
			'high'
		);
	}

	/**
	 * Renderiza a Meta Box lateral no editor do curso.
	 */
	public static function render_sync_meta_box( \WP_Post $post ): void {
		$token     = get_post_meta( $post->ID, '_stackead_ciiam_token', true );
		$synced_at = get_post_meta( $post->ID, '_stackead_ciiam_synced_at', true );
		$pushed_at = get_post_meta( $post->ID, '_stackead_ciiam_last_pushed_at', true );
		$ciiam_id  = get_post_meta( $post->ID, '_stackead_ciiam_id', true );
		?>
		<div style="padding: 6px 0;">
			<p style="font-size: 12px; color: #64748b; margin-top: 0;">
				<?php esc_html_e( 'Vincule esta oferta a uma formação cadastrada no CIIAM.', 'stackead-core' ); ?>
			</p>

			<div style="margin-bottom: 12px;">
				<label style="display: block; font-weight: 600; font-size: 11.5px; margin-bottom: 4px;">
					<?php esc_html_e( 'Token da Formação (CIIAM)', 'stackead-core' ); ?>
				</label>
				<input type="text" name="_stackead_ciiam_token" value="<?php echo esc_attr( $token ); ?>" style="width: 100%; font-family: monospace; font-size: 11px;" placeholder="ciiam_sync_...">
			</div>

			<?php if ( ! empty( $token ) ) : ?>
				<div style="background: #f1f5f9; border-radius: 6px; padding: 10px; margin-bottom: 12px; font-size: 11.5px; line-height: 1.5;">
					<div><strong><?php esc_html_e( 'ID no CIIAM:', 'stackead-core' ); ?></strong> #<?php echo esc_html( $ciiam_id ?: 'N/A' ); ?></div>
					<div><strong><?php esc_html_e( 'Última Importação:', 'stackead-core' ); ?></strong> <?php echo esc_html( $synced_at ?: 'Nunca' ); ?></div>
					<div><strong><?php esc_html_e( 'Último Envio:', 'stackead-core' ); ?></strong> <?php echo esc_html( $pushed_at ?: 'Nunca' ); ?></div>
				</div>

				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=stackead-ciiam-bridge&action=pull_single&post_id=' . $post->ID ), 'ciiam_pull_' . $post->ID ) ); ?>" class="button button-secondary button-small" style="width: 100%; text-align: center;">
					⬇️ <?php esc_html_e( 'Puxar Dados do CIIAM Agora', 'stackead-core' ); ?>
				</a>
			<?php else : ?>
				<p style="font-size: 11px; color: #94a3b8; margin: 0;">
					<?php esc_html_e( 'Insira o Token do CIIAM e salve o curso para sincronizar automaticamente.', 'stackead-core' ); ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Processa ações manuais de sincronização disparadas via interface.
	 */
	public static function handle_manual_sync_actions(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// 1. Salvar configurações gerais da ponte
		if ( isset( $_POST['stackead_save_bridge_settings'] ) && check_admin_referer( 'stackead_bridge_settings_nonce' ) ) {
			$api_url = esc_url_raw( trim( $_POST['stackead_ciiam_api_url'] ?? '' ) );
			update_option( 'stackead_ciiam_api_url', $api_url );
			wp_safe_redirect( admin_url( 'admin.php?page=stackead-ciiam-bridge&msg=settings_saved' ) );
			exit;
		}

		// 2. Importar Formação por Token
		if ( isset( $_POST['stackead_import_token_action'] ) && check_admin_referer( 'stackead_import_token_nonce' ) ) {
			$token = sanitize_text_field( trim( $_POST['ciiam_sync_token'] ?? '' ) );
			if ( empty( $token ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=stackead-ciiam-bridge&error=empty_token' ) );
				exit;
			}

			$result = self::import_from_ciiam( $token );
			if ( is_wp_error( $result ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=stackead-ciiam-bridge&error=' . urlencode( $result->get_error_message() ) ) );
				exit;
			}

			wp_safe_redirect( admin_url( 'admin.php?page=stackead-ciiam-bridge&msg=imported&post_id=' . $result ) );
			exit;
		}

		// 3. Puxar dados de um curso específico (Pull Single)
		if ( isset( $_GET['action'] ) && 'pull_single' === $_GET['action'] && isset( $_GET['post_id'] ) ) {
			$post_id = absint( $_GET['post_id'] );
			check_admin_referer( 'ciiam_pull_' . $post_id );

			$token = get_post_meta( $post_id, '_stackead_ciiam_token', true );
			if ( ! empty( $token ) ) {
				$res = self::import_from_ciiam( $token, $post_id );
				if ( is_wp_error( $res ) ) {
					wp_safe_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit&error=' . urlencode( $res->get_error_message() ) ) );
					exit;
				}
			}
			wp_safe_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit&msg=ciiam_synced' ) );
			exit;
		}
	}

	/**
	 * Importa ou atualiza um curso vindo do CIIAM via REST API.
	 *
	 * @param string   $token
	 * @param int|null $existing_post_id
	 * @return int|\WP_Error ID do post ou WP_Error em falha.
	 */
	public static function import_from_ciiam( string $token, ?int $existing_post_id = null ) {
		$base_url = self::get_ciiam_base_url();
		$api_url  = "{$base_url}/api/v1/sync/courses/{$token}";

		$response = wp_remote_get(
			$api_url,
			array(
				'timeout' => 15,
				'headers' => array(
					'Accept'        => 'application/json',
					'X-Sync-Origin' => 'stackead',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $code || empty( $body['data'] ) ) {
			$msg = $body['message'] ?? __( 'Não foi possível conectar à formação no CIIAM.', 'stackead-core' );
			return new \WP_Error( 'ciiam_sync_error', $msg );
		}

		$data = $body['data'];

		// Localiza post existente se não informado
		if ( ! $existing_post_id ) {
			$query = new \WP_Query(
				array(
					'post_type'      => 'stackead_course',
					'meta_key'       => '_stackead_ciiam_token',
					'meta_value'     => $token,
					'posts_per_page' => 1,
					'post_status'    => 'any',
					'fields'         => 'ids',
				)
			);
			if ( ! empty( $query->posts ) ) {
				$existing_post_id = $query->posts[0];
			}
		}

		self::$is_syncing = true;

		$post_arr = array(
			'post_type'    => 'stackead_course',
			'post_title'   => sanitize_text_field( $data['title'] ),
			'post_content' => wp_kses_post( $data['description'] ?? '' ),
			'post_excerpt' => sanitize_textarea_field( $data['summary'] ?? '' ),
			'post_status'  => 'publish',
		);

		if ( $existing_post_id ) {
			$post_arr['ID'] = $existing_post_id;
			$post_id        = wp_update_post( $post_arr );
		} else {
			$post_id = wp_insert_post( $post_arr );
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			self::$is_syncing = false;
			return new \WP_Error( 'post_save_error', __( 'Erro ao salvar o curso no WordPress.', 'stackead-core' ) );
		}

		// Salva os metadados do StackEAD
		update_post_meta( $post_id, '_stackead_ciiam_id', absint( $data['id'] ) );
		update_post_meta( $post_id, '_stackead_ciiam_token', sanitize_text_field( $token ) );
		update_post_meta( $post_id, '_stackead_ciiam_synced_at', current_time( 'mysql' ) );

		update_post_meta( $post_id, '_stackead_subtitle', sanitize_text_field( $data['summary'] ?? '' ) );
		update_post_meta( $post_id, '_stackead_syllabus', wp_kses_post( $data['description'] ?? '' ) );
		update_post_meta( $post_id, '_stackead_learning_goals', sanitize_textarea_field( $data['learning_goals'] ?? '' ) );
		update_post_meta( $post_id, '_stackead_prerequisites', sanitize_textarea_field( $data['prerequisites'] ?? '' ) );
		update_post_meta( $post_id, '_stackead_video_teaser', esc_url_raw( $data['video_teaser_url'] ?? '' ) );
		update_post_meta( $post_id, '_stackead_workload_hours', absint( $data['workload_hours'] ?? 40 ) );

		$price = floatval( $data['price'] ?? 0 );
		update_post_meta( $post_id, '_stackead_price', $price );
		update_post_meta( $post_id, '_stackead_price_type', $price > 0 ? 'paid' : 'free' );

		update_post_meta( $post_id, '_stackead_checkout_url', esc_url_raw( $data['checkout_url'] ?? '' ) );
		update_post_meta( $post_id, '_stackead_registration_url', esc_url_raw( $data['registration_url'] ?? '' ) );
		update_post_meta( $post_id, '_stackead_simplified_summary', sanitize_textarea_field( $data['simplified_summary'] ?? '' ) );

		if ( ! empty( $data['gallery'] ) && is_array( $data['gallery'] ) ) {
			update_post_meta( $post_id, '_stackead_gallery', wp_json_encode( $data['gallery'] ) );
		}

		if ( ! empty( $data['supporters'] ) && is_array( $data['supporters'] ) ) {
			update_post_meta( $post_id, '_stackead_supporters', wp_json_encode( $data['supporters'] ) );
		}

		if ( ! empty( $data['cover_image'] ) ) {
			update_post_meta( $post_id, '_stackead_cover_url', esc_url_raw( $data['cover_image'] ) );
		}

		self::$is_syncing = false;

		return $post_id;
	}

	/**
	 * Intercepta o salvamento do post no WordPress e reflete alterações de volta ao CIIAM.
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public static function on_save_course( int $post_id, \WP_Post $post ): void {
		// Se estiver no meio de um processo de sincronização vindo do CIIAM, ignora
		if ( self::$is_syncing || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}

		// Se o usuário atualizou o token na meta box
		if ( isset( $_POST['_stackead_ciiam_token'] ) ) {
			update_post_meta( $post_id, '_stackead_ciiam_token', sanitize_text_field( trim( $_POST['_stackead_ciiam_token'] ) ) );
		}

		$token = get_post_meta( $post_id, '_stackead_ciiam_token', true );
		if ( empty( $token ) ) {
			return;
		}

		$base_url = self::get_ciiam_base_url();
		$api_url  = "{$base_url}/api/v1/sync/courses/{$token}/update";

		$payload = array(
			'title'              => $post->post_title,
			'summary'            => get_post_meta( $post_id, '_stackead_subtitle', true ) ?: $post->post_excerpt,
			'description'        => get_post_meta( $post_id, '_stackead_syllabus', true ) ?: $post->post_content,
			'simplified_summary' => get_post_meta( $post_id, '_stackead_simplified_summary', true ),
			'learning_goals'     => get_post_meta( $post_id, '_stackead_learning_goals', true ),
			'prerequisites'      => get_post_meta( $post_id, '_stackead_prerequisites', true ),
			'video_teaser_url'   => get_post_meta( $post_id, '_stackead_video_teaser', true ),
			'price'              => (float) get_post_meta( $post_id, '_stackead_price', true ),
			'checkout_url'       => get_post_meta( $post_id, '_stackead_checkout_url', true ),
			'registration_url'   => get_post_meta( $post_id, '_stackead_registration_url', true ),
			'workload_hours'     => (int) get_post_meta( $post_id, '_stackead_workload_hours', true ),
		);

		// Dispara requisição assíncrona/não-bloqueante para o CIIAM
		wp_remote_post(
			$api_url,
			array(
				'timeout'  => 8,
				'blocking' => false,
				'headers'  => array(
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
					'X-Sync-Origin' => 'stackead',
				),
				'body'     => wp_json_encode( $payload ),
			)
		);

		update_post_meta( $post_id, '_stackead_ciiam_last_pushed_at', current_time( 'mysql' ) );
	}

	/**
	 * Renderiza a página administrativa da Ponte CIIAM no WP-Admin.
	 */
	public static function render_bridge_page(): void {
		$api_url = self::get_ciiam_base_url();

		// Recupera cursos vinculados
		$synced_courses = get_posts(
			array(
				'post_type'      => 'stackead_course',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_stackead_ciiam_token',
						'value'   => '',
						'compare' => '!=',
					),
				),
			)
		);
		?>
		<div class="wrap" style="max-width: 960px; margin-top: 24px;">
			<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
				<span style="font-size: 32px;">🔄</span>
				<div>
					<h1 style="font-size: 24px; font-weight: 800; color: #1e1b4b; margin: 0;">Ponte de Sincronização CIIAM ⇄ StackEAD</h1>
					<p style="font-size: 13px; color: #64748b; margin: 2px 0 0;">
						Gerenciamento da integração bidirecional de ofertas pedagógicas e formações.
					</p>
				</div>
			</div>

			<?php if ( isset( $_GET['msg'] ) && 'settings_saved' === $_GET['msg'] ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Configurações da ponte salvas com sucesso!', 'stackead-core' ); ?></p></div>
			<?php elseif ( isset( $_GET['msg'] ) && 'imported' === $_GET['msg'] ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Formação do CIIAM importada e sincronizada com sucesso!', 'stackead-core' ); ?></p></div>
			<?php elseif ( isset( $_GET['error'] ) ) : ?>
				<div class="notice notice-error is-dismissible"><p><?php echo esc_html( urldecode( $_GET['error'] ) ); ?></p></div>
			<?php endif; ?>

			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
				<!-- Card 1: Configuração da URL da API -->
				<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<h3 style="margin-top: 0; font-size: 15px; font-weight: 700; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
						⚙️ Conexão com o Servidor CIIAM
					</h3>
					<form method="POST" action="">
						<?php wp_nonce_field( 'stackead_bridge_settings_nonce' ); ?>
						<div style="margin-bottom: 14px;">
							<label style="display: block; font-weight: 600; font-size: 12px; margin-bottom: 6px;">
								URL Base do Sistema CIIAM (API)
							</label>
							<input type="url" name="stackead_ciiam_api_url" value="<?php echo esc_attr( $api_url ); ?>" class="regular-text" style="width: 100%; font-family: monospace;" required>
							<p style="font-size: 11px; color: #64748b; margin-top: 4px;">
								Exemplo local: <code>http://127.0.0.1:8000</code> ou <code>http://ciiam.test</code>
							</p>
						</div>
						<button type="submit" name="stackead_save_bridge_settings" class="button button-primary">
							💾 Salvar Conexão
						</button>
					</form>
				</div>

				<!-- Card 2: Importar Nova Formação por Token -->
				<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<h3 style="margin-top: 0; font-size: 15px; font-weight: 700; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
						📥 Importar Formação por Token
					</h3>
					<form method="POST" action="">
						<?php wp_nonce_field( 'stackead_import_token_nonce' ); ?>
						<div style="margin-bottom: 14px;">
							<label style="display: block; font-weight: 600; font-size: 12px; margin-bottom: 6px;">
								Token de Sincronização (Course Sync Token)
							</label>
							<input type="text" name="ciiam_sync_token" placeholder="ciiam_sync_..." class="regular-text" style="width: 100%; font-family: monospace;" required>
							<p style="font-size: 11px; color: #64748b; margin-top: 4px;">
								Copie o token gerado no cadastro da oferta pedagógica no CIIAM.
							</p>
						</div>
						<button type="submit" name="stackead_import_token_action" class="button button-primary" style="background: #4f46e5; border-color: #4338ca;">
							⬇️ Importar do CIIAM
						</button>
					</form>
				</div>
			</div>

			<!-- Tabela de Cursos Conectados -->
			<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
				<h3 style="margin-top: 0; font-size: 15px; font-weight: 700; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
					📚 Formações Sincronizadas (<?php echo count( $synced_courses ); ?>)
				</h3>

				<?php if ( empty( $synced_courses ) ) : ?>
					<div style="text-align: center; padding: 32px; color: #94a3b8; font-size: 13px;">
						Nenhuma formação vinculada à ponte ainda.<br>
						Insira um token acima para realizar a primeira importação.
					</div>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th>Título no StackEAD</th>
								<th>Token CIIAM</th>
								<th>Última Importação</th>
								<th>Último Envio</th>
								<th style="width: 140px; text-align: right;">Ações</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $synced_courses as $c ) : ?>
								<?php
								$t         = get_post_meta( $c->ID, '_stackead_ciiam_token', true );
								$synced_at = get_post_meta( $c->ID, '_stackead_ciiam_synced_at', true );
								$pushed_at = get_post_meta( $c->ID, '_stackead_ciiam_last_pushed_at', true );
								?>
								<tr>
									<td>
										<strong><a href="<?php echo esc_url( get_edit_post_link( $c->ID ) ); ?>"><?php echo esc_html( $c->post_title ); ?></a></strong>
									</td>
									<td><code><?php echo esc_html( substr( $t, 0, 16 ) . '...' ); ?></code></td>
									<td><?php echo esc_html( $synced_at ?: '—' ); ?></td>
									<td><?php echo esc_html( $pushed_at ?: '—' ); ?></td>
									<td style="text-align: right;">
										<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=stackead-ciiam-bridge&action=pull_single&post_id=' . $c->ID ), 'ciiam_pull_' . $c->ID ) ); ?>" class="button button-small" title="Puxar atualizações mais recentes do CIIAM">
											🔄 Puxar
										</a>
										<a href="<?php echo esc_url( get_edit_post_link( $c->ID ) ); ?>" class="button button-small">
											✏️ Editar
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
