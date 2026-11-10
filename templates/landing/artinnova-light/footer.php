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
          <div><strong data-i18n="footer.contact_label">Contato oficial da coordenação:</strong></div>
          <a href="mailto:contact@artinnovaresidency.com" class="footer-contact-link">contact@artinnovaresidency.com</a>
        </div>
      </div>

      <div class="footer-copyright">
        <span>© 2026–2027 Residência Internacional ArtInnova. Todos os direitos reservados.</span>
        <span>Cooperação Bilateral Brasil · França</span>
      </div>
    </div>
  </footer>

  <?php wp_footer(); ?>
</body>
</html>
