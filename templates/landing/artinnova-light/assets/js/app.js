/**
 * ArtInnova Light — International Residency Main Application
 * Standalone Multilingual Engine (PT, EN, ES, FR)
 * Generative Multicolor Pixel Engine (Royal Blue, Crimson Red, Warm Amber, Emerald Green, Sky Blue)
 * GSAP Motion + Smooth Interactivity
 */

(function () {
  'use strict';

  // State
  let currentLang = 'pt';
  const availableLangs = ['pt', 'en', 'es', 'fr'];
  let langDataCache = {};

  // DOM Elements
  const header = document.querySelector('.site-header');
  const scrollProgressBar = document.querySelector('.scroll-progress-bar');
  const mobileToggle = document.querySelector('.mobile-toggle');
  const headerNavBar = document.querySelector('.header-nav-bar');
  const leadForm = document.getElementById('lead-form');
  const formStatus = document.getElementById('form-status');
  const heroCanvas = document.getElementById('hero-pixel-canvas');
  const heroSection = document.getElementById('hero');

  // Currency & Pricing Adaptation
  const currencyConfig = {
    pt: {
      installments: '10 × R$ 300',
      total: 'Total: R$ 3.000 · cartão de crédito',
      badge: 'Matrícula Brasil',
      heroPrice: 'Brasil: 10 × R$ 300 no cartão de crédito.'
    },
    en: {
      installments: '10 × $ 60 USD',
      total: 'Total: $ 600 USD · credit card / stripe',
      badge: 'International Admission',
      heroPrice: 'International: 10 × $ 60 USD (approx. R$ 300/mo).'
    },
    es: {
      installments: '10 × 55 € EUR',
      total: 'Total: 550 € EUR · tarjeta de crédito',
      badge: 'Matrícula Internacional',
      heroPrice: 'Internacional: 10 × 55 € EUR (aprox. R$ 300/mes).'
    },
    fr: {
      installments: '10 × 55 € EUR',
      total: 'Total: 550 € EUR · carte bancaire',
      badge: 'Inscription Internationale',
      heroPrice: 'International : 10 × 55 € EUR (env. R$ 300/mois).'
    }
  };

  // Initialize
  document.addEventListener('DOMContentLoaded', init);

  function init() {
    initLanguage();
    initScrollInteractions();
    initFaqAccordion();
    initLeadForm();
    initPrivacyCompliance();
    initMobileNav();
    initPixelParticles();
    initCardTilts();
    initGsapAnimations();
  }

  /* ============================================================
     1. Language & i18n Engine
     ============================================================ */
  function initLanguage() {
    const urlParams = new URLSearchParams(window.location.search);
    const paramLang = urlParams.get('lang');
    const storedLang = localStorage.getItem('artinnova_light_lang');
    const browserLang = (navigator.language || navigator.userLanguage || '').slice(0, 2).toLowerCase();

    if (paramLang && availableLangs.includes(paramLang)) {
      currentLang = paramLang;
    } else if (storedLang && availableLangs.includes(storedLang)) {
      currentLang = storedLang;
    } else if (availableLangs.includes(browserLang)) {
      currentLang = browserLang;
    } else {
      currentLang = 'pt';
    }

    document.querySelectorAll('.lang-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const targetLang = btn.getAttribute('data-lang');
        if (targetLang && targetLang !== currentLang) {
          switchLanguage(targetLang);
        }
      });
    });

    switchLanguage(currentLang, false);
  }

  async function switchLanguage(newLang, animate = true) {
    if (!availableLangs.includes(newLang)) return;
    currentLang = newLang;
    localStorage.setItem('artinnova_light_lang', newLang);

    // Update buttons
    document.querySelectorAll('.lang-btn').forEach(btn => {
      if (btn.getAttribute('data-lang') === newLang) {
        btn.classList.add('active');
        btn.setAttribute('aria-pressed', 'true');
      } else {
        btn.classList.remove('active');
        btn.setAttribute('aria-pressed', 'false');
      }
    });

    // Form hidden input
    const langInput = document.getElementById('form-lang-input');
    if (langInput) langInput.value = newLang;

    // Fetch data or fallback bundle
    let data = langDataCache[newLang];
    if (!data && window.ARTINNOVA_TRANSLATIONS && window.ARTINNOVA_TRANSLATIONS[newLang]) {
      data = window.ARTINNOVA_TRANSLATIONS[newLang];
      langDataCache[newLang] = data;
    }
    if (!data) {
      try {
        const localesBase = (window.artinnova_ajax && window.artinnova_ajax.locales_path) ? window.artinnova_ajax.locales_path : 'locales/';
        const response = await fetch(`${localesBase}${newLang}.json`);
        if (response.ok) {
          data = await response.json();
          langDataCache[newLang] = data;
        }
      } catch (err) {
        console.warn(`Could not load ${newLang}.json via fetch`, err);
      }
    }

    if (!data) {
      console.warn(`Translation data unavailable for ${newLang}`);
      return;
    }

    if (animate && window.gsap) {
      gsap.to('[data-i18n], [data-i18n-html]', {
        opacity: 0.25,
        duration: 0.18,
        ease: 'power1.out',
        onComplete: () => {
          renderTranslations(data);
          applyCurrencyAdaptation(newLang);
          gsap.to('[data-i18n], [data-i18n-html]', {
            opacity: 1,
            duration: 0.28,
            ease: 'power1.in'
          });
          if (window.ScrollTrigger) ScrollTrigger.refresh();
        }
      });
    } else {
      renderTranslations(data);
      applyCurrencyAdaptation(newLang);
      if (window.ScrollTrigger) ScrollTrigger.refresh();
    }
  }

  function renderTranslations(data) {
    window.i18nData = data;
    document.documentElement.lang = data.lang || currentLang;

    if (data.meta && data.meta.title) {
      document.title = data.meta.title;
    }

    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      const val = getNestedValue(data, key);
      if (val !== undefined && val !== null) {
        el.textContent = val;
      }
    });

    document.querySelectorAll('[data-i18n-html]').forEach(el => {
      const key = el.getAttribute('data-i18n-html');
      const val = getNestedValue(data, key);
      if (val !== undefined && val !== null) {
        el.innerHTML = val;
      }
    });

    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
      const key = el.getAttribute('data-i18n-placeholder');
      const val = getNestedValue(data, key);
      if (val !== undefined && val !== null) {
        el.placeholder = val;
      }
    });

    // Profile options in select
    if (data.form && Array.isArray(data.form.profile_opts)) {
      const select = document.getElementById('perfil');
      if (select) {
        const curVal = select.value;
        select.innerHTML = `<option value="">${data.form.profile_placeholder || 'Selecione seu perfil'}</option>` +
          data.form.profile_opts.map(opt => `<option value="${opt}">${opt}</option>`).join('');
        if (curVal) select.value = curVal;
      }
    }
  }

  function applyCurrencyAdaptation(lang) {
    const cfg = currencyConfig[lang] || currencyConfig.pt;
    const installmentsEl = document.querySelector('[data-i18n="pricing.price_installments"]');
    const totalEl = document.querySelector('[data-i18n="pricing.price_total"]');
    const badgeEl = document.querySelector('[data-i18n="pricing.badge"]');
    const heroPriceEl = document.querySelector('[data-i18n="hero.meta_price"]');

    if (installmentsEl) installmentsEl.textContent = cfg.installments;
    if (totalEl) totalEl.textContent = cfg.total;
    if (badgeEl) badgeEl.textContent = cfg.badge;
    if (heroPriceEl) heroPriceEl.textContent = cfg.heroPrice;
  }

  function getNestedValue(obj, path) {
    return path.split('.').reduce((acc, part) => (acc && acc[part] !== undefined ? acc[part] : undefined), obj);
  }

  /* ============================================================
     2. Generative Multicolor Pixel Engine (White Theme Contrast)
     ============================================================ */
  function initPixelParticles() {
    if (!heroCanvas || !heroSection) return;

    const ctx = heroCanvas.getContext('2d');
    if (!ctx) return;

    let width = 0;
    let height = 0;
    let dpr = 1;
    let particles = [];
    let animationFrameId = null;
    let isVisible = true;

    const mouse = {
      x: -9999,
      y: -9999,
      active: false
    };

    // The 5 vibrant colors requested: Azul, Vermelho, Amarelo, Verde, Azul Claro
    const pixelColors = [
      { r: 29,  g: 78,  b: 216 }, // Royal Blue
      { r: 239, g: 68,  b: 68  }, // Crimson Red
      { r: 245, g: 158, b: 11  }, // Warm Amber Yellow
      { r: 16,  g: 185, b: 129 }, // Emerald Green
      { r: 14,  g: 165, b: 233 }  // Sky Cyan / Light Blue
    ];

    const pixelSizes = [3, 4, 6, 8, 11, 15];

    function resize() {
      const rect = heroSection.getBoundingClientRect();
      width = rect.width;
      height = rect.height;
      dpr = Math.min(window.devicePixelRatio || 1, 2);

      heroCanvas.width = width * dpr;
      heroCanvas.height = height * dpr;
      heroCanvas.style.width = width + 'px';
      heroCanvas.style.height = height + 'px';

      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      initParticles();
    }

    function initParticles() {
      particles = [];
      const count = width < 768 ? 45 : (width < 1200 ? 75 : 110);

      for (let i = 0; i < count; i++) {
        const colorObj = pixelColors[Math.floor(Math.random() * pixelColors.length)];
        const size = pixelSizes[Math.floor(Math.random() * pixelSizes.length)];
        const isCluster = Math.random() < 0.18;
        const isWireframe = Math.random() < 0.20;

        particles.push({
          x: Math.random() * width,
          y: Math.random() * height,
          vx: (Math.random() - 0.5) * 0.38,
          vy: (Math.random() - 0.5) * 0.32,
          size: size,
          color: colorObj,
          baseAlpha: 0.35 + Math.random() * 0.45,
          alpha: 0.45,
          pulseSpeed: 0.012 + Math.random() * 0.026,
          pulseAngle: Math.random() * Math.PI * 2,
          isCluster: isCluster,
          isWireframe: isWireframe,
          pushX: 0,
          pushY: 0
        });
      }
    }

    // Mouse Interaction
    heroSection.addEventListener('mousemove', (e) => {
      const rect = heroSection.getBoundingClientRect();
      mouse.x = e.clientX - rect.left;
      mouse.y = e.clientY - rect.top;
      mouse.active = true;
    }, { passive: true });

    heroSection.addEventListener('mouseleave', () => {
      mouse.active = false;
      mouse.x = -9999;
      mouse.y = -9999;
    });

    // Render loop
    function render() {
      if (!isVisible) {
        animationFrameId = requestAnimationFrame(render);
        return;
      }

      ctx.clearRect(0, 0, width, height);

      // Subtle network connection lines between close pixels
      const maxConnectDist = 65;
      const pLen = particles.length;
      for (let i = 0; i < pLen; i++) {
        for (let j = i + 1; j < pLen; j++) {
          const dx = particles[i].x - particles[j].x;
          const dy = particles[i].y - particles[j].y;
          const dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < maxConnectDist) {
            const lineAlpha = (1 - dist / maxConnectDist) * 0.14 * Math.min(particles[i].alpha, particles[j].alpha);
            ctx.beginPath();
            ctx.moveTo(Math.floor(particles[i].x), Math.floor(particles[i].y));
            ctx.lineTo(Math.floor(particles[j].x), Math.floor(particles[j].y));
            ctx.strokeStyle = `rgba(29, 78, 216, ${lineAlpha.toFixed(3)})`;
            ctx.lineWidth = 1;
            ctx.stroke();
          }
        }
      }

      // Draw Pixels
      for (let i = 0; i < pLen; i++) {
        const p = particles[i];

        // Pulse alpha
        p.pulseAngle += p.pulseSpeed;
        p.alpha = p.baseAlpha + Math.sin(p.pulseAngle) * 0.18;
        if (p.alpha < 0.15) p.alpha = 0.15;
        if (p.alpha > 0.95) p.alpha = 0.95;

        // Mouse Repulsion & Excitation
        if (mouse.active) {
          const mdx = p.x - mouse.x;
          const mdy = p.y - mouse.y;
          const mDist = Math.sqrt(mdx * mdx + mdy * mdy);
          const mouseRadius = 140;

          if (mDist < mouseRadius && mDist > 0) {
            const force = (1 - mDist / mouseRadius) * 2.5;
            p.pushX += (mdx / mDist) * force;
            p.pushY += (mdy / mDist) * force;
            p.alpha = Math.min(1, p.alpha + 0.35);
          }
        }

        // Apply friction
        p.pushX *= 0.88;
        p.pushY *= 0.88;

        // Position drift
        p.x += p.vx + p.pushX;
        p.y += p.vy + p.pushY;

        // Wrap around boundaries
        if (p.x < -20) p.x = width + 20;
        else if (p.x > width + 20) p.x = -20;
        if (p.y < -20) p.y = height + 20;
        else if (p.y > height + 20) p.y = -20;

        // Snap to sharp pixel grid
        const drawX = Math.round(p.x);
        const drawY = Math.round(p.y);
        const { r, g, b } = p.color;

        ctx.fillStyle = `rgba(${r}, ${g}, ${b}, ${p.alpha.toFixed(3)})`;
        ctx.strokeStyle = `rgba(${r}, ${g}, ${b}, ${(p.alpha * 1.15).toFixed(3)})`;

        if (p.isCluster) {
          // 2x2 Mini Pixel Cluster
          const s = Math.max(3, Math.round(p.size * 0.45));
          ctx.fillRect(drawX, drawY, s, s);
          ctx.fillRect(drawX + s + 1, drawY, s, s);
          ctx.fillRect(drawX, drawY + s + 1, s, s);
          ctx.fillRect(drawX + s + 1, drawY + s + 1, s, s);
        } else if (p.isWireframe) {
          ctx.lineWidth = 1.2;
          ctx.strokeRect(drawX, drawY, p.size, p.size);
        } else {
          ctx.fillRect(drawX, drawY, p.size, p.size);
        }
      }

      animationFrameId = requestAnimationFrame(render);
    }

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          isVisible = entry.isIntersecting;
        });
      }, { threshold: 0.05 });
      observer.observe(heroSection);
    }

    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(resize, 150);
    });

    resize();
    render();
  }

  /* ============================================================
     3. 3D Tilt Micro-Interactions on Cards
     ============================================================ */
  function initCardTilts() {
    if (!window.matchMedia('(pointer: fine)').matches) return;

    const tiltElements = document.querySelectorAll('[data-tilt]');
    tiltElements.forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;

        const rotateX = -(y / rect.height) * 7;
        const rotateY = (x / rect.width) * 7;

        if (window.gsap) {
          gsap.to(card, {
            transform: `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-3px)`,
            duration: 0.3,
            ease: 'power1.out'
          });
        }
      });

      card.addEventListener('mouseleave', () => {
        if (window.gsap) {
          gsap.to(card, {
            transform: 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)',
            duration: 0.5,
            ease: 'power2.out'
          });
        }
      });
    });
  }

  /* ============================================================
     4. Scroll Interactions & Progress Bar
     ============================================================ */
  function initScrollInteractions() {
    window.addEventListener('scroll', () => {
      const scrollTop = window.scrollY;
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;

      if (scrollProgressBar) {
        scrollProgressBar.style.width = `${progress}%`;
      }

      if (header) {
        if (scrollTop > 40) {
          header.classList.add('header-scrolled');
        } else {
          header.classList.remove('header-scrolled');
        }
      }
    }, { passive: true });
  }

  /* ============================================================
     5. FAQ Accordion
     ============================================================ */
  function initFaqAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
      const btn = item.querySelector('.faq-question');
      if (!btn) return;

      btn.addEventListener('click', () => {
        const isOpen = item.classList.contains('active');
        // Close others
        faqItems.forEach(other => {
          other.classList.remove('active');
          const otherBtn = other.querySelector('.faq-question');
          if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
        });

        if (!isOpen) {
          item.classList.add('active');
          btn.setAttribute('aria-expanded', 'true');
        }
      });
    });
  }

  /* ============================================================
     6. Mobile Nav
     ============================================================ */
  function initMobileNav() {
    if (!mobileToggle || !headerNavBar) return;

    mobileToggle.addEventListener('click', () => {
      const expanded = mobileToggle.getAttribute('aria-expanded') === 'true';
      mobileToggle.setAttribute('aria-expanded', !expanded);
      headerNavBar.classList.toggle('mobile-open');
    });

    document.querySelectorAll('.nav-links a').forEach(link => {
      link.addEventListener('click', () => {
        headerNavBar.classList.remove('mobile-open');
        mobileToggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* ============================================================
     7. Lead Form Handler (AJAX + Fallback)
     ============================================================ */
  function initLeadForm() {
    if (!leadForm) return;

    leadForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const name = document.getElementById('nome').value.trim();
      const email = document.getElementById('email').value.trim();
      const city = document.getElementById('cidade').value.trim();
      const profile = document.getElementById('perfil').value;
      const referral = document.getElementById('origem').value.trim();
      const consent = document.getElementById('consent').checked;
      const lang = document.getElementById('form-lang-input') ? document.getElementById('form-lang-input').value : currentLang;

      const validationMsgs = {
        pt: 'Por favor, preencha todos os campos obrigatórios (*) e autorize o contato.',
        en: 'Please fill in all required fields (*) and authorize contact.',
        es: 'Por favor, complete todos los campos obligatorios (*) y autorice el contacto.',
        fr: 'Veuillez remplir tous les champs obligatoires (*) et autoriser le contact.'
      };

      if (!name || !email || !city || !consent) {
        showStatus(validationMsgs[lang] || validationMsgs.pt, 'error');
        return;
      }

      const submitBtn = document.getElementById('btn-submit-lead');
      const sendingMsgs = {
        pt: 'Enviando manifestação...',
        en: 'Sending submission...',
        es: 'Enviando manifestación...',
        fr: 'Envoi en cours...'
      };

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = sendingMsgs[lang] || sendingMsgs.pt;
      }

      showStatus(sendingMsgs[lang] || sendingMsgs.pt, '');

      const ajaxUrl = (window.artinnova_ajax && window.artinnova_ajax.ajax_url) ? window.artinnova_ajax.ajax_url : '/wp-admin/admin-ajax.php';
      const formData = new FormData();
      formData.append('action', 'artinnova_submit_lead');
      if (window.artinnova_ajax && window.artinnova_ajax.nonce) {
        formData.append('nonce', window.artinnova_ajax.nonce);
      }
      formData.append('name', name);
      formData.append('email', email);
      formData.append('city', city);
      formData.append('phone', city);
      formData.append('profile', profile);
      formData.append('referral', referral);
      formData.append('country', referral);
      formData.append('lang', lang);
      formData.append('consent', consent ? '1' : '0');
      formData.append('consent_version', 'v2.0-LGPD-GDPR-2026');

      try {
        const res = await fetch(ajaxUrl, {
          method: 'POST',
          body: formData
        });
        const json = await res.json();

        if (json && json.success) {
          const successMsg = (json.data && json.data.message) ||
            (window.i18nData && window.i18nData.form && window.i18nData.form.status_success) ||
            'Manifestação enviada com sucesso! Enviamos um e-mail de confirmação para você.';
          showStatus(successMsg, 'success');
          leadForm.reset();
        } else {
          const errMsg = (json && json.data && json.data.message) ||
            'Ocorreu um erro ao enviar sua manifestação. Por favor, tente novamente ou entre em contato diretamente pelo e-mail contact@artinnovaresidency.com.';
          showStatus(errMsg, 'error');
        }
      } catch (err) {
        console.error('Lead submission network/parse error:', err);
        showStatus('Não foi possível conectar ao servidor. Por favor, tente novamente ou escreva para contact@artinnovaresidency.com.', 'error');
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = (window.i18nData && window.i18nData.form && window.i18nData.form.btn_submit) || 'Manifestar interesse ↗';
        }
      }
    });
  }

  function showStatus(msg, type) {
    if (!formStatus) return;
    formStatus.textContent = msg;
    formStatus.className = 'form-status';
    if (type) formStatus.classList.add(type);
  }

  /* ============================================================
     7b. LGPD (Brasil) & GDPR / CNIL (EU) Cookie & Privacy Engine
     ============================================================ */
  function initPrivacyCompliance() {
    const STORAGE_KEY = 'artinnova_cookie_consent_v2';
    const banner = document.getElementById('cookie-consent-banner');
    const modal = document.getElementById('privacy-modal');
    const prefFunctional = document.getElementById('pref-cookie-functional');
    const prefAnalytics = document.getElementById('pref-cookie-analytics');

    const getStoredConsent = () => {
      try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
      } catch (e) {
        return null;
      }
    };

    const saveConsent = (functional, analytics, source) => {
      const record = {
        essential: true,
        functional: !!functional,
        analytics: !!analytics,
        version: 'v2.0-LGPD-GDPR-2026',
        source: source || 'banner',
        timestamp: new Date().toISOString()
      };
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(record));
      } catch (e) {
        // Ignore storage errors in restricted browsing modes
      }
      window.ArtInnovaPrivacyConsent = record;
      document.dispatchEvent(new CustomEvent('artinnova:privacy-consent-updated', { detail: record }));
      if (banner) banner.setAttribute('hidden', 'hidden');
    };

    const syncToggles = () => {
      const current = getStoredConsent();
      if (prefFunctional) {
        prefFunctional.checked = current ? !!current.functional : true;
      }
      if (prefAnalytics) {
        // GDPR Privacy by Default: analytics stays false until explicit opt-in
        prefAnalytics.checked = current ? !!current.analytics : false;
      }
    };

    const openPrivacyModal = (scrollToCookies = false) => {
      if (!modal) return;
      syncToggles();
      modal.removeAttribute('hidden');
      if (scrollToCookies) {
        const cookieBox = document.getElementById('privacy-cookies-section');
        if (cookieBox) {
          setTimeout(() => cookieBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 80);
        }
      }
    };

    const closePrivacyModal = () => {
      if (modal) modal.setAttribute('hidden', 'hidden');
    };

    const existing = getStoredConsent();
    if (!existing && banner) {
      banner.removeAttribute('hidden');
      window.ArtInnovaPrivacyConsent = {
        essential: true,
        functional: true,
        analytics: false,
        version: 'v2.0-LGPD-GDPR-2026',
        source: 'default_privacy_by_default'
      };
    } else if (existing) {
      window.ArtInnovaPrivacyConsent = existing;
    }

    document.getElementById('btn-cookie-accept')?.addEventListener('click', () => {
      saveConsent(true, true, 'banner_accept_all');
    });

    document.getElementById('btn-cookie-reject')?.addEventListener('click', () => {
      saveConsent(false, false, 'banner_essential_only');
    });

    document.getElementById('btn-cookie-customize')?.addEventListener('click', () => {
      openPrivacyModal(true);
    });

    document.getElementById('btn-save-cookie-prefs')?.addEventListener('click', () => {
      saveConsent(
        prefFunctional ? prefFunctional.checked : true,
        prefAnalytics ? prefAnalytics.checked : false,
        'privacy_modal_custom'
      );
      closePrivacyModal();
    });

    document.querySelectorAll('[data-open-privacy-modal]').forEach(trigger => {
      trigger.addEventListener('click', (e) => {
        e.preventDefault();
        openPrivacyModal(trigger.getAttribute('data-open-privacy-modal') === 'cookies');
      });
    });

    document.querySelectorAll('[data-close-privacy-modal]').forEach(closer => {
      closer.addEventListener('click', closePrivacyModal);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal && !modal.hasAttribute('hidden')) {
        closePrivacyModal();
      }
    });
  }

  /* ============================================================
     8. GSAP Motion & Counter Animations
     ============================================================ */
  function initGsapAnimations() {
    if (!window.gsap) return;

    if (window.ScrollTrigger) {
      gsap.registerPlugin(ScrollTrigger);

      // Animate Metrics Counters
      document.querySelectorAll('[data-counter]').forEach(el => {
        const targetVal = parseInt(el.getAttribute('data-counter'), 10) || 0;
        const obj = { val: 0 };

        ScrollTrigger.create({
          trigger: el,
          start: 'top 88%',
          once: true,
          onEnter: () => {
            gsap.to(obj, {
              val: targetVal,
              duration: 1.8,
              ease: 'power2.out',
              onUpdate: () => {
                el.textContent = Math.round(obj.val);
              }
            });
          }
        });
      });

      // Subtle Stagger on Section Elements
      document.querySelectorAll('.section').forEach(sec => {
        const title = sec.querySelector('.section-title');
        const intro = sec.querySelector('.section-intro');
        if (title) {
          gsap.from(title, {
            scrollTrigger: {
              trigger: title,
              start: 'top 85%',
              once: true
            },
            opacity: 0,
            y: 28,
            duration: 0.7,
            ease: 'power2.out'
          });
        }
      });
    }
  }

})();
