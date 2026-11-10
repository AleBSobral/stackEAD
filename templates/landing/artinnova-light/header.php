<?php
/**
 * Header Template for ArtInnova Light Residency (Pure Residency Standalone)
 */
if (!defined('ABSPATH')) exit;
$theme_uri = get_template_directory_uri();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> data-theme-uri="<?php echo esc_url($theme_uri); ?>">
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ArtInnova · Residência Internacional de Formação e Inovação em Artes Midiáticas entre Brasil e França.">
  <link rel="icon" type="image/png" href="<?php echo esc_url($theme_uri); ?>/assets/images/artinnova_white_logo.png">
  <?php wp_head(); ?>
  <!-- Critical Fallback CSS for Immediate Correct Rendering -->
  <style>
    .site-header { background: #081426 !important; }
    .brand-artinnova-img { height: 54px !important; width: auto; max-height: 54px; }
    .trust-icon { width: 20px !important; height: 20px !important; min-width: 20px !important; max-width: 20px !important; flex-shrink: 0; }
    .pillar-icon-box svg { width: 26px !important; height: 26px !important; max-width: 26px !important; }
    .seal-gold-img { width: 124px !important; max-width: 124px !important; height: auto !important; }
    .section-dark-pavilion { background: #060b14 !important; color: #ffffff !important; }
    .site-footer { background: #081426 !important; }
  </style>
</head>
<body <?php body_class('theme-light'); ?>>
<?php wp_body_open(); ?>

  <!-- Accessibility Skip Link -->
  <a class="skip-link" href="#main-content">Pular para o conteúdo principal</a>

  <!-- Scroll Progress Bar with Vibrant Color Ribbon -->
  <div class="scroll-progress-bar" aria-hidden="true"></div>

  <!-- ============================================================
       Clean Light Header (Standalone ArtInnova Residency)
       ============================================================ -->
  <header class="site-header" role="banner">
    <!-- Linha 1: Identidade Visual ArtInnova + Utilidades -->
    <div class="header-top-bar wrap">
      <a class="brand-link" href="<?php echo esc_url(home_url('/')); ?>" aria-label="ArtInnova International Residency">
        <picture>
          <source srcset="<?php echo esc_url($theme_uri); ?>/assets/images/artinnova_white_logo.webp" type="image/webp">
          <img class="brand-artinnova-img" src="<?php echo esc_url($theme_uri); ?>/assets/images/artinnova_white_logo.png" alt="ArtInnova International Residency">
        </picture>
      </a>

      <div class="header-utilities">
        <!-- Seletor de Idiomas com 4 Bandeiras Nacionais Oficiais -->
        <div class="lang-selector" role="group" aria-label="Selecione o idioma">
          <button class="lang-btn active" data-lang="pt" aria-label="Português" title="Português (Brasil)" aria-pressed="true">
            <svg class="flag-icon" viewBox="0 0 640 480" aria-hidden="true">
              <path fill="#009c3b" d="M0 0h640v480H0z"/>
              <path fill="#ffdf00" d="m320 40 280 200-280 200L40 240z"/>
              <circle fill="#002776" cx="320" cy="240" r="105"/>
              <path fill="#ffffff" d="M215 240c40-35 170-35 210 0-40-20-170-20-210 0z"/>
            </svg>
            <span>PT</span>
          </button>

          <button class="lang-btn" data-lang="en" aria-label="English" title="English (United States / International)" aria-pressed="false">
            <svg class="flag-icon" viewBox="0 0 640 480" aria-hidden="true">
              <path fill="#bf0a30" d="M0 0h640v480H0z"/>
              <path stroke="#fff" stroke-width="37" d="M0 55h640M0 129h640M0 203h640M0 277h640M0 351h640M0 425h640"/>
              <path fill="#002868" d="M0 0h260v260H0z"/>
              <circle fill="#fff" cx="130" cy="130" r="18"/>
            </svg>
            <span>EN</span>
          </button>

          <button class="lang-btn" data-lang="es" aria-label="Español" title="Español (España / América Latina)" aria-pressed="false">
            <svg class="flag-icon" viewBox="0 0 640 480" aria-hidden="true">
              <path fill="#aa151b" d="M0 0h640v480H0z"/>
              <path fill="#f1bf00" d="M0 120h640v240H0z"/>
              <circle fill="#aa151b" cx="150" cy="240" r="28"/>
            </svg>
            <span>ES</span>
          </button>

          <button class="lang-btn" data-lang="fr" aria-label="Français" title="Français (France)" aria-pressed="false">
            <svg class="flag-icon" viewBox="0 0 640 480" aria-hidden="true">
              <path fill="#002654" d="M0 0h213.3v480H0z"/>
              <path fill="#ce1126" d="M426.7 0H640v480H426.7z"/>
              <path fill="#ffffff" d="M213.3 0h213.4v480H213.3z"/>
            </svg>
            <span>FR</span>
          </button>
        </div>

        <a class="btn btn-primary" href="#inscricao" data-i18n="nav.cta">Tenho interesse ↗</a>

        <!-- Mobile Hamburguer Toggle -->
        <button class="mobile-toggle" aria-label="Abrir menu de navegação" aria-expanded="false">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </div>

    <!-- Linha 2: Barra de Navegação Editorial com Acentos Coloridos -->
    <div class="header-nav-bar">
      <div class="wrap">
        <nav class="nav-links" aria-label="Navegação de seções">
          <a href="#experiencia"><span class="nav-num num-blue">01</span> <span data-i18n="nav.experience">A experiência</span></a>
          <a href="#para-quem"><span class="nav-num num-green">02</span> <span data-i18n="nav.audience">Para quem</span></a>
          <a href="#pontes"><span class="nav-num num-yellow">03</span> <span data-i18n="nav.bridges">Pontes</span></a>
          <a href="#jornada"><span class="nav-num num-red">04</span> <span data-i18n="nav.journey">Jornada</span></a>
          <a href="#premio"><span class="nav-num num-cyan">05</span> <span data-i18n="nav.award">Prêmio</span></a>
          <a href="#investimento"><span class="nav-num num-blue">06</span> <span data-i18n="nav.pricing">Investimento</span></a>
          <a href="#duvidas"><span class="nav-num num-green">07</span> <span data-i18n="nav.faq">Dúvidas</span></a>
        </nav>
      </div>
    </div>
  </header>
