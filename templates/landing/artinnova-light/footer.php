<?php
/**
 * Footer Template for ArtInnova Light Residency (Pure Residency Standalone)
 */
if (!defined('ABSPATH')) exit;
$theme_uri = get_template_directory_uri();
?>
  <!-- ============================================================
       Footer Institucional ArtInnova Residência (Tema Claro)
       ============================================================ -->
  <footer class="site-footer" role="contentinfo">
    <div class="wrap">
      <div class="footer-inner">
        <div class="footer-brand-block">
          <div class="footer-logos">
            <picture>
              <source srcset="<?php echo esc_url($theme_uri); ?>/assets/images/artinnova_white_logo.webp" type="image/webp">
              <img class="footer-artinnova-img" src="<?php echo esc_url($theme_uri); ?>/assets/images/artinnova_white_logo.png" alt="ArtInnova International Residency">
            </picture>
          </div>
          <div class="footer-meta-copy">
            <span data-i18n="footer.tagline">Residência Internacional ArtInnova · Artes Midiáticas e Inovação Territorial</span>
            <br>
            <span data-i18n="footer.cities">Campina Grande (Brasil) · Enghien-les-Bains (França)</span>
          </div>
        </div>

        <div class="footer-links">
          <div><strong data-i18n="footer.contact_label">Contato oficial &amp; DPO (Privacidade):</strong></div>
          <a href="mailto:contact@artinnovaresidency.com" class="footer-contact-link">contact@artinnovaresidency.com</a>
          <div class="footer-legal-links">
            <button type="button" class="footer-legal-btn" data-open-privacy-modal="true" data-i18n="footer.privacy_link">Política de Privacidade (LGPD &amp; GDPR)</button>
            <span aria-hidden="true">·</span>
            <button type="button" class="footer-legal-btn" data-open-privacy-modal="true" data-i18n="footer.rights_link">Direitos do Titular &amp; DPO</button>
            <span aria-hidden="true">·</span>
            <button type="button" class="footer-legal-btn" data-open-privacy-modal="cookies" data-i18n="footer.cookies_link">Preferências de Cookies</button>
          </div>
        </div>
      </div>

      <div class="footer-copyright">
        <span data-i18n="footer.copyright_text">© 2026–2027 Residência Internacional ArtInnova. Todos os direitos reservados.</span>
        <span data-i18n="footer.bilateral_tag">Cooperação Bilateral Brasil · França · Conformidade LGPD &amp; GDPR</span>
      </div>
    </div>
  </footer>

  <!-- ============================================================
       LGPD (Brasil) & GDPR / CNIL (EU) Cookie Consent Banner
       ============================================================ -->
  <aside id="cookie-consent-banner" class="cookie-consent-banner" role="region" aria-label="Cookie Consent" hidden>
    <div class="cookie-banner-inner">
      <div class="cookie-banner-copy">
        <strong class="cookie-banner-title" data-i18n="privacy.banner_title">Privacidade, Cookies &amp; Proteção de Dados (LGPD · GDPR)</strong>
        <p class="cookie-banner-text" data-i18n="privacy.banner_text">Utilizamos cookies estritamente necessários para idioma e segurança. Cookies analíticos só são ativados se você autorizar expressamente, conforme a LGPD (Brasil) e o GDPR / CNIL (União Europeia).</p>
      </div>
      <div class="cookie-banner-actions">
        <button type="button" id="btn-cookie-reject" class="cookie-btn cookie-btn-equal" data-i18n="privacy.btn_reject_non_essential">Apenas Essenciais</button>
        <button type="button" id="btn-cookie-accept" class="cookie-btn cookie-btn-equal" data-i18n="privacy.btn_accept_all">Aceitar Todos</button>
        <button type="button" id="btn-cookie-customize" class="cookie-btn cookie-btn-subtle" data-i18n="privacy.btn_customize">Personalizar &amp; Política</button>
      </div>
    </div>
  </aside>

  <!-- ============================================================
       Central Legal & Política de Privacidade (LGPD + GDPR Modal)
       ============================================================ -->
  <div id="privacy-modal" class="privacy-modal" role="dialog" aria-modal="true" aria-labelledby="privacy-modal-title" hidden>
    <div class="privacy-modal-backdrop" data-close-privacy-modal="true"></div>
    <div class="privacy-modal-dialog" role="document">
      <div class="privacy-modal-header">
        <div>
          <h2 id="privacy-modal-title" data-i18n="privacy.modal_title">Política de Privacidade, Cookies &amp; Direitos do Titular (LGPD &amp; GDPR)</h2>
          <p class="privacy-modal-subtitle" data-i18n="privacy.modal_subtitle">Transparência internacional aplicada à cooperação bilateral Campina Grande (Brasil) ↔ Enghien-les-Bains (França / UE).</p>
        </div>
        <button type="button" class="privacy-modal-close" data-close-privacy-modal="true" aria-label="Close">&times;</button>
      </div>

      <div class="privacy-modal-body">
        <section class="privacy-doc-block">
          <h3 data-i18n="privacy.section_controller_title">1. Controlador Bilateral &amp; Encarregado (DPO)</h3>
          <p data-i18n="privacy.section_controller_body">Seus dados são tratados em conjunto pela coordenação da Residência Internacional ArtInnova no Brasil e na França/União Europeia. Canal oficial do Encarregado pelo Tratamento de Dados (DPO): contact@artinnovaresidency.com.</p>
        </section>

        <section class="privacy-doc-block">
          <h3 data-i18n="privacy.section_legal_title">2. Finalidade &amp; Bases Legais (LGPD Art. 7º · GDPR Art. 6º)</h3>
          <p data-i18n="privacy.section_legal_body">Coletamos nome, e-mail, cidade/país, perfil e indicação mediante seu consentimento explícito para contato institucional, processo seletivo e acolhimento acadêmico, bem como por execução contratual quando matriculado na plataforma EAD.</p>
        </section>

        <section class="privacy-doc-block">
          <h3 data-i18n="privacy.section_transfer_title">3. Transferência Internacional Brasil ↔ França (UE)</h3>
          <p data-i18n="privacy.section_transfer_body">Por se tratar de uma residência bilateral, seus dados acadêmicos podem ser acessados pela equipe curatorial e docente no Brasil e na França sob cláusulas contratuais padrão, criptografia TLS e salvaguardas equivalentes (LGPD Art. 33 · GDPR Capítulo V, Arts. 44–49).</p>
        </section>

        <section class="privacy-doc-block">
          <h3 data-i18n="privacy.section_rights_title">4. Retenção &amp; Seus Direitos (ANPD Brasil · CNIL França)</h3>
          <p data-i18n="privacy.section_rights_body">Os dados de manifestação de interesse são mantidos por até 24 meses ou até revogação do consentimento. Você possui direito garantido de acesso, retificação, portabilidade (JSON), oposição e eliminação completa escrevendo para contact@artinnovaresidency.com ou diretamente em 'Meu Perfil &amp; Privacidade' dentro da plataforma EAD. Autoridades competentes: ANPD (Brasil) e CNIL (França).</p>
        </section>

        <section class="privacy-cookies-box" id="privacy-cookies-section">
          <h3 data-i18n="privacy.cookies_prefs_title">Preferências de Cookies neste Navegador</h3>

          <div class="privacy-toggle-row">
            <div>
              <strong data-i18n="privacy.cookie_essential_label">Cookies Essenciais (Idioma e Segurança da Sessão)</strong>
              <span data-i18n="privacy.cookie_essential_desc">Sempre ativos para garantir funcionamento, idioma escolhido e proteção contra envios automatizados.</span>
            </div>
            <input type="checkbox" checked disabled aria-label="Cookies Essenciais">
          </div>

          <div class="privacy-toggle-row">
            <div>
              <strong data-i18n="privacy.cookie_functional_label">Preferências Funcionais &amp; Acessibilidade</strong>
              <span data-i18n="privacy.cookie_functional_desc">Memoriza suas preferências visuais e estado de animações reduzidas.</span>
            </div>
            <input type="checkbox" id="pref-cookie-functional" checked>
          </div>

          <div class="privacy-toggle-row">
            <div>
              <strong data-i18n="privacy.cookie_analytics_label">Métricas Pedagógicas &amp; Analíticos Anonimizados</strong>
              <span data-i18n="privacy.cookie_analytics_desc">Desativado por padrão (Privacy by Default - GDPR). Ajuda a melhorar o portal sem rastreamento publicitário.</span>
            </div>
            <input type="checkbox" id="pref-cookie-analytics">
          </div>
        </section>
      </div>

      <div class="privacy-modal-footer">
        <button type="button" class="cookie-btn cookie-btn-subtle" data-close-privacy-modal="true" data-i18n="privacy.btn_close_modal">Fechar</button>
        <button type="button" id="btn-save-cookie-prefs" class="cookie-btn cookie-btn-equal" data-i18n="privacy.btn_save_prefs">Salvar Preferências</button>
      </div>
    </div>
  </div>

  <?php wp_footer(); ?>
</body>
</html>
