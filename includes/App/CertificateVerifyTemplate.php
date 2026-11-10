<?php
namespace StackEAD\App;

use StackEAD\Services\CertificateService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public Certificate Verification Page (/certificados/{hash}).
 */
class CertificateVerifyTemplate {

	/**
	 * Render certificate validation page.
	 *
	 * @param string $hash
	 */
	public static function render( string $hash ): void {
		$data = CertificateService::verify_certificate( $hash );
		status_header( 200 );
		?>
		<!DOCTYPE html>
		<html lang="pt-BR">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php esc_html_e( 'Validação de Certificado | ArtInnova EAD', 'stackead-core' ); ?></title>
			<link rel="preconnect" href="https://fonts.googleapis.com">
			<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
			<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
			<style>
				:root {
					--primary: #4f46e5;
					--primary-dark: #3730a3;
					--success: #10b981;
					--bg: #0b0f19;
					--card: #111827;
					--border: rgba(255, 255, 255, 0.1);
					--text: #f3f4f6;
					--text-muted: #9ca3af;
				}
				* { box-sizing: border-box; margin: 0; padding: 0; }
				body {
					font-family: 'Plus Jakarta Sans', sans-serif;
					background: radial-gradient(circle at 50% 0%, #1e1b4b 0%, var(--bg) 70%);
					color: var(--text);
					min-height: 100vh;
					display: flex;
					flex-direction: column;
					align-items: center;
					justify-content: center;
					padding: 24px;
				}
				.cert-card {
					background: rgba(17, 24, 39, 0.95);
					backdrop-filter: blur(16px);
					border: 1px solid var(--border);
					border-radius: 24px;
					max-width: 680px;
					width: 100%;
					padding: 48px;
					box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 30px rgba(79, 70, 229, 0.2);
					position: relative;
					overflow: hidden;
				}
				.cert-card::before {
					content: '';
					position: absolute;
					top: 0; left: 0; right: 0; height: 6px;
					background: linear-gradient(90deg, #4f46e5, #06b6d4, #10b981);
				}
				.badge-valid {
					display: inline-flex;
					align-items: center;
					gap: 8px;
					background: rgba(16, 185, 129, 0.15);
					color: #34d399;
					border: 1px solid rgba(16, 185, 129, 0.3);
					padding: 8px 16px;
					border-radius: 9999px;
					font-size: 0.875rem;
					font-weight: 600;
					margin-bottom: 24px;
				}
				.badge-invalid {
					display: inline-flex;
					align-items: center;
					gap: 8px;
					background: rgba(239, 68, 68, 0.15);
					color: #f87171;
					border: 1px solid rgba(239, 68, 68, 0.3);
					padding: 8px 16px;
					border-radius: 9999px;
					font-size: 0.875rem;
					font-weight: 600;
					margin-bottom: 24px;
				}
				h1 {
					font-family: 'Space Grotesk', sans-serif;
					font-size: 1.875rem;
					font-weight: 700;
					margin-bottom: 12px;
					line-height: 1.2;
				}
				p.subtitle {
					color: var(--text-muted);
					font-size: 1rem;
					margin-bottom: 32px;
				}
				.info-grid {
					display: grid;
					grid-template-columns: 1fr 1fr;
					gap: 20px;
					background: rgba(255, 255, 255, 0.03);
					border: 1px solid rgba(255, 255, 255, 0.06);
					border-radius: 16px;
					padding: 24px;
					margin-bottom: 32px;
				}
				.info-item span.label {
					display: block;
					font-size: 0.75rem;
					text-transform: uppercase;
					letter-spacing: 0.05em;
					color: var(--text-muted);
					margin-bottom: 6px;
					font-weight: 600;
				}
				.info-item span.value {
					font-size: 1rem;
					font-weight: 600;
					color: #fff;
				}
				.hash-box {
					background: rgba(0, 0, 0, 0.4);
					border: 1px dashed rgba(255, 255, 255, 0.15);
					border-radius: 12px;
					padding: 16px;
					text-align: center;
					margin-bottom: 32px;
				}
				.hash-box code {
					font-family: monospace;
					font-size: 1.1rem;
					color: #818cf8;
					letter-spacing: 0.1em;
					word-break: break-all;
				}
				.actions {
					display: flex;
					gap: 16px;
					justify-content: center;
				}
				.btn {
					display: inline-flex;
					align-items: center;
					gap: 8px;
					padding: 12px 24px;
					border-radius: 12px;
					font-size: 0.95rem;
					font-weight: 600;
					text-decoration: none;
					transition: all 0.2s ease;
					cursor: pointer;
				}
				.btn-primary {
					background: var(--primary);
					color: #fff;
				}
				.btn-primary:hover {
					background: var(--primary-dark);
					transform: translateY(-2px);
				}
				.btn-secondary {
					background: rgba(255, 255, 255, 0.08);
					color: #fff;
				}
				.btn-secondary:hover {
					background: rgba(255, 255, 255, 0.15);
				}
				@media (max-width: 600px) {
					.info-grid { grid-template-columns: 1fr; }
					.cert-card { padding: 32px 20px; }
				}
				@media print {
					body { background: #fff; color: #000; }
					.cert-card { border: 2px solid #000; box-shadow: none; color: #000; background: #fff; }
					.actions, .badge-valid { display: none; }
				}
			</style>
		</head>
		<body>
			<div class="cert-card">
				<?php if ( $data ) : ?>
					<div class="badge-valid">
						<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
						<?php esc_html_e( 'CERTIFICADO AUTÊNTICO E VERIFICADO', 'stackead-core' ); ?>
					</div>
					<h1><?php echo esc_html( $data['student_name'] ); ?></h1>
					<p class="subtitle"><?php esc_html_e( 'Concluiu com êxito todas as exigências curriculares, avaliações práticas e frequência obrigatória na formação:', 'stackead-core' ); ?></p>

					<div class="info-grid">
						<div class="info-item">
							<span class="label"><?php esc_html_e( 'Formação / Curso', 'stackead-core' ); ?></span>
							<span class="value"><?php echo esc_html( $data['course_title'] ); ?></span>
						</div>
						<div class="info-item">
							<span class="label"><?php esc_html_e( 'Turma / Cohort', 'stackead-core' ); ?></span>
							<span class="value"><?php echo esc_html( $data['cohort_title'] ); ?></span>
						</div>
						<div class="info-item">
							<span class="label"><?php esc_html_e( 'Carga Horária Cumprida', 'stackead-core' ); ?></span>
							<span class="value"><?php echo esc_html( $data['workload_hours'] ); ?> horas</span>
						</div>
						<div class="info-item">
							<span class="label"><?php esc_html_e( 'Data de Emissão', 'stackead-core' ); ?></span>
							<span class="value"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $data['issued_at'] ) ) ); ?></span>
						</div>
					</div>

					<div class="hash-box">
						<span class="label" style="display:block;font-size:0.75rem;color:#9ca3af;margin-bottom:6px;"><?php esc_html_e( 'CÓDIGO DE AUTENTICIDADE CRIPTOGRÁFICA (SHA-256)', 'stackead-core' ); ?></span>
						<code><?php echo esc_html( $data['hash'] ); ?></code>
					</div>

					<div class="actions">
						<button class="btn btn-primary" onclick="window.print()">
							<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
							<?php esc_html_e( 'Imprimir / Salvar PDF', 'stackead-core' ); ?>
						</button>
						<a href="<?php echo esc_url( home_url( '/plataforma' ) ); ?>" class="btn btn-secondary">
							<?php esc_html_e( 'Acessar Plataforma EAD', 'stackead-core' ); ?>
						</a>
					</div>
				<?php else : ?>
					<div class="badge-invalid">
						<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
						<?php esc_html_e( 'REGISTRO NÃO ENCONTRADO', 'stackead-core' ); ?>
					</div>
					<h1><?php esc_html_e( 'Certificado Inválido ou Inexistente', 'stackead-core' ); ?></h1>
					<p class="subtitle"><?php esc_html_e( 'O código hash fornecido não corresponde a nenhum certificado autêntico emitido pela ArtInnova.', 'stackead-core' ); ?></p>
					<div class="hash-box">
						<code><?php echo esc_html( $hash ); ?></code>
					</div>
					<div class="actions">
						<a href="<?php echo esc_url( home_url( '/plataforma' ) ); ?>" class="btn btn-primary">
							<?php esc_html_e( 'Ir para a Plataforma', 'stackead-core' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</body>
		</html>
		<?php
	}
}
