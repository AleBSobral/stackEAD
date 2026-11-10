<?php
namespace StackEAD\Auth;

use StackEAD\Admin\WhiteLabelSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LoginTemplate - Luxury White Label Standalone Authentication Screen.
 *
 * Provides a responsive, accessible (W3C COGA compliant), glassmorphic login
 * and password recovery interface completely independent of WordPress' default wp-login.
 */
class LoginTemplate {

	/**
	 * Renders the standalone login application.
	 */
	public static function render(): void {
		$settings = WhiteLabelSettings::get_settings();
		$primary  = esc_attr( $settings['primary_color'] );
		$primary_text = esc_attr( WhiteLabelSettings::get_contrast_color( $settings['primary_color'] ) );
		$secondary = esc_attr( $settings['secondary_color'] );
		$inst_name = esc_html( $settings['institution_name'] );
		$inst_tag  = esc_html( $settings['institution_tagline'] );
		$plugin_url = defined( 'STACKEAD_URL' ) ? STACKEAD_URL : plugin_dir_url( dirname( __DIR__ ) );
		$logo_url  = ! empty( $settings['logo_url'] ) ? esc_url( $settings['logo_url'] ) : esc_url( $plugin_url . 'assets/images/artinnova-logo-white.png' );

		$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/plataforma' );
		$is_logged_out = isset( $_GET['loggedout'] ) && 'true' === $_GET['loggedout'];

		if ( is_user_logged_in() && ! $is_logged_out ) {
			wp_safe_redirect( $redirect_to );
			exit;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
		if ( ! headers_sent() ) {
			header( 'X-LiteSpeed-Cache-Control: no-cache' );
		}
		status_header( 200 );
		?>
		<!DOCTYPE html>
		<html lang="pt-BR">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo esc_html( sprintf( __( 'Entrar | %s', 'stackead-core' ), $settings['institution_name'] ) ); ?></title>

			<!-- Google Fonts -->
			<link rel="preconnect" href="https://fonts.googleapis.com">
			<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
			<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

			<!-- Lucide Icons -->
			<script src="https://unpkg.com/lucide@latest"></script>

			<?php
			WhiteLabelSettings::inject_brand_tokens();
			WhiteLabelSettings::inject_favicon();
			?>

			<style>
				*, *::before, *::after {
					box-sizing: border-box;
					margin: 0;
					padding: 0;
				}

				:root {
					--bg-deep: #07090e;
					--bg-card: rgba(15, 23, 42, 0.78);
					--border-card: rgba(255, 255, 255, 0.12);
					--text-main: #f8fafc;
					--text-muted: #94a3b8;
					--text-dim: #64748b;
					--input-bg: #131b2e;
					--input-border: #23314d;
					--input-focus: var(--brand-primary, #4f46e5);
					--input-text: #ffffff;
					--danger-bg: rgba(239, 68, 68, 0.15);
					--danger-border: rgba(239, 68, 68, 0.35);
					--danger-text: #fca5a5;
					--success-bg: rgba(16, 185, 129, 0.15);
					--success-border: rgba(16, 185, 129, 0.35);
					--success-text: #6ee7b7;
				}

				body {
					font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
					background-color: var(--bg-deep);
					color: var(--text-main);
					min-height: 100vh;
					display: flex;
					align-items: center;
					justify-content: center;
					padding: 24px 16px;
					position: relative;
					overflow-x: hidden;
				}

				/* Ambient lighting blobs */
				.ambient-glow {
					position: fixed;
					width: 600px;
					height: 600px;
					border-radius: 50%;
					pointer-events: none;
					z-index: 0;
					filter: blur(140px);
					opacity: 0.18;
				}
				.glow-top-left {
					top: -200px;
					left: -200px;
					background: var(--brand-primary, #4f46e5);
				}
				.glow-bottom-right {
					bottom: -200px;
					right: -200px;
					background: var(--brand-secondary, #06b6d4);
				}

				/* Container & Card */
				.auth-container {
					position: relative;
					z-index: 10;
					width: 100%;
					max-width: 440px;
				}

				.auth-card {
					background: linear-gradient(180deg, rgba(19, 28, 49, 0.88) 0%, rgba(11, 16, 30, 0.94) 100%);
					border: 1px solid rgba(255, 255, 255, 0.14);
					border-top: 1px solid rgba(255, 255, 255, 0.24);
					border-radius: 26px;
					padding: 42px 38px;
					box-shadow: 0 32px 64px -16px rgba(0, 0, 0, 0.85),
								0 0 0 1px rgba(255, 255, 255, 0.05),
								inset 0 1px 0 rgba(255, 255, 255, 0.1);
					backdrop-filter: blur(28px);
					-webkit-backdrop-filter: blur(28px);
					transition: transform 0.3s ease;
				}

				.brand-header {
					text-align: center;
					margin-bottom: 30px;
				}

				.brand-logo-wrap {
					display: inline-flex;
					align-items: center;
					justify-content: center;
					padding: 8px 16px;
					margin-bottom: 14px;
				}

				.brand-logo-img {
					max-height: 68px;
					max-width: 220px;
					width: auto;
					height: auto;
					object-fit: contain;
					display: block;
					filter: brightness(0) invert(1) drop-shadow(0 4px 16px rgba(255, 255, 255, 0.16));
				}

				.brand-badge {
					display: inline-flex;
					align-items: center;
					gap: 6px;
					padding: 5px 12px;
					border-radius: 999px;
					background: rgba(37, 99, 235, 0.16);
					border: 1px solid rgba(96, 165, 250, 0.3);
					color: #93c5fd;
					font-size: 11.5px;
					font-weight: 700;
					text-transform: uppercase;
					letter-spacing: 0.06em;
					margin-bottom: 10px;
				}

				.brand-title {
					font-family: 'Space Grotesk', sans-serif;
					font-size: 24px;
					font-weight: 700;
					letter-spacing: -0.02em;
					color: #ffffff;
					margin-bottom: 6px;
				}

				.brand-tagline {
					font-size: 13px;
					color: #cbd5e1;
					line-height: 1.55;
					max-width: 340px;
					margin: 0 auto;
				}

				/* Alerts */
				.auth-alert {
					border-radius: 12px;
					padding: 12px 16px;
					font-size: 13px;
					line-height: 1.5;
					margin-bottom: 20px;
					display: flex;
					align-items: center;
					gap: 10px;
					display: none;
				}
				.auth-alert.visible {
					display: flex;
				}
				.auth-alert.alert-danger {
					background: var(--danger-bg);
					border: 1px solid var(--danger-border);
					color: var(--danger-text);
				}
				.auth-alert.alert-success {
					background: var(--success-bg);
					border: 1px solid var(--success-border);
					color: var(--success-text);
				}

				/* Form Fields */
				.form-group {
					margin-bottom: 20px;
				}

				.form-label {
					display: block;
					font-size: 13px;
					font-weight: 600;
					color: var(--text-muted);
					margin-bottom: 8px;
				}

				.input-wrap {
					position: relative;
					display: flex;
					align-items: center;
				}

				.input-icon-left {
					position: absolute;
					left: 14px;
					color: var(--text-dim);
					pointer-events: none;
					display: flex;
					align-items: center;
				}

				.input-icon-left svg {
					width: 18px;
					height: 18px;
				}

				.form-control {
					width: 100%;
					background: var(--input-bg);
					border: 1px solid var(--input-border);
					border-radius: 12px;
					padding: 13px 14px 13px 44px;
					color: var(--input-text);
					font-size: 14px;
					font-family: inherit;
					outline: none;
					transition: all 0.2s ease;
				}

				.form-control:focus {
					border-color: var(--brand-primary, #4f46e5);
					box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25);
					background: #18223a;
				}

				.btn-toggle-pwd {
					position: absolute;
					right: 12px;
					background: transparent;
					border: none;
					color: var(--text-dim);
					cursor: pointer;
					padding: 6px;
					display: flex;
					align-items: center;
					border-radius: 6px;
					transition: color 0.2s;
				}
				.btn-toggle-pwd:hover {
					color: #ffffff;
				}
				.btn-toggle-pwd svg {
					width: 18px;
					height: 18px;
				}

				/* Form Options */
				.form-row-options {
					display: flex;
					align-items: center;
					justify-content: space-between;
					margin-bottom: 24px;
					font-size: 13px;
				}

				.checkbox-label {
					display: flex;
					align-items: center;
					gap: 8px;
					color: var(--text-muted);
					cursor: pointer;
					user-select: none;
				}

				.checkbox-label input[type="checkbox"] {
					accent-color: var(--brand-primary, #4f46e5);
					width: 16px;
					height: 16px;
					border-radius: 4px;
					cursor: pointer;
				}

				.link-forgot {
					color: var(--brand-secondary, #06b6d4);
					text-decoration: none;
					font-weight: 500;
					transition: opacity 0.2s;
				}
				.link-forgot:hover {
					text-decoration: underline;
					opacity: 0.9;
				}

				/* Buttons */
				.btn-submit {
					width: 100%;
					background: var(--brand-primary, #4f46e5);
					color: var(--brand-primary-text, #ffffff);
					border: none;
					border-radius: 12px;
					padding: 14px 20px;
					font-size: 15px;
					font-weight: 700;
					font-family: inherit;
					cursor: pointer;
					display: flex;
					align-items: center;
					justify-content: center;
					gap: 10px;
					box-shadow: 0 4px 16px rgba(79, 70, 229, 0.35);
					transition: all 0.2s ease;
				}

				.btn-submit:hover {
					filter: brightness(1.1);
					transform: translateY(-1px);
					box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
				}

				.btn-submit:active {
					transform: translateY(0);
				}

				.btn-submit:disabled {
					opacity: 0.6;
					cursor: not-allowed;
					transform: none !important;
				}

				.btn-submit svg {
					width: 18px;
					height: 18px;
				}

				/* Spinner */
				.spinner {
					width: 18px;
					height: 18px;
					border: 2px solid rgba(255, 255, 255, 0.3);
					border-top-color: #ffffff;
					border-radius: 50%;
					animation: spin 0.8s linear infinite;
					display: none;
				}
				@keyframes spin {
					to { transform: rotate(360deg); }
				}

				/* Panels Switch */
				.auth-panel {
					display: none;
				}
				.auth-panel.active {
					display: block;
				}

				/* Footer Links & Badge */
				.auth-footer {
					margin-top: 28px;
					text-align: center;
					padding-top: 20px;
					border-top: 1px solid rgba(255, 255, 255, 0.08);
				}

				.btn-back-home {
					display: inline-flex;
					align-items: center;
					gap: 6px;
					color: var(--text-muted);
					text-decoration: none;
					font-size: 13px;
					transition: color 0.2s;
				}
				.btn-back-home:hover {
					color: #ffffff;
				}
				.btn-back-home svg {
					width: 15px;
					height: 15px;
				}

				.security-badge {
					margin-top: 18px;
					font-size: 11px;
					color: var(--text-dim);
					display: flex;
					align-items: center;
					justify-content: center;
					gap: 6px;
				}
				.security-badge svg {
					width: 13px;
					height: 13px;
					color: var(--success-text);
				}

				/* Shake animation on error */
				@keyframes shake {
					0%, 100% { transform: translateX(0); }
					20%, 60% { transform: translateX(-6px); }
					40%, 80% { transform: translateX(6px); }
				}
				.shake {
					animation: shake 0.4s ease;
				}
			</style>
		</head>
		<body>

			<!-- Ambient Background Glows -->
			<div class="ambient-glow glow-top-left"></div>
			<div class="ambient-glow glow-bottom-right"></div>

			<main class="auth-container">
				<div class="auth-card" id="auth-card">

					<!-- Brand Header -->
					<div class="brand-header">
						<?php if ( ! empty( $logo_url ) ) : ?>
							<div class="brand-logo-wrap">
								<img src="<?php echo $logo_url; ?>" alt="<?php echo $inst_name; ?>" class="brand-logo-img">
							</div>
							<div>
								<span class="brand-badge">
									<i data-lucide="sparkles" style="width: 12px; height: 12px;"></i>
									<?php esc_html_e( 'Ambiente Acadêmico & Residência', 'stackead-core' ); ?>
								</span>
							</div>
						<?php else : ?>
							<h1 class="brand-title"><?php echo $inst_name; ?></h1>
						<?php endif; ?>
						<p class="brand-tagline"><?php echo $inst_tag; ?></p>
					</div>

					<!-- Feedback Alert Box -->
					<div id="auth-alert" class="auth-alert" role="alert" aria-live="polite">
						<i data-lucide="info" id="alert-icon"></i>
						<span id="alert-text"></span>
					</div>

					<?php if ( $is_logged_out ) : ?>
						<div class="auth-alert alert-success visible" style="display: flex;">
							<i data-lucide="check-circle-2"></i>
							<span><?php esc_html_e( 'Você saiu da sua conta com segurança. Até breve!', 'stackead-core' ); ?></span>
						</div>
					<?php endif; ?>

					<!-- Panel 1: Login Form -->
					<div id="panel-login" class="auth-panel active">
						<form id="form-login" novalidate>
							<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">

							<div class="form-group">
								<label for="user_login" class="form-label"><?php esc_html_e( 'Usuário ou E-mail', 'stackead-core' ); ?></label>
								<div class="input-wrap">
									<span class="input-icon-left"><i data-lucide="user"></i></span>
									<input type="text" id="user_login" name="user_login" class="form-control" placeholder="ex: aluno@artinnova.org" required autocomplete="username">
								</div>
							</div>

							<div class="form-group">
								<label for="user_password" class="form-label"><?php esc_html_e( 'Senha de Acesso', 'stackead-core' ); ?></label>
								<div class="input-wrap">
									<span class="input-icon-left"><i data-lucide="lock"></i></span>
									<input type="password" id="user_password" name="user_password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
									<button type="button" class="btn-toggle-pwd" id="btn-toggle-pwd" aria-label="Mostrar/Ocultar Senha">
										<i data-lucide="eye" id="eye-icon"></i>
									</button>
								</div>
							</div>

							<div class="form-row-options">
								<label class="checkbox-label">
									<input type="checkbox" name="remember" id="remember" value="1" checked>
									<span><?php esc_html_e( 'Lembrar de mim', 'stackead-core' ); ?></span>
								</label>
								<a href="#recuperar" id="link-show-forgot" class="link-forgot">
									<?php esc_html_e( 'Esqueceu a senha?', 'stackead-core' ); ?>
								</a>
							</div>

							<button type="submit" class="btn-submit" id="btn-login-submit">
								<span class="spinner" id="login-spinner"></span>
								<span id="login-btn-text"><?php esc_html_e( 'Entrar na Plataforma', 'stackead-core' ); ?></span>
								<i data-lucide="arrow-right" id="login-arrow-icon"></i>
							</button>
						</form>
					</div>

					<!-- Panel 2: Forgot Password Form -->
					<div id="panel-forgot" class="auth-panel">
						<div style="margin-bottom: 20px; font-size: 13px; color: var(--text-muted); line-height: 1.6;">
							<?php esc_html_e( 'Informe seu e-mail ou nome de usuário cadastrado para receber um link direto de redefinição de senha.', 'stackead-core' ); ?>
						</div>

						<form id="form-forgot" novalidate>
							<div class="form-group">
								<label for="forgot_user_login" class="form-label"><?php esc_html_e( 'E-mail ou Usuário', 'stackead-core' ); ?></label>
								<div class="input-wrap">
									<span class="input-icon-left"><i data-lucide="mail"></i></span>
									<input type="text" id="forgot_user_login" name="forgot_user_login" class="form-control" placeholder="seu-email@exemplo.com" required>
								</div>
							</div>

							<button type="submit" class="btn-submit" id="btn-forgot-submit">
								<span class="spinner" id="forgot-spinner"></span>
								<span id="forgot-btn-text"><?php esc_html_e( 'Enviar Instruções', 'stackead-core' ); ?></span>
								<i data-lucide="send" id="forgot-arrow-icon"></i>
							</button>

							<div style="text-align: center; margin-top: 16px;">
								<a href="#entrar" id="link-back-login" class="link-forgot" style="font-size: 13px;">
									&larr; <?php esc_html_e( 'Voltar para o Login', 'stackead-core' ); ?>
								</a>
							</div>
						</form>
					</div>

					<!-- Card Footer -->
					<div class="auth-footer">
						<a href="<?php echo esc_url( home_url() ); ?>" class="btn-back-home">
							<i data-lucide="home"></i>
							<span><?php esc_html_e( 'Voltar ao Portal Principal', 'stackead-core' ); ?></span>
						</a>

						<div class="security-badge">
							<i data-lucide="shield-check"></i>
							<span><?php esc_html_e( 'Ambiente Seguro TLS · Acessibilidade W3C COGA', 'stackead-core' ); ?></span>
						</div>
					</div>

				</div>
			</main>

			<script>
				document.addEventListener('DOMContentLoaded', function() {
					if (window.lucide) {
						lucide.createIcons();
					}

					const restBase = '<?php echo esc_url_raw( rest_url( 'stackead/v1' ) ); ?>';
					const nonce    = '<?php echo wp_create_nonce( 'wp_rest' ); ?>';

					const card        = document.getElementById('auth-card');
					const panelLogin  = document.getElementById('panel-login');
					const panelForgot = document.getElementById('panel-forgot');
					const alertBox    = document.getElementById('auth-alert');
					const alertText   = document.getElementById('alert-text');

					const formLogin   = document.getElementById('form-login');
					const btnLogin    = document.getElementById('btn-login-submit');
					const loginSpinner= document.getElementById('login-spinner');
					const loginArrow  = document.getElementById('login-arrow-icon');
					const loginText   = document.getElementById('login-btn-text');

					const formForgot  = document.getElementById('form-forgot');
					const btnForgot   = document.getElementById('btn-forgot-submit');
					const forgotSpinner = document.getElementById('forgot-spinner');
					const forgotArrow   = document.getElementById('forgot-arrow-icon');
					const forgotText    = document.getElementById('forgot-btn-text');

					// Show alert helper
					function showAlert(message, type) {
						alertBox.className = 'auth-alert alert-' + type + ' visible';
						alertText.textContent = message;
						if (window.lucide) lucide.createIcons();
						if (type === 'danger') {
							card.classList.remove('shake');
							void card.offsetWidth;
							card.classList.add('shake');
						}
					}

					function hideAlert() {
						alertBox.className = 'auth-alert';
						alertText.textContent = '';
					}

					// Toggle password visibility
					const btnTogglePwd = document.getElementById('btn-toggle-pwd');
					const inputPwd     = document.getElementById('user_password');
					if (btnTogglePwd && inputPwd) {
						btnTogglePwd.addEventListener('click', function() {
							const isPass = inputPwd.type === 'password';
							inputPwd.type = isPass ? 'text' : 'password';
							const eyeIcon = document.getElementById('eye-icon');
							if (eyeIcon) {
								eyeIcon.setAttribute('data-lucide', isPass ? 'eye-off' : 'eye');
								if (window.lucide) lucide.createIcons();
							}
						});
					}

					// Switch Panels
					document.getElementById('link-show-forgot').addEventListener('click', function(e) {
						e.preventDefault();
						hideAlert();
						panelLogin.classList.remove('active');
						panelForgot.classList.add('active');
					});

					document.getElementById('link-back-login').addEventListener('click', function(e) {
						e.preventDefault();
						hideAlert();
						panelForgot.classList.remove('active');
						panelLogin.classList.add('active');
					});

					// Login Submit
					formLogin.addEventListener('submit', async function(e) {
						e.preventDefault();
						hideAlert();

						const userLogin = document.getElementById('user_login').value.trim();
						const userPass  = document.getElementById('user_password').value;
						const remember  = document.getElementById('remember').checked;
						const redirectTo = formLogin.querySelector('[name="redirect_to"]').value;

						if (!userLogin || !userPass) {
							showAlert('Por favor, informe seu usuário/e-mail e sua senha.', 'danger');
							return;
						}

						btnLogin.disabled = true;
						loginSpinner.style.display = 'inline-block';
						loginArrow.style.display   = 'none';
						loginText.textContent      = 'Autenticando...';

						try {
							const res = await fetch(restBase + '/auth/login', {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'X-WP-Nonce': nonce
								},
								body: JSON.stringify({
									user_login: userLogin,
									user_password: userPass,
									remember: remember,
									redirect_to: redirectTo
								})
							});

							const data = await res.json();

							if (res.ok && data.success) {
								showAlert('Autenticado com sucesso! Redirecionando...', 'success');
								loginText.textContent = 'Carregando Plataforma...';
								setTimeout(function() {
									window.location.href = data.redirect_url || redirectTo || '<?php echo esc_url( home_url( '/plataforma' ) ); ?>';
								}, 400);
							} else {
								showAlert(data.message || 'Credenciais inválidas. Verifique seu login e senha.', 'danger');
								btnLogin.disabled = false;
								loginSpinner.style.display = 'none';
								loginArrow.style.display   = 'inline-block';
								loginText.textContent      = 'Entrar na Plataforma';
							}
						} catch (err) {
							showAlert('Erro de conexão ao autenticar. Tente novamente.', 'danger');
							btnLogin.disabled = false;
							loginSpinner.style.display = 'none';
							loginArrow.style.display   = 'inline-block';
							loginText.textContent      = 'Entrar na Plataforma';
						}
					});

					// Forgot Password Submit
					formForgot.addEventListener('submit', async function(e) {
						e.preventDefault();
						hideAlert();

						const forgotLogin = document.getElementById('forgot_user_login').value.trim();
						if (!forgotLogin) {
							showAlert('Informe seu e-mail ou nome de usuário.', 'danger');
							return;
						}

						btnForgot.disabled = true;
						forgotSpinner.style.display = 'inline-block';
						forgotArrow.style.display   = 'none';
						forgotText.textContent      = 'Processando...';

						try {
							const res = await fetch(restBase + '/auth/forgot-password', {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'X-WP-Nonce': nonce
								},
								body: JSON.stringify({
									user_login: forgotLogin
								})
							});

							const data = await res.json();
							showAlert(data.message || 'Se os dados informados existirem, enviamos as instruções para sua caixa de entrada.', 'success');
							btnForgot.disabled = false;
							forgotSpinner.style.display = 'none';
							forgotArrow.style.display   = 'inline-block';
							forgotText.textContent      = 'Enviar Instruções';
						} catch (err) {
							showAlert('Erro ao enviar solicitação de recuperação.', 'danger');
							btnForgot.disabled = false;
							forgotSpinner.style.display = 'none';
							forgotArrow.style.display   = 'inline-block';
							forgotText.textContent      = 'Enviar Instruções';
						}
					});
				});
			</script>
		</body>
		</html>
		<?php
	}
}
