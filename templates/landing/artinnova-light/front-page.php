<?php
/**
 * Front Page Template - ArtInnova Light (Standalone International Residency)
 * Design & Marketing Refined Version: High Contrast Hero, Super Award Banner & Dual Guarantee
 */
if (!defined('ABSPATH')) exit;
get_header();
$theme_uri = get_template_directory_uri();
?>

  <main id="main-content">
    <!-- ============================================================
         Hero Section Claro com Alto Contraste & Partículas Pixel Multicor
         ============================================================ -->
    <section id="hero" class="hero-section light-hero">
      <canvas id="hero-pixel-canvas" class="hero-pixel-canvas" aria-hidden="true"></canvas>

      <div class="hero-bg">
        <picture>
          <source srcset="<?php echo esc_url($theme_uri); ?>/assets/images/hero_artinnova_rich_light.webp" type="image/webp">
          <img class="hero-bg-img" src="<?php echo esc_url($theme_uri); ?>/assets/images/hero_artinnova_rich_light.jpg" alt="Ateliê de artes midiáticas da Residência ArtInnova com projeções e feixes de luz volumétrica">
        </picture>
        <div class="hero-overlay"></div>
      </div>

      <div class="wrap hero-content">
        <div class="hero-badges-row">
          <div class="hero-floating-badge" id="hero-floating-badge">
            <span class="pulse-dot"></span>
            <span data-i18n="hero.eyebrow">Residência Internacional ArtInnova · Brasil + França</span>
          </div>
          <div class="hero-sub-badge">
            <span class="badge-star">★</span>
            <span data-i18n="hero.award_callout">Super Prêmio: Imersão Internacional em Paris/Enghien-les-Bains</span>
          </div>
        </div>

        <h1 class="hero-title">
          <span data-i18n="hero.title_part1">Sua próxima inovação começa com uma </span>
          <span class="accent-color" data-i18n="hero.title_accent">ponte.</span>
        </h1>

        <div class="hero-grid">
          <div>
            <p class="hero-lead" data-i18n="hero.lead">Aprenda Artes Midiáticas. Construa pontes transatlânticas. Transforme criatividade em impacto real.</p>
            <div class="hero-actions">
              <a class="btn btn-primary" href="#inscricao" data-i18n="hero.cta_primary">Quero participar ↗</a>
              <a class="btn btn-secondary" href="#premio" data-i18n="hero.cta_secondary">Conheça o Super Prêmio ↓</a>
            </div>
            
            <!-- Trust badges rápidos no Hero -->
            <div class="hero-trust-row">
              <div class="trust-item">
                <svg class="trust-icon" width="20" height="20" viewBox="0 0 24 24" style="width:20px;height:20px;min-width:20px;max-width:20px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span data-i18n="hero.trust_guarantee">Garantia incondicional de 7 dias</span>
              </div>
              <div class="trust-item">
                <svg class="trust-icon" width="20" height="20" viewBox="0 0 24 24" style="width:20px;height:20px;min-width:20px;max-width:20px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span data-i18n="hero.trust_slots">Turma exclusiva de 60 participantes</span>
              </div>
            </div>
          </div>

          <div class="hero-meta-box" data-tilt>
            <div class="hero-meta-header">
              <div class="hero-meta-title" data-i18n="hero.meta_dates">INSCRIÇÕES 2026 · FORMAÇÃO JANEIRO–ABRIL 2027</div>
              <span class="hero-meta-tag">Turma Piloto</span>
            </div>
            <p class="hero-meta-desc" data-i18n="hero.meta_text">Uma experiência internacional de formação, criação e colaboração entre Campina Grande e Enghien-les-Bains. 60 participantes: 50 no Brasil (40 matrículas pagas e 10 sociais) e 10 matrículas sociais na França.</p>
            <div class="hero-meta-price-badge">
              <div class="price-badge-label" data-i18n="hero.meta_price_label">Condição de Inscrição</div>
              <div class="price-badge-val" data-i18n="hero.meta_price">Brasil: 10 × R$ 300 no cartão de crédito</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================
         Interactive Metrics Bar com Cores do Sistema
         ============================================================ -->
    <div class="metrics-bar" role="region" aria-label="Números da Residência">
      <div class="wrap">
        <div class="metrics-grid">
          <div class="metric-item">
            <div class="metric-num-wrap">
              <span class="metric-number metric-blue" data-counter="60">60</span>
            </div>
            <div class="metric-label" data-i18n="metrics.slots">vagas internacionais</div>
            <div class="metric-sub" data-i18n="metrics.slots_sub">50 Brasil · 10 França</div>
          </div>

          <div class="metric-item">
            <div class="metric-num-wrap">
              <span class="metric-number metric-green" data-counter="50">50</span>
            </div>
            <div class="metric-label" data-i18n="metrics.br_slots">participantes no Brasil</div>
            <div class="metric-sub" data-i18n="metrics.br_slots_sub">40 pagas · 10 sociais</div>
          </div>

          <div class="metric-item">
            <div class="metric-num-wrap">
              <span class="metric-number metric-yellow" data-counter="10">10</span>
            </div>
            <div class="metric-label" data-i18n="metrics.fr_slots">participantes na França</div>
            <div class="metric-sub" data-i18n="metrics.fr_slots_sub">100% bolsas sociais</div>
          </div>

          <div class="metric-item">
            <div class="metric-num-wrap">
              <span class="metric-number metric-cyan" data-counter="4">4</span>
            </div>
            <div class="metric-label" data-i18n="metrics.travel_awards">super prêmios de viagem</div>
            <div class="metric-sub" data-i18n="metrics.travel_awards_sub">2 BR para França · 2 FR para Brasil</div>
          </div>

          <div class="metric-item">
            <div class="metric-num-wrap">
              <span class="metric-number metric-red" data-counter="1">1</span>
            </div>
            <div class="metric-label" data-i18n="metrics.mission">missão contínua</div>
            <div class="metric-sub" data-i18n="metrics.mission_sub">transformar ideias em pontes</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================
         01 — A Experiência (Editorial Moderno)
         ============================================================ -->
    <section id="experiencia" class="section light-surface">
      <div class="wrap">
        <div class="eyebrow eyebrow-blue" data-i18n="experience.eyebrow">01 — A experiência</div>
        <h2 class="section-title">
          <span data-i18n="experience.title_part1">Quatro meses para conectar o que você sabe ao que você ainda pode </span>
          <span class="accent-color" data-i18n="experience.title_accent">inventar.</span>
          <span class="title-underline-anim"></span>
        </h2>
        <p class="section-intro" data-i18n="experience.intro">A ArtInnova é um espaço de formação aplicada, criação de projetos e intercâmbio entre o ecossistema brasileiro de inovação e a cena europeia de Artes Midiáticas.</p>

        <div class="experience-grid">
          <div class="experience-quote-col">
            <div class="editorial-quote-card">
              <div class="quote-text" data-i18n="experience.quote">
                "Não basta dominar ferramentas digitais: é preciso construir projetos com propósito, sensibilidade artística e capacidade de dialogar com redes internacionais."
              </div>
              <div class="quote-author" data-i18n="experience.quote_author">Diretriz Curatorial · Residência ArtInnova</div>
            </div>

            <div class="experience-media-wrap" data-tilt>
              <picture>
                <source srcset="<?php echo esc_url($theme_uri); ?>/assets/images/atelier_cocreation.webp" type="image/webp">
                <img src="<?php echo esc_url($theme_uri); ?>/assets/images/atelier_cocreation.jpg" alt="Atelier de co-criação internacional de artes midiáticas" class="experience-img">
              </picture>
              <div class="experience-media-caption">Atelier de Co-criação Internacional · Metodologias Interdisciplinares</div>
            </div>
          </div>

          <div class="pillars-col">
            <div class="pillar-card border-accent-blue" data-tilt>
              <div class="pillar-card-header">
                <span class="card-num-badge bg-blue" data-i18n="experience.items.0.num">01</span>
                <h3 class="card-title" data-i18n="experience.items.0.title">Aprender</h3>
              </div>
              <p class="card-desc" data-i18n="experience.items.0.desc">Explorar Artes Midiáticas, tecnologias e metodologias de inovação com uma abordagem interdisciplinar e imersiva.</p>
            </div>

            <div class="pillar-card border-accent-green" data-tilt>
              <div class="pillar-card-header">
                <span class="card-num-badge bg-green" data-i18n="experience.items.1.num">02</span>
                <h3 class="card-title" data-i18n="experience.items.1.title">Criar</h3>
              </div>
              <p class="card-desc" data-i18n="experience.items.1.desc">Desenvolver uma obra, protótipo ou iniciativa conectada a um desafio que faça sentido para você e seu território.</p>
            </div>

            <div class="pillar-card border-accent-yellow" data-tilt>
              <div class="pillar-card-header">
                <span class="card-num-badge bg-yellow" data-i18n="experience.items.2.num">03</span>
                <h3 class="card-title" data-i18n="experience.items.2.title">Conectar</h3>
              </div>
              <p class="card-desc" data-i18n="experience.items.2.desc">Conhecer outros participantes, mestres e redes criativas, ampliando oportunidades de colaboração bilateral Brasil–França.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stripe / Marquee Banner com Cores Vivas -->
    <div class="stripe-banner" aria-hidden="true">
      <div class="marquee-track">
        <div class="marquee-item">
          <span data-i18n="stripe">CRIAR. EXPERIMENTAR. COMPARTILHAR. CONECTAR.</span>
          <span data-i18n="stripe">CRIAR. EXPERIMENTAR. COMPARTILHAR. CONECTAR.</span>
          <span data-i18n="stripe">CRIAR. EXPERIMENTAR. COMPARTILHAR. CONECTAR.</span>
        </div>
        <div class="marquee-item">
          <span data-i18n="stripe">CRIAR. EXPERIMENTAR. COMPARTILHAR. CONECTAR.</span>
          <span data-i18n="stripe">CRIAR. EXPERIMENTAR. COMPARTILHAR. CONECTAR.</span>
          <span data-i18n="stripe">CRIAR. EXPERIMENTAR. COMPARTILHAR. CONECTAR.</span>
        </div>
      </div>
    </div>

    <!-- ============================================================
         02 — Para Quem (Cards em Fundo Suave com Acentos Coloridos)
         ============================================================ -->
    <section id="para-quem" class="section light-bg">
      <div class="section-watermark watermark-light" style="background-image: url('<?php echo esc_url($theme_uri); ?>/assets/images/artinnova_white_logo.png');" aria-hidden="true"></div>

      <div class="wrap two-cols-asym">
        <div>
          <div class="eyebrow eyebrow-green" data-i18n="audience.eyebrow">02 — Para quem</div>
          <h2 class="section-title">
            <span data-i18n="audience.title_part1">Para quem não se conforma em apenas </span>
            <span class="accent-color" data-i18n="audience.title_accent">imaginar o futuro.</span>
            <span class="title-underline-anim"></span>
          </h2>
          <p class="section-intro" data-i18n="audience.intro">Para pessoas com experiência, curiosidade, sensibilidade artística e tecnológica e vontade real de transformar ideias em iniciativas concretas.</p>
        </div>

        <div id="audience-list-container" class="audience-list">
          <div class="audience-item border-left-blue">
            <span class="audience-item-num num-blue">01</span>
            <span class="audience-item-text">Criadores, artistas, designers e realizadores visuais</span>
          </div>
          <div class="audience-item border-left-green">
            <span class="audience-item-num num-green">02</span>
            <span class="audience-item-text">Empreendedores, tecnólogos e profissionais de inovação</span>
          </div>
          <div class="audience-item border-left-yellow">
            <span class="audience-item-num num-yellow">03</span>
            <span class="audience-item-text">Educadores, pesquisadores e estudantes com vivência profissional</span>
          </div>
          <div class="audience-item border-left-red">
            <span class="audience-item-num num-red">04</span>
            <span class="audience-item-text">Gestores de cultura, tecnologia e desenvolvimento territorial</span>
          </div>
          <div class="audience-item border-left-cyan">
            <span class="audience-item-num num-cyan">05</span>
            <span class="audience-item-text">Profissionais de outras áreas interessados em criar com Artes Midiáticas</span>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================
         03 — Pontes Internacionais (Fotografia Diurna Vibrante)
         ============================================================ -->
    <section id="pontes" class="section light-surface">
      <div class="section-watermark watermark-light watermark-left" style="background-image: url('<?php echo esc_url($theme_uri); ?>/assets/images/artinnova_white_logo.png');" aria-hidden="true"></div>

      <div class="wrap bridges-grid">
        <div class="media-frame bridges-media-frame" data-tilt>
          <picture>
            <source srcset="<?php echo esc_url($theme_uri); ?>/assets/images/international_bridge_light.webp" type="image/webp">
            <img src="<?php echo esc_url($theme_uri); ?>/assets/images/international_bridge_light.jpg" alt="Ponte internacional conectando cidades criativas sob a luz do dia">
          </picture>
          <div class="media-frame-overlay">
            <span class="frame-tag bg-blue">Territórios Conectados</span>
            <div class="media-frame-caption">Cooperação Internacional · Campina Grande ✕ Enghien-les-Bains</div>
          </div>
        </div>

        <div class="bridges-content">
          <div class="eyebrow eyebrow-yellow" data-i18n="bridges.eyebrow">03 — Pontes internacionais</div>
          <h2 class="section-title">
            <span data-i18n="bridges.title_part1">Do seu território para uma rede </span>
            <span class="accent-color" data-i18n="bridges.title_accent">sem fronteiras.</span>
            <span class="title-underline-anim"></span>
          </h2>
          <p class="section-intro" data-i18n="bridges.intro">A turma piloto nasce da cooperação entre Campina Grande, no Brasil, e Enghien-les-Bains, na França, dialogando com conexões desenvolvidas em cidades e territórios criativos globais.</p>
          <p data-i18n="bridges.p1">A cooperação internacional e a mobilização da Rede Brasileira de Cidades Criativas da UNESCO (RBCC) ampliam as possibilidades de encontro entre repertórios, desafios e iniciativas. A participação de instituições e mestres específicos será divulgada após confirmação.</p>
          <div class="bridges-disclaimer" data-i18n="bridges.disclaimer">A referência à RBCC e às cidades criativas descreve conexões de cooperação; não implica certificação ou chancela direta da UNESCO para a Residência.</div>
        </div>
      </div>
    </section>

    <!-- ============================================================
         04 — Jornada Formativa (Linha do Tempo com 5 Cores)
         ============================================================ -->
    <section id="jornada" class="section light-bg">
      <div class="wrap">
        <div class="eyebrow eyebrow-red" data-i18n="journey.eyebrow">04 — Programa formativo · calendário macro provisório</div>
        <h2 class="section-title">
          <span data-i18n="journey.title_part1">De suas ideias a uma criação </span>
          <span class="accent-color" data-i18n="journey.title_accent">compartilhada.</span>
          <span class="title-underline-anim"></span>
        </h2>
        <p class="section-intro" data-i18n="journey.intro">Uma trilha pensada para quem tem rotina profissional e precisa de flexibilidade, sem abrir mão do rigor prático e do trabalho colaborativo.</p>

        <div class="timeline-grid">
          <article class="timeline-card border-top-blue" data-tilt>
            <div class="timeline-step tag-blue" data-i18n="journey.steps.0.step">ETAPA 01 · DEZEMBRO 2026</div>
            <h3 class="timeline-title" data-i18n="journey.steps.0.title">Acolhimento & Preparação</h3>
            <p class="timeline-desc" data-i18n="journey.steps.0.desc">Apresentação da turma, alinhamento metodológico, plataformas, primeiros contatos e ambientação na dinâmica internacional.</p>
          </article>

          <article class="timeline-card border-top-green" data-tilt>
            <div class="timeline-step tag-green" data-i18n="journey.steps.1.step">ETAPA 02 · JANEIRO 2027</div>
            <h3 class="timeline-title" data-i18n="journey.steps.1.title">Fundamentos & Repertório</h3>
            <p class="timeline-desc" data-i18n="journey.steps.1.desc">Imersão em linguagens de Artes Midiáticas, referências contemporâneas, ética, inteligência artificial e definição de desafios.</p>
          </article>

          <article class="timeline-card border-top-yellow" data-tilt>
            <div class="timeline-step tag-yellow" data-i18n="journey.steps.2.step">ETAPA 03 · FEVEREIRO 2027</div>
            <h3 class="timeline-title" data-i18n="journey.steps.2.title">Ideação & Prototipagem</h3>
            <p class="timeline-desc" data-i18n="journey.steps.2.desc">Desenvolvimento de conceitos em equipes interdisciplinares, testes rápidos de viabilidade e primeiras sessões de mentoria.</p>
          </article>

          <article class="timeline-card border-top-red" data-tilt>
            <div class="timeline-step tag-red" data-i18n="journey.steps.3.step">ETAPA 04 · MARÇO 2027</div>
            <h3 class="timeline-title" data-i18n="journey.steps.3.title">Desenvolvimento & Maturação</h3>
            <p class="timeline-desc" data-i18n="journey.steps.3.desc">Refinamento técnico das propostas, workshops com convidados internacionais e preparação para a banca avaliadora.</p>
          </article>

          <article class="timeline-card border-top-cyan" data-tilt>
            <div class="timeline-step tag-cyan" data-i18n="journey.steps.4.step">ETAPA 05 · ABRIL 2027</div>
            <h3 class="timeline-title" data-i18n="journey.steps.4.title">HackArtThon & Apresentação</h3>
            <p class="timeline-desc" data-i18n="journey.steps.4.desc">Maratona de encerramento, mostra coletiva de projetos e anúncio dos premiados que viajam à França e ao Brasil.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- ============================================================
         05 — Super Premiação Internacional (Destaque Máximo da Landing)
         ============================================================ -->
    <section id="premio" class="award-super-section section-dark-pavilion">
      <div class="pavilion-ambient-light" aria-hidden="true"></div>
      <div class="wrap">
        <div class="award-header-center">
          <div class="award-prestige-badge">
            <span class="badge-trophy-icon">🏆</span>
            <span data-i18n="award.badge">Prêmio Internacional de Artes Midiáticas · França & Brasil</span>
          </div>
          <h2 class="award-main-title">
            <span class="title-white" data-i18n="award.title_part1">Media Arts Innovation Bridges</span>
            <span class="title-gold-glow" data-i18n="award.title_accent">4 Bolsas de Imersão Presencial com Tudo Pago na França e no Brasil</span>
          </h2>
          <p class="award-main-intro" data-i18n="award.intro">Uma oportunidade de intercâmbio prático e curatorial: os projetos desenvolvidos nos ateliês concorrem a quatro bolsas integrais com passagens aéreas internacionais, alojamento completo, seguro, per diem e ateliê técnico no prestigiado Centre des Arts em Enghien-les-Bains (região metropolitana de Paris) e em Campina Grande.</p>
        </div>

        <!-- Super Banner Cinematográfico do Prêmio -->
        <div class="award-heroic-banner" data-tilt>
          <div class="banner-img-wrap">
            <picture>
              <source srcset="<?php echo esc_url($theme_uri); ?>/assets/images/award_super_banner.webp" type="image/webp">
              <img src="<?php echo esc_url($theme_uri); ?>/assets/images/award_super_banner.jpg" alt="Centre des Arts Enghien-les-Bains Paris e premiação de artes digitais">
            </picture>
            <div class="banner-gradient-overlay"></div>
          </div>

          <div class="banner-content-floating">
            <div class="banner-location-badge">
              <span class="loc-pin">📍</span> Centre des Arts · Enghien-les-Bains / Paris, França
            </div>
            <h3 class="banner-heading" data-i18n="award.banner_title">Da sua criação local para o palco da inovação europeia</h3>
            <p class="banner-subtext" data-i18n="award.banner_desc">Exposição de projetos, intercâmbio com centros de pesquisa, ateliês com mestres internacionais e vivência em uma das cidades criativas mais emblemáticas do mundo.</p>
          </div>
        </div>

        <!-- Os 4 Pilares da Super Premiação (Cards Ricos e Coloridos) -->
        <div class="award-pillars-grid">
          <div class="award-pillar-card pillar-flight" data-tilt>
            <div class="pillar-icon-box">
              <svg width="26" height="26" viewBox="0 0 24 24" style="width:26px;height:26px;max-width:26px;" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.3c.4-.2.6-.6.5-1.1z"/></svg>
            </div>
            <h4 class="pillar-title" data-i18n="award.pillars.0.title">Passagens & Hospedagem 100% Pagas</h4>
            <p class="pillar-desc" data-i18n="award.pillars.0.desc">Custeio integral de transporte aéreo internacional, estadia e seguro para os residentes selecionados pela banca mista.</p>
          </div>

          <div class="award-pillar-card pillar-artcenter" data-tilt>
            <div class="pillar-icon-box">
              <svg width="26" height="26" viewBox="0 0 24 24" style="width:26px;height:26px;max-width:26px;" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 10h18M5 10v11M19 10v11M9 10v11M15 10v11M12 2 2 7h20L12 2z"/></svg>
            </div>
            <h4 class="pillar-title" data-i18n="award.pillars.1.title">Residência no Centre des Arts (França)</h4>
            <p class="pillar-desc" data-i18n="award.pillars.1.desc">Acesso a infraestrutura e ateliês profissionais de criação em artes midiáticas e tecnologias interativas na Europa.</p>
          </div>

          <div class="award-pillar-card pillar-mentors" data-tilt>
            <div class="pillar-icon-box">
              <svg width="26" height="26" viewBox="0 0 24 24" style="width:26px;height:26px;max-width:26px;" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h4 class="pillar-title" data-i18n="award.pillars.2.title">Mentoria Direta com Mestres Europeus</h4>
            <p class="pillar-desc" data-i18n="award.pillars.2.desc">Sessões individuais e em equipe com curadores, diretores artísticos e pesquisadores do ecossistema francês.</p>
          </div>

          <div class="award-pillar-card pillar-expo" data-tilt>
            <div class="pillar-icon-box">
              <svg width="26" height="26" viewBox="0 0 24 24" style="width:26px;height:26px;max-width:26px;" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <h4 class="pillar-title" data-i18n="award.pillars.3.title">Apresentação Pública & Visibilidade</h4>
            <p class="pillar-desc" data-i18n="award.pillars.3.desc">Mostra e difusão do seu projeto em redes culturais internacionais, festivais de artes digitais e canais institucionais.</p>
          </div>
        </div>

        <!-- 2 Cards de Vagas Premiadas em Grid Nobre -->
        <div class="award-grid">
          <article class="award-card border-accent-blue" data-tilt>
            <div class="award-card-top">
              <span class="award-flag-badge">🇧🇷 Brasil</span>
              <div class="award-tag tag-blue" data-i18n="award.br_tag">02 VAGAS PREMIADAS</div>
            </div>
            <h3 class="award-title" data-i18n="award.br_title">Dois premiados entre os 50 participantes brasileiros</h3>
            <p class="award-desc" data-i18n="award.br_desc">Elegíveis participantes de matrículas pagas e sociais com igualdade de condições, avaliados por critérios de criatividade, rigor técnico e cooperação durante o HackArtThon.</p>
          </article>

          <article class="award-card border-accent-cyan" data-tilt>
            <div class="award-card-top">
              <span class="award-flag-badge">🇫🇷 França</span>
              <div class="award-tag tag-cyan" data-i18n="award.fr_tag">02 VAGAS PREMIADAS</div>
            </div>
            <h3 class="award-title" data-i18n="award.fr_title">Dois premiados entre os 10 participantes franceses</h3>
            <p class="award-desc" data-i18n="award.fr_desc">Dois participantes franceses viajam para o Brasil para imersão presencial no ecossistema de Campina Grande, completando a ponte bilateral de conhecimento.</p>
          </article>
        </div>

        <div class="award-footer-bar">
          <div class="award-footer-info">
            <strong data-i18n="award.footer_title">Critérios Claros e Transparentes:</strong>
            <p class="award-details-text" data-i18n="award.details">A seleção final ocorrerá no encerramento da formação (HackArtThon de abril), por banca mista Brasil–França. Viagem prevista para junho de 2027. Todas as condições estão publicadas em minuta pública.</p>
          </div>
          <a class="btn btn-secondary" href="<?php echo esc_url($theme_uri); ?>/Regulamento_Media_Arts_Innovation_Bridges_MINUTA.docx" data-i18n="award.cta_rules">Ver minuta do regulamento ↗</a>
        </div>
      </div>
    </section>
<!-- ============================================================
         06 — Investimento & Dupla Garantia Blindada (Marketing & Confiança)
         ============================================================ -->
    <section id="investimento" class="section light-bg">
      <div class="wrap">
        <div class="eyebrow eyebrow-blue" data-i18n="pricing.eyebrow">06 — Investimento & Segurança</div>
        <h2 class="section-title">
          <span data-i18n="pricing.title_part1">Uma formação internacional. Um investimento protegido por </span>
          <span class="accent-color" data-i18n="pricing.title_accent">garantias reais.</span>
          <span class="title-underline-anim"></span>
        </h2>
        <p class="section-intro" data-i18n="pricing.intro">Inscrições e acolhimento previstos para dezembro de 2026; Ateliers de janeiro a abril de 2027. Sua decisão é respaldada por compromisso ético e total transparência.</p>

        <div class="pricing-layout-grid">
          <!-- Card de Matrícula Brasil -->
          <div class="card-matricula-brasil" data-tilt>
            <div class="pricing-card-header-banner">
              <span data-i18n="pricing.badge">Matrícula Brasil · Turma 2026</span>
              <span>50 Vagas</span>
            </div>
            <div class="pricing-card-body">
              <div class="price-display">
                <span data-i18n="pricing.price_installments">10 × R$ 300</span>
              </div>
              <div class="price-total" data-i18n="pricing.price_total">Total: R$ 3.000 · Sem juros no cartão ou à vista</div>
              <div class="price-sub" data-i18n="pricing.price_sub">Condição especial de parcelamento. Checkout oficial e confirmação das condições comerciais sujeitos à abertura oficial das vagas.</div>

              <ul class="pricing-benefits-list">
                <li><span class="check-bullet">✓</span> <span>4 meses de ateliês práticos e formação aplicada</span></li>
                <li><span class="check-bullet">✓</span> <span>Elegibilidade integral às bolsas de imersão presencial na França</span></li>
                <li><span class="check-bullet">✓</span> <span>Acesso direto a mestres, curadores e comunidade internacional</span></li>
                <li><span class="check-bullet">✓</span> <span>Certificação internacional da Residência ArtInnova</span></li>
              </ul>

              <a class="btn btn-primary btn-block" href="#inscricao" data-i18n="pricing.cta">Quero me inscrever na turma ↗</a>
            </div>
          </div>

          <!-- Card de Satisfação & Garantias com Selo Dourado -->
          <div class="card-garantia-transparente" data-tilt>
            <div class="guarantee-seal-header">
              <div class="guarantee-header-text">
                <span class="guarantee-kicker-badge">COMPROMISSO FORMAL ARTINNOVA</span>
                <h3 class="guarantee-main-title" data-i18n="pricing.guarantee_title">Satisfação Garantida ou Reembolso Integral</h3>
                <p class="guarantee-subtitle" data-i18n="pricing.guarantee_subtitle">Acreditamos tanto na qualidade dos nossos ateliês que assumimos o compromisso formal com a sua tranquilidade.</p>
              </div>
              <div class="guarantee-seal-media">
                <img src="<?php echo esc_url($theme_uri); ?>/assets/images/selo-garantia-7-dias.png" alt="Selo Oficial 100% Dinheiro de Volta - 7 Dias Garantido" class="seal-gold-img" width="124" height="124" style="width:124px;max-width:124px;height:auto;display:block;">
              </div>
            </div>

            <!-- Garantia 1: 7 Dias Incondicional -->
            <div class="guarantee-box box-incondicional">
              <div class="guarantee-box-header">
                <span class="badge-guarantee-pill pill-emerald">Garantia 01 · 7 Dias Incondicional</span>
                <span class="guarantee-status-tag">100% Reembolso</span>
              </div>
              <h4 class="guarantee-box-title" data-i18n="pricing.g1_title">Experimente a Primeira Semana sem Risco</h4>
              <p class="guarantee-box-desc" data-i18n="pricing.g1_desc">Após a confirmação da sua matrícula, você tem 7 dias corridos para participar dos primeiros encontros e conhecer nossa metodologia. Se por qualquer motivo pessoal, profissional ou de agenda decidir não continuar, basta nos enviar um e-mail: estornamos 100% do valor pago, sem burocracia nem questionamentos.</p>
            </div>

            <!-- Garantia 2: Conhecimento e Transformação -->
            <div class="guarantee-box box-transformacao">
              <div class="guarantee-box-header">
                <span class="badge-guarantee-pill pill-amber">Garantia 02 · Evolução do Conhecimento</span>
                <span class="guarantee-status-tag">Devolução Integral</span>
              </div>
              <h4 class="guarantee-box-title" data-i18n="pricing.g2_title">Compromisso com o Seu Ganho Prático</h4>
              <p class="guarantee-box-desc" data-i18n="pricing.g2_desc">Se você comparecer a pelo menos 80% das atividades propostas e, ao término dos 4 meses de formação, avaliar de boa-fé que os conteúdos e ateliês não agregaram valor para seu percurso artístico e profissional, analisaremos seu caso para a devolução integral da taxa de matrícula.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
<!-- ============================================================
         07 — Inscrição / Manifestação de Interesse
         ============================================================ -->
    <section id="inscricao" class="section light-surface">
      <div class="wrap two-cols-asym">
        <div class="form-intro-col">
          <div class="eyebrow eyebrow-red" data-i18n="form.eyebrow">07 — Próximo passo</div>
          <h2 class="section-title">
            <span data-i18n="form.title_part1">Entre. Experimente. </span>
            <span class="accent-color" data-i18n="form.title_accent">Faça parte.</span>
            <span class="title-underline-anim"></span>
          </h2>
          <p class="section-intro" data-i18n="form.intro">Deixe seu contato para receber as informações finais da turma piloto, o edital oficial e o acesso prioritário à matrícula com as garantias ativas.</p>
          <p class="form-sub-intro" data-i18n="form.sub_intro">Sua manifestação será encaminhada diretamente à coordenação da Residência Internacional ArtInnova.</p>

          <div class="form-perks-box">
            <div class="perk-row">
              <span class="perk-check">✓</span>
              <span>Acesso prévio ao regulamento e cronograma de aulas</span>
            </div>
            <div class="perk-row">
              <span class="perk-check">✓</span>
              <span>Prioridade na fila de matrículas antes da abertura pública</span>
            </div>
            <div class="perk-row">
              <span class="perk-check">✓</span>
              <span>Proteção integral com a Dupla Garantia de 7 dias e Transformação</span>
            </div>
          </div>
        </div>

        <div class="form-card" data-tilt>
          <form id="lead-form" novalidate>
            <input type="hidden" name="lang" id="form-lang-input" value="pt">

            <div class="field">
              <label for="nome" data-i18n="form.label_name">Nome completo *</label>
              <input id="nome" name="nome" type="text" required autocomplete="name" maxlength="120" placeholder="Seu nome">
            </div>

            <div class="field">
              <label for="email" data-i18n="form.label_email">E-mail de contato *</label>
              <input id="email" name="email" type="email" required autocomplete="email" maxlength="180" placeholder="seuemail@exemplo.com">
            </div>

            <div class="field">
              <label for="cidade" data-i18n="form.label_city">Cidade / Estado / País *</label>
              <input id="cidade" name="cidade" type="text" required maxlength="120" placeholder="Ex: Campina Grande, PB ou Paris, França">
            </div>

            <div class="field">
              <label for="perfil" data-i18n="form.label_profile">Perfil principal</label>
              <select id="perfil" name="perfil">
                <option value="" data-i18n="form.profile_placeholder">Selecione seu perfil</option>
                <option>Criador(a) / Artista / Designer</option>
                <option>Empreendedor(a) / Profissional de Inovação</option>
                <option>Educador(a) / Pesquisador(a)</option>
                <option>Gestor(a) Cultural ou Tecnológico</option>
                <option>Outro perfil interdisciplinar</option>
              </select>
            </div>

            <div class="field">
              <label for="origem" data-i18n="form.label_referral">Quem indicou? (Cidade criativa, Ponto Focal ou parceiro)</label>
              <input id="origem" name="origem" type="text" maxlength="150" placeholder="Opcional">
            </div>

            <div class="field field-checkbox">
              <input id="consent" name="consent" type="checkbox" required>
              <label for="consent" data-i18n="form.label_consent">Autorizo o uso dos meus dados para contato sobre a Residência ArtInnova e concordo com os termos de garantia. *</label>
            </div>

            <button class="btn btn-primary" id="btn-submit-lead" type="submit" style="width: 100%;" data-i18n="form.btn_submit">
              Manifestar interesse ↗
            </button>

            <div id="form-status" class="form-status" role="status" aria-live="polite"></div>
          </form>
        </div>
      </div>
    </section>

    <!-- ============================================================
         08 — Dúvidas Frequentes (FAQ)
         ============================================================ -->
    <section id="duvidas" class="section light-bg" style="padding-top: 0;">
      <div class="wrap">
        <div class="eyebrow eyebrow-green" data-i18n="faq.eyebrow">Dúvidas frequentes</div>
        <h2 class="section-title">
          <span data-i18n="faq.title_part1">Antes de construir sua </span>
          <span class="accent-color" data-i18n="faq.title_accent">ponte.</span>
          <span class="title-underline-anim"></span>
        </h2>

        <div class="faq-list">
          <div class="faq-item">
            <button class="faq-question" aria-expanded="false">
              <span data-i18n="faq.items.0.q">Preciso ser artista profissional para participar?</span>
              <span class="faq-icon" aria-hidden="true">+</span>
            </button>
            <div class="faq-answer">
              <p data-i18n="faq.items.0.a">Não. A residência é voltada para perfis híbridos: criadores, designers, desenvolvedores, pesquisadores e gestores. O requisito essencial é ter maturidade profissional ou acadêmica e interesse concreto em criar e colaborar.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" aria-expanded="false">
              <span data-i18n="faq.items.1.q">Como funciona o Super Prêmio de Viagem Internacional?</span>
              <span class="faq-icon" aria-hidden="true">+</span>
            </button>
            <div class="faq-answer">
              <p data-i18n="faq.items.1.a">Quatro residentes selecionados (dois do Brasil e dois da França) terão passagens aéreas e hospedagem custeadas para uma imersão de 10 dias no Centre des Arts em Enghien-les-Bains (região de Paris) ou Campina Grande, com ateliês, mentorias e mostra de obras.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" aria-expanded="false">
              <span data-i18n="faq.items.2.q">Como funciona a Dupla Garantia de 7 dias e de Transformação?</span>
              <span class="faq-icon" aria-hidden="true">+</span>
            </button>
            <div class="faq-answer">
              <p data-i18n="faq.items.2.a">Você tem 7 dias após o início para desistir e receber 100% do seu dinheiro de volta sem qualquer questionamento. Além disso, se você participar ativamente de toda a residência e comprovar que a formação não agregou benefícios para você, você pode solicitar o reembolso integral ao final do programa.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" aria-expanded="false">
              <span data-i18n="faq.items.3.q">Qual é o formato das atividades (online ou presencial)?</span>
              <span class="faq-icon" aria-hidden="true">+</span>
            </button>
            <div class="faq-answer">
              <p data-i18n="faq.items.3.a">A formação é híbrida: encontros síncronos online semanais, acompanhamento contínuo em ateliê virtual e momentos de imersão prática (como o HackArtThon e a viagem de intercâmbio dos premiados).</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" aria-expanded="false">
              <span data-i18n="faq.items.4.q">Como funcionam as bolsas / matrículas sociais?</span>
              <span class="faq-icon" aria-hidden="true">+</span>
            </button>
            <div class="faq-answer">
              <p data-i18n="faq.items.4.a">O projeto prevê 10 vagas sociais integrais para o Brasil e 10 vagas sociais para a França. Os critérios socioeconômicos e de representatividade territorial serão detalhados na abertura do edital de seleção social.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

<?php get_footer(); ?>
