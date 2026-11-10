/**
 * StackEAD Core - Single Page Application Controller
 * Standalone EAD Dashboard, Immersive Classroom Player, Anti-Anxiety Quiz & Master Desks.
 */

(function () {
	'use strict';

	const API = {
		root: window.StackEAD_Config ? window.StackEAD_Config.root + 'stackead/v1/' : '/wp-json/stackead/v1/',
		nonce: window.StackEAD_Config ? window.StackEAD_Config.nonce : '',
		user: window.StackEAD_Config ? window.StackEAD_Config.user : null
	};

	let state = {
		currentView: 'dashboard',
		cohorts: [],
		activeCohortId: null,
		activeCurriculum: null,
		activeLesson: null,
		timerInterval: null,
		watchTimer: null,
		secondsWatched: 0
	};

	// DOM Elements
	let contentRoot, cohortSelect, viewTitleEl;

	// Global Safe HTML Escaping Helper
	function escapeHtml(str) {
		if (str === null || str === undefined) return '';
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}
	/* ==========================================================================
	   Rich WYSIWYG Editor Component
	   ========================================================================== */
	function createWysiwygEditor(containerEl, initialHtml = '', options = {}) {
		const editorId = 'wysiwyg-' + Math.random().toString(36).substr(2, 9);
		const minHeight = options.minHeight || '160px';
		const placeholder = options.placeholder || 'Digite e formate seu texto aqui...';

		containerEl.innerHTML = `
			<div class="wysiwyg-wrapper">
				<div class="wysiwyg-toolbar">
					<button type="button" class="wysiwyg-btn" data-cmd="bold" title="Negrito (Ctrl+B)">
						<i data-lucide="bold"></i>
					</button>
					<button type="button" class="wysiwyg-btn" data-cmd="italic" title="Itálico (Ctrl+I)">
						<i data-lucide="italic"></i>
					</button>
					<button type="button" class="wysiwyg-btn" data-cmd="underline" title="Sublinhado (Ctrl+U)">
						<i data-lucide="underline"></i>
					</button>
					<button type="button" class="wysiwyg-btn" data-cmd="strikeThrough" title="Tachado">
						<i data-lucide="strikethrough"></i>
					</button>

					<div class="wysiwyg-divider"></div>

					<select class="wysiwyg-format-select" title="Estilo do Parágrafo">
						<option value="p">¶ Normal</option>
						<option value="h2">H2 Título 2</option>
						<option value="h3">H3 Título 3</option>
						<option value="blockquote">❝ Citação</option>
						<option value="pre">&lt;/&gt; Código</option>
					</select>

					<div class="wysiwyg-divider"></div>

					<button type="button" class="wysiwyg-btn" data-cmd="justifyLeft" title="Alinhar à Esquerda">
						<i data-lucide="align-left"></i>
					</button>
					<button type="button" class="wysiwyg-btn" data-cmd="justifyCenter" title="Centralizar">
						<i data-lucide="align-center"></i>
					</button>
					<button type="button" class="wysiwyg-btn" data-cmd="justifyRight" title="Alinhar à Direita">
						<i data-lucide="align-right"></i>
					</button>
					<button type="button" class="wysiwyg-btn" data-cmd="justifyFull" title="Justificar">
						<i data-lucide="align-justify"></i>
					</button>

					<div class="wysiwyg-divider"></div>

					<button type="button" class="wysiwyg-btn" data-cmd="insertUnorderedList" title="Lista com Marcadores">
						<i data-lucide="list"></i>
					</button>
					<button type="button" class="wysiwyg-btn" data-cmd="insertOrderedList" title="Lista Numerada">
						<i data-lucide="list-ordered"></i>
					</button>

					<div class="wysiwyg-divider"></div>

					<button type="button" class="wysiwyg-btn wysiwyg-link-btn" title="Inserir Link">
						<i data-lucide="link"></i>
					</button>
					<button type="button" class="wysiwyg-btn wysiwyg-img-btn" title="Inserir Imagem (URL)">
						<i data-lucide="image"></i>
					</button>
					<button type="button" class="wysiwyg-btn" data-cmd="removeFormat" title="Limpar Formatação">
						<i data-lucide="eraser"></i>
					</button>
				</div>
				<div id="${editorId}" class="wysiwyg-content" contenteditable="true" style="min-height: ${minHeight};" placeholder="${escapeHtml(placeholder)}">${initialHtml || ''}</div>
				<div class="wysiwyg-statusbar">
					<span class="wysiwyg-word-count">0 palavra(s) &bull; 0 caractere(s)</span>
					<span style="display: flex; align-items: center; gap: 4px;"><i data-lucide="pen-tool" style="width: 12px; height: 12px;"></i> Editor Formatado</span>
				</div>
			</div>
		`;

		if (window.lucide) window.lucide.createIcons();

		const contentArea = containerEl.querySelector(`#${editorId}`);
		const wordCountEl = containerEl.querySelector('.wysiwyg-word-count');

		function updateCounts() {
			const text = contentArea.innerText.trim();
			const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
			const chars = text.length;
			if (wordCountEl) wordCountEl.textContent = `${words} palavra(s) • ${chars} caractere(s)`;
		}
		updateCounts();
		contentArea.addEventListener('input', updateCounts);

		containerEl.querySelectorAll('.wysiwyg-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				const cmd = btn.getAttribute('data-cmd');
				document.execCommand(cmd, false, null);
				contentArea.focus();
				updateCounts();
			});
		});

		const formatSelect = containerEl.querySelector('.wysiwyg-format-select');
		if (formatSelect) {
			formatSelect.addEventListener('change', () => {
				const val = formatSelect.value;
				document.execCommand('formatBlock', false, `<${val}>`);
				contentArea.focus();
			});
		}

		const linkBtn = containerEl.querySelector('.wysiwyg-link-btn');
		if (linkBtn) {
			linkBtn.addEventListener('click', (e) => {
				e.preventDefault();
				const url = prompt('Digite a URL do link:');
				if (url) {
					document.execCommand('createLink', false, url);
					contentArea.focus();
				}
			});
		}

		const imgBtn = containerEl.querySelector('.wysiwyg-img-btn');
		if (imgBtn) {
			imgBtn.addEventListener('click', (e) => {
				e.preventDefault();
				const url = prompt('Digite a URL da imagem:');
				if (url) {
					document.execCommand('insertImage', false, url);
					contentArea.focus();
				}
			});
		}

		return {
			getHtml: () => contentArea.innerHTML.trim(),
			getText: () => contentArea.innerText.trim(),
			setHtml: (html) => {
				contentArea.innerHTML = html;
				updateCounts();
			}
		};
	}


	// Early export to prevent inline onclick race conditions
	window.StackEAD_App = window.StackEAD_App || {};
	window.StackEAD = window.StackEAD_App;

	function init() {
		contentRoot = document.getElementById('app-content-root');
		cohortSelect = document.getElementById('select-active-cohort');
		viewTitleEl = document.getElementById('view-title');

		setupGlobalEvents();
		loadUserCohorts();
	}

	/* ==========================================================================
	   API Request Helper
	   ========================================================================== */
	async function fetchAPI(endpoint, method = 'GET', body = null) {
		const options = {
			method: method,
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': API.nonce
			}
		};

		if (body) {
			options.body = JSON.stringify(body);
		}

		try {
			const res = await fetch(API.root + endpoint, options);
			if (!res.ok) {
				const errData = await res.json().catch(() => ({}));
				throw new Error(errData.error || errData.message || 'Erro na requisição');
			}
			return await res.json();
		} catch (err) {
			showToast(err.message, 'danger');
			throw err;
		}
	}

	/* ==========================================================================
	   Global UI Event Listeners
	   ========================================================================== */
	function setupGlobalEvents() {
		// Navigation buttons
		document.querySelectorAll('.sidebar-nav .nav-link').forEach(btn => {
			btn.addEventListener('click', () => {
				document.querySelectorAll('.sidebar-nav .nav-link').forEach(b => b.classList.remove('active'));
				btn.classList.add('active');
				const view = btn.getAttribute('data-view');
				navigate(view);

				// Close mobile sidebar if open
				const sidebar = document.getElementById('app-sidebar');
				if (sidebar) sidebar.classList.remove('open');
				const backdrop = document.getElementById('sidebar-backdrop');
				if (backdrop) backdrop.classList.remove('active');
			});
		});

		// Accordion group toggles (Menu Lateral Sanfonado)
		document.querySelectorAll('.nav-accordion-trigger').forEach(trigger => {
			trigger.addEventListener('click', (e) => {
				e.preventDefault();
				const group = trigger.closest('.nav-accordion-group');
				if (group) {
					group.classList.toggle('is-open');
				}
			});
		});

		// Language & Currency Switcher
		const langSelect = document.getElementById('select-app-language');
		if (langSelect && window.StackEAD_I18n) {
			langSelect.value = window.StackEAD_I18n.getLanguage();
			langSelect.addEventListener('change', (e) => {
				window.StackEAD_I18n.setLanguage(e.target.value);
				window.StackEAD_I18n.applyTranslations(document.body);
				showToast(`Idioma alterado para ${e.target.selectedOptions[0]?.text || e.target.value}`, 'info');
				navigate(state.currentView);
			});
		}

		const currSelect = document.getElementById('select-app-currency');
		if (currSelect && window.StackEAD_I18n) {
			currSelect.value = window.StackEAD_I18n.getCurrency();
			currSelect.addEventListener('change', (e) => {
				window.StackEAD_I18n.setCurrency(e.target.value);
				showToast(`Moeda padrão alterada para ${e.target.value}`, 'info');
				navigate(state.currentView);
			});
		}

		// Virtual Coin Pill -> Gamification
		const coinPill = document.getElementById('topbar-coin-pill');
		if (coinPill) {
			coinPill.addEventListener('click', () => {
				navigate('gamification');
			});
		}

		// Cohort Switcher
		if (cohortSelect) {
			cohortSelect.addEventListener('change', (e) => {
				state.activeCohortId = parseInt(e.target.value, 10);
				navigate(state.currentView);
			});
		}

		// Quick Create Cohort (+ Nova Turma on Sidebar)
		const btnSidebarCreateCohort = document.getElementById('btn-sidebar-create-cohort');
		if (btnSidebarCreateCohort) {
			btnSidebarCreateCohort.addEventListener('click', () => {
				openCreateCohortModal();
			});
		}

		// Theme toggle
		const themeBtn = document.getElementById('btn-toggle-theme');
		if (themeBtn) {
			const currentTheme = localStorage.getItem('stackead_theme') || 'dark';
			document.documentElement.setAttribute('data-theme', currentTheme);
			updateThemeIcon(currentTheme);

			themeBtn.addEventListener('click', () => {
				const active = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
				document.documentElement.setAttribute('data-theme', active);
				localStorage.setItem('stackead_theme', active);
				updateThemeIcon(active);
			});
		}

		// Mobile sidebar toggle & backdrop
		const mobileMenuBtn = document.getElementById('btn-mobile-menu');
		const sidebar = document.getElementById('app-sidebar');
		const backdrop = document.getElementById('sidebar-backdrop');

		function toggleSidebar(forceState) {
			if (!sidebar) return;
			const isOpen = typeof forceState === 'boolean' ? forceState : !sidebar.classList.contains('open');
			sidebar.classList.toggle('open', isOpen);
			if (backdrop) {
				backdrop.classList.toggle('active', isOpen);
			}
		}

		if (mobileMenuBtn) {
			mobileMenuBtn.addEventListener('click', () => toggleSidebar());
		}

		if (backdrop) {
			backdrop.addEventListener('click', () => toggleSidebar(false));
		}

		const toggleSidebarBtn = document.getElementById('btn-toggle-sidebar');
		if (toggleSidebarBtn) {
			toggleSidebarBtn.addEventListener('click', () => toggleSidebar());
		}

		// Topbar XP Pill -> Gamification
		const xpPill = document.getElementById('topbar-xp-pill');
		if (xpPill) {
			xpPill.addEventListener('click', () => {
				navigate('gamification');
			});
		}

		// User Profile Modal Trigger
		const userProfileBtn = document.getElementById('btn-open-user-profile');
		if (userProfileBtn) {
			userProfileBtn.addEventListener('click', () => {
				openUserProfileModal();
			});
		}
	}

	function updateThemeIcon(theme) {
		const icon = document.getElementById('theme-icon');
		if (icon) {
			icon.setAttribute('data-lucide', theme === 'dark' ? 'sun' : 'moon');
			if (window.lucide) window.lucide.createIcons();
		}
	}

	/* ==========================================================================
	   Cohorts & Initial Load
	   ========================================================================== */
	function canManageActiveCohort() {
		if (API.user?.is_admin || API.user?.is_coord) return true;
		const activeCohort = (state.cohorts || []).find(c => Number(c.id) === Number(state.activeCohortId));
		if (activeCohort && typeof activeCohort.can_manage === 'boolean') {
			return activeCohort.can_manage;
		}
		return !!(API.user?.is_master || API.user?.is_admin || API.user?.is_coord);
	}

	async function loadUserCohorts() {
		try {
			const cohorts = await fetchAPI('cohorts');
			state.cohorts = cohorts;

			if (cohortSelect) {
				cohortSelect.innerHTML = '';
				if (cohorts.length === 0) {
					cohortSelect.innerHTML = '<option value="">Nenhuma turma ativa</option>';
				} else {
					const roleNames = {
						administrator: 'Admin',
						coordinator: 'Coord.',
						master: 'Prof.',
						professor: 'Prof.',
						facilitador: 'Prof.',
						tutor: 'Tutor',
						student: 'Aluno'
					};
					cohorts.forEach(c => {
						const opt = document.createElement('option');
						opt.value = c.id;
						const roleTag = c.my_role && roleNames[c.my_role] ? ` [${roleNames[c.my_role]}]` : '';
						opt.textContent = c.title + (c.course_title ? ' (' + c.course_title + ')' : '') + roleTag;
						cohortSelect.appendChild(opt);
					});
					state.activeCohortId = cohorts[0].id;
				}
			}

			// Update user XP pill
			updateGamificationHeader();

			// Load initial dashboard
			navigate('dashboard');
		} catch (e) {
			renderError('Não foi possível carregar suas turmas.');
		}
	}

	function updateGamificationHeader() {
		if (API.user && API.user.gamification) {
			const g = API.user.gamification;
			const levelEl = document.getElementById('topbar-level-num');
			const xpText = document.getElementById('topbar-xp-text');
			const xpFill = document.getElementById('topbar-xp-fill');

			if (levelEl) levelEl.textContent = 'Nv. ' + g.level;
			if (xpText) xpText.textContent = g.total_xp + ' XP';
			if (xpFill) xpFill.style.width = g.progress_percent + '%';
		}
		if (API.user) {
			const coinAmountEl = document.getElementById('topbar-coin-amount');
			const coinSymbolEl = document.getElementById('topbar-coin-symbol');
			if (coinAmountEl) coinAmountEl.textContent = API.user.coins || 0;
			if (coinSymbolEl) coinSymbolEl.textContent = API.user.coin_config?.coin_name || 'ArtCoins';
		}
	}

	/* ==========================================================================
	   Router / Navigator
	   ========================================================================== */
	function navigate(viewName) {
		// Strict Role & View Permission Gating
		if (API.user && Array.isArray(API.user.allowed_views) && API.user.allowed_views.length > 0) {
			if (!API.user.allowed_views.includes(viewName)) {
				showToast('Acesso restrito: Sua categoria de usuário não possui permissão para esta seção.', 'danger');
				viewName = 'dashboard';
				// Sync active class on sidebar buttons
				document.querySelectorAll('.sidebar-nav .nav-link').forEach(b => {
					b.classList.toggle('active', b.getAttribute('data-view') === 'dashboard');
				});
			}
		}

		state.currentView = viewName;

		// Close any open full-page workspace views / modals when navigating
		document.querySelectorAll('.modal-backdrop').forEach(m => m.remove());

		// Auto-expand accordion group containing this view and sync active class
		document.querySelectorAll('.sidebar-nav .nav-link').forEach(b => {
			const isActive = b.getAttribute('data-view') === viewName;
			b.classList.toggle('active', isActive);
			if (isActive) {
				const group = b.closest('.nav-accordion-group');
				if (group) group.classList.add('is-open');
			}
		});

		// Clear watch time interval
		if (state.watchTimer) {
			clearInterval(state.watchTimer);
			state.watchTimer = null;
		}

		const titles = {
			'dashboard': API.user?.is_master && !API.user?.is_admin ? 'Painel Docente' : (API.user?.is_coord && !API.user?.is_admin ? 'Painel de Coordenação' : 'Meu Painel'),
			'curriculum': API.user?.is_master ? 'Conteúdos & Aulas' : 'Trilha & Aulas',
			'player': 'Sala de Aula',
			'sessions': 'Sessões Ao Vivo',
			'assignments': API.user?.is_master ? 'Entregas dos Alunos' : 'Projetos Práticos',
			'community': 'Fórum da Turma',
			'gamification': 'Conquistas & Ranking',
			'certificate': 'Certificação Oficial',
			'attendance-desk': 'Diário de Presença',
			'grading-desk': 'Bancada de Correção',
			'accommodations-desk': 'Laudos & Acomodações COGA',
			'finance-desk': 'Finanças & Planos',
			'users-admin': 'Gestão de Usuários & Membros',
			'courses-admin': 'Gestão & Criação de Cursos',
			'whitelabel-desk': 'Identidade & White Label',
			'landing-builder': 'Construtor de Landing Page',
			'news-admin': 'Mural de Notícias & Avisos',
			'categories-admin': 'Gestão de Categorias',
			'leads-admin': 'Leads & Inscrições',
			'gamification-admin': 'Gestão de Gamificação & Moedas'
		};

		if (viewTitleEl) {
			const rawTitle = titles[viewName] || 'Plataforma';
			viewTitleEl.textContent = window.StackEAD_I18n ? window.StackEAD_I18n.translateText(rawTitle) : rawTitle;
			if (viewTitleEl.firstChild) {
				viewTitleEl.firstChild.__origText = rawTitle;
			}
		}

		renderLoading();

		switch (viewName) {
			case 'dashboard':
				renderDashboard();
				break;
			case 'curriculum':
				renderCurriculum();
				break;
			case 'player':
				renderPlayer();
				break;
			case 'sessions':
				renderSessions();
				break;
			case 'assignments':
				renderAssignments();
				break;
			case 'community':
				renderCommunity();
				break;
			case 'gamification':
				renderGamification();
				break;
			case 'certificate':
				renderCertificate();
				break;
			case 'attendance-desk':
				renderAttendanceDesk();
				break;
			case 'grading-desk':
				renderGradingDesk();
				break;
			case 'accommodations-desk':
				renderAccommodationsDesk();
				break;
			case 'finance-desk':
				renderFinanceDesk();
				break;
			case 'users-admin':
				renderUsersAdminView();
				break;
			case 'courses-admin':
				renderCoursesAdminView();
				break;
			case 'whitelabel-desk':
				renderWhiteLabelDesk();
				break;
			case 'landing-builder':
				renderLandingBuilderView();
				break;
			case 'news-admin':
				renderNewsAdminView();
				break;
			case 'categories-admin':
				renderCategoriesAdminView();
				break;
			case 'leads-admin':
				renderLeadsAdminView();
				break;
			case 'gamification-admin':
				renderGamificationAdmin();
				break;
			default:
				renderDashboard();
		}
	}

	function renderLoading() {
		if (contentRoot) {
			contentRoot.innerHTML = `
				<div class="loading-state">
					<div class="spinner"></div>
					<p>Carregando conteúdo...</p>
				</div>
			`;
		}
	}

	function renderError(msg) {
		if (contentRoot) {
			contentRoot.innerHTML = `
				<div class="error-state" style="text-align: center; padding: 48px 20px;">
					<i data-lucide="alert-circle" style="width: 48px; height: 48px; color: var(--danger); margin-bottom: 12px;"></i>
					<h3>Ocorreu um erro</h3>
					<p style="color: var(--text-muted);">${msg}</p>
				</div>
			`;
			if (window.lucide) window.lucide.createIcons();
		}
	}

	/* ==========================================================================
	   1. Student Dashboard ("Minha Formação")
	   ========================================================================== */
	async function renderDashboard() {
		const activeCohort = state.cohorts.find(c => c.id === state.activeCohortId);
		const isMaster = API.user?.is_master && !API.user?.is_admin;

		let statsHtml = '';
		if (isMaster) {
			statsHtml = `
				<div class="stat-card">
					<div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #6366f1;">
						<i data-lucide="users"></i>
					</div>
					<div class="stat-info">
						<h4>Turmas Atribuídas</h4>
						<div class="stat-value">${state.cohorts.length}</div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
						<i data-lucide="user-check"></i>
					</div>
					<div class="stat-info">
						<h4>Diário Docente</h4>
						<div class="stat-value">Disponível</div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
						<i data-lucide="clipboard-check"></i>
					</div>
					<div class="stat-info">
						<h4>Bancada Notas</h4>
						<div class="stat-value">Ativa</div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon" style="background: rgba(6, 182, 212, 0.15); color: #06b6d4;">
						<i data-lucide="message-square"></i>
					</div>
					<div class="stat-info">
						<h4>Fórum Docente</h4>
						<div class="stat-value">Conectado</div>
					</div>
				</div>
			`;
		} else {
			statsHtml = `
				<div class="stat-card">
					<div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #6366f1;">
						<i data-lucide="book-open"></i>
					</div>
					<div class="stat-info">
						<h4>Progresso da Turma</h4>
						<div class="stat-value">${activeCohort ? activeCohort.progress : 0}%</div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
						<i data-lucide="zap"></i>
					</div>
					<div class="stat-info">
						<h4>Total de XP</h4>
						<div class="stat-value">${API.user?.gamification?.total_xp || 0}</div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
						<i data-lucide="award"></i>
					</div>
					<div class="stat-info">
						<h4>Nível Atual</h4>
						<div class="stat-value">Nv. ${API.user?.gamification?.level || 1}</div>
					</div>
				</div>
				<div class="stat-card">
					<div class="stat-icon" style="background: rgba(6, 182, 212, 0.15); color: #06b6d4;">
						<i data-lucide="award"></i>
					</div>
					<div class="stat-info">
						<h4>Emblemas</h4>
						<div class="stat-value">${API.user?.badges?.length || 0}</div>
					</div>
				</div>
			`;
		}

		contentRoot.innerHTML = `
			<div class="dashboard-wrapper">
				<div class="stats-grid">
					${statsHtml}
				</div>

				<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 24px;">
					<!-- Turma Atual Card -->
					<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 28px;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
							<div>
								<span style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary); font-weight: 700;">Em Andamento</span>
								<h2 style="font-size: 1.4rem; font-weight: 700; margin-top: 4px;">${activeCohort ? activeCohort.title : 'Selecione uma turma'}</h2>
								<p style="color: var(--text-muted); font-size: 0.9rem;">${activeCohort?.course_title || 'Formação Especializada'}</p>
							</div>
							<button class="btn btn-primary" id="btn-resume-course">
								<i data-lucide="play"></i> Continuar Estudos
							</button>
						</div>

						<div style="margin-top: 24px;">
							<div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">
								<span>Conclusão Curricular</span>
								<span>${activeCohort ? activeCohort.progress : 0}%</span>
							</div>
							<div style="height: 8px; background: rgba(255,255,255,0.08); border-radius: 999px; overflow: hidden;">
								<div style="width: ${activeCohort ? activeCohort.progress : 0}%; height: 100%; background: linear-gradient(90deg, var(--primary), var(--accent));"></div>
							</div>
						</div>
					</div>

					<!-- Acomodações e Apoio Inclusivo -->
					<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px;">
						<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px; color: var(--accent);">
							<i data-lucide="heart-handshake"></i>
							<h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main);">Apoio Neurodivergente</h3>
						</div>
						<p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.5; margin-bottom: 20px;">
							Seu ambiente de aprendizagem possui suporte a TDAH, dislexia e espectro autista com linguagem simplificada, pausas de respiração e régua de foco.
						</p>
						<button class="btn btn-secondary btn-block btn-sm" id="btn-request-coga-modal">
							<i data-lucide="plus-circle"></i> Solicitar Acomodação
						</button>
					</div>
				</div>
			</div>
		`;

		if (window.lucide) window.lucide.createIcons();

		const resumeBtn = document.getElementById('btn-resume-course');
		if (resumeBtn) {
			resumeBtn.addEventListener('click', () => navigate('curriculum'));
		}

		const reqBtn = document.getElementById('btn-request-coga-modal');
		if (reqBtn) {
			reqBtn.addEventListener('click', openAccommodationModal);
		}
	}

	/* ==========================================================================
	   2. Curriculum & Modules View
	   ========================================================================== */
	async function renderCurriculum() {
		if (!state.activeCohortId) {
			renderError('Selecione uma turma no topo da barra lateral para visualizar o currículo.');
			return;
		}

		try {
			const data = await fetchAPI(`cohorts/${state.activeCohortId}/curriculum`);
			state.activeCurriculum = data;
			const canManage = canManageActiveCohort();

			let html = `
				<div class="curriculum-view">
					<div class="desk-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
						<div>
							<h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">Trilha Formativa & Módulos</h2>
							<p style="color: var(--text-muted); font-size: 0.95rem;">Acesse as aulas em sequência ou gerencie os módulos e aulas da turma.</p>
						</div>
						${canManage ? `
							<button id="btn-create-module-modal" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
								<i data-lucide="plus-circle"></i>
								<span>Cadastrar Novo Módulo</span>
							</button>
						` : ''}
					</div>
			`;

			if (!data.modules || data.modules.length === 0) {
				html += `
					<div class="empty-state" style="padding: 48px; text-align: center; background: var(--bg-card); border: 1px dashed var(--border); border-radius: var(--radius-lg);">
						<i data-lucide="book-open" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 12px;"></i>
						<h3>Nenhum módulo cadastrado nesta trilha</h3>
						<p style="color: var(--text-muted); margin-bottom: 20px;">Comece estruturando os módulos e aulas desta turma.</p>
						${canManage ? `<button class="btn btn-primary" id="btn-empty-create-module" onclick="window.StackEAD_App.openCreateModuleModal()">Cadastrar Primeiro Módulo</button>` : ''}
					</div>
				`;
			} else {
				// 1. Milestone Timeline Stepper (Linha de Marcos da Formação)
				html += `
					<div class="timeline-stepper-container">
						<div class="timeline-stepper-header">
							<h3><i data-lucide="compass"></i> Linha do Tempo & Marcos da Formação</h3>
							<span style="font-size: 12px; color: #94a3b8; font-weight: 600;">${data.modules.filter(m => m.status === 'completed').length} de ${data.modules.length} módulos concluídos</span>
						</div>
						<div class="timeline-stepper-track">
							${data.modules.map((m, idx) => {
								const isCompleted = m.status === 'completed';
								const isCurrent = m.status === 'in_progress';
								const isLocked = m.is_locked;
								const statusClass = isCompleted ? 'is-completed' : (isCurrent ? 'is-current' : 'is-locked');
								const badgeClass = isCompleted ? 'status-completed' : (isCurrent ? 'status-current' : 'status-locked');
								const badgeText = isCompleted ? 'Concluído' : (isCurrent ? 'Em Curso' : 'Bloqueado');
								return `
									<div class="timeline-milestone ${statusClass}" onclick="document.getElementById('module-card-${m.id}')?.scrollIntoView({ behavior: 'smooth' });">
										<div class="timeline-milestone-bar"></div>
										<div class="timeline-milestone-node">
											${isCompleted ? '<i data-lucide="check" style="width: 18px; height: 18px;"></i>' : (isLocked ? '<i data-lucide="lock" style="width: 16px; height: 16px;"></i>' : idx + 1)}
										</div>
										<div class="timeline-milestone-info">
											<span class="timeline-milestone-title" title="${escapeHtml(m.title)}">M${idx + 1}: ${escapeHtml(m.title)}</span>
											<span class="timeline-milestone-status ${badgeClass}">${badgeText}</span>
										</div>
									</div>
								`;
							}).join('')}
						</div>
					</div>
				`;

				data.modules.forEach((mod, mIdx) => {
					const modLocked = mod.is_locked;
					html += `
						<div id="module-card-${mod.id}" class="module-accordion ${modLocked ? 'is-locked' : ''}" style="background: var(--bg-card); border: 1px solid ${modLocked ? 'rgba(239, 68, 68, 0.3)' : 'var(--border)'}; border-radius: var(--radius-lg); margin-bottom: 16px; overflow: hidden; ${modLocked ? 'opacity: 0.85;' : ''}">
							<div class="module-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; cursor: pointer; border-bottom: 1px solid var(--border); background: ${modLocked ? 'rgba(239, 68, 68, 0.03)' : 'rgba(255,255,255,0.01)'};">
								<div class="module-title-box" style="display: flex; align-items: center; gap: 16px;">
									<div class="module-index" style="width: 36px; height: 36px; border-radius: var(--radius-md); background: ${modLocked ? 'rgba(100, 116, 139, 0.2)' : (mod.status === 'completed' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(37, 99, 235, 0.15)')}; color: ${modLocked ? '#94a3b8' : (mod.status === 'completed' ? '#34d399' : '#60a5fa')}; display: flex; align-items: center; justify-content: center; font-weight: 800;">
										${modLocked ? '<i data-lucide="lock" style="width: 18px; height: 18px;"></i>' : (mod.status === 'completed' ? '<i data-lucide="check" style="width: 20px; height: 20px;"></i>' : mIdx + 1)}
									</div>
									<div>
										<div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px;">
											<h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin: 0;">${escapeHtml(mod.title)}</h3>
											${mod.workload_hours ? `<span style="font-size: 0.75rem; color: #94a3b8; background: rgba(148, 163, 184, 0.12); padding: 2px 7px; border-radius: 4px; font-weight: 600;"><i data-lucide="clock" style="width: 11px; height: 11px; display: inline-block; vertical-align: middle;"></i> ${mod.workload_hours}h</span>` : ''}
											${mod.has_final_project ? `<span class="final-project-badge"><i data-lucide="star" style="width: 11px; height: 11px;"></i> Projeto Integrador</span>` : ''}
											${mod.theme ? `<span style="font-size: 0.75rem; color: #a78bfa; background: rgba(167, 139, 250, 0.12); padding: 2px 7px; border-radius: 4px; font-weight: 600;">${escapeHtml(mod.theme)}</span>` : ''}
											${mod.is_standalone ? `<span style="font-size: 0.72rem; color: #38bdf8; background: rgba(56, 189, 248, 0.15); padding: 2px 7px; border-radius: 4px; font-weight: 700; text-transform: uppercase;">Minicurso Avulso</span>` : ''}
										</div>
										<span style="font-size: 0.8rem; color: var(--text-muted); display: block;">${mod.lessons.length} aulas cadastradas ${mod.description ? '&bull; ' + escapeHtml(mod.description) : ''}</span>
									</div>
								</div>

								<div style="display: flex; align-items: center; gap: 10px;">
									${canManage ? `
										<button type="button" class="btn btn-sm btn-secondary btn-add-lesson" data-module-id="${mod.id}" data-module-title="${escapeHtml(mod.title)}" style="font-size: 0.8rem; padding: 4px 10px; display: flex; align-items: center; gap: 4px;" onclick="event.stopPropagation();">
											<i data-lucide="plus"></i>
											<span>Aula</span>
										</button>
										<button type="button" class="btn btn-sm btn-secondary btn-edit-module" data-module-id="${mod.id}" data-module-title="${escapeHtml(mod.title)}" style="font-size: 0.8rem; padding: 4px 10px;" onclick="event.stopPropagation();">
											<i data-lucide="edit-3"></i>
										</button>
										<button type="button" class="btn btn-sm btn-delete-module" data-module-id="${mod.id}" data-module-title="${escapeHtml(mod.title)}" style="font-size: 0.8rem; padding: 4px 8px; background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer;" onclick="event.stopPropagation();">
											<i data-lucide="trash-2"></i>
										</button>
									` : ''}
									<i data-lucide="chevron-down" style="color: var(--text-muted);"></i>
								</div>
							</div>

							<ul class="lesson-list" style="list-style: none; padding: 0; margin: 0;">
					`;

					if (mod.lessons.length === 0) {
						html += `<li style="padding: 20px 24px; color: var(--text-muted); font-size: 0.88rem; text-align: center;">Nenhuma aula cadastrada neste módulo. ${canManage ? '<a href="javascript:void(0)" class="btn-add-lesson" data-module-id="' + mod.id + '" data-module-title="' + escapeHtml(mod.title) + '" style="color: var(--primary); font-weight: 600;">Adicionar primeira aula &rarr;</a>' : ''}</li>`;
					} else {
						mod.lessons.forEach((les) => {
							const isLocked = les.is_locked;
							const isDone = les.completed;

							html += `
								<li class="lesson-item ${isLocked ? 'is-locked' : ''}" data-lesson-id="${les.id}" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 24px; border-bottom: 1px solid var(--border); transition: background 0.2s; cursor: pointer;">
									<div class="lesson-meta-left" style="display: flex; align-items: center; gap: 14px;">
										${isDone 
											? '<i data-lucide="check-circle" style="color: var(--success); width: 20px;"></i>' 
											: (isLocked 
												? '<i data-lucide="lock" style="color: var(--text-muted); width: 20px;"></i>' 
												: '<i data-lucide="play-circle" style="color: var(--primary); width: 20px;"></i>')}
										<div>
											<div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
												<strong style="font-size: 0.95rem; color: var(--text-main); display: block;">${escapeHtml(les.title)}</strong>
												${les.quiz_id ? '<span style="font-size: 0.72rem; padding: 2px 7px; border-radius: 4px; background: rgba(168, 85, 247, 0.15); color: #c084fc; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="help-circle" style="width: 12px; height: 12px;"></i> Quiz</span>' : ''}
												${les.assignment_id ? '<span style="font-size: 0.72rem; padding: 2px 7px; border-radius: 4px; background: rgba(59, 130, 246, 0.15); color: #60a5fa; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;"><i data-lucide="file-text" style="width: 12px; height: 12px;"></i> Entrega</span>' : ''}
												${les.attachments && les.attachments.length > 0 ? `<span style="font-size: 0.72rem; padding: 2px 7px; border-radius: 4px; background: rgba(16, 185, 129, 0.15); color: #34d399; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="${les.attachments.length} arquivo(s) disponível(is)"><i data-lucide="paperclip" style="width: 12px; height: 12px;"></i> ${les.attachments.length} ${les.attachments.length === 1 ? 'Anexo' : 'Anexos'}</span>` : ''}
												${les.links && les.links.length > 0 ? `<span style="font-size: 0.72rem; padding: 2px 7px; border-radius: 4px; background: rgba(14, 165, 233, 0.15); color: #38bdf8; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="${les.links.length} link(s) complementar(es)"><i data-lucide="link" style="width: 12px; height: 12px;"></i> ${les.links.length} ${les.links.length === 1 ? 'Link' : 'Links'}</span>` : ''}
											</div>
											${isLocked ? `<span style="display:block; font-size: 0.75rem; color: #f87171;">${les.lock_reason}</span>` : ''}
										</div>
									</div>
									<div style="display: flex; align-items: center; gap: 10px;">
										<span style="font-size: 0.8rem; color: var(--text-muted);">${les.duration_min > 0 ? les.duration_min + ' min' : 'Aula'}</span>
										<button class="btn btn-secondary btn-sm" ${isLocked ? 'disabled' : ''}>
											${isDone ? 'Rever' : 'Assistir'}
										</button>
										${canManage ? `
											<button type="button" class="btn btn-sm btn-secondary btn-manage-quiz" data-lesson-id="${les.id}" data-quiz-id="${les.quiz_id || ''}" data-lesson-title="${escapeHtml(les.title)}" title="Configurar Quiz da Aula" style="font-size: 0.8rem; padding: 4px 8px;" onclick="event.stopPropagation();">
												<i data-lucide="help-circle"></i>
											</button>
											<button type="button" class="btn btn-sm btn-secondary btn-edit-lesson" data-lesson-id="${les.id}" style="font-size: 0.8rem; padding: 4px 8px;" onclick="event.stopPropagation();">
												<i data-lucide="edit-3"></i>
											</button>
											<button type="button" class="btn btn-sm btn-delete-lesson" data-lesson-id="${les.id}" data-lesson-title="${escapeHtml(les.title)}" style="font-size: 0.8rem; padding: 4px 8px; background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer;" onclick="event.stopPropagation();">
												<i data-lucide="trash-2"></i>
											</button>
										` : ''}
									</div>
								</li>
							`;
						});
					}

					html += `</ul></div>`;
				});
			}

			html += `</div>`;
			contentRoot.innerHTML = html;
			if (window.lucide) window.lucide.createIcons();

			// Listeners
			const createModBtn = document.getElementById('btn-create-module-modal');
			if (createModBtn) {
				createModBtn.addEventListener('click', () => openCreateModuleModal());
			}
			const emptyCreateModBtn = document.getElementById('btn-empty-create-module');
			if (emptyCreateModBtn) {
				emptyCreateModBtn.addEventListener('click', () => openCreateModuleModal());
			}

			document.querySelectorAll('.btn-add-lesson').forEach(b => {
				b.addEventListener('click', (e) => {
					e.stopPropagation();
					const mid = parseInt(b.getAttribute('data-module-id'), 10);
					const mtitle = b.getAttribute('data-module-title');
					openCreateLessonModal(mid, mtitle);
				});
			});

			document.querySelectorAll('.btn-edit-module').forEach(b => {
				b.addEventListener('click', (e) => {
					e.stopPropagation();
					const mid = parseInt(b.getAttribute('data-module-id'), 10);
					const mtitle = b.getAttribute('data-module-title');
					openEditModuleModal(mid, mtitle);
				});
			});

			document.querySelectorAll('.btn-delete-module').forEach(b => {
				b.addEventListener('click', (e) => {
					e.stopPropagation();
					const mid = parseInt(b.getAttribute('data-module-id'), 10);
					const mtitle = b.getAttribute('data-module-title');
					deleteModule(mid, mtitle);
				});
			});

			document.querySelectorAll('.btn-manage-quiz').forEach(b => {
				b.addEventListener('click', (e) => {
					e.stopPropagation();
					const lid = parseInt(b.getAttribute('data-lesson-id'), 10);
					const qid = parseInt(b.getAttribute('data-quiz-id'), 10) || 0;
					const ltitle = b.getAttribute('data-lesson-title');
					openManageQuizModal(qid, lid, ltitle);
				});
			});

			document.querySelectorAll('.btn-edit-lesson').forEach(b => {
				b.addEventListener('click', (e) => {
					e.stopPropagation();
					const lid = parseInt(b.getAttribute('data-lesson-id'), 10);
					openEditLessonModal(lid);
				});
			});

			document.querySelectorAll('.btn-delete-lesson').forEach(b => {
				b.addEventListener('click', (e) => {
					e.stopPropagation();
					const lid = parseInt(b.getAttribute('data-lesson-id'), 10);
					const ltitle = b.getAttribute('data-lesson-title');
					deleteLesson(lid, ltitle);
				});
			});

			// Module accordion header toggle
			document.querySelectorAll('.module-accordion .module-header').forEach(hdr => {
				hdr.addEventListener('click', () => {
					const acc = hdr.closest('.module-accordion');
					const list = acc ? acc.querySelector('.lesson-list') : null;
					if (list) {
						list.style.display = list.style.display === 'none' ? 'block' : 'none';
					}
				});
			});

			// Lesson click handlers
			document.querySelectorAll('.lesson-item:not(.is-locked)').forEach(item => {
				item.addEventListener('click', () => {
					const lessonId = parseInt(item.getAttribute('data-lesson-id'), 10);
					loadLesson(lessonId);
				});
			});
		} catch (e) {
			console.error('StackEAD Curriculum Render Error:', e);
			renderError('Não foi possível carregar a trilha da turma: ' + (e.message || 'Erro de execução'));
		}
	}

	async function openCreateModuleModal() {
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 620px; width: 90%;">
				<div class="modal-header">
					<h3>Cadastrar Novo Módulo (Lego Didático)</h3>
					<button class="btn-icon btn-close-modal">&times;</button>
				</div>
				<form id="form-create-module" style="padding: 24px;">
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Título do Módulo *</label>
						<input type="text" id="module-title" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required placeholder="Ex: Módulo 1: Fundamentos da Imersão Digital">
					</div>
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Eixo Temático</label>
							<input type="text" id="module-theme" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Ex: Criação Audiovisual">
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Carga Horária (horas)</label>
							<input type="number" id="module-hours" min="1" max="1000" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Ex: 20">
						</div>
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Descrição do Módulo</label>
						<textarea id="module-desc" rows="2" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Breve introdução dos tópicos deste módulo..."></textarea>
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Ementa / Objetivos Pedagógicos</label>
						<textarea id="module-syllabus" rows="3" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Ementa curricular detalhada e competências..."></textarea>
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
							<input type="checkbox" id="module-standalone" value="yes">
							<span>Disponibilizar como Minicurso Avulso independente</span>
						</label>
					</div>

					<!-- Projeto Final / Integrador Opcional -->
					<div class="form-group" style="margin-bottom: 24px; border: 1px solid rgba(245, 158, 11, 0.35); background: rgba(245, 158, 11, 0.05); padding: 16px; border-radius: 8px;">
						<label style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #fbbf24; cursor: pointer;">
							<input type="checkbox" id="module-has-final-project" value="yes">
							<span>Projeto Final de Módulo / Projeto Integrador (Opcional do Professor/Coordenador)</span>
						</label>
						<div id="module-final-project-fields" style="display: none; margin-top: 14px; flex-direction: column; gap: 12px;">
							<div>
								<label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px;">Título do Projeto Integrador *</label>
								<input type="text" id="module-final-project-title" class="form-control" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Ex: Projeto Integrador: Curadoria e Portfólio do Módulo">
							</div>
							<div>
								<label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px;">Instruções e Critérios de Entrega</label>
								<textarea id="module-final-project-instructions" rows="2" class="form-control" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Descreva os requisitos práticos exigidos..."></textarea>
							</div>
							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
								<div>
									<label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px;">Nota Mínima para Aprovação</label>
									<input type="number" id="module-final-project-min-score" min="1" max="100" value="70" class="form-control" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								</div>
								<div style="display: flex; align-items: center; padding-top: 18px;">
									<label style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; cursor: pointer; color: #fca5a5;">
										<input type="checkbox" id="module-lock-next" value="yes" checked>
										<span>Bloquear próximo módulo até aprovação</span>
									</label>
								</div>
							</div>
						</div>
					</div>
					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="btn-save-module">Salvar Módulo</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		const hasFpCb = modal.querySelector('#module-has-final-project');
		const fpFields = modal.querySelector('#module-final-project-fields');
		if (hasFpCb && fpFields) {
			hasFpCb.addEventListener('change', () => {
				fpFields.style.display = hasFpCb.checked ? 'flex' : 'none';
			});
		}

		modal.querySelector('#form-create-module').onsubmit = async (e) => {
			e.preventDefault();
			const saveBtn = modal.querySelector('#btn-save-module');
			saveBtn.disabled = true;
			saveBtn.textContent = 'Salvando...';

			const hasFp = modal.querySelector('#module-has-final-project')?.checked ? 'yes' : 'no';
			const fpTitle = modal.querySelector('#module-final-project-title')?.value.trim() || '';
			const fpInst = modal.querySelector('#module-final-project-instructions')?.value.trim() || '';
			const fpMinScore = parseFloat(modal.querySelector('#module-final-project-min-score')?.value) || 70;
			const fpLockNext = modal.querySelector('#module-lock-next')?.checked ? 'yes' : 'no';

			try {
				await fetchAPI('modules', 'POST', {
					cohort_id: state.activeCohortId,
					title: document.getElementById('module-title').value.trim(),
					theme: document.getElementById('module-theme').value.trim(),
					workload_hours: parseInt(document.getElementById('module-hours').value, 10) || 0,
					description: document.getElementById('module-desc').value.trim(),
					syllabus: document.getElementById('module-syllabus').value.trim(),
					is_standalone: document.getElementById('module-standalone').checked,
					has_final_project: hasFp,
					final_project_title: fpTitle,
					final_project_instructions: fpInst,
					final_project_min_score: fpMinScore,
					lock_next_module: fpLockNext
				});

				showToast('Módulo criado com sucesso!', 'success');
				modal.remove();
				renderCurriculum();
			} catch (err) {
				saveBtn.disabled = false;
				saveBtn.textContent = 'Salvar Módulo';
				showToast(err.message || 'Erro ao criar módulo.', 'danger');
			}
		};
	}

	async function openEditModuleModal(moduleId, currentTitle) {
		const existingMod = (state.activeCurriculum?.modules || []).find(m => m.id === moduleId) || {};
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 620px; width: 90%;">
				<div class="modal-header">
					<h3>Editar Módulo</h3>
					<button class="btn-icon btn-close-modal">&times;</button>
				</div>
				<form id="form-edit-module" style="padding: 24px;">
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Título do Módulo *</label>
						<input type="text" id="edit-mod-title" class="form-control" value="${escapeHtml(existingMod.title || currentTitle)}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required>
					</div>
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Eixo Temático</label>
							<input type="text" id="edit-mod-theme" class="form-control" value="${escapeHtml(existingMod.theme || '')}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Ex: Artes Visuais">
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Carga Horária (horas)</label>
							<input type="number" id="edit-mod-hours" min="1" max="1000" value="${existingMod.workload_hours || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Ex: 20">
						</div>
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Descrição do Módulo</label>
						<textarea id="edit-mod-desc" rows="2" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">${escapeHtml(existingMod.description || '')}</textarea>
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Ementa / Objetivos Pedagógicos</label>
						<textarea id="edit-mod-syllabus" rows="3" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">${escapeHtml(existingMod.syllabus || '')}</textarea>
					</div>
					<div class="form-group" style="margin-bottom: 24px;">
						<label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
							<input type="checkbox" id="edit-mod-standalone" value="yes" ${existingMod.is_standalone ? 'checked' : ''}>
							<span>Disponibilizar como Minicurso Avulso independente</span>
						</label>
					</div>
					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="btn-update-mod">Atualizar Módulo</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		modal.querySelector('#form-edit-module').onsubmit = async (e) => {
			e.preventDefault();
			const saveBtn = modal.querySelector('#btn-update-mod');
			saveBtn.disabled = true;
			saveBtn.textContent = 'Atualizando...';

			try {
				await fetchAPI(`modules/${moduleId}`, 'POST', {
					title: modal.querySelector('#edit-mod-title').value.trim(),
					theme: modal.querySelector('#edit-mod-theme').value.trim(),
					workload_hours: parseInt(modal.querySelector('#edit-mod-hours').value, 10) || 0,
					description: modal.querySelector('#edit-mod-desc').value.trim(),
					syllabus: modal.querySelector('#edit-mod-syllabus').value.trim(),
					is_standalone: modal.querySelector('#edit-mod-standalone').checked
				});
				showToast('Módulo atualizado com sucesso!', 'success');
				modal.remove();
				renderCurriculum();
			} catch (err) {
				saveBtn.disabled = false;
				saveBtn.textContent = 'Atualizar Módulo';
				showToast(err.message || 'Erro ao atualizar módulo.', 'danger');
			}
		};
	}

	async function deleteModule(moduleId, title) {
		if (!confirm(`Tem certeza que deseja excluir o módulo "${title}" e todas as suas aulas?`)) return;

		try {
			await fetchAPI(`modules/${moduleId}`, 'DELETE');
			showToast('Módulo excluído com sucesso.', 'success');
			renderCurriculum();
		} catch (e) {
			showToast('Erro ao excluir módulo.', 'danger');
		}
	}

	/* ==========================================================================
	   Advanced Lesson Modal (Full-Featured Pedagogy Control Station)
	   ========================================================================== */
	async function openLessonModal({ moduleId = 0, moduleTitle = '', lessonId = null }) {
		const isEditing = !!lessonId;
		let lesson = null;

		if (isEditing) {
			lesson = await fetchAPI(`lessons/${lessonId}`).catch(() => null);
			if (!lesson) {
				showToast('Não foi possível carregar os dados da aula.', 'danger');
				return;
			}
			moduleId = lesson.module_id || moduleId;
		}

		// Pre-fetch available quizzes and cohort assignments
		const [quizzes, assignments] = await Promise.all([
			fetchAPI('quizzes').catch(() => []),
			state.activeCohortId ? fetchAPI(`cohorts/${state.activeCohortId}/assignments`).catch(() => []) : Promise.resolve([])
		]);

		let attachments = (lesson && Array.isArray(lesson.attachments)) ? [...lesson.attachments] : [];
		let links = (lesson && Array.isArray(lesson.links)) ? [...lesson.links] : [];

		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 100001; display: flex; align-items: center; justify-content: center; padding: 20px;';

		modal.innerHTML = `
			<div class="modal-card" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-xl); max-width: 860px; width: 100%; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.7); overflow: hidden;">
				<!-- Modal Header -->
				<div class="modal-header" style="padding: 20px 28px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--bg-card);">
					<div>
						<div style="display: flex; align-items: center; gap: 8px;">
							<span class="badge" style="background: rgba(37,99,235,0.15); color: #60a5fa; font-weight: 700; font-size: 0.72rem; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
								${isEditing ? 'Edição Pedagógica' : 'Nova Aula'}
							</span>
							<span style="font-size: 0.85rem; color: var(--text-muted);">${escapeHtml(moduleTitle || 'Módulo Didático')}</span>
						</div>
						<h3 style="font-size: 1.35rem; font-weight: 800; color: var(--text-main); margin: 4px 0 0;">
							${isEditing ? 'Editar Aula' : 'Cadastrar Nova Aula'}
						</h3>
					</div>
					<button type="button" class="btn-icon btn-close-modal" style="background: none; border: none; font-size: 1.6rem; color: var(--text-muted); cursor: pointer;">&times;</button>
				</div>

				<!-- Tabs Navigation -->
				<div class="lesson-modal-tabs" style="display: flex; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.15); padding: 0 24px; gap: 6px; overflow-x: auto;">
					<button type="button" class="tab-link active" data-tab="tab-general" style="padding: 12px 16px; background: none; border: none; border-bottom: 3px solid var(--primary); font-weight: 700; color: #fff; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
						<i data-lucide="video" style="width: 16px; height: 16px; color: var(--primary);"></i>
						<span>1. Mídia & Conteúdo</span>
					</button>
					<button type="button" class="tab-link" data-tab="tab-materials" style="padding: 12px 16px; background: none; border: none; border-bottom: 3px solid transparent; font-weight: 600; color: var(--text-muted); font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
						<i data-lucide="paperclip" style="width: 16px; height: 16px;"></i>
						<span>2. Anexos & Links</span>
						<span id="badge-total-files" class="badge" style="background: rgba(255,255,255,0.12); color: #e2e8f0; font-size: 0.7rem; padding: 2px 7px; border-radius: 999px;">${attachments.length + links.length}</span>
					</button>
					<button type="button" class="tab-link" data-tab="tab-quiz" style="padding: 12px 16px; background: none; border: none; border-bottom: 3px solid transparent; font-weight: 600; color: var(--text-muted); font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
						<i data-lucide="help-circle" style="width: 16px; height: 16px;"></i>
						<span>3. Quiz Avaliativo</span>
						<span id="badge-has-quiz" style="width: 8px; height: 8px; border-radius: 50%; background: ${lesson?.quiz_id ? '#34d399' : 'transparent'};"></span>
					</button>
					<button type="button" class="tab-link" data-tab="tab-activity" style="padding: 12px 16px; background: none; border: none; border-bottom: 3px solid transparent; font-weight: 600; color: var(--text-muted); font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
						<i data-lucide="file-check" style="width: 16px; height: 16px;"></i>
						<span>4. Atividade Prática</span>
						<span id="badge-has-activity" style="width: 8px; height: 8px; border-radius: 50%; background: ${lesson?.assignment_id ? '#60a5fa' : 'transparent'};"></span>
					</button>
				</div>

				<!-- Form Content -->
				<form id="form-lesson-station" style="flex: 1; overflow-y: auto; padding: 24px 28px; display: flex; flex-direction: column; gap: 20px;">
					
					<!-- TAB 1: General & Media -->
					<div id="pane-tab-general" class="lesson-pane" style="display: block;">
						<div class="form-group" style="margin-bottom: 16px;">
							<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Título da Aula *</label>
							<input type="text" id="lesson-f-title" class="form-control" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main); font-size: 0.95rem;" required value="${escapeHtml(lesson?.title || '')}" placeholder="Ex: Aula 1: Instalações Imersivas & Iluminação Dinâmica">
						</div>

						<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px;">
							<div class="form-group">
								<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">URL do Vídeo (YouTube, Vimeo, MP4) *</label>
								<input type="url" id="lesson-f-video" class="form-control" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);" required value="${escapeHtml(lesson?.video_url || '')}" placeholder="https://www.youtube.com/watch?v=...">
							</div>
							<div class="form-group">
								<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Duração Estimada (min)</label>
								<input type="number" id="lesson-f-duration" class="form-control" min="1" max="600" style="width: 100%; padding: 11px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);" value="${lesson?.duration_min || 15}">
							</div>
						</div>

						<div class="form-group" style="margin-bottom: 16px;">
							<label style="display: block; font-weight: 600; color: var(--text-muted); margin-bottom: 6px;">URL do Áudio / Podcast MP3 (Opcional)</label>
							<input type="url" id="lesson-f-audio" class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);" value="${escapeHtml(lesson?.audio_url || '')}" placeholder="https://.../podcast-aula.mp3">
						</div>

						<div class="form-group" style="margin-bottom: 16px;">
							<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Conteúdo / Instruções e Roteiro da Aula</label>
							<textarea id="lesson-f-content" rows="4" class="form-control" style="width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main); line-height: 1.5;" placeholder="Apresentação dos conceitos, referências teóricas e instruções didáticas da aula...">${escapeHtml(lesson?.content || '')}</textarea>
						</div>

						<!-- COGA Simplificado -->
						<div class="form-group" style="margin-bottom: 16px; background: rgba(37, 99, 235, 0.06); border: 1.5px solid rgba(59, 130, 246, 0.3); border-radius: 10px; padding: 16px;">
							<label style="display: flex; align-items: center; gap: 8px; font-weight: 800; color: #60a5fa; margin-bottom: 6px;">
								<i data-lucide="accessibility" style="width: 18px; height: 18px;"></i>
								<span>♿ Resumo em Linguagem Simplificada (Apoio Cognitivo W3C COGA)</span>
							</label>
							<p style="font-size: 0.8rem; color: #94a3b8; margin: 0 0 8px;">Versão acessível e objetiva com frases curtas e tópicos diretos para alunos neurodivergentes (TDAH, Dislexia, TEA).</p>
							<textarea id="lesson-f-coga" rows="2" class="form-control" style="width: 100%; padding: 10px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main); font-size: 0.88rem;" placeholder="Ex: 1. Neste vídeo aprendemos a montar o circuito; 2. Próximo passo é conectar o cabo USB...">${escapeHtml(lesson?.simplified_text || '')}</textarea>
						</div>

						<div class="form-group">
							<label style="display: flex; align-items: center; gap: 10px; font-weight: 600; cursor: pointer; color: var(--text-main);">
								<input type="checkbox" id="lesson-f-lock" value="yes" ${lesson?.sequential_lock ? 'checked' : ''} style="width: 18px; height: 18px; accent-color: var(--primary);">
								<span>Trava Sequencial: Exigir conclusão da aula anterior antes de liberar esta aula ao aluno</span>
							</label>
						</div>
					</div>

					<!-- TAB 2: Materials & Links -->
					<div id="pane-tab-materials" class="lesson-pane" style="display: none;">
						<!-- Anexos de Download -->
						<div style="margin-bottom: 24px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 20px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
								<div>
									<h4 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
										<i data-lucide="download-cloud" style="color: var(--primary);"></i>
										<span>Materiais & Arquivos para Download dos Alunos</span>
									</h4>
									<p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0;">Anexe apostilas (PDF), slides, datasets (.ZIP) ou códigos para download direto na aula.</p>
								</div>
							</div>

							<!-- Lista Atual de Anexos -->
							<div id="lesson-attachments-list" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;"></div>

							<!-- Formulário de Adicionar Anexo -->
							<div style="background: rgba(0,0,0,0.2); border: 1px dashed var(--border); border-radius: 8px; padding: 14px;">
								<div style="font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 8px;">Adicionar Novo Arquivo para Download:</div>
								<div style="display: grid; grid-template-columns: 2fr 3fr 1fr 1fr auto; gap: 8px; align-items: center;">
									<input type="text" id="new-att-name" placeholder="Nome (Ex: Apostila PDF)" class="form-control" style="padding: 8px 10px; font-size: 0.85rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
									<input type="url" id="new-att-url" placeholder="URL do Arquivo (https://...)" class="form-control" style="padding: 8px 10px; font-size: 0.85rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
									<select id="new-att-type" class="form-control" style="padding: 8px; font-size: 0.85rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
										<option value="pdf">PDF</option>
										<option value="zip">ZIP / Arquivo</option>
										<option value="doc">Documento</option>
										<option value="audio">Áudio MP3</option>
										<option value="other">Outro</option>
									</select>
									<input type="text" id="new-att-size" placeholder="Tam (ex: 3.2MB)" class="form-control" style="padding: 8px; font-size: 0.85rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
									<button type="button" id="btn-add-attachment" class="btn btn-primary btn-sm" style="padding: 8px 14px; white-space: nowrap; display: flex; align-items: center; gap: 4px;">
										<i data-lucide="plus"></i> Adicionar
									</button>
								</div>
							</div>
						</div>

						<!-- Links Complementares -->
						<div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 20px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
								<div>
									<h4 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
										<i data-lucide="external-link" style="color: var(--accent);"></i>
										<span>Links Complementares & Leituras Sugeridas</span>
									</h4>
									<p style="font-size: 0.82rem; color: var(--text-muted); margin: 2px 0 0;">Indique artigos externos, documentações oficiais ou repositórios.</p>
								</div>
							</div>

							<!-- Lista Atual de Links -->
							<div id="lesson-links-list" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;"></div>

							<!-- Formulário de Adicionar Link -->
							<div style="background: rgba(0,0,0,0.2); border: 1px dashed var(--border); border-radius: 8px; padding: 14px;">
								<div style="font-size: 0.82rem; font-weight: 700; color: #cbd5e1; margin-bottom: 8px;">Adicionar Novo Link Útil:</div>
								<div style="display: grid; grid-template-columns: 2fr 3fr 2fr auto; gap: 8px; align-items: center;">
									<input type="text" id="new-lnk-title" placeholder="Título (Ex: Documentação React)" class="form-control" style="padding: 8px 10px; font-size: 0.85rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
									<input type="url" id="new-lnk-url" placeholder="URL (https://...)" class="form-control" style="padding: 8px 10px; font-size: 0.85rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
									<input type="text" id="new-lnk-desc" placeholder="Descrição curta (opcional)" class="form-control" style="padding: 8px 10px; font-size: 0.85rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
									<button type="button" id="btn-add-link" class="btn btn-primary btn-sm" style="padding: 8px 14px; white-space: nowrap; display: flex; align-items: center; gap: 4px;">
										<i data-lucide="plus"></i> Adicionar
									</button>
								</div>
							</div>
						</div>
					</div>

					<!-- TAB 3: Quiz Avaliativo -->
					<div id="pane-tab-quiz" class="lesson-pane" style="display: none;">
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 22px;">
							<h4 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
								<i data-lucide="help-circle" style="color: #c084fc;"></i>
								<span>Vincular Avaliação / Quiz de Fixação</span>
							</h4>
							<p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">
								O quiz avalia a retenção do aluno ao final da aula com perguntas objetivas e controle de ansiedade pedagógica.
							</p>

							<div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 20px;">
								<label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
									<input type="radio" name="quiz_choice" value="none" ${!lesson?.quiz_id ? 'checked' : ''} style="width: 18px; height: 18px; accent-color: var(--primary);">
									<span style="font-size: 0.92rem; font-weight: 600; color: var(--text-main);">Nenhum quiz vinculado a esta aula</span>
								</label>

								<label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
									<input type="radio" name="quiz_choice" value="existing" ${lesson?.quiz_id ? 'checked' : ''} style="width: 18px; height: 18px; accent-color: var(--primary);">
									<span style="font-size: 0.92rem; font-weight: 600; color: var(--text-main);">Vincular Quiz existente do sistema</span>
								</label>

								<div id="box-quiz-existing" style="display: ${lesson?.quiz_id ? 'block' : 'none'}; padding-left: 28px;">
									<select id="lesson-select-quiz" class="form-control" style="width: 100%; max-width: 500px; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
										<option value="">Selecione um Quiz Cadastrado</option>
										${quizzes.map(q => `
											<option value="${q.id}" ${q.id === lesson?.quiz_id ? 'selected' : ''}>
												${escapeHtml(q.title)} (${q.questions_count || 0} questões &bull; Mín: ${q.passing_score}%)
											</option>
										`).join('')}
									</select>
								</div>

								<label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
									<input type="radio" name="quiz_choice" value="new" style="width: 18px; height: 18px; accent-color: var(--primary);">
									<span style="font-size: 0.92rem; font-weight: 600; color: var(--text-main);">Criar Novo Quiz para esta aula</span>
								</label>

								<div id="box-quiz-new" style="display: none; padding-left: 28px; background: rgba(168, 85, 247, 0.06); border: 1px solid rgba(168, 85, 247, 0.25); border-radius: 8px; padding: 16px; margin-top: 6px;">
									<div class="form-group" style="margin-bottom: 12px;">
										<label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Título do Novo Quiz *</label>
										<input type="text" id="new-quiz-title" class="form-control" placeholder="Ex: Quiz de Fixação da Aula" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
									</div>
									<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
										<div>
											<label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Nota Mínima de Corte (%)</label>
											<input type="number" id="new-quiz-passing" value="70" min="0" max="100" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
										</div>
										<div>
											<label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Tempo Limite (minutos)</label>
											<input type="number" id="new-quiz-time" value="15" min="1" max="180" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- TAB 4: Atividade Prática -->
					<div id="pane-tab-activity" class="lesson-pane" style="display: none;">
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 22px;">
							<h4 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
								<i data-lucide="file-check" style="color: #60a5fa;"></i>
								<span>Vincular Atividade Prática / Entrega de Projeto</span>
							</h4>
							<p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">
								Exija que os alunos entreguem um projeto ou relatório prático desta aula, avaliado pelos mestres na Bancada de Correção.
							</p>

							<div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 20px;">
								<label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
									<input type="radio" name="asg_choice" value="none" ${!lesson?.assignment_id ? 'checked' : ''} style="width: 18px; height: 18px; accent-color: var(--primary);">
									<span style="font-size: 0.92rem; font-weight: 600; color: var(--text-main);">Nenhuma atividade prática obrigatória nesta aula</span>
								</label>

								<label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
									<input type="radio" name="asg_choice" value="existing" ${lesson?.assignment_id ? 'checked' : ''} style="width: 18px; height: 18px; accent-color: var(--primary);">
									<span style="font-size: 0.92rem; font-weight: 600; color: var(--text-main);">Vincular Atividade existente da turma</span>
								</label>

								<div id="box-asg-existing" style="display: ${lesson?.assignment_id ? 'block' : 'none'}; padding-left: 28px;">
									<select id="lesson-select-asg" class="form-control" style="width: 100%; max-width: 500px; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
										<option value="">Selecione uma Atividade Cadastrada</option>
										${assignments.map(a => `
											<option value="${a.id}" ${a.id === lesson?.assignment_id ? 'selected' : ''}>
												${escapeHtml(a.title)} (${a.max_score || 100} pts &bull; Prazo: ${a.due_date || 'Sem prazo'})
											</option>
										`).join('')}
									</select>
								</div>

								<label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
									<input type="radio" name="asg_choice" value="new" style="width: 18px; height: 18px; accent-color: var(--primary);">
									<span style="font-size: 0.92rem; font-weight: 600; color: var(--text-main);">Criar Nova Atividade Prática vinculada a esta aula</span>
								</label>

								<div id="box-asg-new" style="display: none; padding-left: 28px; background: rgba(59, 130, 246, 0.06); border: 1px solid rgba(59, 130, 246, 0.25); border-radius: 8px; padding: 16px; margin-top: 6px;">
									<div class="form-group" style="margin-bottom: 12px;">
										<label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Título do Projeto / Atividade *</label>
										<input type="text" id="new-asg-title" class="form-control" placeholder="Ex: Exercício Prático: Implementando a Solução" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
									</div>
									<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 12px;">
										<div>
											<label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Prazo Final de Entrega</label>
											<input type="date" id="new-asg-due" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
										</div>
										<div>
											<label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Pontuação Máxima (Pontos)</label>
											<input type="number" id="new-asg-score" value="100" min="1" max="1000" class="form-control" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);">
										</div>
									</div>
									<div class="form-group">
										<label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 4px;">Instruções e Rúbrica de Avaliação</label>
										<textarea id="new-asg-prompt" rows="3" class="form-control" placeholder="Descreva os requisitos práticos exigidos e critérios de correção..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-surface); color: var(--text-main);"></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Modal Footer Actions -->
					<div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 18px; margin-top: auto;">
						<span style="font-size: 0.82rem; color: var(--text-muted);"><i data-lucide="info" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;"></i> Salve as alterações para persistir todos os dados, anexos e vínculos.</span>
						<div style="display: flex; gap: 12px;">
							<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
							<button type="submit" class="btn btn-primary" id="btn-save-lesson-station" style="display: flex; align-items: center; gap: 8px;">
								<i data-lucide="save"></i>
								<span>${isEditing ? 'Salvar Alterações' : 'Cadastrar Aula'}</span>
							</button>
						</div>
					</div>
				</form>
			</div>
		`;

		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		// Tab switching logic
		modal.querySelectorAll('.lesson-modal-tabs .tab-link').forEach(btn => {
			btn.addEventListener('click', () => {
				modal.querySelectorAll('.lesson-modal-tabs .tab-link').forEach(b => {
					b.classList.remove('active');
					b.style.borderBottomColor = 'transparent';
					b.style.color = 'var(--text-muted)';
				});
				btn.classList.add('active');
				btn.style.borderBottomColor = 'var(--primary)';
				btn.style.color = '#fff';

				const target = btn.getAttribute('data-tab');
				modal.querySelectorAll('.lesson-pane').forEach(p => p.style.display = 'none');
				const pane = modal.querySelector('#pane-' + target);
				if (pane) pane.style.display = 'block';
				if (window.lucide) window.lucide.createIcons();
			});
		});

		// Quiz radios toggle
		modal.querySelectorAll('input[name="quiz_choice"]').forEach(r => {
			r.addEventListener('change', () => {
				const boxEx = modal.querySelector('#box-quiz-existing');
				const boxNew = modal.querySelector('#box-quiz-new');
				if (boxEx) boxEx.style.display = r.value === 'existing' ? 'block' : 'none';
				if (boxNew) boxNew.style.display = r.value === 'new' ? 'block' : 'none';
			});
		});

		// Activity radios toggle
		modal.querySelectorAll('input[name="asg_choice"]').forEach(r => {
			r.addEventListener('change', () => {
				const boxEx = modal.querySelector('#box-asg-existing');
				const boxNew = modal.querySelector('#box-asg-new');
				if (boxEx) boxEx.style.display = r.value === 'existing' ? 'block' : 'none';
				if (boxNew) boxNew.style.display = r.value === 'new' ? 'block' : 'none';
			});
		});

		function updateFilesBadge() {
			const badge = modal.querySelector('#badge-total-files');
			if (badge) badge.textContent = attachments.length + links.length;
		}

		// Render attachment list
		function renderAttachmentRows() {
			const container = modal.querySelector('#lesson-attachments-list');
			if (!container) return;
			if (attachments.length === 0) {
				container.innerHTML = `<div style="font-size: 0.82rem; color: var(--text-muted); font-style: italic; padding: 6px 0;">Nenhum arquivo anexado para download.</div>`;
				return;
			}
			container.innerHTML = attachments.map((att, idx) => `
				<div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
					<div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
						<i data-lucide="${att.type === 'pdf' ? 'file-text' : (att.type === 'zip' ? 'archive' : 'paperclip')}" style="width: 16px; height: 16px; color: #60a5fa; flex-shrink: 0;"></i>
						<span style="font-weight: 600; color: var(--text-main);">${escapeHtml(att.name)}</span>
						<span style="color: var(--text-muted); font-size: 0.75rem;">(${att.size || att.type || 'Arquivo'})</span>
					</div>
					<div style="display: flex; align-items: center; gap: 8px;">
						<a href="${escapeHtml(att.url)}" target="_blank" class="btn-icon btn-sm" title="Abrir link" style="color: #60a5fa;"><i data-lucide="external-link" style="width: 14px; height: 14px;"></i></a>
						<button type="button" class="btn-icon btn-sm btn-del-att" data-idx="${idx}" title="Remover anexo" style="color: #f87171; background: none; border: none; cursor: pointer;"><i data-lucide="trash-2" style="width: 14px; height: 14px;"></i></button>
					</div>
				</div>
			`).join('');
			if (window.lucide) window.lucide.createIcons();

			container.querySelectorAll('.btn-del-att').forEach(b => {
				b.onclick = () => {
					const idx = parseInt(b.getAttribute('data-idx'), 10);
					attachments.splice(idx, 1);
					renderAttachmentRows();
					updateFilesBadge();
				};
			});
		}

		// Render link list
		function renderLinkRows() {
			const container = modal.querySelector('#lesson-links-list');
			if (!container) return;
			if (links.length === 0) {
				container.innerHTML = `<div style="font-size: 0.82rem; color: var(--text-muted); font-style: italic; padding: 6px 0;">Nenhum link complementar adicionado.</div>`;
				return;
			}
			container.innerHTML = links.map((lnk, idx) => `
				<div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
					<div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
						<i data-lucide="link" style="width: 16px; height: 16px; color: var(--accent); flex-shrink: 0;"></i>
						<span style="font-weight: 600; color: var(--text-main);">${escapeHtml(lnk.title)}</span>
						${lnk.description ? `<span style="color: var(--text-muted); font-size: 0.78rem;">&bull; ${escapeHtml(lnk.description)}</span>` : ''}
					</div>
					<div style="display: flex; align-items: center; gap: 8px;">
						<a href="${escapeHtml(lnk.url)}" target="_blank" class="btn-icon btn-sm" title="Abrir link" style="color: #60a5fa;"><i data-lucide="external-link" style="width: 14px; height: 14px;"></i></a>
						<button type="button" class="btn-icon btn-sm btn-del-lnk" data-idx="${idx}" title="Remover link" style="color: #f87171; background: none; border: none; cursor: pointer;"><i data-lucide="trash-2" style="width: 14px; height: 14px;"></i></button>
					</div>
				</div>
			`).join('');
			if (window.lucide) window.lucide.createIcons();

			container.querySelectorAll('.btn-del-lnk').forEach(b => {
				b.onclick = () => {
					const idx = parseInt(b.getAttribute('data-idx'), 10);
					links.splice(idx, 1);
					renderLinkRows();
					updateFilesBadge();
				};
			});
		}

		renderAttachmentRows();
		renderLinkRows();

		// Add Attachment Button
		modal.querySelector('#btn-add-attachment').onclick = () => {
			const nameIn = modal.querySelector('#new-att-name');
			const urlIn = modal.querySelector('#new-att-url');
			const typeIn = modal.querySelector('#new-att-type');
			const sizeIn = modal.querySelector('#new-att-size');

			const name = nameIn.value.trim();
			const url = urlIn.value.trim();
			if (!name || !url) {
				showToast('Informe o nome e a URL do arquivo.', 'danger');
				return;
			}
			attachments.push({
				name: name,
				url: url,
				type: typeIn.value,
				size: sizeIn.value.trim() || 'Arquivo'
			});
			nameIn.value = '';
			urlIn.value = '';
			sizeIn.value = '';
			renderAttachmentRows();
			updateFilesBadge();
			showToast('Arquivo adicionado à lista!', 'success');
		};

		// Add Link Button
		modal.querySelector('#btn-add-link').onclick = () => {
			const titleIn = modal.querySelector('#new-lnk-title');
			const urlIn = modal.querySelector('#new-lnk-url');
			const descIn = modal.querySelector('#new-lnk-desc');

			const title = titleIn.value.trim();
			const url = urlIn.value.trim();
			if (!title || !url) {
				showToast('Informe o título e a URL do link.', 'danger');
				return;
			}
			links.push({
				title: title,
				url: url,
				description: descIn.value.trim()
			});
			titleIn.value = '';
			urlIn.value = '';
			descIn.value = '';
			renderLinkRows();
			updateFilesBadge();
			showToast('Link adicionado à lista!', 'success');
		};

		// Close Modal
		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		// Submit form
		modal.querySelector('#form-lesson-station').onsubmit = async (e) => {
			e.preventDefault();
			const saveBtn = modal.querySelector('#btn-save-lesson-station');
			saveBtn.disabled = true;
			saveBtn.innerHTML = '<div class="spinner" style="width:16px;height:16px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite;display:inline-block;"></div> Salvando...';

			try {
				let finalQuizId = 0;
				const quizChoice = modal.querySelector('input[name="quiz_choice"]:checked')?.value || 'none';
				if (quizChoice === 'existing') {
					finalQuizId = parseInt(modal.querySelector('#lesson-select-quiz')?.value, 10) || 0;
				} else if (quizChoice === 'new') {
					const newQTitle = modal.querySelector('#new-quiz-title')?.value.trim() || ('Quiz: ' + modal.querySelector('#lesson-f-title').value.trim());
					const newQPass = parseFloat(modal.querySelector('#new-quiz-passing')?.value) || 70;
					const newQTime = parseInt(modal.querySelector('#new-quiz-time')?.value, 10) || 15;
					const createdQuiz = await fetchAPI('quizzes', 'POST', {
						title: newQTitle,
						passing_score: newQPass,
						time_limit_minutes: newQTime,
						is_mandatory: 1
					});
					finalQuizId = createdQuiz.id;
				}

				let finalAsgId = 0;
				const asgChoice = modal.querySelector('input[name="asg_choice"]:checked')?.value || 'none';
				if (asgChoice === 'existing') {
					finalAsgId = parseInt(modal.querySelector('#lesson-select-asg')?.value, 10) || 0;
				} else if (asgChoice === 'new') {
					const newATitle = modal.querySelector('#new-asg-title')?.value.trim() || ('Atividade: ' + modal.querySelector('#lesson-f-title').value.trim());
					const newADue = modal.querySelector('#new-asg-due')?.value || '';
					const newAScore = parseFloat(modal.querySelector('#new-asg-score')?.value) || 100;
					const newAPrompt = modal.querySelector('#new-asg-prompt')?.value.trim() || '';
					const createdAsg = await fetchAPI('assignments', 'POST', {
						title: newATitle,
						cohort_id: state.activeCohortId,
						module_id: moduleId,
						due_date: newADue,
						max_score: newAScore,
						submission_types: ['file_upload', 'external_link', 'text_essay'],
						prompt: newAPrompt
					});
					finalAsgId = createdAsg.id;
				}

				const payload = {
					module_id: moduleId,
					title: modal.querySelector('#lesson-f-title').value.trim(),
					video_url: modal.querySelector('#lesson-f-video').value.trim(),
					audio_url: modal.querySelector('#lesson-f-audio').value.trim(),
					duration_minutes: parseInt(modal.querySelector('#lesson-f-duration').value, 10) || 15,
					content: modal.querySelector('#lesson-f-content').value.trim(),
					simplified_text: modal.querySelector('#lesson-f-coga').value.trim(),
					sequential_lock: modal.querySelector('#lesson-f-lock').checked,
					quiz_id: finalQuizId,
					assignment_id: finalAsgId,
					attachments: attachments,
					links: links
				};

				await fetchAPI(isEditing ? `lessons/${lessonId}` : 'lessons', 'POST', payload);

				showToast(isEditing ? 'Aula atualizada com sucesso!' : 'Aula cadastrada com sucesso!', 'success');
				modal.remove();
				renderCurriculum();
			} catch (err) {
				saveBtn.disabled = false;
				saveBtn.innerHTML = `<i data-lucide="save"></i> ${isEditing ? 'Salvar Alterações' : 'Cadastrar Aula'}`;
				if (window.lucide) window.lucide.createIcons();
			}
		};
	}

	async function openCreateLessonModal(moduleId, moduleTitle) {
		openLessonModal({ moduleId, moduleTitle });
	}

	async function openEditLessonModal(lessonId) {
		openLessonModal({ lessonId });
	}

	async function deleteLesson(lessonId, title) {
		if (!confirm(`Tem certeza que deseja excluir a aula "${title}"?`)) return;

		try {
			await fetchAPI(`lessons/${lessonId}`, 'DELETE');
			showToast('Aula excluída com sucesso.', 'success');
			renderCurriculum();
		} catch (e) {
			showToast('Erro ao excluir aula.', 'danger');
		}
	}

	/* ==========================================================================
	   3. Immersive Classroom Player View
	   ========================================================================== */
	async function loadLesson(lessonId) {
		renderLoading();
		try {
			const lesson = await fetchAPI(`lessons/${lessonId}`);
			state.activeLesson = lesson;
			renderPlayer();
		} catch (e) {
			renderError('Não foi possível carregar os detalhes da aula.');
		}
	}

	function renderPlayer() {
		if (!state.activeLesson) {
			navigate('curriculum');
			return;
		}

		const l = state.activeLesson;
		const canManage = canManageActiveCohort();
		state.secondsWatched = 0;

		contentRoot.innerHTML = `
			<div class="player-container">
				<div class="player-main">
					<!-- Video Container -->
					<div class="video-frame-box">
						${l.video_url 
							? `<iframe src="${l.video_url}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`
							: `<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted);">
								<i data-lucide="video-off" style="width: 48px; height: 48px; margin-bottom: 12px;"></i>
								<p>Esta aula possui formato textual ou materiais complementares.</p>
							   </div>`}
					</div>

					<!-- Lesson Control Bar -->
					<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
						<div>
							<h1 style="font-size: 1.5rem; font-weight: 700;">${l.title}</h1>
							<span style="color: var(--text-muted); font-size: 0.85rem;" id="player-watch-timer">Tempo ativo: 00:00</span>
						</div>
						<div style="display: flex; gap: 12px;">
							${l.quiz_id ? `<button class="btn btn-secondary" id="btn-open-quiz"><i data-lucide="help-circle"></i> Fazer Quiz</button>` : (canManage ? `<button class="btn btn-secondary" id="btn-setup-lesson-quiz"><i data-lucide="help-circle"></i> Criar Quiz</button>` : '')}
							${l.assignment_id ? `<button class="btn btn-secondary" id="btn-open-assignment"><i data-lucide="upload"></i> Entregar Trabalho</button>` : ''}
							<button class="btn btn-primary" id="btn-complete-lesson">
								<i data-lucide="check"></i> Concluir Aula
							</button>
						</div>
					</div>

					<!-- Linked Practical Assignment Card (Pedagógica) -->
					${l.assignment_id ? `
						<div class="card" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(99, 102, 241, 0.12) 100%); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 18px 22px; margin-top: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
								<span style="font-size: 11px; font-weight: 800; background: var(--primary); color: #fff; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">
									<i data-lucide="award" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle;"></i> Atividade Prática Vinculada a Esta Aula
								</span>
								<button type="button" class="btn btn-sm btn-primary" id="btn-submit-lesson-activity" data-asg-id="${l.assignment_id}">
									<i data-lucide="send"></i> Fazer Entrega / Enviar Projeto
								</button>
							</div>
							<h4 style="font-size: 1.05rem; font-weight: 700; color: #fff; margin: 4px 0;">${escapeHtml(l.assignment_title || 'Atividade Prática Aplicada')}</h4>
							<p style="font-size: 0.88rem; color: #cbd5e1; margin: 0;">${escapeHtml(l.assignment_prompt || 'Complete o exercício prático correspondente a esta aula para validar seu aprendizado.')}</p>
						</div>
					` : ''}

					<!-- Content Tabs (W3C COGA Dual Presentation & Support Materials) -->
					<div class="lesson-tabs-nav">
						<button class="tab-btn ${!l.accommodations.default_simplified ? 'active' : ''}" data-tab="full-text">
							Texto Completo
						</button>
						<button class="tab-btn ${l.accommodations.default_simplified ? 'active' : ''}" data-tab="simplified-text">
							<i data-lucide="sparkles"></i> Linguagem Simplificada (COGA)
						</button>
						${(l.attachments && l.attachments.length > 0) ? `
							<button class="tab-btn" data-tab="materials">
								<i data-lucide="paperclip"></i> Materiais (${l.attachments.length})
							</button>
						` : ''}
						${(l.links && l.links.length > 0) ? `
							<button class="tab-btn" data-tab="links">
								<i data-lucide="link"></i> Links Úteis (${l.links.length})
							</button>
						` : ''}
						${l.transcription ? `<button class="tab-btn" data-tab="transcription">Transcrição em Áudio</button>` : ''}
						<button class="tab-btn" data-tab="lesson-qa">Dúvidas da Aula</button>
					</div>

					<div class="lesson-tab-content">
						<div id="tab-pane-full-text" class="tab-pane" style="${l.accommodations.default_simplified ? 'display: none;' : ''}">
							${l.content || '<p style="color:var(--text-muted);">Nenhum conteúdo textual fornecido para esta aula.</p>'}
						</div>
						<div id="tab-pane-simplified-text" class="tab-pane" style="${!l.accommodations.default_simplified ? 'display: none;' : ''}">
							<div style="background: rgba(6, 182, 212, 0.08); border: 1px solid rgba(6, 182, 212, 0.2); border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px; font-size: 0.9rem; color: #22d3ee;">
								<i data-lucide="info"></i> Versão otimizada com frases curtas, tópicos objetivos e suporte visual para reduzir sobrecarga cognitiva.
							</div>
							${l.simplified_text || '<p style="color:var(--text-muted);">Esta aula ainda não possui versão simplificada cadastrada.</p>'}
						</div>
						
						<!-- Materials / Attachments Pane -->
						<div id="tab-pane-materials" class="tab-pane" style="display: none;">
							<h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 16px; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
								<i data-lucide="folder-down" style="color: var(--primary);"></i>
								<span>Arquivos e Materiais para Download</span>
							</h3>
							${(l.attachments && l.attachments.length > 0) ? `
								<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px;">
									${l.attachments.map(att => `
										<div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
											<div style="display: flex; align-items: center; gap: 12px; overflow: hidden;">
												<div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(37,99,235,0.12); display: flex; align-items: center; justify-content: center; color: var(--primary); flex-shrink: 0;">
													<i data-lucide="file-text"></i>
												</div>
												<div style="overflow: hidden;">
													<strong style="display: block; font-size: 0.92rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(att.name || 'Arquivo da Aula')}</strong>
													<span style="font-size: 0.78rem; color: var(--text-muted);">${att.size ? att.size + ' &bull; ' : ''}${escapeHtml(att.type || 'Documento')}</span>
												</div>
											</div>
											<a href="${escapeHtml(att.url)}" target="_blank" rel="noopener noreferrer" download class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0; text-decoration: none;">
												<i data-lucide="download" style="width: 14px; height: 14px;"></i> Baixar
											</a>
										</div>
									`).join('')}
								</div>
							` : '<p style="color:var(--text-muted);">Nenhum arquivo anexado a esta aula.</p>'}
						</div>

						<!-- Links Pane -->
						<div id="tab-pane-links" class="tab-pane" style="display: none;">
							<h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 16px; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
								<i data-lucide="globe" style="color: #0284c7;"></i>
								<span>Links Complementares & Bibliografia</span>
							</h3>
							${(l.links && l.links.length > 0) ? `
								<div style="display: flex; flex-direction: column; gap: 12px;">
									${l.links.map(lnk => `
										<div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 16px; display: flex; justify-content: space-between; align-items: center; gap: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
											<div>
												<h4 style="font-size: 0.98rem; font-weight: 700; color: var(--text-main); margin: 0 0 4px 0;">${escapeHtml(lnk.title || 'Link Recomendado')}</h4>
												${lnk.description ? `<p style="font-size: 0.85rem; color: var(--text-muted); margin: 0 0 6px 0;">${escapeHtml(lnk.description)}</p>` : ''}
												<span style="font-size: 0.78rem; color: var(--primary); font-family: monospace;">${escapeHtml(lnk.url)}</span>
											</div>
											<a href="${escapeHtml(lnk.url)}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0; text-decoration: none;">
												<i data-lucide="external-link" style="width: 14px; height: 14px;"></i> Acessar
											</a>
										</div>
									`).join('')}
								</div>
							` : '<p style="color:var(--text-muted);">Nenhum link complementar indicado para esta aula.</p>'}
						</div>

						${l.transcription ? `<div id="tab-pane-transcription" class="tab-pane" style="display: none;">${l.transcription}</div>` : ''}
						<div id="tab-pane-lesson-qa" class="tab-pane" style="display: none;">
							<div id="lesson-qa-container">Carregando dúvidas da aula...</div>
						</div>
					</div>
				</div>

				<!-- Sidebar Drawer with Playlist -->
				<div class="player-sidebar">
					<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px;">
						<h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px;">Plano da Trilha</h3>
						<button class="btn btn-secondary btn-block btn-sm" id="btn-back-curriculum" style="margin-bottom: 16px;">
							<i data-lucide="arrow-left"></i> Voltar à Lista de Módulos
						</button>
						<p style="font-size: 0.8rem; color: var(--text-muted);">Seu progresso é gravado automaticamente a cada segundo de aula assistida.</p>
					</div>
				</div>
			</div>
		`;

		if (window.lucide) window.lucide.createIcons();

		// Watch time tracking (every 1s, syncs every 15s)
		const timerText = document.getElementById('player-watch-timer');
		state.watchTimer = setInterval(() => {
			state.secondsWatched++;
			const mins = String(Math.floor(state.secondsWatched / 60)).padStart(2, '0');
			const secs = String(state.secondsWatched % 60).padStart(2, '0');
			if (timerText) timerText.textContent = `Tempo ativo: ${mins}:${secs}`;

			// Sync progress periodically
			if (state.secondsWatched % 20 === 0) {
				fetchAPI('progress', 'POST', {
					lesson_id: l.id,
					cohort_id: state.activeCohortId,
					percent: 50.0,
					seconds_watched: state.secondsWatched
				});
			}
		}, 1000);

		// Tab switching
		document.querySelectorAll('.lesson-tabs-nav .tab-btn').forEach(tBtn => {
			tBtn.addEventListener('click', () => {
				document.querySelectorAll('.lesson-tabs-nav .tab-btn').forEach(b => b.classList.remove('active'));
				tBtn.classList.add('active');

				const tab = tBtn.getAttribute('data-tab');
				document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
				const target = document.getElementById('tab-pane-' + tab);
				if (target) target.style.display = 'block';

				if (tab === 'lesson-qa') {
					loadLessonDiscussions(l.id);
				}
			});
		});

		// Buttons
		const backBtn = document.getElementById('btn-back-curriculum');
		if (backBtn) backBtn.addEventListener('click', () => navigate('curriculum'));

		const completeBtn = document.getElementById('btn-complete-lesson');
		if (completeBtn) {
			completeBtn.addEventListener('click', async () => {
				await fetchAPI('progress', 'POST', {
					lesson_id: l.id,
					cohort_id: state.activeCohortId,
					percent: 100.0,
					seconds_watched: state.secondsWatched,
					force_complete: true
				});
				showToast('Aula concluída com sucesso! +25 XP', 'success');
				navigate('curriculum');
			});
		}

		const quizBtn = document.getElementById('btn-open-quiz');
		if (quizBtn) quizBtn.addEventListener('click', () => openQuizModal(l.quiz_id, l.id));

		const setupQuizBtn = document.getElementById('btn-setup-lesson-quiz');
		if (setupQuizBtn) setupQuizBtn.addEventListener('click', () => openManageQuizModal(0, l.id, l.title));

		const assignBtn = document.getElementById('btn-open-assignment');
		if (assignBtn) assignBtn.addEventListener('click', () => openAssignmentModal(l.assignment_id));

		const submitActBtn = document.getElementById('btn-submit-lesson-activity');
		if (submitActBtn) submitActBtn.addEventListener('click', () => openAssignmentModal(l.assignment_id));
	}

	async function loadLessonDiscussions(lessonId) {
		const container = document.getElementById('lesson-qa-container');
		if (!container) return;
		try {
			const topics = await fetchAPI(`cohorts/${state.activeCohortId}/discussions?lesson_id=${lessonId}`);
			let html = `
				<div style="margin-bottom: 18px; display: flex; gap: 10px;">
					<input type="text" id="input-lesson-qa-question" class="form-control" placeholder="Digite sua dúvida sobre esta aula..." style="flex: 1; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
					<button type="button" class="btn btn-primary btn-sm" id="btn-ask-lesson-qa">Enviar Pergunta</button>
				</div>
			`;
			if (topics.length === 0) {
				html += `
					<div style="text-align: center; padding: 24px; color: var(--text-muted);">
						<p>Nenhuma dúvida enviada para esta aula ainda. Seja o primeiro a perguntar!</p>
					</div>
				`;
			} else {
				html += `<div style="display: flex; flex-direction: column; gap: 16px;">`;
				topics.forEach(t => {
					html += `
						<div class="card" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
								<strong style="color: var(--primary);">${escapeHtml(t.display_name)}</strong>
								<span style="font-size: 0.75rem; color: var(--text-muted);">${t.created_at}</span>
							</div>
							<p style="font-size: 0.95rem;">${escapeHtml(t.content)}</p>
						</div>
					`;
				});
				html += `</div>`;
			}
			container.innerHTML = html;

			const askBtn = document.getElementById('btn-ask-lesson-qa');
			const askInput = document.getElementById('input-lesson-qa-question');
			if (askBtn && askInput) {
				askBtn.addEventListener('click', async () => {
					const qText = askInput.value.trim();
					if (!qText) return;
					askBtn.disabled = true;
					try {
						await fetchAPI(`cohorts/${state.activeCohortId}/discussions`, 'POST', {
							lesson_id: lessonId,
							content: qText
						});
						showToast('Dúvida enviada para a aula!', 'success');
						loadLessonDiscussions(lessonId);
					} catch (err) {
						askBtn.disabled = false;
					}
				});
			}
		} catch (e) {}
	}

	/* ==========================================================================
	   4. Anti-Anxiety Quiz Runner Modal (W3C COGA Pausa Respiratória)
	   ========================================================================== */
	async function openQuizModal(quizId, lessonId) {
		const targetId = quizId || 1;

		const modal = document.createElement('div');
		modal.id = 'quiz-modal';
		modal.className = 'modal-backdrop';
		modal.style = `
			position: fixed; top: 0; left: 0; right: 0; bottom: 0;
			background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(8px);
			z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 20px;
		`;
		modal.innerHTML = `
			<div class="quiz-box card" style="width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 28px; box-shadow: var(--shadow-lg);">
				<div style="text-align: center; padding: 40px 20px;">
					<div class="spinner" style="margin: 0 auto 16px; width: 36px; height: 36px; border: 3px solid rgba(255,255,255,0.1); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
					<p style="color: var(--text-muted); font-size: 0.95rem;">Carregando avaliação...</p>
				</div>
			</div>
		`;
		document.body.appendChild(modal);

		try {
			const quiz = await fetchAPI(`quizzes/${targetId}`);
			if (!quiz || !quiz.questions || quiz.questions.length === 0) {
				throw new Error('Nenhuma questão cadastrada para este quiz.');
			}

			let isPaused = false;
			let secondsRemaining = (quiz.time_limit_minutes || 15) * 60;
			let timerInterval = null;

			modal.innerHTML = `
				<div class="quiz-box card" style="width: 100%; max-width: 720px; max-height: 90vh; overflow-y: auto; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 28px; box-shadow: var(--shadow-lg);">
					<div class="quiz-header" style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border); padding-bottom: 18px; margin-bottom: 24px;">
						<div>
							<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
								<span style="font-size: 0.75rem; text-transform: uppercase; color: var(--primary); font-weight: 700; letter-spacing: 0.5px;">Avaliação Prática</span>
								${quiz.has_coga_accommodation ? `<span style="font-size: 0.72rem; padding: 2px 7px; border-radius: 4px; background: rgba(6, 182, 212, 0.15); color: #22d3ee; font-weight: 600;">+50% Tempo COGA</span>` : ''}
							</div>
							<h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-main); margin: 0;">${escapeHtml(quiz.title || 'Quiz de Verificação')}</h2>
							<p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Nota mínima para aprovação: <strong>${quiz.passing_score || 70}%</strong> (${quiz.questions.length} questões)</p>
						</div>
						<div style="display: flex; align-items: center; gap: 12px;">
							<div style="background: rgba(0,0,0,0.3); border: 1px solid var(--border); border-radius: 8px; padding: 6px 12px; display: flex; align-items: center; gap: 6px;">
								<i data-lucide="clock" style="width: 16px; height: 16px; color: var(--primary);"></i>
								<span id="quiz-timer" style="font-family: monospace; font-size: 1.1rem; font-weight: 700; color: var(--text-main);">--:--</span>
							</div>
							<button type="button" class="btn btn-secondary btn-sm" id="btn-quiz-breathe" style="font-size: 0.8rem; display: flex; align-items: center; gap: 5px;">
								<i data-lucide="pause-circle"></i> Pausa Anti-Ansiedade
							</button>
						</div>
					</div>

					<!-- Pause Prompt Screen (Anti-Anxiety W3C COGA) -->
					<div id="quiz-breathe-screen" style="display: none; text-align: center; padding: 48px 20px;">
						<div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(16, 185, 129, 0.2); color: #10b981; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
							<i data-lucide="wind" style="width: 36px; height: 36px;"></i>
						</div>
						<h3 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main);">Momento de Respiração</h3>
						<p style="color: var(--text-muted); max-width: 440px; margin: 8px auto 24px; line-height: 1.6;">O cronômetro está pausado. Não há pressa. Inspire pelo nariz em 4 segundos, segure por 2 e expire suavemente pela boca.</p>
						<button type="button" class="btn btn-primary" id="btn-resume-quiz">Retomar Avaliação</button>
					</div>

					<!-- Result Screen -->
					<div id="quiz-result-screen" style="display: none;"></div>

					<!-- Questions Form -->
					<form id="quiz-form" novalidate>
						<div class="questions-list" style="display: flex; flex-direction: column; gap: 20px;">
							${quiz.questions.map((q, qIndex) => `
								<div class="card quiz-q-item" data-q-id="${q.id}" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 10px; padding: 18px;">
									<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
										<strong style="color: var(--primary); font-size: 0.92rem;">Questão ${qIndex + 1} de ${quiz.questions.length}</strong>
										<span style="font-size: 0.8rem; color: var(--text-muted);">${q.points || 1} ponto(s)</span>
									</div>
									<p style="font-weight: 600; font-size: 1rem; color: var(--text-main); line-height: 1.5; margin-bottom: 14px;">${escapeHtml(q.question_text)}</p>
									<div class="options-group" style="display: flex; flex-direction: column; gap: 8px;">
										${(q.options || []).map(opt => `
											<label style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer; transition: background 0.15s; background: var(--bg-card);">
												<input type="radio" name="q_${q.id}" value="${opt.id}" style="margin-top: 4px; accent-color: var(--primary);">
												<span style="font-size: 0.92rem; color: var(--text-main); line-height: 1.4;">${escapeHtml(opt.text)}</span>
											</label>
										`).join('')}
									</div>
								</div>
							`).join('')}
						</div>

						<div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 28px; border-top: 1px solid var(--border); padding-top: 20px;">
							<button type="button" class="btn btn-secondary" id="btn-close-quiz">Cancelar</button>
							<button type="submit" class="btn btn-primary" id="btn-submit-quiz-answers">
								<i data-lucide="check"></i> Enviar Avaliação
							</button>
						</div>
					</form>
				</div>
			`;
			if (window.lucide) window.lucide.createIcons();

			const timerEl = modal.querySelector('#quiz-timer');
			const breatheScreen = modal.querySelector('#quiz-breathe-screen');
			const quizForm = modal.querySelector('#quiz-form');
			const resultScreen = modal.querySelector('#quiz-result-screen');

			const updateTimerDisplay = () => {
				const m = String(Math.floor(secondsRemaining / 60)).padStart(2, '0');
				const s = String(secondsRemaining % 60).padStart(2, '0');
				if (timerEl) timerEl.textContent = `${m}:${s}`;
			};
			updateTimerDisplay();

			timerInterval = setInterval(() => {
				if (!isPaused && secondsRemaining > 0) {
					secondsRemaining--;
					updateTimerDisplay();
					if (secondsRemaining === 0) {
						clearInterval(timerInterval);
						showToast('Tempo limite esgotado! Enviando respostas...', 'info');
						quizForm.requestSubmit();
					}
				}
			}, 1000);

			modal.querySelector('#btn-quiz-breathe').onclick = () => {
				isPaused = true;
				quizForm.style.display = 'none';
				breatheScreen.style.display = 'block';
				if (window.lucide) window.lucide.createIcons();
			};

			modal.querySelector('#btn-resume-quiz').onclick = () => {
				isPaused = false;
				quizForm.style.display = 'block';
				breatheScreen.style.display = 'none';
			};

			modal.querySelector('#btn-close-quiz').onclick = () => {
				clearInterval(timerInterval);
				modal.remove();
			};

			quizForm.onsubmit = async (e) => {
				e.preventDefault();

				const unanswered = quiz.questions.filter(q => !modal.querySelector(`input[name="q_${q.id}"]:checked`));
				if (unanswered.length > 0) {
					showToast(`Por favor, responda todas as questões (${unanswered.length} pendente(s)).`, 'warning');
					const firstPending = modal.querySelector(`.quiz-q-item[data-q-id="${unanswered[0].id}"]`);
					if (firstPending) {
						firstPending.scrollIntoView({ behavior: 'smooth', block: 'center' });
						firstPending.style.borderColor = '#f59e0b';
					}
					return;
				}

				clearInterval(timerInterval);
				const submitBtn = modal.querySelector('#btn-submit-quiz-answers');
				submitBtn.disabled = true;
				submitBtn.innerHTML = 'Avaliando...';

				const answers = {};
				quiz.questions.forEach(q => {
					const checked = modal.querySelector(`input[name="q_${q.id}"]:checked`);
					if (checked) answers[q.id] = checked.value;
				});

				try {
					const res = await fetchAPI(`quizzes/${quiz.id}/submit`, 'POST', {
						cohort_id: state.activeCohortId,
						answers: answers
					});

					quizForm.style.display = 'none';
					if (modal.querySelector('.quiz-header')) {
						modal.querySelector('.quiz-header').style.display = 'none';
					}

					resultScreen.style.display = 'block';
					resultScreen.innerHTML = `
						<div style="text-align: center; padding: 24px 10px;">
							<div style="width: 72px; height: 72px; border-radius: 50%; background: ${res.passed ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)'}; color: ${res.passed ? '#10b981' : '#f87171'}; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
								<i data-lucide="${res.passed ? 'award' : 'alert-circle'}" style="width: 36px; height: 36px;"></i>
							</div>
							<h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
								${res.passed ? 'Aprovado com Sucesso!' : 'Tentativa Concluída'}
							</h2>
							<div style="font-size: 2.2rem; font-weight: 800; color: ${res.passed ? '#10b981' : '#f87171'}; margin-bottom: 8px;">
								${res.percentage}%
							</div>
							<p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 24px;">
								${res.score} de ${res.total_points} pontos conquistados. Nota mínima: ${res.passing_score}%.
							</p>
							${res.xp_awarded > 0 ? `
								<div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; padding: 12px; display: inline-flex; align-items: center; gap: 8px; color: #fbbf24; font-weight: 600; margin-bottom: 24px;">
									<i data-lucide="zap"></i> Recompensa: +${res.xp_awarded} XP adicionados ao seu perfil!
								</div>
							` : ''}

							${res.feedback && res.feedback.length > 0 ? `
								<div style="text-align: left; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 24px; max-height: 220px; overflow-y: auto;">
									<strong style="display: block; margin-bottom: 10px; font-size: 0.9rem;">Gabarito e Justificativas:</strong>
									${res.feedback.map(f => `
										<div style="padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 0.85rem;">
											<div style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: ${f.is_correct ? '#10b981' : '#f87171'};">
												<i data-lucide="${f.is_correct ? 'check' : 'x'}" style="width: 14px; height: 14px;"></i>
												${escapeHtml(f.question_text || '')}
											</div>
											${f.explanation ? `<p style="margin: 4px 0 0 20px; color: var(--text-muted);">${escapeHtml(f.explanation)}</p>` : ''}
										</div>
									`).join('')}
								</div>
							` : ''}

							<button type="button" class="btn btn-primary" id="btn-finish-quiz-modal" style="padding: 10px 24px;">
								${res.passed ? 'Continuar Trilha' : 'Fechar e Revisar Aula'}
							</button>
						</div>
					`;
					if (window.lucide) window.lucide.createIcons();

					modal.querySelector('#btn-finish-quiz-modal').onclick = () => {
						modal.remove();
						renderCurriculum();
					};

					showToast(res.passed ? `Parabéns! Quiz aprovado (${res.percentage}%)` : `Quiz concluído com ${res.percentage}%. Tente novamente!`, res.passed ? 'success' : 'info');
				} catch (err) {
					submitBtn.disabled = false;
					submitBtn.innerHTML = '<i data-lucide="check"></i> Enviar Avaliação';
					showToast(err.message || 'Erro ao processar respostas do quiz.', 'danger');
				}
			};

		} catch (err) {
			modal.innerHTML = `
				<div class="card" style="width: 100%; max-width: 480px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 24px; text-align: center;">
					<i data-lucide="alert-triangle" style="width: 40px; height: 40px; color: #f87171; margin-bottom: 12px;"></i>
					<h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 8px;">Não foi possível carregar o Quiz</h3>
					<p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">${escapeHtml(err.message || 'Erro desconhecido.')}</p>
					<button type="button" class="btn btn-secondary" onclick="this.closest('.modal-backdrop').remove()">Fechar</button>
				</div>
			`;
			if (window.lucide) window.lucide.createIcons();
		}
	}

	async function openManageQuizModal(quizId, lessonId, lessonTitle) {
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.style = `
			position: fixed; top: 0; left: 0; right: 0; bottom: 0;
			background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(8px);
			z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 20px;
		`;
		modal.innerHTML = `
			<div class="modal-card card" style="max-width: 780px; width: 95%; max-height: 90vh; overflow-y: auto; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 24px;">
				<div style="text-align: center; padding: 30px 0;">
					<div class="spinner" style="margin: 0 auto 12px; width: 32px; height: 32px; border: 3px solid rgba(255,255,255,0.1); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
					<p style="color: var(--text-muted);">Carregando configurações do quiz...</p>
				</div>
			</div>
		`;
		document.body.appendChild(modal);

		try {
			const targetId = quizId || 1;
			const quiz = await fetchAPI(`quizzes/${targetId}`).catch(() => null) || {
				title: `Quiz de Fixação: ${lessonTitle || 'Aula'}`,
				passing_score: 70,
				time_limit_minutes: 15,
				questions: []
			};

			let questionsList = quiz.questions ? [...quiz.questions] : [];

			function renderEditor() {
				modal.innerHTML = `
					<div class="modal-card card" style="max-width: 820px; width: 95%; max-height: 90vh; overflow-y: auto; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 28px;">
						<div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border); padding-bottom: 16px; margin-bottom: 20px;">
							<div>
								<span style="font-size: 0.75rem; text-transform: uppercase; color: var(--primary); font-weight: 700;">Gestão Pedagógica</span>
								<h2 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin: 0 0 4px;">Configurar Quiz da Aula</h2>
								<span style="font-size: 0.85rem; color: var(--text-muted);">${escapeHtml(lessonTitle || 'Aula')}</span>
							</div>
							<button type="button" class="btn-icon btn-close-modal" style="background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
						</div>

						<form id="form-manage-quiz">
							<div class="form-group" style="margin-bottom: 16px;">
								<label style="display: block; font-weight: 600; margin-bottom: 6px;">Título do Quiz</label>
								<input type="text" id="quiz-edit-title" class="form-control" value="${escapeHtml(quiz.title || '')}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required>
							</div>

							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Nota Mínima para Aprovação (%)</label>
									<input type="number" id="quiz-edit-passing" value="${quiz.passing_score || 70}" min="0" max="100" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required>
								</div>
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Tempo Limite Padrão (minutos)</label>
									<input type="number" id="quiz-edit-time" value="${quiz.time_limit_minutes || quiz.base_time_minutes || 15}" min="1" max="180" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required>
								</div>
							</div>

							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
								<h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin: 0;">Banco de Questões (${questionsList.length})</h3>
								<button type="button" class="btn btn-secondary btn-sm" id="btn-add-quiz-q" style="font-size: 0.8rem; display: flex; align-items: center; gap: 4px;">
									<i data-lucide="plus"></i> Adicionar Questão
								</button>
							</div>

							<div id="quiz-questions-editor-list" style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
								${questionsList.length === 0 ? `
									<div style="text-align: center; padding: 24px; border: 1px dashed var(--border); border-radius: 8px; color: var(--text-muted); font-size: 0.9rem;">
										Nenhuma questão configurada. Clique em "Adicionar Questão" acima para criar a primeira pergunta.
									</div>
								` : questionsList.map((q, idx) => `
									<div class="card" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px;">
										<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
											<strong style="color: var(--primary); font-size: 0.9rem;">Questão ${idx + 1}</strong>
											<button type="button" class="btn-del-q" data-q-idx="${idx}" style="background: none; border: none; color: #f87171; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 4px;">
												<i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Remover
											</button>
										</div>
										<div class="form-group" style="margin-bottom: 12px;">
											<label style="display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 4px;">Enunciado da Questão *</label>
											<input type="text" class="form-control q-text-input" data-q-idx="${idx}" value="${escapeHtml(q.question_text || '')}" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required placeholder="Digite a pergunta...">
										</div>
										<div class="form-group" style="margin-bottom: 8px;">
											<label style="display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 4px;">Opções de Resposta e Gabarito:</label>
											${(q.options || [
												{ id: 'a', text: 'Primeira alternativa' },
												{ id: 'b', text: 'Segunda alternativa' },
												{ id: 'c', text: 'Terceira alternativa' }
											]).map((opt, oIdx) => `
												<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
													<input type="radio" name="correct_q_${idx}" class="q-correct-radio" data-q-idx="${idx}" value="${opt.id}" ${q.correct_answer === opt.id || (!q.correct_answer && oIdx === 0) ? 'checked' : ''} title="Marcar como resposta correta" style="accent-color: #10b981;">
													<input type="text" class="form-control q-opt-input" data-q-idx="${idx}" data-opt-id="${opt.id}" value="${escapeHtml(opt.text)}" style="flex: 1; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.85rem;" required>
												</div>
											`).join('')}
										</div>
										<div class="form-group" style="margin-top: 8px;">
											<label style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 4px;">Explicação Pedagógica (exibida após submissão):</label>
											<input type="text" class="form-control q-expl-input" data-q-idx="${idx}" value="${escapeHtml(q.explanation || '')}" style="width: 100%; padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.82rem;" placeholder="Explicação do porquê esta é a resposta correta...">
										</div>
									</div>
								`).join('')}
							</div>

							<div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 18px;">
								<button type="button" class="btn btn-secondary btn-close-modal">Fechar</button>
								<button type="submit" class="btn btn-primary" id="btn-save-quiz-config">
									<i data-lucide="check"></i> Salvar Quiz & Associar à Aula
								</button>
							</div>
						</form>
					</div>
				`;
				if (window.lucide) window.lucide.createIcons();

				modal.querySelectorAll('.btn-close-modal').forEach(b => {
					b.onclick = () => modal.remove();
				});

				const addQBtn = modal.querySelector('#btn-add-quiz-q');
				if (addQBtn) {
					addQBtn.onclick = () => {
						syncQuestionsFromDOM();
						questionsList.push({
							id: 0,
							question_text: '',
							question_type: 'single_choice',
							options: [
								{ id: 'a', text: 'Primeira alternativa' },
								{ id: 'b', text: 'Segunda alternativa' },
								{ id: 'c', text: 'Terceira alternativa' }
							],
							correct_answer: 'a',
							explanation: '',
							points: 1
						});
						renderEditor();
					};
				}

				modal.querySelectorAll('.btn-del-q').forEach(b => {
					b.onclick = () => {
						syncQuestionsFromDOM();
						const qIdx = parseInt(b.getAttribute('data-q-idx'), 10);
						questionsList.splice(qIdx, 1);
						renderEditor();
					};
				});

				function syncQuestionsFromDOM() {
					modal.querySelectorAll('.q-text-input').forEach(input => {
						const idx = parseInt(input.getAttribute('data-q-idx'), 10);
						if (questionsList[idx]) questionsList[idx].question_text = input.value;
					});
					modal.querySelectorAll('.q-opt-input').forEach(input => {
						const idx = parseInt(input.getAttribute('data-q-idx'), 10);
						const optId = input.getAttribute('data-opt-id');
						if (questionsList[idx]) {
							const opt = (questionsList[idx].options || []).find(o => o.id === optId);
							if (opt) opt.text = input.value;
						}
					});
					modal.querySelectorAll('.q-correct-radio:checked').forEach(radio => {
						const idx = parseInt(radio.getAttribute('data-q-idx'), 10);
						if (questionsList[idx]) questionsList[idx].correct_answer = radio.value;
					});
					modal.querySelectorAll('.q-expl-input').forEach(input => {
						const idx = parseInt(input.getAttribute('data-q-idx'), 10);
						if (questionsList[idx]) questionsList[idx].explanation = input.value;
					});
				}

				modal.querySelector('#form-manage-quiz').onsubmit = async (e) => {
					e.preventDefault();
					syncQuestionsFromDOM();
					const saveBtn = modal.querySelector('#btn-save-quiz-config');
					saveBtn.disabled = true;
					saveBtn.textContent = 'Salvando Quiz...';

					try {
						const payload = {
							id: quiz.id || 0,
							lesson_id: lessonId,
							title: modal.querySelector('#quiz-edit-title').value.trim(),
							passing_score: parseFloat(modal.querySelector('#quiz-edit-passing').value) || 70,
							time_limit_minutes: parseInt(modal.querySelector('#quiz-edit-time').value, 10) || 15,
							questions: questionsList
						};

						const res = await fetchAPI('quizzes', 'POST', payload);
						const savedQuizId = res.id || quiz.id;

						if (lessonId && savedQuizId) {
							await fetchAPI(`lessons/${lessonId}`, 'POST', { quiz_id: savedQuizId });
						}

						showToast('Quiz e questões salvos com sucesso!', 'success');
						modal.remove();
						renderCurriculum();
					} catch (err) {
						saveBtn.disabled = false;
						saveBtn.textContent = 'Salvar Quiz & Associar à Aula';
						showToast(err.message || 'Erro ao salvar quiz.', 'danger');
					}
				};
			}

			renderEditor();

		} catch (err) {
			modal.innerHTML = `
				<div class="card" style="max-width: 440px; padding: 24px; text-align: center;">
					<p style="color: #f87171;">Erro ao carregar quiz: ${escapeHtml(err.message)}</p>
					<button type="button" class="btn btn-secondary" onclick="this.closest('.modal-backdrop').remove()">Fechar</button>
				</div>
			`;
		}
	}

	/* ==========================================================================
	   5. Practical Assignment Submission Modal
	   ========================================================================== */
	async function openAssignmentModal(assignmentId) {
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.style = `
			position: fixed; top: 0; left: 0; right: 0; bottom: 0;
			background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(8px);
			z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 20px;
		`;
		modal.innerHTML = `
			<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 28px; max-width: 720px; width: 100%; max-height: 90vh; overflow-y: auto;">
				<div style="text-align: center; padding: 30px 0;">
					<div class="spinner" style="margin: 0 auto 12px; width: 32px; height: 32px; border: 3px solid rgba(255,255,255,0.1); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
					<p style="color: var(--text-muted);">Carregando atividade prática...</p>
				</div>
			</div>
		`;
		document.body.appendChild(modal);

		try {
			const targetId = assignmentId || 1;
			const asg = await fetchAPI(`assignments/${targetId}`).catch(() => null) || {
				title: 'Atividade Prática Formativa',
				prompt: 'Submeta seu artigo, documento, projeto visual ou link para avaliação do Mestre.',
				max_score: 100,
				submission_types: ['text_essay', 'file_upload', 'external_link', 'image_gallery']
			};

			const allowedTypes = asg.submission_types && asg.submission_types.length > 0
				? asg.submission_types
				: ['text_essay', 'file_upload', 'external_link', 'image_gallery'];

			modal.innerHTML = `
				<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 28px; max-width: 760px; width: 100%; max-height: 90vh; overflow-y: auto;">
					<div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border); padding-bottom: 16px; margin-bottom: 20px;">
						<div>
							<span style="font-size: 0.75rem; text-transform: uppercase; color: var(--primary); font-weight: 700;">Entrega de Atividade</span>
							<h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-main); margin: 0 0 4px;">${escapeHtml(asg.title)}</h2>
							<span style="font-size: 0.85rem; color: var(--text-muted);">Valor: <strong>${asg.max_score || 100} Pontos</strong> ${asg.due_date ? '&bull; Prazo: ' + asg.due_date : ''}</span>
						</div>
						<button type="button" class="btn-icon btn-close-modal" style="background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
					</div>

					<!-- Prompt / Criteria -->
					<div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 24px; font-size: 0.92rem; line-height: 1.5;">
						<strong style="display: block; color: var(--text-main); margin-bottom: 6px;">Enunciado & Instruções:</strong>
						<div>${asg.prompt || 'Sem instruções adicionais.'}</div>
					</div>

					<!-- Polymorphic Tabs Navigation -->
					<div style="display: flex; gap: 8px; border-bottom: 1px solid var(--border); margin-bottom: 20px; overflow-x: auto;">
						${allowedTypes.includes('text_essay') ? `
							<button type="button" class="asg-tab-btn active" data-asg-tab="essay" style="padding: 8px 16px; border: none; background: none; border-bottom: 2px solid var(--primary); color: var(--primary); font-weight: 600; cursor: pointer; font-size: 0.9rem;">
								📝 Artigo / Resumo
							</button>
						` : ''}
						${allowedTypes.includes('file_upload') ? `
							<button type="button" class="asg-tab-btn ${!allowedTypes.includes('text_essay') ? 'active' : ''}" data-asg-tab="file" style="padding: 8px 16px; border: none; background: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; cursor: pointer; font-size: 0.9rem;">
								📁 Documento / Arquivo
							</button>
						` : ''}
						${allowedTypes.includes('external_link') ? `
							<button type="button" class="asg-tab-btn" data-asg-tab="link" style="padding: 8px 16px; border: none; background: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; cursor: pointer; font-size: 0.9rem;">
								🔗 Link / Vídeo
							</button>
						` : ''}
						${allowedTypes.includes('image_gallery') ? `
							<button type="button" class="asg-tab-btn" data-asg-tab="gallery" style="padding: 8px 16px; border: none; background: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-weight: 600; cursor: pointer; font-size: 0.9rem;">
								🖼️ Portfólio Visual
							</button>
						` : ''}
					</div>

					<form id="form-polymorphic-submission">
						<!-- Pane 1: WYSIWYG Essay -->
						<div class="asg-pane" id="asg-pane-essay" style="${!allowedTypes.includes('text_essay') ? 'display: none;' : ''}">
							<div class="form-group" style="margin-bottom: 20px;">
								<label style="display: block; font-weight: 600; margin-bottom: 6px;">Artigo, Resumo ou Memorial Descritivo (Editor Formatado)</label>
								<div id="asg-essay-editor"></div>
							</div>
						</div>

						<!-- Pane 2: File Upload -->
						<div class="asg-pane" id="asg-pane-file" style="${allowedTypes.includes('text_essay') ? 'display: none;' : ''}">
							<div class="form-group" style="margin-bottom: 20px;">
								<label style="display: block; font-weight: 600; margin-bottom: 6px;">URL do Documento (PDF, DOCX, ZIP no Google Drive, OneDrive ou Nuvem)</label>
								<input type="url" id="asg-doc-url" class="form-control" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="https://drive.google.com/... ou link direto">
								<span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 4px;">Certifique-se de que o link esteja com permissão de leitura para avaliação docente.</span>
							</div>
						</div>

						<!-- Pane 3: External Link / Video -->
						<div class="asg-pane" id="asg-pane-link" style="display: none;">
							<div class="form-group" style="margin-bottom: 20px;">
								<label style="display: block; font-weight: 600; margin-bottom: 6px;">Link do Vídeo ou Projeto (YouTube, Loom, Vimeo, Figma, GitHub)</label>
								<input type="url" id="asg-external-url" class="form-control" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="https://www.youtube.com/watch?v=... ou https://figma.com/file/...">
							</div>
						</div>

						<!-- Pane 4: Image Gallery -->
						<div class="asg-pane" id="asg-pane-gallery" style="display: none;">
							<div class="form-group" style="margin-bottom: 20px;">
								<label style="display: block; font-weight: 600; margin-bottom: 6px;">URLs das Imagens do Portfólio (separadas por linha)</label>
								<textarea id="asg-gallery-urls" rows="4" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="https://.../imagem1.jpg&#10;https://.../imagem2.png"></textarea>
							</div>
						</div>

						<!-- Optional General Note -->
						<div class="form-group" style="margin-bottom: 24px;">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Observações Adicionais ao Facilitador (Opcional)</label>
							<input type="text" id="asg-notes" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Algum detalhe ou contexto importante sobre seu envio...">
						</div>

						<div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 18px;">
							<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
							<button type="submit" class="btn btn-primary" id="btn-submit-polymorphic-asg">
								<i data-lucide="upload-cloud"></i> Submeter para Avaliação
							</button>
						</div>
					</form>
				</div>
			`;
			if (window.lucide) window.lucide.createIcons();

			// Initialize WYSIWYG editor if essay pane exists
			let essayEditor = null;
			const essayContainer = modal.querySelector('#asg-essay-editor');
			if (essayContainer) {
				essayEditor = createWysiwygEditor(essayContainer, '', {
					minHeight: '180px',
					placeholder: 'Escreva seu artigo acadêmico, memorial ou reflexão crítica aqui...'
				});
			}

			// Tab switching
			modal.querySelectorAll('.asg-tab-btn').forEach(btn => {
				btn.addEventListener('click', () => {
					modal.querySelectorAll('.asg-tab-btn').forEach(b => {
						b.style.borderBottomColor = 'transparent';
						b.style.color = 'var(--text-muted)';
						b.classList.remove('active');
					});
					btn.style.borderBottomColor = 'var(--primary)';
					btn.style.color = 'var(--primary)';
					btn.classList.add('active');

					const tabKey = btn.getAttribute('data-asg-tab');
					modal.querySelectorAll('.asg-pane').forEach(p => p.style.display = 'none');
					const targetPane = modal.querySelector('#asg-pane-' + tabKey);
					if (targetPane) targetPane.style.display = 'block';
				});
			});

			modal.querySelectorAll('.btn-close-modal').forEach(b => {
				b.onclick = () => modal.remove();
			});

			modal.querySelector('#form-polymorphic-submission').onsubmit = async (e) => {
				e.preventDefault();
				const submitBtn = modal.querySelector('#btn-submit-polymorphic-asg');
				submitBtn.disabled = true;
				submitBtn.textContent = 'Enviando Trabalho...';

				const essayHtml = essayEditor ? essayEditor.getHtml() : '';
				const docUrl = (modal.querySelector('#asg-doc-url')?.value || '').trim();
				const externalUrl = (modal.querySelector('#asg-external-url')?.value || '').trim();
				const galleryUrls = (modal.querySelector('#asg-gallery-urls')?.value || '').trim();
				const notes = (modal.querySelector('#asg-notes')?.value || '').trim();

				let primaryUrl = externalUrl || docUrl || '';
				let fullContentText = essayHtml;
				if (notes) {
					fullContentText += `<br><p><em>Observação do Estudante:</em> ${escapeHtml(notes)}</p>`;
				}

				let attachmentIds = galleryUrls ? galleryUrls.split(/\n+/).filter(Boolean).join(',') : (docUrl ? docUrl : '');

				if (!fullContentText && !primaryUrl && !attachmentIds) {
					showToast('Por favor, preencha pelo menos um dos formatos de entrega (texto, link ou documento).', 'warning');
					submitBtn.disabled = false;
					submitBtn.textContent = 'Submeter para Avaliação';
					return;
				}

				try {
					await fetchAPI('submissions', 'POST', {
						assignment_id: targetId,
						cohort_id: state.activeCohortId,
						content_text: fullContentText,
						content_url: primaryUrl,
						attachment_ids: attachmentIds
					});

					showToast('Trabalho prático enviado com sucesso! Aguarde o parecer do Mestre.', 'success');
					modal.remove();
					renderAssignments();
				} catch (err) {
					submitBtn.disabled = false;
					submitBtn.textContent = 'Submeter para Avaliação';
					showToast(err.message || 'Erro ao enviar atividade prática.', 'danger');
				}
			};

		} catch (err) {
			modal.innerHTML = `
				<div class="card" style="max-width: 440px; padding: 24px; text-align: center;">
					<p style="color: #f87171;">Erro ao carregar atividade: ${escapeHtml(err.message)}</p>
					<button type="button" class="btn btn-secondary" onclick="this.closest('.modal-backdrop').remove()">Fechar</button>
				</div>
			`;
		}
	}

	/* ==========================================================================
	   6. Certification View & Cumulative Check
	   ========================================================================== */
	async function renderCertificate() {
		if (!state.activeCohortId) {
			renderError('Selecione uma turma para verificar o status de certificação.');
			return;
		}

		try {
			const check = await fetchAPI(`cohorts/${state.activeCohortId}/certificate-status`);

			contentRoot.innerHTML = `
				<div style="max-width: 720px; margin: 0 auto;">
					<div style="margin-bottom: 28px; text-align: center;">
						<div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(79, 70, 229, 0.15); color: var(--primary); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
							<i data-lucide="award" style="width: 32px; height: 32px;"></i>
						</div>
						<h1 style="font-size: 1.8rem; font-weight: 800;">Certificação Oficial ArtInnova</h1>
						<p style="color: var(--text-muted);">Critérios cumulativos obrigatórios para expedição de diploma com código SHA-256 verificado.</p>
					</div>

					<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 32px; margin-bottom: 24px;">
						<h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px;">Quadro de Requisitos Pedagógicos</h3>

						<div style="display: flex; flex-direction: column; gap: 16px;">
							<div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
								<div>
									<strong>1. Aulas Concluídas</strong>
									<span style="display: block; font-size: 0.8rem; color: var(--text-muted);">Exigido 100% de conclusão</span>
								</div>
								<span class="badge" style="background: ${check.progress >= 100 ? 'var(--success-bg)' : 'var(--warning-bg)'}; color: ${check.progress >= 100 ? 'var(--success)' : 'var(--warning)'}; font-weight: 700; padding: 6px 12px; border-radius: 999px;">
									${check.progress}%
								</span>
							</div>

							<div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
								<div>
									<strong>2. Frequência em Sessões Síncronas</strong>
									<span style="display: block; font-size: 0.8rem; color: var(--text-muted);">Mínimo de 75% de presença</span>
								</div>
								<span class="badge" style="background: ${check.attendance >= 75 ? 'var(--success-bg)' : 'var(--warning-bg)'}; color: ${check.attendance >= 75 ? 'var(--success)' : 'var(--warning)'}; font-weight: 700; padding: 6px 12px; border-radius: 999px;">
									${check.attendance}%
								</span>
							</div>

							<div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
								<div>
									<strong>3. Média dos Quizzes</strong>
									<span style="display: block; font-size: 0.8rem; color: var(--text-muted);">Nota de corte mínima: 70%</span>
								</div>
								<span class="badge" style="background: ${check.quiz_avg >= 70 ? 'var(--success-bg)' : 'var(--warning-bg)'}; color: ${check.quiz_avg >= 70 ? 'var(--success)' : 'var(--warning)'}; font-weight: 700; padding: 6px 12px; border-radius: 999px;">
									${check.quiz_avg}%
								</span>
							</div>

							<div style="display: flex; justify-content: space-between; align-items: center;">
								<div>
									<strong>4. Atividades Práticas Homologadas</strong>
									<span style="display: block; font-size: 0.8rem; color: var(--text-muted);">Todos os projetos avaliados pelo Mestre</span>
								</div>
								<span class="badge" style="background: ${check.assignments_ok ? 'var(--success-bg)' : 'var(--warning-bg)'}; color: ${check.assignments_ok ? 'var(--success)' : 'var(--warning)'}; font-weight: 700; padding: 6px 12px; border-radius: 999px;">
									${check.assignments_ok ? 'Aprovados' : 'Pendentes'}
								</span>
							</div>
						</div>
					</div>

					<div style="text-align: center;">
						${check.issued_cert ? `
							<div class="card" style="background: rgba(16, 185, 129, 0.1); border: 2px solid var(--success); border-radius: var(--radius-xl); padding: 28px; margin-bottom: 20px; text-align: center;">
								<div style="width: 56px; height: 56px; border-radius: 50%; background: var(--success); color: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
									<i data-lucide="shield-check" style="width: 32px; height: 32px;"></i>
								</div>
								<h3 style="color: var(--text-main); font-size: 1.4rem; font-weight: 800; margin-bottom: 6px;">Certificado Oficial Emitido</h3>
								<p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 14px;">Autenticação criptográfica registrada na blockchain pedagógica.</p>
								<div style="display: inline-flex; align-items: center; gap: 8px; background: var(--bg-card); border: 1px solid var(--border); padding: 8px 16px; border-radius: 8px; font-family: monospace; font-size: 0.95rem; color: #34d399; margin-bottom: 18px;">
									<span>SHA-256: ${check.issued_cert.hash}</span>
								</div>
								<div style="display: flex; gap: 12px; justify-content: center;">
									<a href="${check.issued_cert.verify_url}" target="_blank" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px; padding: 12px 24px; font-size: 1rem;">
										<i data-lucide="external-link"></i>
										<span>Visualizar / Imprimir Diploma A4</span>
									</a>
								</div>
							</div>
						` : (check.eligible ? `
							<button class="btn btn-primary" id="btn-issue-cert" style="padding: 14px 28px; font-size: 1rem;">
								<i data-lucide="award"></i> Emitir Meu Certificado Oficial (+500 XP)
							</button>
						` : `
							<div style="background: var(--warning-bg); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: var(--radius-md); padding: 16px; color: #fbbf24; font-size: 0.9rem; margin-bottom: 16px;">
								Ainda restam requisitos pendentes para você graduar nesta formação.
							</div>
							${(API.user?.is_master || API.user?.is_admin || API.user?.is_coord) ? `
								<button class="btn btn-secondary" id="btn-issue-cert-bypass" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;">
									<i data-lucide="zap"></i> Emitir Certificado (Modo Homologação / Teste Docente)
								</button>
							` : ''}
						`)}
					</div>
				</div>
			`;

			if (window.lucide) window.lucide.createIcons();

			const handleCertIssue = async (forceBypass = false) => {
				try {
					showToast('Gerando certificado oficial...', 'info');
					const res = await fetchAPI(`cohorts/${state.activeCohortId}/issue-certificate`, 'POST', { force: forceBypass });
					if (res && res.verify_url) {
						window.open(res.verify_url, '_blank');
						showToast('Certificado gerado com sucesso!', 'success');
						renderCertificate();
					} else {
						showToast(res && res.message ? res.message : 'Certificado emitido com sucesso!', 'success');
						renderCertificate();
					}
				} catch (err) {
					showToast(err.message || 'Erro ao emitir certificado.', 'danger');
				}
			};

			const issueBtn = document.getElementById('btn-issue-cert');
			if (issueBtn) issueBtn.onclick = () => handleCertIssue(false);

			const bypassBtn = document.getElementById('btn-issue-cert-bypass');
			if (bypassBtn) bypassBtn.onclick = () => handleCertIssue(true);
		} catch (e) {
			renderError('Não foi possível verificar a elegibilidade de certificação.');
		}
	}

	/* ==========================================================================
	   7. Synchronous Sessions View (Full In-App CRUD)
	   ========================================================================== */
	async function renderSessions() {
		if (!state.activeCohortId) {
			renderError('Selecione uma turma para visualizar as sessões síncronas.');
			return;
		}

		try {
			const data = await fetchAPI(`cohorts/${state.activeCohortId}/curriculum`);
			const sessions = data.sessions || [];
			const canManage = canManageActiveCohort();

			let html = `
				<div class="sessions-wrapper">
					<div class="desk-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
						<div>
							<h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">Sessões Ao Vivo & Replays</h2>
							<p style="color: var(--text-muted); font-size: 0.95rem;">Encontros síncronos, mentorias e transmissões ao vivo da turma.</p>
						</div>
						${canManage ? `
							<button id="btn-create-session-modal" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
								<i data-lucide="plus-circle"></i>
								<span>Agendar Nova Sessão</span>
							</button>
						` : ''}
					</div>
			`;

			if (sessions.length === 0) {
				html += `
					<div class="empty-state" style="padding: 48px; text-align: center; background: var(--bg-card); border: 1px dashed var(--border); border-radius: var(--radius-lg);">
						<i data-lucide="video" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 12px;"></i>
						<h3>Nenhuma sessão ao vivo agendada</h3>
						<p style="color: var(--text-muted); margin-bottom: 20px;">Não há encontros síncronos marcados para esta turma no momento.</p>
						${canManage ? `<button class="btn btn-primary" id="btn-empty-create-session" onclick="window.StackEAD_App.openCreateSessionModal()">Agendar Primeira Sessão</button>` : ''}
					</div>
				`;
			} else {
				html += `<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px;">`;
				sessions.forEach(s => {
					html += `
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
							<div>
								<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
									<div style="display: flex; align-items: center; gap: 8px; color: var(--primary); font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">
										<i data-lucide="radio" style="width: 16px;"></i> Sessão Síncrona
									</div>
									<span class="badge" style="background: rgba(37, 99, 235, 0.15); color: #60a5fa; font-size: 0.75rem; font-weight: 700; padding: 4px 8px; border-radius: 6px;">
										Turma #${state.activeCohortId}
									</span>
								</div>
								<h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">${s.title}</h3>
								<p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 16px;">
									<strong>Data & Horário:</strong> ${s.session_date ? s.session_date.replace('T', ' às ') : 'A definir'}
								</p>
							</div>

							<div style="margin-top: 16px;">
								${s.meet_url ? `
									<a href="${s.meet_url}" target="_blank" class="btn btn-primary btn-block" style="margin-bottom: 8px; display: flex; align-items: center; justify-content: center; gap: 8px;">
										<i data-lucide="video"></i>
										<span>Entrar na Sala Virtual</span>
									</a>
								` : ''}
								${s.recording ? `
									<a href="${s.recording}" target="_blank" class="btn btn-secondary btn-block" style="margin-bottom: 8px; display: flex; align-items: center; justify-content: center; gap: 8px;">
										<i data-lucide="play"></i>
										<span>Ver Gravação / Replay</span>
									</a>
								` : ''}

								${canManage ? `
									<div style="display: flex; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border);">
										<button type="button" class="btn btn-sm btn-secondary btn-edit-session" data-session-id="${s.id}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;">
											<i data-lucide="edit-3"></i>
											<span>Editar</span>
										</button>
										<button type="button" class="btn btn-sm btn-delete-session" data-session-id="${s.id}" data-session-title="${s.title}" style="padding: 6px 12px; background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer;">
											<i data-lucide="trash-2"></i>
										</button>
									</div>
								` : ''}
							</div>
						</div>
					`;
				});
				html += `</div>`;
			}

			html += `</div>`;
			contentRoot.innerHTML = html;
			if (window.lucide) window.lucide.createIcons();

			// Listeners
			const createBtn = document.getElementById('btn-create-session-modal');
			if (createBtn) {
				createBtn.addEventListener('click', () => openCreateSessionModal());
			}
			const emptyCreateBtn = document.getElementById('btn-empty-create-session');
			if (emptyCreateBtn) {
				emptyCreateBtn.addEventListener('click', () => openCreateSessionModal());
			}

			document.querySelectorAll('.btn-edit-session').forEach(btn => {
				btn.addEventListener('click', () => {
					const sid = parseInt(btn.getAttribute('data-session-id'), 10);
					openEditSessionModal(sid);
				});
			});

			document.querySelectorAll('.btn-delete-session').forEach(btn => {
				btn.addEventListener('click', () => {
					const sid = parseInt(btn.getAttribute('data-session-id'), 10);
					const stitle = btn.getAttribute('data-session-title');
					deleteSession(sid, stitle);
				});
			});

		} catch (e) {
			console.error('StackEAD Sessions Render Error:', e);
			renderError('Não foi possível carregar as sessões ao vivo: ' + (e.message || 'Erro de execução'));
		}
	}

	async function openCreateSessionModal() {
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 600px; width: 90%;">
				<div class="modal-header">
					<h3>Agendar Nova Sessão Ao Vivo</h3>
					<button class="btn-icon btn-close-modal">&times;</button>
				</div>
				<form id="form-create-session" style="padding: 24px;">
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Título da Sessão *</label>
						<input type="text" id="session-title" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required placeholder="Ex: Masterclass: Curadoria Imersiva & IA">
					</div>

					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Data e Horário *</label>
						<input type="datetime-local" id="session-date" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required>
					</div>

					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Link da Sala Virtual (Google Meet, Zoom, Teams)</label>
						<input type="url" id="session-meet" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="https://meet.google.com/xyz-abcd-efg">
					</div>

					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Link da Gravação / Replay (Pós-Transmissão)</label>
						<input type="url" id="session-recording" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="https://vimeo.com/... ou YouTube">
					</div>

					<div class="form-group" style="margin-bottom: 24px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Pauta / Descrição da Sessão</label>
						<textarea id="session-desc" rows="3" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Descreva os tópicos que serão abordados nesta aula ao vivo..."></textarea>
					</div>

					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="btn-save-session">Salvar & Agendar</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		modal.querySelector('#form-create-session').onsubmit = async (e) => {
			e.preventDefault();
			const saveBtn = modal.querySelector('#btn-save-session');
			saveBtn.disabled = true;
			saveBtn.textContent = 'Agendando...';

			try {
				await fetchAPI('sessions', 'POST', {
					cohort_id: state.activeCohortId,
					title: document.getElementById('session-title').value.trim(),
					session_date: document.getElementById('session-date').value,
					meet_url: document.getElementById('session-meet').value.trim(),
					recording_url: document.getElementById('session-recording').value.trim(),
					description: document.getElementById('session-desc').value.trim()
				});

				showToast('Sessão síncrona agendada com sucesso!', 'success');
				modal.remove();
				renderSessions();
			} catch (err) {
				saveBtn.disabled = false;
				saveBtn.textContent = 'Salvar & Agendar';
			}
		};
	}

	async function openEditSessionModal(sessionId) {
		const session = await fetchAPI(`sessions/${sessionId}`).catch(() => null);
		if (!session) {
			showToast('Não foi possível carregar os dados da sessão.', 'danger');
			return;
		}

		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 600px; width: 90%;">
				<div class="modal-header">
					<h3>Editar Sessão Ao Vivo</h3>
					<button class="btn-icon btn-close-modal">&times;</button>
				</div>
				<form id="form-edit-session" style="padding: 24px;">
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Título da Sessão *</label>
						<input type="text" id="edit-session-title" class="form-control" value="${escapeHtml(session.title)}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required>
					</div>

					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Data e Horário</label>
						<input type="datetime-local" id="edit-session-date" class="form-control" value="${session.session_date || ''}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
					</div>

					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Link da Sala Virtual (Google Meet, Zoom, Teams)</label>
						<input type="url" id="edit-session-meet" class="form-control" value="${escapeHtml(session.meet_url || '')}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
					</div>

					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Link da Gravação / Replay</label>
						<input type="url" id="edit-session-recording" class="form-control" value="${escapeHtml(session.recording_url || '')}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
					</div>

					<div class="form-group" style="margin-bottom: 24px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Pauta / Descrição</label>
						<textarea id="edit-session-desc" rows="3" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">${escapeHtml(session.description || '')}</textarea>
					</div>

					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="btn-update-session">Atualizar Sessão</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		modal.querySelector('#form-edit-session').onsubmit = async (e) => {
			e.preventDefault();
			const saveBtn = modal.querySelector('#btn-update-session');
			saveBtn.disabled = true;
			saveBtn.textContent = 'Salvando...';

			try {
				await fetchAPI(`sessions/${sessionId}`, 'POST', {
					title: document.getElementById('edit-session-title').value.trim(),
					session_date: document.getElementById('edit-session-date').value,
					meet_url: document.getElementById('edit-session-meet').value.trim(),
					recording_url: document.getElementById('edit-session-recording').value.trim(),
					description: document.getElementById('edit-session-desc').value.trim()
				});

				showToast('Sessão atualizada com sucesso!', 'success');
				modal.remove();
				renderSessions();
			} catch (err) {
				saveBtn.disabled = false;
				saveBtn.textContent = 'Atualizar Sessão';
			}
		};
	}

	async function deleteSession(sessionId, title) {
		if (!confirm(`Tem certeza que deseja excluir a sessão "${title}"?`)) {
			return;
		}

		try {
			await fetchAPI(`sessions/${sessionId}`, 'DELETE');
			showToast('Sessão excluída com sucesso.', 'success');
			renderSessions();
		} catch (e) {
			showToast('Erro ao excluir sessão.', 'danger');
		}
	}

	/* ==========================================================================
	   8. Practical Assignments View (Full In-App CRUD)
	   ========================================================================== */
	async function renderAssignments() {
		if (!state.activeCohortId) {
			renderError('Selecione uma turma para visualizar as atividades práticas.');
			return;
		}

		try {
			const assignments = await fetchAPI(`cohorts/${state.activeCohortId}/assignments`).catch(() => []);
			const canManage = canManageActiveCohort();

			let html = `
				<div class="assignments-wrapper">
					<div class="desk-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
						<div>
							<h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">Projetos Práticos & Avaliação por Rúbricas</h2>
							<p style="color: var(--text-muted); font-size: 0.95rem;">Submeta seus trabalhos práticos e receba pareceres formativos emitidos pelos mestres.</p>
						</div>
						${canManage ? `
							<button id="btn-create-assignment-modal" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
								<i data-lucide="plus-circle"></i>
								<span>Cadastrar Nova Atividade</span>
							</button>
						` : ''}
					</div>
			`;

			if (assignments.length === 0) {
				html += `
					<div class="empty-state" style="padding: 48px; text-align: center; background: var(--bg-card); border: 1px dashed var(--border); border-radius: var(--radius-lg);">
						<i data-lucide="folder-git2" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 12px;"></i>
						<h3>Nenhuma atividade prática cadastrada</h3>
						<p style="color: var(--text-muted); margin-bottom: 20px;">Não há desafios ou projetos práticos ativos para esta turma.</p>
						${canManage ? `<button class="btn btn-primary" id="btn-empty-create-assignment" onclick="window.StackEAD_App.openCreateAssignmentModal()">Cadastrar Primeira Atividade</button>` : ''}
					</div>
				`;
			} else {
				html += `<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px;">`;
				assignments.forEach(asg => {
					const sub = asg.submission;
					html += `
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
							<div>
								<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
									<span class="badge" style="background: rgba(37, 99, 235, 0.15); color: #60a5fa; font-size: 0.75rem; font-weight: 700; padding: 4px 8px; border-radius: 6px;">
										VALOR: ${asg.max_score} PTS
									</span>
									${sub ? `
										<span class="badge ${sub.status === 'graded' ? 'badge-success' : 'badge-warning'}" style="font-size: 0.75rem; font-weight: 700; padding: 4px 8px; border-radius: 6px;">
											${sub.status === 'graded' ? 'Nota: ' + sub.score : 'Em Avaliação'}
										</span>
									` : `
										<span class="badge badge-warning" style="font-size: 0.75rem; font-weight: 700; padding: 4px 8px; border-radius: 6px;">Pendente</span>
									`}
								</div>

								<h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">${asg.title}</h3>
								<p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 12px;">
									<strong>Prazo de Entrega:</strong> ${asg.due_date ? asg.due_date : 'Sem prazo limite'}
								</p>
								<div style="font-size: 0.85rem; color: var(--text-muted); background: var(--bg-main); padding: 12px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 16px; max-height: 100px; overflow-y: auto;">
									${asg.prompt || 'Sem instruções adicionais.'}
								</div>

								${sub && sub.feedback ? `
									<div style="background: rgba(16, 185, 129, 0.1); border-left: 3px solid var(--success); padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; font-size: 0.82rem;">
										<strong style="color: var(--success); display: block; margin-bottom: 4px;">Parecer Formativo do Mestre:</strong>
										<p style="margin: 0; color: var(--text-main);">${sub.feedback}</p>
									</div>
								` : ''}
							</div>

							<div style="margin-top: 16px;">
								${!canManage ? `
									<button class="btn ${sub ? 'btn-secondary' : 'btn-primary'} btn-block btn-submit-asg" data-asg-id="${asg.id}" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
										<i data-lucide="${sub ? 'check-circle' : 'upload-cloud'}"></i>
										<span>${sub ? 'Reenviar Trabalho' : 'Submeter Projeto'}</span>
									</button>
								` : `
									<div style="display: flex; gap: 8px;">
										<button type="button" class="btn btn-sm btn-primary btn-edit-asg" data-asg-id="${asg.id}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;">
											<i data-lucide="edit-3"></i>
											<span>Editar</span>
										</button>
										<button type="button" class="btn btn-sm btn-secondary" onclick="window.StackEAD_App.openAssignmentModal(${asg.id})" title="Testar Envio / Submeter" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
											<i data-lucide="upload-cloud"></i>
											<span>Submeter</span>
										</button>
										<button type="button" class="btn btn-sm btn-secondary" onclick="window.StackEAD_App.navigate('grading-desk')" title="Ver Entregas e Avaliar" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
											<i data-lucide="check-square"></i>
											<span>Avaliar</span>
										</button>
										<button type="button" class="btn btn-sm btn-delete-asg" data-asg-id="${asg.id}" data-asg-title="${asg.title}" style="padding: 6px 12px; background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer;">
											<i data-lucide="trash-2"></i>
										</button>
									</div>
								`}
							</div>
						</div>
					`;
				});
				html += `</div>`;
			}

			html += `</div>`;
			contentRoot.innerHTML = html;
			if (window.lucide) window.lucide.createIcons();

			// Listeners
			const createBtn = document.getElementById('btn-create-assignment-modal');
			if (createBtn) {
				createBtn.addEventListener('click', () => openCreateAssignmentModal());
			}
			const emptyCreateAsgBtn = document.getElementById('btn-empty-create-assignment');
			if (emptyCreateAsgBtn) {
				emptyCreateAsgBtn.addEventListener('click', () => openCreateAssignmentModal());
			}

			document.querySelectorAll('.btn-edit-asg').forEach(btn => {
				btn.addEventListener('click', () => {
					const id = parseInt(btn.getAttribute('data-asg-id'), 10);
					openEditAssignmentModal(id);
				});
			});

			document.querySelectorAll('.btn-delete-asg').forEach(btn => {
				btn.addEventListener('click', () => {
					const id = parseInt(btn.getAttribute('data-asg-id'), 10);
					const title = btn.getAttribute('data-asg-title');
					deleteAssignment(id, title);
				});
			});

			document.querySelectorAll('.btn-submit-asg').forEach(btn => {
				btn.addEventListener('click', () => {
					const id = parseInt(btn.getAttribute('data-asg-id'), 10);
					openSubmitAssignmentModal(id);
				});
			});

		} catch (e) {
			console.error('StackEAD Assignments Render Error:', e);
			renderError('Não foi possível carregar as atividades da turma: ' + (e.message || 'Erro de execução'));
		}
	}

	async function openCreateAssignmentModal() {
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 680px; width: 90%; max-height: 90vh; overflow-y: auto;">
				<div class="modal-header">
					<h3>Cadastrar Nova Atividade Prática</h3>
					<button class="btn-icon btn-close-modal">&times;</button>
				</div>
				<form id="form-create-assignment" style="padding: 24px;">
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Título da Atividade *</label>
						<input type="text" id="asg-title" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required placeholder="Ex: Projeto Prático de Curadoria Interativa">
					</div>

					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Vincular Pedagogicamente a uma Aula ou Módulo (Opcional):</label>
						<select id="asg-target-link" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
							<option value="none:0">Geral da Turma (Sem vínculo específico)</option>
							${(state.activeCurriculum?.modules || []).map(m => `
								<option value="module:${m.id}">📁 Módulo: ${escapeHtml(m.title)}</option>
								${(m.lessons || []).map(l => `
									<option value="lesson:${l.id}">&nbsp;&nbsp;&nbsp;&nbsp;↳ 📄 Aula: ${escapeHtml(l.title)}</option>
								`).join('')}
							`).join('')}
						</select>
					</div>

					<div id="asg-final-project-box" style="margin-bottom: 16px; padding: 12px; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px;">
						<label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #fbbf24; cursor: pointer;">
							<input type="checkbox" id="asg-is-final-project" value="1">
							<span>Definir como Projeto Integrador / Projeto Final de Módulo</span>
						</label>
						<div id="asg-lock-next-wrapper" style="margin-top: 8px; padding-left: 24px;">
							<label style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; cursor: pointer; color: #fca5a5;">
								<input type="checkbox" id="asg-lock-next-module" value="1" checked>
								<span>Bloquear próximo módulo da formação até aprovação</span>
							</label>
						</div>
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Prazo Final (Data)</label>
							<input type="date" id="asg-due-date" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Pontuação Máxima</label>
							<input type="number" id="asg-max-score" value="100" min="1" max="1000" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
						</div>
					</div>

					<div class="form-group" style="margin-bottom: 16px; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 8px; padding: 14px;">
						<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Formatos de Entrega Permitidos aos Alunos:</label>
						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.88rem;">
							<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
								<input type="checkbox" class="asg-type-cb" value="text_essay" checked> Artigo / Resumo (WYSIWYG)
							</label>
							<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
								<input type="checkbox" class="asg-type-cb" value="file_upload" checked> Documentos (PDF, DOCX, ZIP)
							</label>
							<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
								<input type="checkbox" class="asg-type-cb" value="external_link" checked> Links & Vídeos (YouTube, Loom)
							</label>
							<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
								<input type="checkbox" class="asg-type-cb" value="image_gallery" checked> Portfólio Visual (Fotos/Projetos)
							</label>
						</div>
					</div>

					<div class="form-group" style="margin-bottom: 24px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Instruções & Rúbricas de Avaliação (Editor WYSIWYG) *</label>
						<div id="asg-create-prompt-editor"></div>
					</div>

					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="btn-save-asg">Cadastrar Atividade</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		const promptEditor = createWysiwygEditor(modal.querySelector('#asg-create-prompt-editor'), '', {
			minHeight: '160px',
			placeholder: 'Detalhe as etapas que o estudante deve cumprir, diretrizes e critérios de avaliação...'
		});

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		modal.querySelector('#form-create-assignment').onsubmit = async (e) => {
			e.preventDefault();
			const saveBtn = modal.querySelector('#btn-save-asg');
			saveBtn.disabled = true;
			saveBtn.textContent = 'Salvando...';

			const selectedTypes = [];
			modal.querySelectorAll('.asg-type-cb:checked').forEach(cb => selectedTypes.push(cb.value));

			const targetVal = (modal.querySelector('#asg-target-link')?.value || 'none:0').split(':');
			const targetType = targetVal[0];
			const targetId = parseInt(targetVal[1], 10);
			const isFinalProj = modal.querySelector('#asg-is-final-project')?.checked ? 1 : 0;
			const lockNextMod = modal.querySelector('#asg-lock-next-module')?.checked ? 1 : 0;

			try {
				await fetchAPI('assignments', 'POST', {
					cohort_id: state.activeCohortId,
					title: document.getElementById('asg-title').value.trim(),
					due_date: document.getElementById('asg-due-date').value,
					max_score: parseFloat(document.getElementById('asg-max-score').value) || 100,
					prompt: promptEditor.getHtml(),
					submission_types: selectedTypes.length > 0 ? selectedTypes : ['text_essay', 'file_upload', 'external_link'],
					module_id: targetType === 'module' ? targetId : 0,
					lesson_id: targetType === 'lesson' ? targetId : 0,
					is_final_project: isFinalProj,
					lock_next_module: lockNextMod,
					min_score: 70
				});

				showToast('Atividade prática cadastrada com sucesso!', 'success');
				modal.remove();
				renderAssignments();
			} catch (err) {
				saveBtn.disabled = false;
				saveBtn.textContent = 'Cadastrar Atividade';
				showToast(err.message || 'Erro ao cadastrar atividade.', 'danger');
			}
		};
	}

	async function openEditAssignmentModal(asgId) {
		const asg = await fetchAPI(`assignments/${asgId}`).catch(() => null);
		if (!asg) {
			showToast('Não foi possível carregar os dados da atividade.', 'danger');
			return;
		}

		const existingTypes = asg.submission_types || ['text_essay', 'file_upload', 'external_link', 'image_gallery'];

		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 680px; width: 90%; max-height: 90vh; overflow-y: auto;">
				<div class="modal-header">
					<h3>Editar Atividade Prática</h3>
					<button class="btn-icon btn-close-modal">&times;</button>
				</div>
				<form id="form-edit-assignment" style="padding: 24px;">
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Título da Atividade *</label>
						<input type="text" id="edit-asg-title" class="form-control" value="${escapeHtml(asg.title)}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required>
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Prazo Final</label>
							<input type="date" id="edit-asg-due-date" class="form-control" value="${asg.due_date || ''}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Pontuação Máxima</label>
							<input type="number" id="edit-asg-max-score" value="${asg.max_score || 100}" min="1" max="1000" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
						</div>
					</div>

					<div class="form-group" style="margin-bottom: 16px; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 8px; padding: 14px;">
						<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Formatos de Entrega Permitidos:</label>
						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.88rem;">
							<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
								<input type="checkbox" class="edit-asg-type-cb" value="text_essay" ${existingTypes.includes('text_essay') ? 'checked' : ''}> Artigo / Resumo (WYSIWYG)
							</label>
							<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
								<input type="checkbox" class="edit-asg-type-cb" value="file_upload" ${existingTypes.includes('file_upload') ? 'checked' : ''}> Documentos (PDF, DOCX, ZIP)
							</label>
							<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
								<input type="checkbox" class="edit-asg-type-cb" value="external_link" ${existingTypes.includes('external_link') ? 'checked' : ''}> Links & Vídeos (YouTube, Loom)
							</label>
							<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
								<input type="checkbox" class="edit-asg-type-cb" value="image_gallery" ${existingTypes.includes('image_gallery') ? 'checked' : ''}> Portfólio Visual (Fotos)
							</label>
						</div>
					</div>

					<div class="form-group" style="margin-bottom: 24px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Instruções & Rúbricas (Editor WYSIWYG) *</label>
						<div id="asg-edit-prompt-editor"></div>
					</div>

					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="btn-update-asg">Atualizar Atividade</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		const promptEditor = createWysiwygEditor(modal.querySelector('#asg-edit-prompt-editor'), asg.prompt || '', {
			minHeight: '160px',
			placeholder: 'Instruções e critérios da atividade...'
		});

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		modal.querySelector('#form-edit-assignment').onsubmit = async (e) => {
			e.preventDefault();
			const saveBtn = modal.querySelector('#btn-update-asg');
			saveBtn.disabled = true;
			saveBtn.textContent = 'Salvando...';

			const selectedTypes = [];
			modal.querySelectorAll('.edit-asg-type-cb:checked').forEach(cb => selectedTypes.push(cb.value));

			try {
				await fetchAPI(`assignments/${asgId}`, 'POST', {
					title: document.getElementById('edit-asg-title').value.trim(),
					due_date: document.getElementById('edit-asg-due-date').value,
					max_score: parseFloat(document.getElementById('edit-asg-max-score').value) || 100,
					prompt: promptEditor.getHtml(),
					submission_types: selectedTypes.length > 0 ? selectedTypes : ['text_essay', 'file_upload', 'external_link']
				});

				showToast('Atividade prática atualizada com sucesso!', 'success');
				modal.remove();
				renderAssignments();
			} catch (err) {
				saveBtn.disabled = false;
				saveBtn.textContent = 'Atualizar Atividade';
				showToast(err.message || 'Erro ao atualizar atividade.', 'danger');
			}
		};
	}

	async function deleteAssignment(asgId, title) {
		if (!confirm(`Tem certeza que deseja excluir a atividade "${title}"?`)) {
			return;
		}

		try {
			await fetchAPI(`assignments/${asgId}`, 'DELETE');
			showToast('Atividade excluída com sucesso.', 'success');
			renderAssignments();
		} catch (e) {
			showToast('Erro ao excluir atividade.', 'danger');
		}
	}

	function openSubmitAssignmentModal(asgId) {
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 580px; width: 90%;">
				<div class="modal-header">
					<h3>Submeter Trabalho / Projeto Prático</h3>
					<button class="btn-icon btn-close-modal">&times;</button>
				</div>
				<form id="form-submit-asg" style="padding: 24px;">
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Link do Projeto (Google Drive, GitHub, Figma, YouTube, Behance) *</label>
						<input type="url" id="sub-url" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required placeholder="https://...">
					</div>

					<div class="form-group" style="margin-bottom: 24px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Comentários & Memorial Descritivo</label>
						<textarea id="sub-notes" rows="4" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Descreva brevemente o projeto, técnicas utilizadas e desafios encontrados..."></textarea>
					</div>

					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="btn-do-submit-asg">Confirmar Submissão</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		modal.querySelector('#form-submit-asg').onsubmit = async (e) => {
			e.preventDefault();
			const saveBtn = modal.querySelector('#btn-do-submit-asg');
			saveBtn.disabled = true;
			saveBtn.textContent = 'Enviando...';

			try {
				await fetchAPI('submissions', 'POST', {
					cohort_id: state.activeCohortId,
					assignment_id: asgId,
					artifact_url: document.getElementById('sub-url').value.trim(),
					notes: document.getElementById('sub-notes').value.trim()
				});

				showToast('Trabalho submetido com sucesso! O mestre avaliará em breve.', 'success');
				modal.remove();
				renderAssignments();
			} catch (err) {
				saveBtn.disabled = false;
				saveBtn.textContent = 'Confirmar Submissão';
			}
		};
	}

	/* ==========================================================================
	   9. Community & Cohort Forum View
	   ========================================================================== */
	async function renderCommunity() {
		if (!state.activeCohortId) {
			renderError('Selecione uma turma para ver o fórum.');
			return;
		}

		try {
			const topics = await fetchAPI(`cohorts/${state.activeCohortId}/discussions`);

			let html = `
				<div>
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
						<div>
							<h2 style="font-size: 1.5rem; font-weight: 700;">Fórum & Comunidade da Turma</h2>
							<p style="color: var(--text-muted);">Espaço de diálogo, colaboração e mentoria com outros alunos e mestres.</p>
						</div>
						<button class="btn btn-primary" id="btn-new-topic"><i data-lucide="plus"></i> Novo Tópico</button>
					</div>
			`;

			if (topics.length === 0) {
				html += `<div style="padding: 40px; text-align: center; color: var(--text-muted);">Nenhum tópico aberto ainda. Inicie a primeira discussão!</div>`;
			} else {
				html += `<div style="display: flex; flex-direction: column; gap: 16px;">`;
				topics.forEach(t => {
					html += `
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px;">
							<div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
								<h3 style="font-size: 1.15rem; font-weight: 700;">${t.title || 'Dúvida da Turma'}</h3>
								<button class="btn btn-secondary btn-sm" onclick="window.StackEAD_App.upvoteTopic(${t.id})">
									<i data-lucide="thumbs-up"></i> ${t.upvotes}
								</button>
							</div>
							<p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 16px;">${t.content}</p>
							<div style="font-size: 0.78rem; color: var(--text-muted);">Publicado por <strong>${t.display_name}</strong> em ${t.created_at}</div>
						</div>
					`;
				});
				html += `</div>`;
			}

			html += `</div>`;
			contentRoot.innerHTML = html;
			if (window.lucide) window.lucide.createIcons();

			const newTopicBtn = document.getElementById('btn-new-topic');
			if (newTopicBtn) {
				newTopicBtn.onclick = () => openNewTopicModal();
			}
		} catch (e) {
			renderError('Não foi possível carregar os tópicos do fórum.');
		}
	}

	function openNewTopicModal() {
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.style = `position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 20px;`;
		modal.innerHTML = `
			<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 32px; max-width: 560px; width: 100%;">
				<h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 16px;">Criar Novo Tópico</h3>
				<form id="new-topic-form">
					<div style="margin-bottom: 16px;">
						<label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Título do Tópico</label>
						<input type="text" id="topic-title" required placeholder="Ex.: Dúvida sobre shaders no módulo 2" style="width: 100%; padding: 12px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); color: #fff;">
					</div>
					<div style="margin-bottom: 24px;">
						<label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Mensagem / Pergunta</label>
						<textarea id="topic-content" rows="4" required placeholder="Escreva sua pergunta ou contribuição detalhada..." style="width: 100%; padding: 12px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); color: #fff; font-family: inherit;"></textarea>
					</div>
					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary" id="btn-close-topic">Cancelar</button>
						<button type="submit" class="btn btn-primary">Publicar Tópico</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		document.getElementById('btn-close-topic').onclick = () => modal.remove();
		document.getElementById('new-topic-form').onsubmit = async (e) => {
			e.preventDefault();
			await fetchAPI(`cohorts/${state.activeCohortId}/discussions`, 'POST', {
				title: document.getElementById('topic-title').value,
				content: document.getElementById('topic-content').value
			});
			modal.remove();
			showToast('Tópico publicado no fórum!', 'success');
			renderCommunity();
		};
	}

	async function upvoteTopic(topicId) {
		await fetchAPI(`discussions/${topicId}/upvote`, 'POST');
		showToast('Voto registrado!', 'success');
		renderCommunity();
	}

	/* ==========================================================================
	   10. Gamification Showcase & Leaderboard
	   ========================================================================== */
	async function renderGamification() {
		try {
			const board = await fetchAPI('gamification/leaderboard');
			const myGam = API.user?.gamification || {};
			const badges = API.user?.badges || [];

			contentRoot.innerHTML = `
				<div>
					<div style="margin-bottom: 28px;">
						<h2 style="font-size: 1.5rem; font-weight: 700;">Conquistas, Níveis & Ranking</h2>
						<p style="color: var(--text-muted);">Sua evolução na plataforma é recompensada por aulas concluídas, presenças e notas.</p>
					</div>

					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 32px;">
						<!-- Card Meu Nível -->
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 28px;">
							<span style="font-size: 0.8rem; text-transform: uppercase; color: var(--primary); font-weight: 700;">Seu Rank Atual</span>
							<h3 style="font-size: 1.8rem; font-weight: 800; margin: 4px 0 12px;">${myGam.title || 'Aprendiz'} (Nv. ${myGam.level || 1})</h3>
							<div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">
								<span>${myGam.total_xp || 0} XP Acumulado</span>
								<span>Próximo nível: ${myGam.next_level_xp || 100} XP</span>
							</div>
							<div style="height: 10px; background: rgba(255,255,255,0.08); border-radius: 999px; overflow: hidden;">
								<div style="width: ${myGam.progress_percent || 0}%; height: 100%; background: linear-gradient(90deg, #fbbf24, #f59e0b);"></div>
							</div>
						</div>

						<!-- Card Carteira Moedas Virtuais -->
						<div class="card coin-wallet-card" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.08) 0%, rgba(202, 138, 4, 0.15) 100%); border: 1px solid rgba(234, 179, 8, 0.35); border-radius: var(--radius-xl); padding: 28px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
								<span style="font-size: 0.8rem; text-transform: uppercase; color: #fbbf24; font-weight: 700;">Carteira Digital</span>
								<i data-lucide="coins" style="color: #fbbf24; width: 22px; height: 22px;"></i>
							</div>
							<h3 style="font-size: 1.8rem; font-weight: 800; color: #fef08a; margin: 4px 0 8px;">${API.user?.coins || 0} <span style="font-size: 1rem; color: #fbbf24; font-weight: 600;">${API.user?.coin_config?.coin_name || 'ArtCoins'}</span></h3>
							<p style="font-size: 0.82rem; color: #fde68a; margin: 0;">Acumule moedas concluindo aulas, entregando projetos e participando ao vivo.</p>
						</div>

						<!-- Card Medalhas -->
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 28px;">
							<h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 12px;">Mural de Emblemas (${badges.length})</h3>
							<div style="display: flex; flex-wrap: wrap; gap: 8px;">
								${badges.length === 0 
									? '<p style="color:var(--text-muted);font-size:0.85rem;">Conclua sua primeira aula ou participe de uma sessão ao vivo para desbloquear insígnias!</p>'
									: badges.map(b => `
										<div style="background: rgba(79, 70, 229, 0.15); border: 1px solid rgba(79, 70, 229, 0.3); border-radius: var(--radius-md); padding: 6px 12px; display: flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 600;">
											<i data-lucide="award" style="color: var(--primary); width: 14px; height: 14px;"></i> ${b.title}
										</div>
									`).join('')}
							</div>
						</div>
					</div>

					<!-- Leaderboard -->
					<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 32px;">
						<h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 20px;">Quadro de Honra / Top Estudantes</h3>
						<div style="display: flex; flex-direction: column; gap: 10px;">
							${board.map((item, idx) => `
								<div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-radius: var(--radius-md); background: ${idx === 0 ? 'rgba(251, 191, 36, 0.1)' : 'var(--bg-hover)'}; border: 1px solid ${idx === 0 ? 'rgba(251, 191, 36, 0.3)' : 'var(--border)'};">
									<div style="display: flex; align-items: center; gap: 14px;">
										<span style="font-weight: 800; font-size: 1.1rem; width: 24px; color: ${idx === 0 ? '#fbbf24' : 'var(--text-muted)'};">#${idx + 1}</span>
										<strong>${item.display_name}</strong>
									</div>
									<span style="font-weight: 700; color: #fbbf24;">${item.total_xp} XP</span>
								</div>
							`).join('')}
						</div>
					</div>
				</div>
			`;
			if (window.lucide) window.lucide.createIcons();
		} catch (e) {
			renderError('Não foi possível carregar o mural de conquistas.');
		}
	}

	/* ==========================================================================
	   11. Master Desk: Attendance Sheet
	   ========================================================================== */
	async function renderAttendanceDesk() {
		if (!state.activeCohortId) {
			renderError('Selecione uma turma para gerenciar o diário de presença.');
			return;
		}

		try {
			const data = await fetchAPI(`cohorts/${state.activeCohortId}/curriculum`);
			const sessions = data.sessions || [];

			contentRoot.innerHTML = `
				<div>
					<h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 8px;">Diário de Chamada das Sessões Ao Vivo</h2>
					<p style="color: var(--text-muted); margin-bottom: 24px;">Registre a presença de cada estudante nas aulas síncronas obrigatórias.</p>

					${sessions.length === 0 
						? '<div style="padding: 40px; text-align: center; color: var(--text-muted);">Nenhuma sessão síncrona cadastrada para esta turma.</div>'
						: `<div style="margin-bottom: 20px;">
							<label style="font-weight: 600; font-size: 0.9rem; margin-right: 12px;">Selecione a Sessão:</label>
							<select id="select-session-attendance" style="padding: 10px 14px; border-radius: var(--radius-sm); background: var(--bg-card); border: 1px solid var(--border); color: #fff;">
								${sessions.map(s => `<option value="${s.id}">${s.title} (${s.session_date || 'Data'})</option>`).join('')}
							</select>
						   </div>
						   <div id="attendance-sheet-target">Carregando lista de chamada...</div>`
					}
				</div>
			`;

			const selectSession = document.getElementById('select-session-attendance');
			if (selectSession) {
				selectSession.onchange = () => loadSessionAttendance(parseInt(selectSession.value, 10));
				loadSessionAttendance(parseInt(selectSession.value, 10));
			}
		} catch (e) {
			renderError('Erro ao carregar o diário de presença.');
		}
	}

	async function loadSessionAttendance(sessionId) {
		const target = document.getElementById('attendance-sheet-target');
		if (!target) return;

		try {
			const sheet = await fetchAPI(`sessions/${sessionId}/attendance`);

			let html = `
				<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 24px;">
					<table style="width: 100%; border-collapse: collapse; text-align: left;">
						<thead>
							<tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 0.85rem;">
								<th style="padding: 12px 16px;">Estudante</th>
								<th style="padding: 12px 16px;">Status de Presença</th>
								<th style="padding: 12px 16px;">Minutos</th>
								<th style="padding: 12px 16px;">Observação</th>
							</tr>
						</thead>
						<tbody>
			`;

			sheet.forEach((row, idx) => {
				html += `
					<tr style="border-bottom: 1px solid var(--border-light);" class="attendance-row" data-user-id="${row.user_id}">
						<td style="padding: 14px 16px;">
							<strong>${row.display_name}</strong>
							<span style="display: block; font-size: 0.75rem; color: var(--text-muted);">${row.user_email}</span>
						</td>
						<td style="padding: 14px 16px;">
							<select class="att-status" style="padding: 8px 12px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); color: #fff;">
								<option value="present" ${row.status === 'present' ? 'selected' : ''}>Presente</option>
								<option value="absent" ${row.status === 'absent' ? 'selected' : ''}>Falta</option>
								<option value="excused" ${row.status === 'excused' ? 'selected' : ''}>Justificada</option>
								<option value="late" ${row.status === 'late' ? 'selected' : ''}>Atraso</option>
							</select>
						</td>
						<td style="padding: 14px 16px;">
							<input type="number" class="att-minutes" value="${row.minutes_attended || 60}" style="width: 70px; padding: 8px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); color: #fff;">
						</td>
						<td style="padding: 14px 16px;">
							<input type="text" class="att-note" value="${row.note || ''}" placeholder="Justificativa..." style="width: 100%; padding: 8px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); color: #fff;">
						</td>
					</tr>
				`;
			});

			html += `
						</tbody>
					</table>
					<div style="display: flex; justify-content: flex-end; margin-top: 24px;">
						<button class="btn btn-primary" id="btn-save-attendance-sheet"><i data-lucide="check"></i> Salvar Diário de Chamada</button>
					</div>
				</div>
			`;

			target.innerHTML = html;
			if (window.lucide) window.lucide.createIcons();

			document.getElementById('btn-save-attendance-sheet').onclick = async () => {
				const rows = [];
				document.querySelectorAll('.attendance-row').forEach(tr => {
					rows.push({
						user_id: parseInt(tr.getAttribute('data-user-id'), 10),
						status: tr.querySelector('.att-status').value,
						minutes: parseInt(tr.querySelector('.att-minutes').value, 10),
						note: tr.querySelector('.att-note').value
					});
				});

				await fetchAPI(`sessions/${sessionId}/attendance`, 'POST', { attendances: rows });
				showToast('Diário de chamada salvo com sucesso!', 'success');
			};
		} catch (e) {
			target.innerHTML = '<p style="color:var(--danger);">Erro ao carregar folha de chamada.</p>';
		}
	}

	/* ==========================================================================
	   12. Master Desk: Grading Desk (Bancada de Correção com Rúbricas)
	   ========================================================================== */
	async function renderGradingDesk() {
		if (!state.activeCohortId) {
			renderError('Selecione uma turma para acessar a bancada de correção.');
			return;
		}

		try {
			const submissions = await fetchAPI(`cohorts/${state.activeCohortId}/submissions`);

			let html = `
				<div>
					<h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 8px;">Bancada de Correção de Trabalhos Práticos</h2>
					<p style="color: var(--text-muted); margin-bottom: 24px;">Avalie projetos enviados pelos alunos atribuindo pareceres objetivos e rúbricas formativas.</p>
			`;

			if (submissions.length === 0) {
				html += `<div style="padding: 40px; text-align: center; color: var(--text-muted);">Nenhum trabalho submetido nesta turma ainda.</div>`;
			} else {
				html += `<div style="display: flex; flex-direction: column; gap: 16px;">`;
				submissions.forEach(sub => {
					html += `
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 24px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
								<div>
									<h3 style="font-size: 1.2rem; font-weight: 700;">${sub.assignment_title}</h3>
									<span style="font-size: 0.85rem; color: var(--text-muted);">Estudante: <strong>${sub.display_name}</strong> (${sub.user_email})</span>
								</div>
								<span class="badge" style="background: ${sub.status === 'approved' ? 'var(--success-bg)' : 'var(--warning-bg)'}; color: ${sub.status === 'approved' ? 'var(--success)' : 'var(--warning)'}; font-weight: 700; padding: 6px 14px; border-radius: 999px;">
									${sub.status.toUpperCase()}
								</span>
							</div>

							<div style="background: var(--bg-surface); padding: 16px; border-radius: var(--radius-md); margin-bottom: 16px;">
								<p style="font-size: 0.95rem; margin-bottom: 8px;"><strong>Memorial:</strong> ${sub.content_text || 'Não informado'}</p>
								${sub.content_url ? `<p style="font-size: 0.9rem;"><strong>Link do Projeto:</strong> <a href="${sub.content_url}" target="_blank" style="color: var(--primary);">${sub.content_url}</a></p>` : ''}
							</div>

							<div style="display: flex; justify-content: flex-end; gap: 12px;">
								<button class="btn btn-primary btn-sm" onclick="window.StackEAD_App.openGradeSubmissionModal(${sub.id}, '${sub.display_name}')">
									<i data-lucide="edit"></i> Avaliar / Corrigir
								</button>
							</div>
						</div>
					`;
				});
				html += `</div>`;
			}

			html += `</div>`;
			contentRoot.innerHTML = html;
			if (window.lucide) window.lucide.createIcons();
		} catch (e) {
			renderError('Erro ao carregar bancada de correção.');
		}
	}

	function openGradeSubmissionModal(subId, studentName) {
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.style = `position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 20px;`;
		modal.innerHTML = `
			<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 28px; max-width: 680px; width: 100%; max-height: 90vh; overflow-y: auto;">
				<div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border); padding-bottom: 14px; margin-bottom: 20px;">
					<div>
						<span style="font-size: 0.75rem; text-transform: uppercase; color: var(--primary); font-weight: 700;">Bancada de Avaliação</span>
						<h3 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin: 0 0 4px;">Avaliar Trabalho de ${escapeHtml(studentName)}</h3>
					</div>
					<button type="button" class="btn-icon btn-close-modal" style="background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;">&times;</button>
				</div>

				<form id="grade-form">
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
						<div class="form-group">
							<label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Nota Global (0 a 100) *</label>
							<input type="number" id="grade-score" min="0" max="100" value="85" required class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; background: var(--bg-main); border: 1px solid var(--border); color: var(--text-main);">
						</div>
						<div class="form-group">
							<label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Status da Avaliação</label>
							<select id="grade-status" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; background: var(--bg-main); border: 1px solid var(--border); color: var(--text-main);">
								<option value="approved">Aprovado (Concede +100 XP)</option>
								<option value="revision_requested">Solicitar Revisão ao Aluno</option>
							</select>
						</div>
					</div>

					<div class="form-group" style="margin-bottom: 24px;">
						<label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Parecer Formativo do Mestre (Editor WYSIWYG)</label>
						<div id="grade-feedback-editor"></div>
					</div>

					<div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 18px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="btn-save-grade">
							<i data-lucide="check"></i> Registrar Parecer Formativo
						</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		const feedbackEditor = createWysiwygEditor(modal.querySelector('#grade-feedback-editor'), '', {
			minHeight: '140px',
			placeholder: 'Escreva um parecer formativo construtivo destacando pontos fortes e oportunidades de aprimoramento...'
		});

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		modal.querySelector('#grade-form').onsubmit = async (e) => {
			e.preventDefault();
			const saveBtn = modal.querySelector('#btn-save-grade');
			saveBtn.disabled = true;
			saveBtn.textContent = 'Registrando...';

			try {
				await fetchAPI(`submissions/${subId}/grade`, 'POST', {
					score: parseFloat(document.getElementById('grade-score').value),
					status: document.getElementById('grade-status').value,
					feedback: feedbackEditor.getHtml(),
					rubric_scores: { technical: 90, concept: 85 }
				});
				modal.remove();
				showToast('Parecer registrado com sucesso! O estudante foi notificado (+XP concedido se aprovado).', 'success');
				renderGradingDesk();
			} catch (err) {
				saveBtn.disabled = false;
				saveBtn.textContent = 'Registrar Parecer Formativo';
				showToast(err.message || 'Erro ao registrar parecer.', 'danger');
			}
		};
	}

	/* ==========================================================================
	   13. Coordinator Desk: COGA Accommodations
	   ========================================================================== */
	async function renderAccommodationsDesk() {
		try {
			const requests = await fetchAPI('accommodations');

			let html = `
				<div>
					<h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 8px;">Laudos e Acomodações de Acessibilidade (COGA)</h2>
					<p style="color: var(--text-muted); margin-bottom: 24px;">Gestão sigilosa de adaptações curriculares para estudantes neurodivergentes.</p>
			`;

			if (requests.length === 0) {
				html += `<div style="padding: 40px; text-align: center; color: var(--text-muted);">Nenhum pedido de acomodação pendente.</div>`;
			} else {
				html += `<div style="display: flex; flex-direction: column; gap: 16px;">`;
				requests.forEach(r => {
					html += `
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 24px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
								<div>
									<strong>${r.display_name}</strong> (${r.user_email})
									<span style="display: block; font-size: 0.8rem; color: var(--accent); font-weight: 600;">Recurso: ${r.resource_type.toUpperCase()}</span>
								</div>
								<span class="badge" style="background: ${r.status === 'approved' ? 'var(--success-bg)' : 'var(--warning-bg)'}; color: ${r.status === 'approved' ? 'var(--success)' : 'var(--warning)'}; font-weight: 700; padding: 4px 12px; border-radius: 999px;">
									${r.status.toUpperCase()}
								</span>
							</div>
							<p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 16px;">${r.instructions}</p>
							${r.status === 'requested' ? `
								<div style="display: flex; gap: 10px;">
									<button class="btn btn-primary btn-sm" onclick="window.StackEAD_App.reviewAcc(${r.id}, 'approved')">Aprovar Acomodação</button>
									<button class="btn btn-secondary btn-sm" onclick="window.StackEAD_App.reviewAcc(${r.id}, 'rejected')">Recusar</button>
								</div>
							` : ''}
						</div>
					`;
				});
				html += `</div>`;
			}

			html += `</div>`;
			contentRoot.innerHTML = html;
		} catch (e) {
			renderError('Erro ao carregar acomodações.');
		}
	}

	async function reviewAcc(id, status) {
		await fetchAPI(`accommodations/${id}/review`, 'POST', { status: status });
		showToast(`Acomodação ${status === 'approved' ? 'aprovada' : 'recusada'}!`, 'success');
		renderAccommodationsDesk();
	}

	/* ==========================================================================
	   14. Coordinator Desk: Finance & Subscriptions
	   ========================================================================== */
	async function renderFinanceDesk() {
		try {
			const summary = await fetchAPI('finance/summary');

			contentRoot.innerHTML = `
				<div>
					<h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 8px;">Painel Financeiro & Assinaturas</h2>
					<p style="color: var(--text-muted); margin-bottom: 24px;">Controle de receita, assinantes ativos e pedidos de cursos.</p>

					<div class="stats-grid">
						<div class="stat-card">
							<div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
								<i data-lucide="dollar-sign"></i>
							</div>
							<div class="stat-info">
								<h4>Receita Total</h4>
								<div class="stat-value">${window.StackEAD_I18n ? window.StackEAD_I18n.formatPrice(summary.total_revenue) : 'R$ ' + summary.total_revenue.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</div>
							</div>
						</div>

						<div class="stat-card">
							<div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #6366f1;">
								<i data-lucide="users"></i>
							</div>
							<div class="stat-info">
								<h4>Assinantes Ativos</h4>
								<div class="stat-value">${summary.active_subscribers}</div>
							</div>
						</div>

						<div class="stat-card">
							<div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
								<i data-lucide="shopping-cart"></i>
							</div>
							<div class="stat-info">
								<h4>Pedidos Pagos</h4>
								<div class="stat-value">${summary.orders_count}</div>
							</div>
						</div>
					</div>

					<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 24px;">
						<h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 16px;">Transações Recentes</h3>
						<table style="width: 100%; border-collapse: collapse; text-align: left;">
							<thead>
								<tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 0.85rem;">
									<th style="padding: 10px 14px;">Usuário</th>
									<th style="padding: 10px 14px;">Plano</th>
									<th style="padding: 10px 14px;">Valor</th>
									<th style="padding: 10px 14px;">Método</th>
									<th style="padding: 10px 14px;">Status</th>
								</tr>
							</thead>
							<tbody>
								${summary.recent_orders.length === 0 
									? '<tr><td colspan="5" style="padding: 24px; text-align: center; color: var(--text-muted);">Nenhum pedido recente.</td></tr>' 
									: summary.recent_orders.map(o => `
										<tr style="border-bottom: 1px solid var(--border-light); font-size: 0.9rem;">
											<td style="padding: 12px 14px;"><strong>${o.display_name}</strong></td>
											<td style="padding: 12px 14px;">${o.plan_key}</td>
											<td style="padding: 12px 14px;">${window.StackEAD_I18n ? window.StackEAD_I18n.formatPrice(o.amount) : 'R$ ' + parseFloat(o.amount).toFixed(2)}</td>
											<td style="padding: 12px 14px; text-transform: uppercase;">${o.payment_method}</td>
											<td style="padding: 12px 14px;"><span class="badge" style="background: var(--success-bg); color: var(--success); font-weight: 700; padding: 4px 10px; border-radius: 999px;">${o.status}</span></td>
										</tr>
									`).join('')}
							</tbody>
						</table>
					</div>
				</div>
			`;
			if (window.lucide) window.lucide.createIcons();
		} catch (e) {
			renderError('Erro ao carregar dados financeiros.');
		}
	}

	/* ==========================================================================
	   Accommodation Modal
	   ========================================================================== */
	function openAccommodationModal() {
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.style = `position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 20px;`;
		modal.innerHTML = `
			<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 32px; max-width: 540px; width: 100%;">
				<h3 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 8px;">Solicitar Acomodação Pedagógica</h3>
				<p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px;">Seu pedido é estritamente confidencial e será analisado pela Coordenação Pedagógica.</p>

				<form id="acc-request-form">
					<div style="margin-bottom: 16px;">
						<label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Recurso Desejado</label>
						<select id="acc-type" style="width: 100%; padding: 10px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); color: #fff;">
							<option value="extra_time">Tempo Extra em Quizzes e Avaliações</option>
							<option value="simplified_language">Versão em Linguagem Simplificada Padrão</option>
							<option value="focus_mode">Modo Sem Distrações Automático</option>
							<option value="reading_guide">Guia de Leitura Padrão</option>
						</select>
					</div>
					<div style="margin-bottom: 24px;">
						<label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Necessidades / Justificativa (Opcional laudo/laudo prévio)</label>
						<textarea id="acc-notes" rows="4" required placeholder="Tenho TDAH/dislexia e necessito de tempo adicional para resolução de quizzes..." style="width: 100%; padding: 10px; border-radius: var(--radius-sm); background: var(--bg-surface); border: 1px solid var(--border); color: #fff; font-family: inherit;"></textarea>
					</div>
					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary" id="btn-close-acc">Cancelar</button>
						<button type="submit" class="btn btn-primary">Enviar à Coordenação</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		document.getElementById('btn-close-acc').onclick = () => modal.remove();
		document.getElementById('acc-request-form').onsubmit = async (e) => {
			e.preventDefault();
			await fetchAPI('accommodations', 'POST', {
				resource_type: document.getElementById('acc-type').value,
				instructions: document.getElementById('acc-notes').value
			});
			modal.remove();
			showToast('Solicitação de acomodação enviada à coordenação!', 'success');
		};
	}

	/* ==========================================================================
	   Courses Admin / Creator View
	   ========================================================================== */
	async function renderCoursesAdminView() {
		try {
			const courses = await fetchAPI('courses');

			let html = `
				<div class="courses-admin-wrapper">
					<div class="desk-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
						<div>
							<h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">Gestão do Catálogo de Cursos & Ementas</h2>
							<p style="color: var(--text-muted); margin: 0; font-size: 0.92rem;">Crie novos cursos, elabore ementas, defina cargas horárias, precificação e configure landing pages.</p>
						</div>
						<div style="display: flex; gap: 12px;">
							<button id="btn-create-course-modal" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
								<i data-lucide="plus-circle"></i>
								<span>Cadastrar Novo Curso</span>
							</button>
						</div>
					</div>

					<div class="courses-admin-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px;">
			`;

			if (!courses || courses.length === 0) {
				html += `
					<div class="empty-state" style="grid-column: 1 / -1; padding: 48px; text-align: center; background: var(--bg-surface); border: 1px dashed var(--border); border-radius: var(--radius-lg);">
						<i data-lucide="book-open" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 12px;"></i>
						<h3>Nenhum curso cadastrado</h3>
						<p style="color: var(--text-muted); margin-bottom: 20px;">Comece cadastrando o primeiro curso da sua plataforma EAD.</p>
						<button class="btn btn-primary" id="btn-empty-create-course" onclick="window.StackEAD_App.openCreateCourseModal()">Cadastrar Primeiro Curso</button>
					</div>
				`;
			} else {
				courses.forEach(c => {
					const hasThumb = c.thumbnail && c.thumbnail.length > 5;
					html += `
						<div class="course-admin-card" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease;">
							${hasThumb ? `
								<div style="height: 150px; overflow: hidden; background: #000; position: relative;">
									<img src="${c.thumbnail}" alt="${c.title}" style="width: 100%; height: 100%; object-fit: cover;">
									<div style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; color: #fff;">
										${c.workload} HORAS
									</div>
								</div>
							` : ''}
							<div style="padding: 22px; flex: 1; display: flex; flex-direction: column;">
								${!hasThumb ? `
									<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
										<span class="badge" style="background: rgba(37, 99, 235, 0.15); color: #60a5fa; padding: 4px 10px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;">
											${c.workload} HORAS
										</span>
										<span style="font-weight: 700; color: var(--accent); font-size: 0.9rem;">
											${c.price_type === 'free' ? 'Gratuito' : (window.StackEAD_I18n ? window.StackEAD_I18n.formatPrice(c.price) : 'R$ ' + parseFloat(c.price).toFixed(2))}
										</span>
									</div>
								` : `
									<div style="display: flex; justify-content: flex-end; margin-bottom: 8px;">
										<span style="font-weight: 700; color: var(--accent); font-size: 0.9rem;">
											${c.price_type === 'free' ? 'Gratuito' : (window.StackEAD_I18n ? window.StackEAD_I18n.formatPrice(c.price) : 'R$ ' + parseFloat(c.price).toFixed(2))}
										</span>
									</div>
								`}
								<h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin: 0 0 8px;">${c.title}</h3>
								${c.subtitle ? `<p style="font-size: 0.88rem; color: var(--text-muted); font-style: italic; margin-bottom: 12px;">${c.subtitle}</p>` : ''}
								${c.syllabus ? `
									<div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 6px; padding: 10px 12px; margin-bottom: 16px; max-height: 90px; overflow-y: auto; font-size: 0.82rem; color: var(--text-muted);">
										<strong>Ementa:</strong> ${c.syllabus}
									</div>
								` : ''}
							</div>
							<div style="display: flex; gap: 8px; padding: 14px 22px; background: rgba(0,0,0,0.15); border-top: 1px solid var(--border);">
								<button type="button" class="btn btn-sm btn-primary btn-edit-course" data-course-id="${c.id}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;">
									<i data-lucide="edit-3"></i>
									<span>Editar Curso</span>
								</button>
								<button type="button" class="btn btn-sm btn-secondary btn-landing-builder-course" data-course-id="${c.id}" title="Personalizar Landing Page" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
									<i data-lucide="layout"></i>
									<span>Landing</span>
								</button>
								<button type="button" class="btn btn-sm btn-delete-course" data-course-id="${c.id}" data-course-title="${c.title}" title="Excluir Curso" style="padding: 6px 10px; background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer;">
									<i data-lucide="trash-2"></i>
								</button>
							</div>
						</div>
					`;
				});
			}

			html += `</div>`;
			contentRoot.innerHTML = html;
			if (window.lucide) window.lucide.createIcons();

			// Listeners
			const addBtn = document.getElementById('btn-create-course-modal');
			if (addBtn) {
				addBtn.onclick = () => openCreateCourseModal();
			}
			const emptyCreateCourseBtn = document.getElementById('btn-empty-create-course');
			if (emptyCreateCourseBtn) {
				emptyCreateCourseBtn.onclick = () => openCreateCourseModal();
			}

			// Edit course buttons
			document.querySelectorAll('.btn-edit-course').forEach(btn => {
				btn.addEventListener('click', () => {
					const cid = parseInt(btn.getAttribute('data-course-id'), 10);
					openEditCourseModal(cid);
				});
			});

			// Landing builder jump
			document.querySelectorAll('.btn-landing-builder-course').forEach(btn => {
				btn.addEventListener('click', () => {
					const cid = parseInt(btn.getAttribute('data-course-id'), 10);
					state.builderCourseId = cid;
					navigate('landing-builder');
				});
			});

			// Delete course buttons
			document.querySelectorAll('.btn-delete-course').forEach(btn => {
				btn.addEventListener('click', async () => {
					const cid = parseInt(btn.getAttribute('data-course-id'), 10);
					const title = btn.getAttribute('data-course-title') || 'este curso';
					if (!confirm(`Deseja realmente excluir "${title}"?\nEsta ação apagará permanentemente o curso e é irreversível.`)) {
						return;
					}
					try {
						await fetchAPI(`courses/${cid}`, 'DELETE');
						showToast('Curso excluído com sucesso!', 'success');
						renderCoursesAdminView();
					} catch (err) {
						showToast(err.message || 'Erro ao excluir curso.', 'danger');
					}
				});
			});

		} catch (err) {
			renderError('Erro ao carregar lista de cursos: ' + err.message);
		}
	}

	async function deleteCourse(courseId, title = 'este curso') {
		if (!confirm(`Deseja realmente excluir "${title}"?\nEsta ação apagará permanentemente o curso e é irreversível.`)) {
			return false;
		}
		try {
			await fetchAPI(`courses/${courseId}`, 'DELETE');
			showToast('Curso excluído com sucesso!', 'success');
			renderCoursesAdminView();
			return true;
		} catch (err) {
			showToast(err.message || 'Erro ao excluir curso.', 'danger');
			return false;
		}
	}

	function openCreateCourseModal() {
		let uploadedAttachmentId = 0;
		let uploadedCoverUrl = '';

		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 100001; display: flex; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;';
		modal.innerHTML = `
			<div class="modal-card" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-xl); max-width: 720px; width: 100%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); max-height: 90vh; overflow-y: auto;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 14px;">
					<h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
						<i data-lucide="book-plus" style="color: var(--primary);"></i>
						Cadastrar Novo Curso
					</h3>
					<button type="button" class="btn-icon" id="btn-close-course-modal"><i data-lucide="x"></i></button>
				</div>
				<form id="create-course-form">
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Título do Curso *</label>
						<input type="text" id="course-title" required class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);" placeholder="Ex.: Residência em Inteligência Artificial e Criatividade">
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Subtítulo / Chamada de Destaque</label>
						<input type="text" id="course-subtitle" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);" placeholder="Ex.: Formação imersiva prática com certificação internacional">
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Ementa Completa / Tópicos Abordados *</label>
						<textarea id="course-syllabus" rows="3" required class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);" placeholder="Descreva os temas, módulos e o conteúdo programático do curso..."></textarea>
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Descrição Detalhada do Curso</label>
						<textarea id="course-desc" rows="3" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);" placeholder="Informações aprofundadas sobre objetivos pedagógicos, público-alvo e metodologia..."></textarea>
					</div>

					<!-- Cover Image with Crop 16:9 -->
					<div class="form-group" style="margin-bottom: 16px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; padding: 14px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 8px;">
							<i data-lucide="image"></i> Capa do Curso (16:9 - WebP < 150KB)
						</label>
						<div style="display: flex; gap: 14px; align-items: center;">
							<div id="course-create-cover-preview" style="width: 120px; height: 68px; border-radius: 6px; background: #000; border: 1px dashed var(--border); display: flex; align-items: center; justify-content: center; overflow: hidden;">
								<span style="font-size: 0.72rem; color: var(--text-muted);">Sem capa</span>
							</div>
							<div>
								<button type="button" class="btn btn-secondary btn-sm" id="btn-crop-cover-create">
									<i data-lucide="crop"></i> Selecionar e Recortar Capa
								</button>
								<span style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Recorte visual 16:9 em canvas e compressão automática.</span>
							</div>
						</div>
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
						<div class="form-group">
							<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Carga Horária (Horas)</label>
							<input type="number" id="course-workload" value="40" min="1" max="1000" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Modelo de Cobrança</label>
							<select id="course-price-type" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
								<option value="free">Gratuito</option>
								<option value="paid">Pago (R$)</option>
								<option value="subscription">Assinatura</option>
							</select>
						</div>
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Preço Regular (R$)</label>
						<input type="number" step="0.01" id="course-price" value="0.00" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
					</div>
					<div class="form-group" style="margin-bottom: 24px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">♿ Resumo em Linguagem Simples (W3C COGA)</label>
						<textarea id="course-coga" rows="2" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);" placeholder="Versão acessível e direta sem jargões complexos..."></textarea>
					</div>
					<div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 16px;">
						<button type="button" class="btn btn-secondary" id="btn-cancel-course">Cancelar</button>
						<button type="submit" class="btn btn-primary" style="display: flex; align-items: center; gap: 6px;">
							<i data-lucide="check"></i>
							<span>Salvar e Criar Curso</span>
						</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		document.getElementById('btn-close-course-modal').onclick = () => modal.remove();
		document.getElementById('btn-cancel-course').onclick = () => modal.remove();

		// Cover Crop button
		document.getElementById('btn-crop-cover-create').onclick = () => {
			openCropModal({
				aspectRatio: 16 / 9,
				preset: 'course_cover',
				title: 'Recortar Capa do Curso (16:9)',
				onSuccess: (res) => {
					uploadedAttachmentId = res.attachment_id || 0;
					uploadedCoverUrl = res.url || '';
					const previewEl = document.getElementById('course-create-cover-preview');
					if (previewEl && uploadedCoverUrl) {
						previewEl.innerHTML = `<img src="${uploadedCoverUrl}" style="width:100%; height:100%; object-fit:cover;">`;
					}
					showToast('Capa recortada e otimizada com sucesso!', 'success');
				}
			});
		};

		document.getElementById('create-course-form').onsubmit = async (e) => {
			e.preventDefault();
			const submitBtn = e.target.querySelector('button[type="submit"]');
			submitBtn.disabled = true;
			submitBtn.textContent = 'Salvando...';

			try {
				await fetchAPI('courses', 'POST', {
					title: document.getElementById('course-title').value,
					subtitle: document.getElementById('course-subtitle').value,
					syllabus: document.getElementById('course-syllabus').value,
					description: document.getElementById('course-desc').value,
					workload_hours: parseInt(document.getElementById('course-workload').value, 10),
					price_type: document.getElementById('course-price-type').value,
					price: parseFloat(document.getElementById('course-price').value),
					simplified_summary: document.getElementById('course-coga').value,
					attachment_id: uploadedAttachmentId
				});

				modal.remove();
				showToast('Curso cadastrado com sucesso!', 'success');
				await loadUserCohorts();
				renderCoursesAdminView();
			} catch (err) {
				submitBtn.disabled = false;
				submitBtn.textContent = 'Salvar e Criar Curso';
				showToast(err.message || 'Erro ao criar curso.', 'danger');
			}
		};
	}

	async function openEditCourseModal(courseId) {
		renderLoading();
		let course;
		try {
			course = await fetchAPI(`courses/${courseId}`);
		} catch (err) {
			showToast('Não foi possível carregar os dados do curso.', 'danger');
			renderCoursesAdminView();
			return;
		}

		let uploadedAttachmentId = 0;
		let uploadedCoverUrl = course.thumbnail || '';

		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 100001; display: flex; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;';
		modal.innerHTML = `
			<div class="modal-card" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-xl); max-width: 720px; width: 100%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); max-height: 90vh; overflow-y: auto;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 14px;">
					<h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
						<i data-lucide="edit" style="color: var(--primary);"></i>
						Editar Curso: ${course.title}
					</h3>
					<button type="button" class="btn-icon" id="btn-close-edit-course-modal"><i data-lucide="x"></i></button>
				</div>
				<form id="edit-course-form">
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Título do Curso *</label>
						<input type="text" id="edit-course-title" required class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);" value="${course.title || ''}">
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Subtítulo / Chamada de Destaque</label>
						<input type="text" id="edit-course-subtitle" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);" value="${course.subtitle || ''}">
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Ementa Completa / Tópicos Abordados *</label>
						<textarea id="edit-course-syllabus" rows="3" required class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">${course.syllabus || ''}</textarea>
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Descrição Detalhada do Curso</label>
						<textarea id="edit-course-desc" rows="3" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">${course.description || ''}</textarea>
					</div>

					<!-- Cover Image with Crop 16:9 -->
					<div class="form-group" style="margin-bottom: 16px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; padding: 14px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 8px;">
							<i data-lucide="image"></i> Capa do Curso (16:9 - WebP < 150KB)
						</label>
						<div style="display: flex; gap: 14px; align-items: center;">
							<div id="course-edit-cover-preview" style="width: 120px; height: 68px; border-radius: 6px; background: #000; border: 1px dashed var(--border); display: flex; align-items: center; justify-content: center; overflow: hidden;">
								${uploadedCoverUrl ? `<img src="${uploadedCoverUrl}" style="width:100%; height:100%; object-fit:cover;">` : '<span style="font-size:0.72rem; color:var(--text-muted);">Sem capa</span>'}
							</div>
							<div>
								<button type="button" class="btn btn-secondary btn-sm" id="btn-crop-cover-edit">
									<i data-lucide="crop"></i> Alterar e Recortar Capa
								</button>
								<span style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Substitui a capa por imagem WebP otimizada em 16:9.</span>
							</div>
						</div>
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
						<div class="form-group">
							<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Carga Horária (Horas)</label>
							<input type="number" id="edit-course-workload" value="${course.workload_hours || 40}" min="1" max="1000" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Modelo de Cobrança</label>
							<select id="edit-course-price-type" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
								<option value="free" ${course.price_type === 'free' ? 'selected' : ''}>Gratuito</option>
								<option value="paid" ${course.price_type === 'paid' ? 'selected' : ''}>Pago (R$)</option>
								<option value="subscription" ${course.price_type === 'subscription' ? 'selected' : ''}>Assinatura</option>
							</select>
						</div>
					</div>
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">Preço Regular (R$)</label>
						<input type="number" step="0.01" id="edit-course-price" value="${course.price || '0.00'}" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
					</div>
					<div class="form-group" style="margin-bottom: 24px;">
						<label style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--text-main); margin-bottom: 6px;">♿ Resumo em Linguagem Simples (W3C COGA)</label>
						<textarea id="edit-course-coga" rows="2" class="form-input" style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">${course.simplified_summary || ''}</textarea>
					</div>
					<div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border); padding-top: 16px;">
						<button type="button" class="btn btn-secondary" id="btn-cancel-edit-course">Cancelar</button>
						<button type="submit" class="btn btn-primary" style="display: flex; align-items: center; gap: 6px;">
							<i data-lucide="check"></i>
							<span>Salvar Alterações</span>
						</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		document.getElementById('btn-close-edit-course-modal').onclick = () => modal.remove();
		document.getElementById('btn-cancel-edit-course').onclick = () => modal.remove();

		// Cover Crop button for edit
		document.getElementById('btn-crop-cover-edit').onclick = () => {
			openCropModal({
				aspectRatio: 16 / 9,
				preset: 'course_cover',
				title: 'Alterar Capa do Curso (16:9)',
				onSuccess: (res) => {
					uploadedAttachmentId = res.attachment_id || 0;
					uploadedCoverUrl = res.url || '';
					const previewEl = document.getElementById('course-edit-cover-preview');
					if (previewEl && uploadedCoverUrl) {
						previewEl.innerHTML = `<img src="${uploadedCoverUrl}" style="width:100%; height:100%; object-fit:cover;">`;
					}
					showToast('Nova capa recortada e otimizada com sucesso!', 'success');
				}
			});
		};

		document.getElementById('edit-course-form').onsubmit = async (e) => {
			e.preventDefault();
			const submitBtn = e.target.querySelector('button[type="submit"]');
			submitBtn.disabled = true;
			submitBtn.textContent = 'Atualizando...';

			try {
				const payload = {
					title: document.getElementById('edit-course-title').value,
					subtitle: document.getElementById('edit-course-subtitle').value,
					syllabus: document.getElementById('edit-course-syllabus').value,
					description: document.getElementById('edit-course-desc').value,
					workload_hours: parseInt(document.getElementById('edit-course-workload').value, 10),
					price_type: document.getElementById('edit-course-price-type').value,
					price: parseFloat(document.getElementById('edit-course-price').value),
					simplified_summary: document.getElementById('edit-course-coga').value
				};

				if (uploadedAttachmentId > 0) {
					payload.attachment_id = uploadedAttachmentId;
				}

				await fetchAPI(`courses/${courseId}`, 'POST', payload);

				modal.remove();
				showToast('Curso atualizado com sucesso!', 'success');
				renderCoursesAdminView();
			} catch (err) {
				submitBtn.disabled = false;
				submitBtn.textContent = 'Salvar Alterações';
				showToast(err.message || 'Erro ao atualizar curso.', 'danger');
			}
		};
	}


	/* ==========================================================================
	   14. White Label Desk (Customização da Instituição & Modo da Home)
	   ========================================================================== */
	async function renderWhiteLabelDesk() {
		try {
			const settings = await fetchAPI('settings/whitelabel');
			const courses = await fetchAPI('courses').catch(() => []);

			contentRoot.innerHTML = `
				<div class="whitelabel-desk-wrapper" style="max-width: 1040px; margin: 0 auto;">
					<div class="desk-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
						<div>
							<h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">Identidade & White Label</h2>
							<p style="color: var(--text-muted); font-size: 0.95rem;">Personalize a identidade da instituição, logotipos, paleta de cores e o modo da página inicial.</p>
						</div>
						<div style="display: flex; gap: 12px;">
							<a href="${window.StackEAD_Config.homeUrl}/landing/" target="_blank" class="btn btn-secondary">
								<i data-lucide="external-link"></i> Ver Landing Page
							</a>
						</div>
					</div>

					<form id="form-whitelabel" style="display: flex; flex-direction: column; gap: 24px;">
						
						<!-- 1. Identidade Visual -->
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 28px;">
							<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
								<i data-lucide="palette" style="color: var(--primary);"></i>
								<h3 style="font-size: 1.2rem; font-weight: 700;">1. Identidade da Marca & Cores</h3>
							</div>

							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Nome da Instituição *</label>
									<input type="text" id="wl-name" value="${settings.institution_name || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required>
								</div>
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Slogan / Tagline Institucional</label>
									<input type="text" id="wl-tagline" value="${settings.institution_tagline || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								</div>
							</div>

							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Cor Primária (Brand Accent)</label>
									<div style="display: flex; gap: 12px; align-items: center;">
										<input type="color" id="wl-primary-color" value="${settings.primary_color || '#4f46e5'}" style="width: 48px; height: 42px; border-radius: 8px; border: none; cursor: pointer; padding: 2px;">
										<input type="text" id="wl-primary-hex" value="${settings.primary_color || '#4f46e5'}" class="form-control" style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
									</div>
								</div>
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Cor Secundária (Accent Secundário)</label>
									<div style="display: flex; gap: 12px; align-items: center;">
										<input type="color" id="wl-secondary-color" value="${settings.secondary_color || '#06b6d4'}" style="width: 48px; height: 42px; border-radius: 8px; border: none; cursor: pointer; padding: 2px;">
										<input type="text" id="wl-secondary-hex" value="${settings.secondary_color || '#06b6d4'}" class="form-control" style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
									</div>
								</div>
							</div>

							<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px;">
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Logotipo Versão Clara (URL)</label>
									<input type="url" id="wl-logo" value="${settings.logo_url || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								</div>
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Logotipo Versão Escura (URL)</label>
									<input type="url" id="wl-logo-dark" value="${settings.logo_dark_url || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								</div>
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Favicon da Aba (URL)</label>
									<input type="url" id="wl-favicon" value="${settings.favicon_url || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="https://.../favicon.ico">
								</div>
							</div>
						</div>

						<!-- 2. Modo da Página Inicial -->
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 28px;">
							<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
								<i data-lucide="layout" style="color: var(--primary);"></i>
								<h3 style="font-size: 1.2rem; font-weight: 700;">2. Configuração da Página Inicial (Home)</h3>
							</div>

							<div style="display: flex; flex-direction: column; gap: 14px;">
								<label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; padding: 14px; border-radius: 8px; background: var(--bg-main); border: 1px solid var(--border);">
									<input type="radio" name="homepage_mode" value="course_landing" ${settings.homepage_mode === 'course_landing' ? 'checked' : ''} style="margin-top: 3px;">
									<div>
										<strong style="color: var(--text-main); font-size: 1rem;">Landing Page de Curso Específico (Alta Conversão)</strong>
										<p style="color: var(--text-muted); font-size: 0.88rem; margin: 4px 0 0;">A página principal (/) carrega diretamente a Landing Page de vendas do curso selecionado (Template ArtInnova Light com garantias, vídeo, métricas e inscrição direta).</p>
									</div>
								</label>

								<label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; padding: 14px; border-radius: 8px; background: var(--bg-main); border: 1px solid var(--border);">
									<input type="radio" name="homepage_mode" value="institutional_portal" ${settings.homepage_mode === 'institutional_portal' ? 'checked' : ''} style="margin-top: 3px;">
									<div>
										<strong style="color: var(--text-main); font-size: 1rem;">Portal Institucional Multicursos</strong>
										<p style="color: var(--text-muted); font-size: 0.88rem; margin: 4px 0 0;">Exibe a vitrine institucional completa com todos os cursos divididos em abas de categorias e mural de notícias recentes.</p>
									</div>
								</label>
							</div>

							<div class="form-group" style="margin-top: 18px;">
								<label style="display: block; font-weight: 600; margin-bottom: 6px;">Curso em Destaque na Home</label>
								<select id="wl-featured-course" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
									<option value="0">Residência Internacional ArtInnova (Padrão Oficial)</option>
									${courses.map(c => `
										<option value="${c.id}" ${settings.featured_course_id == c.id ? 'selected' : ''}>${c.title}</option>
									`).join('')}
								</select>
							</div>
						</div>

						<!-- 3. Contatos & Redes Sociais -->
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 28px;">
							<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
								<i data-lucide="phone-call" style="color: var(--primary);"></i>
								<h3 style="font-size: 1.2rem; font-weight: 700;">3. Canais de Atendimento & Redes</h3>
							</div>

							<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">E-mail de Suporte</label>
									<input type="email" id="wl-email" value="${settings.support_email || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								</div>
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">WhatsApp Oficial (apenas números)</label>
									<input type="text" id="wl-whatsapp" value="${settings.whatsapp_number || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="5583999999999">
								</div>
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Telefone Fixo</label>
									<input type="text" id="wl-phone" value="${settings.contact_phone || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								</div>
							</div>

							<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px;">
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Instagram URL</label>
									<input type="url" id="wl-instagram" value="${settings.instagram_url || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								</div>
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">YouTube URL</label>
									<input type="url" id="wl-youtube" value="${settings.youtube_url || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								</div>
								<div class="form-group">
									<label style="display: block; font-weight: 600; margin-bottom: 6px;">Endereço Físico</label>
									<input type="text" id="wl-address" value="${settings.physical_address || ''}" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								</div>
							</div>
						</div>

						<div style="display: flex; justify-content: flex-end; gap: 14px; margin-top: 10px;">
							<button type="submit" class="btn btn-primary btn-hero" style="padding: 14px 28px; font-size: 15px; font-weight: 700;">
								<i data-lucide="check"></i> Salvar Identidade White Label
							</button>
						</div>
					</form>
				</div>
			`;

			if (window.lucide) window.lucide.createIcons();

			// Bind Color Pickers Hex Sync
			const primaryColor = document.getElementById('wl-primary-color');
			const primaryHex = document.getElementById('wl-primary-hex');
			if (primaryColor && primaryHex) {
				primaryColor.addEventListener('input', (e) => primaryHex.value = e.target.value);
				primaryHex.addEventListener('input', (e) => primaryColor.value = e.target.value);
			}

			const secColor = document.getElementById('wl-secondary-color');
			const secHex = document.getElementById('wl-secondary-hex');
			if (secColor && secHex) {
				secColor.addEventListener('input', (e) => secHex.value = e.target.value);
				secHex.addEventListener('input', (e) => secColor.value = e.target.value);
			}

			// Form Submit Handler
			document.getElementById('form-whitelabel').addEventListener('submit', async (e) => {
				e.preventDefault();
				const submitBtn = e.target.querySelector('button[type="submit"]');
				submitBtn.disabled = true;
				submitBtn.innerHTML = '<div class="spinner-sm"></div> Salvando...';

				const selectedMode = document.querySelector('input[name="homepage_mode"]:checked')?.value || 'course_landing';

				const payload = {
					institution_name: document.getElementById('wl-name').value.trim(),
					institution_tagline: document.getElementById('wl-tagline').value.trim(),
					primary_color: document.getElementById('wl-primary-hex').value.trim(),
					secondary_color: document.getElementById('wl-secondary-hex').value.trim(),
					logo_url: document.getElementById('wl-logo').value.trim(),
					logo_dark_url: document.getElementById('wl-logo-dark').value.trim(),
					favicon_url: document.getElementById('wl-favicon').value.trim(),
					homepage_mode: selectedMode,
					featured_course_id: parseInt(document.getElementById('wl-featured-course').value, 10),
					support_email: document.getElementById('wl-email').value.trim(),
					whatsapp_number: document.getElementById('wl-whatsapp').value.trim(),
					contact_phone: document.getElementById('wl-phone').value.trim(),
					physical_address: document.getElementById('wl-address').value.trim(),
					instagram_url: document.getElementById('wl-instagram').value.trim(),
					youtube_url: document.getElementById('wl-youtube').value.trim()
				};

				try {
					await fetchAPI('settings/whitelabel', 'POST', payload);
					showToast('Configurações White Label salvas com sucesso!', 'success');
					// Instant CSS live update
					document.documentElement.style.setProperty('--brand-primary', payload.primary_color);
					document.documentElement.style.setProperty('--brand-secondary', payload.secondary_color);
				} catch (err) {
					showToast(err.message || 'Erro ao salvar configurações.', 'danger');
				} finally {
					submitBtn.disabled = false;
					submitBtn.innerHTML = '<i data-lucide="check"></i> Salvar Identidade White Label';
					if (window.lucide) window.lucide.createIcons();
				}
			});

		} catch (err) {
			renderError('Não foi possível carregar as configurações de White Label.');
		}
	}

	/* ==========================================================================
	   14.1. Modular Landing Page Builder Desk (Elementor Leve & Templates)
	   ========================================================================== */
	async function renderLandingBuilderView() {
		try {
			const courses = await fetchAPI('courses').catch(() => []);
			if (courses.length === 0) {
				contentRoot.innerHTML = `
					<div class="empty-state" style="padding: 48px; text-align: center;">
						<i data-lucide="alert-circle" style="width: 48px; height: 48px; color: var(--warning); margin-bottom: 12px;"></i>
						<h3>Nenhum curso encontrado</h3>
						<p>Cadastre ao menos um curso para poder customizar sua Landing Page de prospecção.</p>
						<button class="btn btn-primary" onclick="window.StackEAD_App.navigate('courses-admin')">Criar Curso Agora</button>
					</div>
				`;
				if (window.lucide) window.lucide.createIcons();
				return;
			}

			let selectedCourseId = state.builderCourseId || courses[0].id;
			const builderData = await fetchAPI(`courses/${selectedCourseId}/landing-builder`);

			let currentTemplate = builderData.template || 'artinnova_light';
			let blocks = builderData.blocks || [];
			let activeDevice = 'desktop';

			function renderBuilderLayout() {
				contentRoot.innerHTML = `
					<div class="builder-desk-container" style="display: flex; flex-direction: column; gap: 20px; height: calc(100vh - 120px);">
						
						<!-- Top Action Bar -->
						<div class="builder-topbar" style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px 20px;">
							<div style="display: flex; align-items: center; gap: 16px;">
								<div style="display: flex; align-items: center; gap: 8px;">
									<i data-lucide="layout" style="color: var(--primary);"></i>
									<strong style="font-size: 1.1rem; color: var(--text-main);">Construtor de Landing Page</strong>
								</div>
								<div style="display: flex; align-items: center; gap: 8px;">
									<label style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Curso:</label>
									<select id="builder-select-course" class="form-control" style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.88rem;">
										${courses.map(c => `<option value="${c.id}" ${c.id === selectedCourseId ? 'selected' : ''}>${c.title}</option>`).join('')}
									</select>
								</div>
							</div>

							<div style="display: flex; gap: 10px;">
								<button type="button" class="btn btn-secondary" id="btn-builder-reset" title="Restaurar blocos para o padrão do template">
									<i data-lucide="rotate-ccw"></i> Restaurar Padrão
								</button>
								<button type="button" class="btn btn-primary" id="btn-builder-save">
									<i data-lucide="check"></i> <span>Salvar Landing Page</span>
								</button>
							</div>
						</div>

						<!-- Main Editor Area: 2 Columns (Left: Blocks, Right: Live Preview) -->
						<div class="builder-grid" style="display: grid; grid-template-columns: 460px 1fr; gap: 20px; flex: 1; min-height: 0;">
							
							<!-- Left Column: Template & Blocks Accordion -->
							<div class="builder-editor-column" style="display: flex; flex-direction: column; gap: 16px; overflow-y: auto; padding-right: 6px;">
								
								<!-- Template Picker -->
								<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px;">
									<label style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 12px; color: var(--text-main);">
										<i data-lucide="palette"></i> Selecione o Template Base:
									</label>
									<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
										<div class="template-choice-card ${currentTemplate === 'artinnova_light' ? 'active' : ''}" data-tpl="artinnova_light" style="border: 2px solid ${currentTemplate === 'artinnova_light' ? 'var(--primary)' : 'var(--border)'}; border-radius: 8px; padding: 12px; cursor: pointer; background: var(--bg-main); text-align: center;">
											<div style="font-size: 1.4rem; margin-bottom: 4px;">🎨</div>
											<strong style="display: block; font-size: 0.85rem; color: var(--text-main);">ArtInnova Light</strong>
											<span style="font-size: 0.75rem; color: var(--text-muted);">Editorial & Artes</span>
										</div>
										<div class="template-choice-card ${currentTemplate === 'tech_future' ? 'active' : ''}" data-tpl="tech_future" style="border: 2px solid ${currentTemplate === 'tech_future' ? 'var(--primary)' : 'var(--border)'}; border-radius: 8px; padding: 12px; cursor: pointer; background: var(--bg-main); text-align: center;">
											<div style="font-size: 1.4rem; margin-bottom: 4px;">⚡</div>
											<strong style="display: block; font-size: 0.85rem; color: var(--text-main);">Tech Future Lab</strong>
											<span style="font-size: 0.75rem; color: var(--text-muted);">Inovação & Código</span>
										</div>
									</div>
								</div>

								<!-- Blocks List -->
								<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; flex: 1;">
									<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
										<strong style="font-size: 0.95rem; color: var(--text-main);">7 Blocos Modulares</strong>
										<span style="font-size: 0.75rem; color: var(--text-muted);">Use as setas para reordenar</span>
									</div>

									<div id="builder-blocks-list" style="display: flex; flex-direction: column; gap: 10px;">
										${renderBlocksAccordion(blocks)}
									</div>
								</div>

							</div>

							<!-- Right Column: Live Interactive Responsive Preview -->
							<div class="builder-preview-column" style="display: flex; flex-direction: column; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
								
								<!-- Preview Device Toolbar -->
								<div class="preview-toolbar" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 18px; border-bottom: 1px solid var(--border); background: var(--bg-main);">
									<div style="display: flex; gap: 6px;">
										<button type="button" class="btn btn-sm ${activeDevice === 'desktop' ? 'btn-primary' : 'btn-secondary'} btn-device" data-device="desktop" title="Visualização Desktop">
											<i data-lucide="monitor" style="width: 14px; height: 14px;"></i> Desktop
										</button>
										<button type="button" class="btn btn-sm ${activeDevice === 'tablet' ? 'btn-primary' : 'btn-secondary'} btn-device" data-device="tablet" title="Visualização Tablet (768px)">
											<i data-lucide="tablet" style="width: 14px; height: 14px;"></i> Tablet
										</button>
										<button type="button" class="btn btn-sm ${activeDevice === 'mobile' ? 'btn-primary' : 'btn-secondary'} btn-device" data-device="mobile" title="Visualização Mobile (375px)">
											<i data-lucide="smartphone" style="width: 14px; height: 14px;"></i> Mobile
										</button>
									</div>

									<div style="display: flex; gap: 8px;">
										<button type="button" class="btn btn-sm btn-secondary" id="btn-builder-refresh-preview" title="Recarregar Preview">
											<i data-lucide="refresh-cw" style="width: 13px; height: 13px;"></i> Atualizar
										</button>
										<a href="${window.StackEAD_Config.homeUrl}/landing/?preview_course_id=${selectedCourseId}&template=${currentTemplate}" target="_blank" class="btn btn-sm btn-secondary" id="btn-builder-external-preview" title="Abrir página pública em nova aba">
											<i data-lucide="external-link" style="width: 13px; height: 13px;"></i> Ver Página Pública
										</a>
									</div>
								</div>

								<!-- Preview Viewport Frame -->
								<div class="preview-viewport-wrapper" style="flex: 1; background: #0c101c; display: flex; align-items: center; justify-content: center; padding: 16px; overflow: hidden;">
									<iframe id="builder-preview-iframe" src="${window.StackEAD_Config.homeUrl}/landing/?preview_course_id=${selectedCourseId}&template=${currentTemplate}&t=${Date.now()}" style="width: ${getDeviceWidth(activeDevice)}; height: 100%; border: 1px solid var(--border); border-radius: 12px; background: #fff; box-shadow: 0 20px 50px rgba(0,0,0,0.5); transition: width 0.3s ease;"></iframe>
								</div>

							</div>

						</div>

					</div>
				`;

				if (window.lucide) window.lucide.createIcons();
				attachBuilderEvents();
			}

			function getDeviceWidth(dev) {
				if (dev === 'mobile') return '375px';
				if (dev === 'tablet') return '768px';
				return '100%';
			}

			function renderBlocksAccordion(blocksList) {
				const blockIcons = {
					hero: 'layout-template',
					metrics: 'bar-chart-2',
					curriculum: 'book-open',
					instructors: 'users',
					awards: 'award',
					investment: 'shield-check',
					faq: 'help-circle'
				};

				const blockNames = {
					hero: '1. Hero Header (Abertura)',
					metrics: '2. Barra de Métricas & Autoridade',
					curriculum: '3. Ementa & Trilha Formativa',
					instructors: '4. Corpo Docente & Mentores',
					awards: '5. Super Premiação & Destaques',
					investment: '6. Investimento & Dupla Garantia',
					faq: '7. Perguntas Frequentes & COGA'
				};

				return blocksList.map((b, idx) => {
					const icon = blockIcons[b.type] || 'box';
					const name = blockNames[b.type] || b.type;

					return `
						<div class="builder-block-card" data-block-id="${b.id}" data-block-idx="${idx}" style="border: 1px solid var(--border); border-radius: 8px; background: var(--bg-main); overflow: hidden;">
							<div class="builder-block-header" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: rgba(255,255,255,0.02); cursor: pointer;">
								<div style="display: flex; align-items: center; gap: 8px;">
									<div class="reorder-btns" style="display: flex; flex-direction: column; gap: 2px;">
										<button type="button" class="btn-icon btn-sm btn-move-up" data-idx="${idx}" ${idx === 0 ? 'disabled' : ''} style="padding: 2px; height: 16px; line-height: 1;">▲</button>
										<button type="button" class="btn-icon btn-sm btn-move-down" data-idx="${idx}" ${idx === blocksList.length - 1 ? 'disabled' : ''} style="padding: 2px; height: 16px; line-height: 1;">▼</button>
									</div>
									<i data-lucide="${icon}" style="width: 16px; height: 16px; color: var(--primary);"></i>
									<strong style="font-size: 0.85rem; color: var(--text-main);">${name}</strong>
								</div>

								<div style="display: flex; align-items: center; gap: 8px;">
									<label style="display: flex; align-items: center; gap: 4px; font-size: 11px; cursor: pointer; color: ${b.active ? 'var(--primary)' : 'var(--text-muted)'};">
										<input type="checkbox" class="block-active-toggle" data-idx="${idx}" ${b.active ? 'checked' : ''} onclick="event.stopPropagation();">
										<span>${b.active ? 'Ativo' : 'Oculto'}</span>
									</label>
									<span class="accordion-arrow" style="font-size: 12px; color: var(--text-muted);">▼</span>
								</div>
							</div>

							<div class="builder-block-body" style="display: none; padding: 14px; border-top: 1px solid var(--border); font-size: 0.85rem;">
								${renderBlockFields(b, idx)}
							</div>
						</div>
					`;
				}).join('');
			}

			function renderBlockFields(b, idx) {
				const s = b.settings || {};
				switch (b.type) {
					case 'hero':
						return `
							<div style="display: flex; flex-direction: column; gap: 10px;">
								<div>
									<label style="display: block; font-weight: 600; margin-bottom: 4px;">Badge de Destaque:</label>
									<input type="text" class="form-control field-sync" data-idx="${idx}" data-field="badge" value="${s.badge || ''}" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">
								</div>
								<div>
									<label style="display: block; font-weight: 600; margin-bottom: 4px;">Título Principal (Headline):</label>
									<input type="text" class="form-control field-sync" data-idx="${idx}" data-field="headline" value="${s.headline || ''}" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">
								</div>
								<div>
									<label style="display: block; font-weight: 600; margin-bottom: 4px;">Subtítulo:</label>
									<textarea class="form-control field-sync" rows="2" data-idx="${idx}" data-field="subtitle" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">${s.subtitle || ''}</textarea>
								</div>
								<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
									<div>
										<label style="display: block; font-weight: 600; margin-bottom: 4px;">Texto do Botão:</label>
										<input type="text" class="form-control field-sync" data-idx="${idx}" data-field="cta_text" value="${s.cta_text || 'Quero Garantir Minha Vaga'}" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">
									</div>
									<div>
										<label style="display: block; font-weight: 600; margin-bottom: 4px;">Aviso de Urgência:</label>
										<input type="text" class="form-control field-sync" data-idx="${idx}" data-field="urgency_text" value="${s.urgency_text || ''}" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">
									</div>
								</div>
								<div>
									<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
										<label style="font-weight: 600;">Imagem Hero (16:9):</label>
										<button type="button" class="btn btn-sm btn-secondary btn-crop-block-img" data-idx="${idx}" data-preset="course_cover" data-field="image_url" style="font-size: 11px; padding: 2px 8px;">
											<i data-lucide="crop"></i> Recortar (16:9)
										</button>
									</div>
									<input type="url" class="form-control field-sync" id="block-${idx}-image_url" data-idx="${idx}" data-field="image_url" value="${s.image_url || ''}" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);" placeholder="https://...">
								</div>
							</div>
						`;

					case 'metrics':
						const items = s.items || [];
						return `
							<div style="display: flex; flex-direction: column; gap: 8px;">
								<div>
									<label style="display: block; font-weight: 600; margin-bottom: 4px;">Título da Seção:</label>
									<input type="text" class="form-control field-sync" data-idx="${idx}" data-field="title" value="${s.title || ''}" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">
								</div>
								<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
									${items.map((it, i) => `
										<div style="border: 1px solid var(--border); padding: 8px; border-radius: 6px; background: var(--bg-card);">
											<input type="text" class="form-control metric-sync" data-idx="${idx}" data-m-idx="${i}" data-sub="number" value="${it.number}" placeholder="Número" style="width:100%; margin-bottom:4px; font-weight:700; padding:4px 6px;">
											<input type="text" class="form-control metric-sync" data-idx="${idx}" data-m-idx="${i}" data-sub="label" value="${it.label}" placeholder="Legenda" style="width:100%; font-size:11px; padding:4px 6px;">
										</div>
									`).join('')}
								</div>
							</div>
						`;

					case 'investment':
						const gateways = s.payment_gateways || [
							{ provider: 'mercadopago', label: 'Pagar com Mercado Pago (Cartão / Pix)', url: '#', code: '' },
							{ provider: 'paypal', label: 'Pagar com PayPal (Dólar / Euro)', url: '#', code: '' }
						];
						return `
							<div style="display: flex; flex-direction: column; gap: 10px;">
								<div>
									<label style="display: block; font-weight: 600; margin-bottom: 4px;">Título da Seção de Investimento:</label>
									<input type="text" class="form-control field-sync" data-idx="${idx}" data-field="title" value="${s.title || 'Invista no Seu Futuro Criativo'}" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">
								</div>
								<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
									<div>
										<label style="display: block; font-weight: 600; margin-bottom: 4px;">Preço em Reais (BRL):</label>
										<input type="text" class="form-control field-sync" data-idx="${idx}" data-field="price_display" value="${s.price_display || 'R$ 2.490,00'}" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">
									</div>
									<div>
										<label style="display: block; font-weight: 600; margin-bottom: 4px;">Condição de Parcelamento:</label>
										<input type="text" class="form-control field-sync" data-idx="${idx}" data-field="installments" value="${s.installments || '12x de R$ 249,00'}" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">
									</div>
								</div>
								<div>
									<label style="display: block; font-weight: 600; margin-bottom: 4px;">Garantia Incondicional:</label>
									<textarea class="form-control field-sync" rows="2" data-idx="${idx}" data-field="guarantee_text" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">${s.guarantee_text || '7 dias de garantia incondicional.'}</textarea>
								</div>

								<!-- Múltiplos Botões / Códigos de Pagamento -->
								<div style="border: 1px solid var(--border); border-radius: 8px; padding: 12px; background: rgba(30, 41, 59, 0.4);">
									<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
										<strong style="font-size: 12px; color: var(--primary);"><i data-lucide="credit-card" style="width: 13px; height: 13px; display: inline-block; vertical-align: middle;"></i> Botões & Códigos de Pagamento (PayPal, Mercado Pago, Pix)</strong>
									</div>
									<div style="display: flex; flex-direction: column; gap: 8px;">
										<div>
											<label style="font-size: 11px; font-weight: 600;">Mercado Pago (Link ou Código HTML do Botão):</label>
											<textarea class="form-control" id="mp-code-${idx}" rows="2" placeholder="Cole aqui o link ou o código do botão do Mercado Pago" style="width:100%; font-size:11px; font-family:monospace; padding:6px;">${gateways.find(g => g.provider === 'mercadopago')?.code || gateways.find(g => g.provider === 'mercadopago')?.url || ''}</textarea>
										</div>
										<div>
											<label style="font-size: 11px; font-weight: 600;">PayPal (Link ou Código HTML do Botão):</label>
											<textarea class="form-control" id="pp-code-${idx}" rows="2" placeholder="Cole aqui o link ou o código do botão do PayPal" style="width:100%; font-size:11px; font-family:monospace; padding:6px;">${gateways.find(g => g.provider === 'paypal')?.code || gateways.find(g => g.provider === 'paypal')?.url || ''}</textarea>
										</div>
									</div>
								</div>
							</div>
						`;

					default:
						return `
							<div style="display: flex; flex-direction: column; gap: 8px;">
								<div>
									<label style="display: block; font-weight: 600; margin-bottom: 4px;">Título da Seção:</label>
									<input type="text" class="form-control field-sync" data-idx="${idx}" data-field="title" value="${s.title || ''}" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">
								</div>
								<div>
									<label style="display: block; font-weight: 600; margin-bottom: 4px;">Descrição / Resumo:</label>
									<textarea class="form-control field-sync" rows="2" data-idx="${idx}" data-field="description" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--border); background:var(--bg-card); color:var(--text-main);">${s.description || ''}</textarea>
								</div>
							</div>
						`;
				}
			}

			function attachBuilderEvents() {
				// Course switch
				document.getElementById('builder-select-course').addEventListener('change', (e) => {
					state.builderCourseId = parseInt(e.target.value, 10);
					renderLandingBuilderView();
				});

				// Template choice cards
				document.querySelectorAll('.template-choice-card').forEach(card => {
					card.addEventListener('click', () => {
						currentTemplate = card.getAttribute('data-tpl');
						document.querySelectorAll('.template-choice-card').forEach(c => {
							const isActive = c.getAttribute('data-tpl') === currentTemplate;
							c.classList.toggle('active', isActive);
							c.style.borderColor = isActive ? 'var(--primary)' : 'var(--border)';
						});
						refreshIframePreview();
					});
				});

				// Device view toggle
				document.querySelectorAll('.btn-device').forEach(btn => {
					btn.addEventListener('click', () => {
						activeDevice = btn.getAttribute('data-device');
						document.querySelectorAll('.btn-device').forEach(b => {
							b.classList.remove('btn-primary');
							b.classList.add('btn-secondary');
						});
						btn.classList.add('btn-primary');
						btn.classList.remove('btn-secondary');

						const iframe = document.getElementById('builder-preview-iframe');
						if (iframe) iframe.style.width = getDeviceWidth(activeDevice);
					});
				});

				// Refresh Preview button
				document.getElementById('btn-builder-refresh-preview').addEventListener('click', () => {
					refreshIframePreview();
				});

				// Accordion click toggle
				document.querySelectorAll('.builder-block-header').forEach(header => {
					header.addEventListener('click', (e) => {
						if (e.target.closest('.reorder-btns') || e.target.closest('.block-active-toggle')) return;
						const body = header.parentElement.querySelector('.builder-block-body');
						const arrow = header.querySelector('.accordion-arrow');
						const isHidden = body.style.display === 'none';
						body.style.display = isHidden ? 'block' : 'none';
						arrow.textContent = isHidden ? '▲' : '▼';
						if (window.lucide) window.lucide.createIcons();
					});
				});

				// Block Active Toggle
				document.querySelectorAll('.block-active-toggle').forEach(chk => {
					chk.addEventListener('change', (e) => {
						const idx = parseInt(chk.getAttribute('data-idx'), 10);
						blocks[idx].active = chk.checked;
						chk.nextElementSibling.textContent = chk.checked ? 'Ativo' : 'Oculto';
					});
				});

				// Reorder: Move Up
				document.querySelectorAll('.btn-move-up').forEach(btn => {
					btn.addEventListener('click', (e) => {
						e.stopPropagation();
						const idx = parseInt(btn.getAttribute('data-idx'), 10);
						if (idx > 0) {
							const temp = blocks[idx];
							blocks[idx] = blocks[idx - 1];
							blocks[idx - 1] = temp;
							blocks.forEach((b, i) => b.order = i + 1);
							renderBuilderLayout();
						}
					});
				});

				// Reorder: Move Down
				document.querySelectorAll('.btn-move-down').forEach(btn => {
					btn.addEventListener('click', (e) => {
						e.stopPropagation();
						const idx = parseInt(btn.getAttribute('data-idx'), 10);
						if (idx < blocks.length - 1) {
							const temp = blocks[idx];
							blocks[idx] = blocks[idx + 1];
							blocks[idx + 1] = temp;
							blocks.forEach((b, i) => b.order = i + 1);
							renderBuilderLayout();
						}
					});
				});

				// Synchronize input fields
				document.querySelectorAll('.field-sync').forEach(input => {
					input.addEventListener('input', () => {
						const idx = parseInt(input.getAttribute('data-idx'), 10);
						const field = input.getAttribute('data-field');
						if (!blocks[idx].settings) blocks[idx].settings = {};
						blocks[idx].settings[field] = input.value;
					});
				});

				// Synchronize metric sub-fields
				document.querySelectorAll('.metric-sync').forEach(input => {
					input.addEventListener('input', () => {
						const idx = parseInt(input.getAttribute('data-idx'), 10);
						const mIdx = parseInt(input.getAttribute('data-m-idx'), 10);
						const sub = input.getAttribute('data-sub');
						if (blocks[idx].settings && blocks[idx].settings.items && blocks[idx].settings.items[mIdx]) {
							blocks[idx].settings.items[mIdx][sub] = input.value;
						}
					});
				});

				// Crop button triggers inside builder
				document.querySelectorAll('.btn-crop-block-img').forEach(btn => {
					btn.addEventListener('click', () => {
						const idx = parseInt(btn.getAttribute('data-idx'), 10);
						const preset = btn.getAttribute('data-preset') || 'course_cover';
						const field = btn.getAttribute('data-field');

						openCropModal({
							preset: preset,
							title: 'Recortar Imagem para o Bloco',
							endpoint: 'media/upload',
							onComplete: (res) => {
								if (!blocks[idx].settings) blocks[idx].settings = {};
								blocks[idx].settings[field] = res.url;
								const input = document.getElementById(`block-${idx}-${field}`);
								if (input) input.value = res.url;
							}
						});
					});
				});

				// Save button
				document.getElementById('btn-builder-save').addEventListener('click', async () => {
					const btn = document.getElementById('btn-builder-save');
					btn.disabled = true;
					btn.innerHTML = '<span class="spinner" style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin 0.8s linear infinite;"></span> Salvando...';

					try {
						await fetchAPI(`courses/${selectedCourseId}/landing-builder`, 'POST', {
							template: currentTemplate,
							blocks: blocks
						});

						showToast('Landing Page atualizada com sucesso!', 'success');
						refreshIframePreview();
					} catch (err) {
						showToast(err.message || 'Erro ao salvar Landing Page.', 'danger');
					} finally {
						btn.disabled = false;
						btn.innerHTML = '<i data-lucide="check"></i> <span>Salvar Landing Page</span>';
						if (window.lucide) window.lucide.createIcons();
					}
				});

				// Reset button
				document.getElementById('btn-builder-reset').addEventListener('click', async () => {
					if (!confirm('Deseja restaurar os blocos para o padrão deste template? As edições não salvas serão perdidas.')) return;

					try {
						const resetRes = await fetchAPI(`courses/${selectedCourseId}/landing-builder/reset`, 'POST', {
							template: currentTemplate
						});

						blocks = resetRes.blocks || [];
						showToast('Blocos restaurados para o padrão!', 'success');
						renderBuilderLayout();
						refreshIframePreview();
					} catch (err) {
						showToast(err.message || 'Erro ao restaurar blocos.', 'danger');
					}
				});
			}

			function refreshIframePreview() {
				const iframe = document.getElementById('builder-preview-iframe');
				if (iframe) {
					iframe.src = `${window.StackEAD_Config.homeUrl}/landing/?preview_course_id=${selectedCourseId}&template=${currentTemplate}&t=${Date.now()}`;
				}
				const extBtn = document.getElementById('btn-builder-external-preview');
				if (extBtn) {
					extBtn.href = `${window.StackEAD_Config.homeUrl}/landing/?preview_course_id=${selectedCourseId}&template=${currentTemplate}`;
				}
			}

			renderBuilderLayout();

		} catch (err) {
			renderError('Não foi possível carregar o Construtor de Landing Page.');
		}
	}

	/* ==========================================================================
	   15. News & Announcements Desk
	   ========================================================================== */
	async function renderNewsAdminView() {
		try {
			const newsList = await fetchAPI('news');

			contentRoot.innerHTML = `
				<div class="news-admin-wrapper">
					<div class="desk-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
						<div>
							<h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">Mural de Notícias & Comunicados</h2>
							<p style="color: var(--text-muted); font-size: 0.95rem;">Publique avisos, editais, novidades e comunicados acadêmicos diretamente na plataforma externa.</p>
						</div>
						<button id="btn-create-news" class="btn btn-primary">
							<i data-lucide="plus-circle"></i> Publicar Nova Notícia
						</button>
					</div>

					${newsList.length === 0 ? `
						<div class="empty-state" style="text-align: center; padding: 60px 20px; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border);">
							<i data-lucide="newspaper" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 12px;"></i>
							<h3>Nenhuma notícia publicada</h3>
							<p style="color: var(--text-muted); margin-bottom: 20px;">Crie seu primeiro comunicado ou informe acadêmico para os alunos.</p>
							<button class="btn btn-primary" id="btn-empty-create-news" onclick="window.StackEAD_App.openCreateNewsModal()">Publicar Notícia</button>
						</div>
					` : `
						<div class="news-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
							${newsList.map(n => `
								<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; display: flex; flex-direction: column;">
									${n.cover_url ? `
										<img src="${n.cover_url}" alt="${n.title}" style="width: 100%; height: 160px; object-fit: cover;">
									` : `
										<div style="width: 100%; height: 120px; background: rgba(79,70,229,0.1); display: flex; align-items: center; justify-content: center; color: var(--primary);">
											<i data-lucide="newspaper" style="width: 36px; height: 36px;"></i>
										</div>
									`}
									<div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
										<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
											<span style="font-size: 0.75rem; font-weight: 700; color: var(--primary); text-transform: uppercase;">${n.category}</span>
											<span class="badge ${n.status === 'publish' ? 'badge-success' : 'badge-warning'}">${n.status === 'publish' ? 'Publicado' : 'Rascunho'}</span>
										</div>
										<h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">${n.title}</h3>
										<p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.5; margin-bottom: 16px; flex: 1;">${n.summary || ''}</p>
										<div style="display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid var(--border); font-size: 0.8rem; color: var(--text-muted);">
											<span>${n.date} · Por ${n.author_name}</span>
											<div style="display:flex; gap:6px;"><button class="btn btn-sm btn-secondary" onclick="window.StackEAD_App.openEditNewsModal(${n.id})" title="Editar"><i data-lucide="edit-3"></i></button><button class="btn btn-sm btn-danger" onclick="window.StackEAD_App.deleteNews(${n.id})" title="Excluir">
												<i data-lucide="trash-2"></i>
											</button></div>
										</div>
									</div>
								</div>
							`).join('')}
						</div>
					`}
				</div>
			`;

			if (window.lucide) window.lucide.createIcons();

			const createBtn = document.getElementById('btn-create-news');
			if (createBtn) {
				createBtn.addEventListener('click', () => openCreateNewsModal());
			}
			const emptyCreateNewsBtn = document.getElementById('btn-empty-create-news');
			if (emptyCreateNewsBtn) {
				emptyCreateNewsBtn.addEventListener('click', () => openCreateNewsModal());
			}

		} catch (err) {
			renderError('Não foi possível carregar a lista de notícias.');
		}
	}

	async function openCreateNewsModal() {
		const categories = await fetchAPI('categories?type=news').catch(() => []);

		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 740px; width: 90%; max-height: 90vh; overflow-y: auto;">
				<div class="modal-header">
					<h3>Publicar Notícia / Comunicado</h3>
					<button class="btn-icon btn-close-modal">&times;</button>
				</div>
				<form id="form-create-news" style="padding: 24px;">
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Título da Notícia *</label>
						<input type="text" id="news-title" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required placeholder="Ex: Resultado da Seleção das Bolsas de Imersão">
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Categoria</label>
							<select id="news-category" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								<option value="0">Geral / Sem Categoria</option>
								${categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
							</select>
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Status</label>
							<select id="news-status" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								<option value="publish">Publicado Imediatamente</option>
								<option value="draft">Rascunho</option>
							</select>
						</div>
					</div>

					<div class="form-group" style="margin-bottom: 16px;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
							<label style="font-weight: 600;">Imagem de Capa (3:2)</label>
							<button type="button" class="btn btn-sm btn-secondary" id="btn-crop-news-cover" style="font-size: 12px; padding: 4px 10px; display: flex; align-items: center; gap: 4px;">
								<i data-lucide="crop" style="width: 13px; height: 13px;"></i> Recortar Imagem (3:2)
							</button>
						</div>
						<input type="url" id="news-cover" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="https://...">
					</div>

					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Resumo / Ementa Curta</label>
						<textarea id="news-summary" rows="2" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Breve introdução da notícia..."></textarea>
					</div>

					<div class="form-group" style="margin-bottom: 24px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Conteúdo Completo (Editor WYSIWYG) *</label>
						<div id="news-create-wysiwyg-editor"></div>
					</div>

					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary">Publicar Notícia</button>
					</div>
				</form>
			</div>
		`;

		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		const newsEditor = createWysiwygEditor(modal.querySelector('#news-create-wysiwyg-editor'), '', {
			minHeight: '200px',
			placeholder: 'Escreva a matéria completa com parágrafos, subtítulos, links e listas...'
		});

		const cropBtn = modal.querySelector('#btn-crop-news-cover');
		if (cropBtn) {
			cropBtn.addEventListener('click', () => {
				openCropModal({
					preset: 'news_cover',
					title: 'Recortar Capa da Notícia (3:2)',
					endpoint: 'media/upload',
					onComplete: (data) => {
						const coverInput = modal.querySelector('#news-cover');
						if (coverInput && data.url) {
							coverInput.value = data.url;
						}
					}
				});
			});
		}

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.addEventListener('click', () => modal.remove());
		});

		modal.querySelector('form').addEventListener('submit', async (e) => {
			e.preventDefault();
			const submitBtn = e.target.querySelector('button[type="submit"]');
			submitBtn.disabled = true;
			submitBtn.textContent = 'Publicando...';

			try {
				await fetchAPI('news', 'POST', {
					title: document.getElementById('news-title').value.trim(),
					category_id: parseInt(document.getElementById('news-category').value, 10),
					status: document.getElementById('news-status').value,
					cover_url: document.getElementById('news-cover').value.trim(),
					summary: document.getElementById('news-summary').value.trim(),
					content: newsEditor.getHtml()
				});

				modal.remove();
				showToast('Notícia publicada com sucesso!', 'success');
				renderNewsAdminView();
			} catch (err) {
				showToast(err.message || 'Erro ao publicar notícia.', 'danger');
				submitBtn.disabled = false;
				submitBtn.textContent = 'Publicar Notícia';
			}
		});
	}

	async function openEditNewsModal(newsId) {
		const [categories, news] = await Promise.all([
			fetchAPI('categories?type=news').catch(() => []),
			fetchAPI(`news/${newsId}`).catch(() => null)
		]);

		if (!news) {
			showToast('Não foi possível carregar os dados da notícia.', 'danger');
			return;
		}

		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 740px; width: 90%; max-height: 90vh; overflow-y: auto;">
				<div class="modal-header">
					<h3>Editar Notícia / Comunicado</h3>
					<button class="btn-icon btn-close-modal">&times;</button>
				</div>
				<form id="form-edit-news" style="padding: 24px;">
					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Título da Notícia *</label>
						<input type="text" id="edit-news-title" class="form-control" value="${escapeHtml(news.title)}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required>
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Categoria</label>
							<select id="edit-news-category" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								<option value="0">Geral / Sem Categoria</option>
								${categories.map(c => `<option value="${c.id}" ${c.id === news.category_id ? 'selected' : ''}>${c.name}</option>`).join('')}
							</select>
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 600; margin-bottom: 6px;">Status</label>
							<select id="edit-news-status" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								<option value="publish" ${news.status === 'publish' ? 'selected' : ''}>Publicado</option>
								<option value="draft" ${news.status === 'draft' ? 'selected' : ''}>Rascunho</option>
							</select>
						</div>
					</div>

					<div class="form-group" style="margin-bottom: 16px;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
							<label style="font-weight: 600;">Imagem de Capa (3:2)</label>
							<button type="button" class="btn btn-sm btn-secondary" id="btn-crop-edit-news-cover" style="font-size: 12px; padding: 4px 10px; display: flex; align-items: center; gap: 4px;">
								<i data-lucide="crop" style="width: 13px; height: 13px;"></i> Recortar Imagem (3:2)
							</button>
						</div>
						<input type="url" id="edit-news-cover" class="form-control" value="${escapeHtml(news.cover_url || '')}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="https://...">
					</div>

					<div class="form-group" style="margin-bottom: 16px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Resumo / Ementa Curta</label>
						<textarea id="edit-news-summary" rows="2" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">${escapeHtml(news.summary || '')}</textarea>
					</div>

					<div class="form-group" style="margin-bottom: 24px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Conteúdo Completo (Editor WYSIWYG) *</label>
						<div id="news-edit-wysiwyg-editor"></div>
					</div>

					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="btn-save-edit-news">Atualizar Notícia</button>
					</div>
				</form>
			</div>
		`;

		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		const newsEditor = createWysiwygEditor(modal.querySelector('#news-edit-wysiwyg-editor'), news.content || '', {
			minHeight: '200px',
			placeholder: 'Conteúdo completo da matéria...'
		});

		const cropBtn = modal.querySelector('#btn-crop-edit-news-cover');
		if (cropBtn) {
			cropBtn.addEventListener('click', () => {
				openCropModal({
					preset: 'news_cover',
					title: 'Recortar Capa da Notícia (3:2)',
					endpoint: 'media/upload',
					onComplete: (data) => {
						const coverInput = modal.querySelector('#edit-news-cover');
						if (coverInput && data.url) {
							coverInput.value = data.url;
						}
					}
				});
			});
		}

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.addEventListener('click', () => modal.remove());
		});

		modal.querySelector('#form-edit-news').onsubmit = async (e) => {
			e.preventDefault();
			const saveBtn = modal.querySelector('#btn-save-edit-news');
			saveBtn.disabled = true;
			saveBtn.textContent = 'Salvando...';

			try {
				await fetchAPI(`news/${newsId}`, 'POST', {
					title: document.getElementById('edit-news-title').value.trim(),
					category_id: parseInt(document.getElementById('edit-news-category').value, 10),
					status: document.getElementById('edit-news-status').value,
					cover_url: document.getElementById('edit-news-cover').value.trim(),
					summary: document.getElementById('edit-news-summary').value.trim(),
					content: newsEditor.getHtml()
				});

				showToast('Notícia atualizada com sucesso!', 'success');
				modal.remove();
				renderNewsAdminView();
			} catch (err) {
				saveBtn.disabled = false;
				saveBtn.textContent = 'Atualizar Notícia';
				showToast(err.message || 'Erro ao atualizar notícia.', 'danger');
			}
		};
	}

	async function deleteNews(id) {
		if (!confirm('Tem certeza de que deseja excluir esta notícia?')) return;
		try {
			await fetchAPI('news/' + id, 'DELETE');
			showToast('Notícia excluída com sucesso.', 'success');
			renderNewsAdminView();
		} catch (err) {
			showToast(err.message || 'Erro ao excluir notícia.', 'danger');
		}
	}

	/* ==========================================================================
	   16. Dual Categories Desk (Cursos & Notícias)
	   ========================================================================== */
	async function renderCategoriesAdminView() {
		try {
			const courseCats = await fetchAPI('categories?type=course').catch(() => []);
			const newsCats = await fetchAPI('categories?type=news').catch(() => []);

			contentRoot.innerHTML = `
				<div class="categories-desk-wrapper" style="max-width: 1040px; margin: 0 auto;">
					<div class="desk-header" style="margin-bottom: 28px;">
						<h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">Gestão de Categorias</h2>
						<p style="color: var(--text-muted); font-size: 0.95rem;">Organize as áreas temáticas dos Cursos (ex: Arte Mídia, Culinária) e as Categorias de Notícias.</p>
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px;">
						<!-- Categorias de Cursos -->
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
								<div style="display: flex; align-items: center; gap: 8px;">
									<i data-lucide="book-open" style="color: var(--primary);"></i>
									<h3 style="font-size: 1.2rem; font-weight: 700;">Categorias de Cursos</h3>
								</div>
								<span class="badge badge-info">${courseCats.length} áreas</span>
							</div>

							<form id="form-add-course-cat" style="display: flex; gap: 10px; margin-bottom: 20px;">
								<input type="text" id="new-course-cat-name" placeholder="Nova categoria (ex: Culinária)" class="form-control" style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required>
								<button type="submit" class="btn btn-primary"><i data-lucide="plus"></i> Adicionar</button>
							</form>

							<div class="cat-list" style="display: flex; flex-direction: column; gap: 8px;">
								${courseCats.map(c => `
									<div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: var(--bg-main); border-radius: 8px; border: 1px solid var(--border);">
										<div>
											<strong style="color: var(--text-main); font-size: 0.95rem;">${c.name}</strong>
											<span style="color: var(--text-muted); font-size: 0.8rem; margin-left: 8px;">(${c.count} cursos)</span>
										</div>
										<div style="display:flex; gap:4px;"><button class="btn-icon btn-sm" onclick="window.StackEAD_App.openEditCategoryModal(${c.id}, '${c.name}', 'stackead_category')" style="color: var(--text-muted);" title="Renomear"><i data-lucide="edit-3" style="width: 15px; height: 15px;"></i></button><button class="btn-icon btn-sm" onclick="window.StackEAD_App.deleteCategory(${c.id}, 'stackead_category')" style="color: var(--danger);" title="Excluir">
											<i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
										</button>
									</div>
								`).join('')}
							</div>
						</div>

						<!-- Categorias de Notícias -->
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
								<div style="display: flex; align-items: center; gap: 8px;">
									<i data-lucide="newspaper" style="color: var(--accent);"></i>
									<h3 style="font-size: 1.2rem; font-weight: 700;">Categorias de Notícias</h3>
								</div>
								<span class="badge badge-info">${newsCats.length} tipos</span>
							</div>

							<form id="form-add-news-cat" style="display: flex; gap: 10px; margin-bottom: 20px;">
								<input type="text" id="new-news-cat-name" placeholder="Nova categoria (ex: Editais)" class="form-control" style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" required>
								<button type="submit" class="btn btn-primary"><i data-lucide="plus"></i> Adicionar</button>
							</form>

							<div class="cat-list" style="display: flex; flex-direction: column; gap: 8px;">
								${newsCats.map(c => `
									<div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: var(--bg-main); border-radius: 8px; border: 1px solid var(--border);">
										<div>
											<strong style="color: var(--text-main); font-size: 0.95rem;">${c.name}</strong>
											<span style="color: var(--text-muted); font-size: 0.8rem; margin-left: 8px;">(${c.count} notícias)</span>
										</div>
										<div style="display:flex; gap:4px;"><button class="btn-icon btn-sm" onclick="window.StackEAD_App.openEditCategoryModal(${c.id}, '${c.name}', 'stackead_news_cat')" style="color: var(--text-muted);" title="Renomear"><i data-lucide="edit-3" style="width: 15px; height: 15px;"></i></button><button class="btn-icon btn-sm" onclick="window.StackEAD_App.deleteCategory(${c.id}, 'stackead_news_cat')" style="color: var(--danger);" title="Excluir">
											<i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
										</button>
									</div>
								`).join('')}
							</div>
						</div>
					</div>
				</div>
			`;

			if (window.lucide) window.lucide.createIcons();

			// Add Course Cat
			document.getElementById('form-add-course-cat').addEventListener('submit', async (e) => {
				e.preventDefault();
				const name = document.getElementById('new-course-cat-name').value.trim();
				try {
					await fetchAPI('categories', 'POST', { name: name, type: 'course' });
					showToast('Categoria de curso cadastrada!', 'success');
					renderCategoriesAdminView();
				} catch (err) {
					showToast(err.message, 'danger');
				}
			});

			// Add News Cat
			document.getElementById('form-add-news-cat').addEventListener('submit', async (e) => {
				e.preventDefault();
				const name = document.getElementById('new-news-cat-name').value.trim();
				try {
					await fetchAPI('categories', 'POST', { name: name, type: 'news' });
					showToast('Categoria de notícia cadastrada!', 'success');
					renderCategoriesAdminView();
				} catch (err) {
					showToast(err.message, 'danger');
				}
			});

		} catch (err) {
			renderError('Não foi possível carregar as categorias.');
		}
	}

	async function openEditCategoryModal(id, currentName, taxonomy = 'stackead_category') {
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 100001; display: flex; align-items: center; justify-content: center; padding: 20px;';
		modal.innerHTML = `
			<div class="modal-card" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-xl); max-width: 460px; width: 100%; padding: 24px;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
					<h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin: 0;">Renomear Categoria</h3>
					<button type="button" class="btn-icon btn-close-modal">&times;</button>
				</div>
				<form id="form-edit-category">
					<div class="form-group" style="margin-bottom: 18px;">
						<label style="display: block; font-weight: 600; margin-bottom: 6px;">Nome da Categoria *</label>
						<input type="text" id="edit-cat-name-input" class="form-control" value="${escapeHtml(currentName)}" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
					</div>
					<div style="display: flex; justify-content: flex-end; gap: 10px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="btn-save-edit-cat">Salvar Alterações</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});
		modal.querySelector('#form-edit-category').onsubmit = async (e) => {
			e.preventDefault();
			const newName = modal.querySelector('#edit-cat-name-input').value.trim();
			if (!newName) return;
			const saveBtn = modal.querySelector('#btn-save-edit-cat');
			saveBtn.disabled = true;
			try {
				await fetchAPI(`categories/${id}`, 'POST', { name: newName, taxonomy: taxonomy });
				showToast('Categoria renomeada com sucesso!', 'success');
				modal.remove();
				renderCategoriesAdminView();
			} catch (err) {
				saveBtn.disabled = false;
				showToast(err.message || 'Erro ao renomear categoria.', 'danger');
			}
		};
	}

	async function deleteCategory(id, taxonomy) {
		if (!confirm('Tem certeza de que deseja remover esta categoria?')) return;
		try {
			await fetchAPI('categories/' + id + '?taxonomy=' + taxonomy, 'DELETE');
			showToast('Categoria removida com sucesso.', 'success');
			renderCategoriesAdminView();
		} catch (err) {
			showToast(err.message || 'Erro ao remover categoria.', 'danger');
		}
	}

	/* ==========================================================================
	   17. Leads CRM Desk (Manifestações de Interesse da Landing Page)
	   ========================================================================== */
	async function renderLeadsAdminView() {
		try {
			const leads = await fetchAPI('leads');

			contentRoot.innerHTML = `
				<div class="leads-desk-wrapper">
					<div class="desk-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
						<div>
							<h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">Leads & Inscrições</h2>
							<p style="color: var(--text-muted); font-size: 0.95rem;">Contatos qualificados capturados através da Landing Page do curso e formulários de interesse.</p>
						</div>
						<div style="display: flex; gap: 12px; align-items: center;">
							<span class="badge badge-success" style="padding: 8px 14px; font-size: 14px;">${leads.length} Inscritos</span>
							<a href="${API.root}leads/export-csv" class="btn btn-secondary" target="_blank" download>
								<i data-lucide="download"></i> Exportar CSV
							</a>
						</div>
					</div>

					${leads.length === 0 ? `
						<div class="empty-state" style="text-align: center; padding: 60px 20px; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border);">
							<i data-lucide="users" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 12px;"></i>
							<h3>Nenhum lead capturado ainda</h3>
							<p style="color: var(--text-muted);">As manifestações de interesse da Landing Page aparecerão aqui automaticamente.</p>
						</div>
					` : `
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden;">
							<table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
								<thead>
									<tr style="background: rgba(255,255,255,0.03); border-bottom: 1px solid var(--border); color: var(--text-muted);">
										<th style="padding: 14px 20px;">Nome</th>
										<th style="padding: 14px 20px;">E-mail</th>
										<th style="padding: 14px 20px;">WhatsApp</th>
										<th style="padding: 14px 20px;">Cidade</th>
										<th style="padding: 14px 20px;">Perfil</th>
										<th style="padding: 14px 20px;">Data</th>
										<th style="padding: 14px 20px; text-align: right;">Ação</th>
									</tr>
								</thead>
								<tbody>
									${leads.map(l => {
										const cleanPhone = (l.phone || '').replace(/[^0-9]/g, '');
										return `
											<tr style="border-bottom: 1px solid var(--border);">
												<td style="padding: 14px 20px; font-weight: 600; color: var(--text-main);">${l.name}</td>
												<td style="padding: 14px 20px; color: var(--text-muted);">${l.email}</td>
												<td style="padding: 14px 20px; color: var(--text-muted);">${l.phone || '—'}</td>
												<td style="padding: 14px 20px; color: var(--text-muted);">${l.city || '—'}</td>
												<td style="padding: 14px 20px;"><span class="badge badge-info">${l.profile || 'Geral'}</span></td>
												<td style="padding: 14px 20px; color: var(--text-muted); font-size: 0.8rem;">${l.created_at}</td>
												<td style="padding: 14px 20px; text-align: right;">
													${cleanPhone ? `
														<a href="https://wa.me/${cleanPhone}" target="_blank" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; font-size: 12px; border-radius: 6px;">
															<i data-lucide="phone"></i> WhatsApp
														</a>
													` : ''}
												</td>
											</tr>
										`;
									}).join('')}
								</tbody>
							</table>
						</div>
					`}
				</div>
			`;

			if (window.lucide) window.lucide.createIcons();

		} catch (err) {
			renderError('Não foi possível carregar a lista de leads.');
		}
	}


	/* ==========================================================================
	   20. Users & Contextual Cohort Members View (Milestone 4)
	   ========================================================================== */
	async function renderUsersAdminView() {
		try {
			const [users, cohorts] = await Promise.all([
				fetchAPI('users').catch(() => []),
				fetchAPI('cohorts').catch(() => [])
			]);

			let activeTab = 'all';
			let searchQuery = '';

			const renderTable = () => {
				const filtered = users.filter(u => {
					const matchSearch = !searchQuery || 
						u.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
						u.email.toLowerCase().includes(searchQuery.toLowerCase()) || 
						u.username.toLowerCase().includes(searchQuery.toLowerCase());

					if (!matchSearch) return false;

					const uRoles = Array.isArray(u.roles) ? u.roles : [u.primary_role];
					const uMemberships = Array.isArray(u.memberships) ? u.memberships : [];

					if (activeTab === 'admins') {
						return uRoles.includes('administrator');
					}
					if (activeTab === 'coords') {
						return uRoles.includes('stackead_coordinator') || uMemberships.some(m => m.role === 'coordinator');
					}
					if (activeTab === 'masters') {
						return uRoles.includes('stackead_master') || uMemberships.some(m => ['master', 'tutor', 'facilitador', 'professor'].includes(m.role));
					}
					if (activeTab === 'students') {
						return uRoles.includes('stackead_student') || uRoles.includes('subscriber') || uMemberships.some(m => m.role === 'student');
					}
					return true;
				});

				const tbody = document.getElementById('users-table-tbody');
				if (!tbody) return;

				if (filtered.length === 0) {
					tbody.innerHTML = `
						<tr>
							<td colspan="5" style="text-align: center; padding: 48px; color: var(--text-muted);">
								Nenhum usuário localizado com os critérios selecionados.
							</td>
						</tr>
					`;
					return;
				}

				tbody.innerHTML = filtered.map(u => {
					const roleBadges = {
						'administrator': '<span class="badge" style="background: rgba(168, 85, 247, 0.18); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35); font-weight: 700; margin: 2px;">Administrador</span>',
						'stackead_coordinator': '<span class="badge" style="background: rgba(99, 102, 241, 0.18); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.35); font-weight: 700; margin: 2px;">Coordenador</span>',
						'stackead_master': '<span class="badge" style="background: rgba(59, 130, 246, 0.18); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35); font-weight: 700; margin: 2px;">Professor / Mestre</span>',
						'stackead_student': '<span class="badge" style="background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-weight: 700; margin: 2px;">Aluno EAD</span>',
						'subscriber': '<span class="badge" style="background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-weight: 700; margin: 2px;">Aluno / Assinante</span>'
					};

					const orderedPriority = ['administrator', 'stackead_coordinator', 'stackead_master', 'stackead_student', 'subscriber'];
					const userRolesList = Array.isArray(u.roles) && u.roles.length > 0 ? u.roles : [u.primary_role || 'stackead_student'];
					const sortedRoles = [...userRolesList].sort((a, b) => {
						const ia = orderedPriority.indexOf(a);
						const ib = orderedPriority.indexOf(b);
						return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
					});

					const multiRoleBadges = sortedRoles
						.map(r => roleBadges[r] || `<span class="badge" style="margin: 2px;">${escapeHtml(r)}</span>`)
						.join(' ');

					const ctxRoleLabels = {
						student: { text: 'ALUNO', color: '#34d399', bg: 'rgba(16, 185, 129, 0.12)' },
						master: { text: 'PROFESSOR', color: '#60a5fa', bg: 'rgba(59, 130, 246, 0.12)' },
						coordinator: { text: 'COORDENADOR', color: '#818cf8', bg: 'rgba(99, 102, 241, 0.12)' }
					};

					const membershipPills = (u.memberships && u.memberships.length > 0)
						? u.memberships.map(m => {
							const meta = ctxRoleLabels[m.role] || { text: (m.role || '').toUpperCase(), color: 'var(--primary)', bg: 'rgba(255,255,255,0.06)' };
							return `
								<span class="badge" style="background: ${meta.bg}; border: 1px solid var(--border); font-size: 0.75rem; margin: 2px;">
									<strong>${escapeHtml(m.cohort_title)}</strong>: <span style="color: ${meta.color}; font-weight: 800;">${escapeHtml(meta.text)}</span>
								</span>
							`;
						}).join(' ')
						: '<span style="color: var(--text-muted); font-size: 0.8rem;">Sem turmas específicas</span>';

					return `
						<tr style="border-bottom: 1px solid var(--border);">
							<td style="padding: 16px 12px;">
								<div style="display: flex; align-items: center; gap: 12px;">
									<img src="${u.avatar_url}" alt="${escapeHtml(u.name)}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border);">
									<div>
										<strong style="display: block; color: var(--text-main); font-size: 0.95rem;">${escapeHtml(u.name)}</strong>
										<span style="color: var(--text-muted); font-size: 0.8rem;">@${escapeHtml(u.username)}</span>
									</div>
								</div>
							</td>
							<td style="padding: 16px 12px; color: var(--text-muted); font-size: 0.9rem;">
								${escapeHtml(u.email)}
							</td>
							<td style="padding: 16px 12px;">
								<div style="display: flex; flex-wrap: wrap; gap: 4px; max-width: 280px;">
									${multiRoleBadges}
								</div>
							</td>
							<td style="padding: 16px 12px;">
								<div style="display: flex; flex-wrap: wrap; gap: 4px; max-width: 300px;">
									${membershipPills}
								</div>
							</td>
							<td style="padding: 16px 12px; text-align: right;">
								<div style="display: flex; justify-content: flex-end; gap: 8px;">
									<button class="btn btn-sm btn-secondary btn-user-cohorts" data-user-id="${u.id}" title="Gerenciar Vínculos de Turmas">
										<i data-lucide="network"></i> Turmas
									</button>
									<button class="btn btn-sm btn-secondary btn-user-edit" data-user-id="${u.id}" title="Editar Perfil e Papéis">
										<i data-lucide="edit"></i> Editar
									</button>
									${u.id !== 1 && u.id !== API.user?.id ? `
										<button class="btn btn-sm btn-delete-user" data-user-id="${u.id}" data-user-name="${escapeHtml(u.name)}" style="padding: 6px 10px; background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer;">
											<i data-lucide="trash-2"></i>
										</button>
									` : ''}
								</div>
							</td>
						</tr>
					`;
				}).join('');

				if (window.lucide) window.lucide.createIcons();

				// Attach row action handlers
				tbody.querySelectorAll('.btn-user-cohorts').forEach(btn => {
					btn.onclick = () => {
						const uid = parseInt(btn.dataset.userId, 10);
						const userObj = users.find(x => x.id === uid);
						if (userObj) openManageUserCohortsModal(userObj, cohorts);
					};
				});

				tbody.querySelectorAll('.btn-user-edit').forEach(btn => {
					btn.onclick = () => {
						const uid = parseInt(btn.dataset.userId, 10);
						const userObj = users.find(x => x.id === uid);
						if (userObj) openEditUserModal(userObj, cohorts);
					};
				});

				tbody.querySelectorAll('.btn-delete-user').forEach(btn => {
					btn.onclick = () => deleteUser(parseInt(btn.dataset.userId, 10), btn.dataset.userName);
				});
			};

			const countAdmins = users.filter(u => (u.roles || []).includes('administrator')).length;
			const countCoords = users.filter(u => (u.roles || []).includes('stackead_coordinator') || (u.memberships || []).some(m => m.role === 'coordinator')).length;
			const countMasters = users.filter(u => (u.roles || []).includes('stackead_master') || (u.memberships || []).some(m => ['master', 'tutor', 'facilitador', 'professor'].includes(m.role))).length;
			const countStudents = users.filter(u => (u.roles || []).includes('stackead_student') || (u.roles || []).includes('subscriber') || (u.memberships || []).some(m => m.role === 'student')).length;

			contentRoot.innerHTML = `
				<div class="users-admin-wrapper" style="max-width: 1200px; margin: 0 auto;">
					<div class="desk-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
						<div>
							<h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">Gestão de Usuários & Múltiplos Papéis</h2>
							<p style="color: var(--text-muted); font-size: 0.95rem;">Cadastre usuários com múltiplos papéis simultâneos (Admin, Coordenador, Professor e Aluno) e vínculos por turma.</p>
						</div>
						<button id="btn-create-user-modal" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
							<i data-lucide="user-plus"></i>
							<span>Cadastrar Novo Usuário</span>
						</button>
					</div>

					<!-- Metrics Cards -->
					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 16px; margin-bottom: 24px;">
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 18px;">
							<span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700;">TOTAL DE USUÁRIOS</span>
							<h3 style="font-size: 1.8rem; font-weight: 800; margin-top: 6px; color: var(--text-main);">${users.length}</h3>
						</div>
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 18px;">
							<span style="color: #c084fc; font-size: 0.8rem; font-weight: 700;">ADMINISTRADORES</span>
							<h3 style="font-size: 1.8rem; font-weight: 800; margin-top: 6px; color: var(--text-main);">${countAdmins}</h3>
						</div>
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 18px;">
							<span style="color: #818cf8; font-size: 0.8rem; font-weight: 700;">COORDENAÇÃO</span>
							<h3 style="font-size: 1.8rem; font-weight: 800; margin-top: 6px; color: var(--text-main);">${countCoords}</h3>
						</div>
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 18px;">
							<span style="color: #60a5fa; font-size: 0.8rem; font-weight: 700;">PROFESSORES / MESTRES</span>
							<h3 style="font-size: 1.8rem; font-weight: 800; margin-top: 6px; color: var(--text-main);">${countMasters}</h3>
						</div>
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 18px;">
							<span style="color: #34d399; font-size: 0.8rem; font-weight: 700;">ESTUDANTES / ALUNOS</span>
							<h3 style="font-size: 1.8rem; font-weight: 800; margin-top: 6px; color: var(--text-main);">${countStudents}</h3>
						</div>
					</div>

					<!-- Filter & Search Toolbar -->
					<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 24px;">
						<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
							<div style="display: flex; gap: 8px; flex-wrap: wrap;">
								<button class="btn btn-sm btn-tab active" data-tab="all" style="padding: 8px 16px; border-radius: 999px;">Todos (${users.length})</button>
								<button class="btn btn-sm btn-tab" data-tab="admins" style="padding: 8px 16px; border-radius: 999px;">Administradores (${countAdmins})</button>
								<button class="btn btn-sm btn-tab" data-tab="coords" style="padding: 8px 16px; border-radius: 999px;">Coordenação (${countCoords})</button>
								<button class="btn btn-sm btn-tab" data-tab="masters" style="padding: 8px 16px; border-radius: 999px;">Professores (${countMasters})</button>
								<button class="btn btn-sm btn-tab" data-tab="students" style="padding: 8px 16px; border-radius: 999px;">Estudantes (${countStudents})</button>
							</div>
							<div style="position: relative; min-width: 280px;">
								<input type="text" id="users-search-input" class="form-input" placeholder="Buscar por nome, e-mail ou usuário..." style="padding-left: 36px; width: 100%;">
								<i data-lucide="search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-muted);"></i>
							</div>
						</div>
					</div>

					<!-- Users Table -->
					<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden;">
						<div style="overflow-x: auto;">
							<table style="width: 100%; border-collapse: collapse; text-align: left;">
								<thead>
									<tr style="border-bottom: 1px solid var(--border); background: rgba(255, 255, 255, 0.02); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
										<th style="padding: 14px 16px;">Usuário</th>
										<th style="padding: 14px 12px;">E-mail</th>
										<th style="padding: 14px 12px;">Papéis Globais (Múltiplos)</th>
										<th style="padding: 14px 12px;">Vínculos por Turma / Curso</th>
										<th style="padding: 14px 16px; text-align: right;">Ações</th>
									</tr>
								</thead>
								<tbody id="users-table-tbody"></tbody>
							</table>
						</div>
					</div>
				</div>
			`;

			if (window.lucide) window.lucide.createIcons();

			// Tab filtering
			contentRoot.querySelectorAll('.btn-tab').forEach(tabBtn => {
				tabBtn.onclick = () => {
					contentRoot.querySelectorAll('.btn-tab').forEach(b => {
						b.classList.remove('active');
						b.style.background = 'transparent';
						b.style.color = 'var(--text-muted)';
					});
					tabBtn.classList.add('active');
					tabBtn.style.background = 'var(--primary)';
					tabBtn.style.color = '#fff';
					activeTab = tabBtn.dataset.tab;
					renderTable();
				};
			});

			// Search input
			const searchInput = document.getElementById('users-search-input');
			if (searchInput) {
				searchInput.oninput = (e) => {
					searchQuery = e.target.value.trim();
					renderTable();
				};
			}

			// Add User button
			const createBtn = document.getElementById('btn-create-user-modal');
			if (createBtn) {
				createBtn.onclick = () => openCreateUserModal(cohorts);
			}

			renderTable();

		} catch (err) {
			renderError('Erro ao carregar a gestão de usuários: ' + err.message);
		}
	}

	/**
	 * Helper to build multi-role checkboxes + quick preset buttons in Create/Edit User pages.
	 */
	function renderMultiRoleSelectorHtml(selectedRoles = ['stackead_student']) {
		const isChecked = (r) => selectedRoles.includes(r) ? 'checked' : '';
		return `
			<div>
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
					<span style="font-size: 0.84rem; font-weight: 700; color: var(--text-muted);">Atalhos rápidos de combinação:</span>
					<div style="display: flex; flex-wrap: wrap; gap: 8px;">
						<button type="button" class="btn btn-sm btn-secondary btn-role-preset" data-roles="administrator,stackead_coordinator,stackead_master">
							Admin + Coord. + Professor
						</button>
						<button type="button" class="btn btn-sm btn-secondary btn-role-preset" data-roles="administrator,stackead_master">
							Admin + Professor
						</button>
						<button type="button" class="btn btn-sm btn-secondary btn-role-preset" data-roles="stackead_coordinator,stackead_master">
							Coordenador + Professor
						</button>
						<button type="button" class="btn btn-sm btn-secondary btn-role-preset" data-roles="stackead_master,stackead_student">
							Professor + Aluno
						</button>
						<button type="button" class="btn btn-sm btn-secondary btn-role-preset" data-roles="stackead_student">
							Apenas Aluno
						</button>
					</div>
				</div>
				<div class="form-grid-2">
					<label class="role-card-label">
						<input type="checkbox" name="global_roles" value="administrator" ${isChecked('administrator')} style="margin-top: 3px;">
						<div>
							<strong style="display: block; font-size: 0.96rem; color: #c084fc; margin-bottom: 4px;">Administrador Geral</strong>
							<span style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.4; display: block;">Acesso total ao sistema, White Label, usuários e configurações globais.</span>
						</div>
					</label>
					<label class="role-card-label">
						<input type="checkbox" name="global_roles" value="stackead_coordinator" ${isChecked('stackead_coordinator')} style="margin-top: 3px;">
						<div>
							<strong style="display: block; font-size: 0.96rem; color: #818cf8; margin-bottom: 4px;">Coordenador Pedagógico</strong>
							<span style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.4; display: block;">Gere cursos, turmas, matrículas, membros, finanças e acessibilidade COGA.</span>
						</div>
					</label>
					<label class="role-card-label">
						<input type="checkbox" name="global_roles" value="stackead_master" ${isChecked('stackead_master')} style="margin-top: 3px;">
						<div>
							<strong style="display: block; font-size: 0.96rem; color: #60a5fa; margin-bottom: 4px;">Professor / Mestre</strong>
							<span style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.4; display: block;">Ministra aulas, agenda sessões ao vivo, corrige entregas e lança presença.</span>
						</div>
					</label>
					<label class="role-card-label">
						<input type="checkbox" name="global_roles" value="stackead_student" ${isChecked('stackead_student')} style="margin-top: 3px;">
						<div>
							<strong style="display: block; font-size: 0.96rem; color: #34d399; margin-bottom: 4px;">Estudante / Aluno EAD</strong>
							<span style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.4; display: block;">Assiste aulas, submete projetos, participa de turmas, ganha XP e certificados.</span>
						</div>
					</label>
				</div>
			</div>
		`;
	}

	function bindMultiRolePresets(containerEl) {
		containerEl.querySelectorAll('.btn-role-preset').forEach(btn => {
			btn.onclick = () => {
				const targetRoles = (btn.dataset.roles || '').split(',').filter(Boolean);
				containerEl.querySelectorAll('input[name="global_roles"]').forEach(cb => {
					cb.checked = targetRoles.includes(cb.value);
				});
			};
		});
	}

	/**
	 * Dedicated Full-Page View to create a new user with multi-role and multi-cohort support.
	 */
	function openCreateUserModal(cohorts = []) {
		if (viewTitle) viewTitle.textContent = 'Equipe & Usuários / Novo Cadastro';
		window.scrollTo({ top: 0, behavior: 'smooth' });

		const cohortOptions = cohorts.map(c => `<option value="${c.id}">${escapeHtml(c.title)}${c.course_title ? ' (' + escapeHtml(c.course_title) + ')' : ''}</option>`).join('');

		contentRoot.innerHTML = `
			<div class="page-form-wrapper">
				<div class="page-form-topbar">
					<div>
						<button type="button" class="btn-back-page btn-cancel-page">
							<i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
							Voltar para Equipe & Usuários
						</button>
					</div>
					<span style="font-size: 0.84rem; color: var(--text-muted);">Cadastro Multi-Papéis · Acesso Imediato</span>
				</div>

				<div style="margin-bottom: 24px;">
					<h2 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">Cadastrar Novo Usuário</h2>
					<p style="color: var(--text-muted); font-size: 0.95rem;">Preencha os dados básicos, selecione um ou mais papéis globais na plataforma e vincule às turmas desejadas.</p>
				</div>

				<form id="form-create-user">
					<!-- Seção 1: Dados Pessoais e Credenciais -->
					<div class="page-form-section">
						<div class="page-form-section-title">
							<i data-lucide="user-plus" style="color: var(--primary);"></i>
							1. Identificação & Credenciais de Acesso
						</div>
						<p class="page-form-section-desc">Informe o nome completo e o e-mail institucional. O login e a senha são gerados automaticamente caso deixados em branco.</p>

						<div class="form-grid-2" style="margin-bottom: 18px;">
							<div>
								<label class="form-label">Nome Completo *</label>
								<input type="text" name="name" class="form-input" required placeholder="Ex.: Maria Clara">
							</div>
							<div>
								<label class="form-label">E-mail Institucional *</label>
								<input type="email" name="email" class="form-input" required placeholder="Ex.: maria@artinnovaresidency.com">
							</div>
						</div>
						<div class="form-grid-2">
							<div>
								<label class="form-label">Nome de Usuário (Login)</label>
								<input type="text" name="username" class="form-input" placeholder="Opcional (gerado automaticamente a partir do e-mail)">
							</div>
							<div>
								<label class="form-label">Senha Inicial de Acesso</label>
								<input type="password" name="password" class="form-input" placeholder="Opcional (gerada automaticamente se em branco)">
							</div>
						</div>
					</div>

					<!-- Seção 2: Papéis Globais -->
					<div class="page-form-section">
						<div class="page-form-section-title">
							<i data-lucide="shield-check" style="color: var(--primary);"></i>
							2. Papéis na Plataforma (Cumulativos)
						</div>
						<p class="page-form-section-desc">Um mesmo membro pode acumular múltiplos papéis simultâneos (ex.: Administrador + Coordenador + Professor).</p>

						${renderMultiRoleSelectorHtml(['stackead_student'])}
					</div>

					<!-- Seção 3: Vínculos em Turmas -->
					<div class="page-form-section">
						<div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; margin-bottom: 16px;">
							<div>
								<div class="page-form-section-title" style="margin-bottom: 4px;">
									<i data-lucide="network" style="color: var(--primary);"></i>
									3. Vínculos Contextuais em Turmas / Cursos (Opcional)
								</div>
								<p class="page-form-section-desc" style="margin-bottom: 0;">Você pode vincular este usuário como Professor em uma turma e Aluno ou Coordenador em outra.</p>
							</div>
							<button type="button" id="btn-add-cohort-link-row" class="btn btn-secondary">
								<i data-lucide="plus"></i> Adicionar Outra Turma
							</button>
						</div>

						<div id="create-user-cohort-links-container" style="display: flex; flex-direction: column; gap: 12px;">
							<div class="cohort-link-row" style="display: grid; grid-template-columns: 2fr 1.4fr auto; gap: 12px; align-items: center;">
								<select name="link_cohort_id" class="form-select">
									<option value="">Selecione uma turma (opcional)...</option>
									${cohortOptions}
								</select>
								<select name="link_cohort_role" class="form-select">
									<option value="master">Professor / Mestre na Turma</option>
									<option value="coordinator">Coordenador da Turma</option>
									<option value="student" selected>Aluno / Estudante na Turma</option>
								</select>
								<button type="button" class="btn btn-remove-row" style="min-height: 46px; padding: 0 14px; background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.25); border-radius: 12px; cursor: pointer;" title="Remover linha">&times;</button>
							</div>
						</div>
					</div>

					<!-- Barra de Ações Final -->
					<div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px; padding-top: 8px; flex-wrap: wrap;">
						<button type="button" class="btn btn-secondary btn-cancel-page" style="min-height: 48px; padding: 0 24px;">Cancelar e Voltar</button>
						<button type="submit" class="btn btn-primary" id="btn-submit-create-user" style="min-height: 48px; padding: 0 28px; font-size: 1rem;">
							<i data-lucide="check-circle-2"></i> Concluir Cadastro do Usuário
						</button>
					</div>
				</form>
			</div>
		`;

		if (window.lucide) window.lucide.createIcons();

		bindMultiRolePresets(contentRoot);
		contentRoot.querySelectorAll('.btn-cancel-page').forEach(b => b.onclick = () => renderUsersAdminView());

		const linksContainer = contentRoot.querySelector('#create-user-cohort-links-container');
		const bindRemoveRowBtns = () => {
			linksContainer.querySelectorAll('.btn-remove-row').forEach(btn => {
				btn.onclick = () => {
					if (linksContainer.querySelectorAll('.cohort-link-row').length > 1) {
						btn.closest('.cohort-link-row').remove();
					} else {
						btn.closest('.cohort-link-row').querySelector('select[name="link_cohort_id"]').value = '';
					}
				};
			});
		};
		bindRemoveRowBtns();

		const addRowBtn = contentRoot.querySelector('#btn-add-cohort-link-row');
		if (addRowBtn) {
			addRowBtn.onclick = () => {
				const row = document.createElement('div');
				row.className = 'cohort-link-row';
				row.style = 'display: grid; grid-template-columns: 2fr 1.4fr auto; gap: 12px; align-items: center;';
				row.innerHTML = `
					<select name="link_cohort_id" class="form-select">
						<option value="">Selecione uma turma...</option>
						${cohortOptions}
					</select>
					<select name="link_cohort_role" class="form-select">
						<option value="master">Professor / Mestre na Turma</option>
						<option value="coordinator">Coordenador da Turma</option>
						<option value="student">Aluno / Estudante na Turma</option>
					</select>
					<button type="button" class="btn btn-remove-row" style="min-height: 46px; padding: 0 14px; background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.25); border-radius: 12px; cursor: pointer;" title="Remover linha">&times;</button>
				`;
				linksContainer.appendChild(row);
				bindRemoveRowBtns();
			};
		}

		const form = contentRoot.querySelector('#form-create-user');
		form.onsubmit = async (e) => {
			e.preventDefault();
			const checkedRoles = Array.from(form.querySelectorAll('input[name="global_roles"]:checked')).map(cb => cb.value);
			if (checkedRoles.length === 0) {
				showToast('Selecione pelo menos um papel global para o usuário.', 'danger');
				return;
			}

			const memberships = [];
			linksContainer.querySelectorAll('.cohort-link-row').forEach(row => {
				const cid = parseInt(row.querySelector('select[name="link_cohort_id"]')?.value || '0', 10);
				const crole = row.querySelector('select[name="link_cohort_role"]')?.value || 'student';
				if (cid > 0) {
					memberships.push({ cohort_id: cid, role: crole });
				}
			});

			const submitBtn = contentRoot.querySelector('#btn-submit-create-user');
			submitBtn.disabled = true;
			submitBtn.textContent = 'Cadastrando usuário...';

			const formData = new FormData(form);
			const payload = {
				name: formData.get('name'),
				email: formData.get('email'),
				username: formData.get('username'),
				password: formData.get('password'),
				global_roles: checkedRoles,
				global_role: checkedRoles[0],
				memberships: memberships
			};

			try {
				await fetchAPI('users', 'POST', payload);
				showToast('Usuário cadastrado com sucesso!', 'success');
				renderUsersAdminView();
			} catch (err) {
				showToast(err.message || 'Erro ao cadastrar usuário.', 'danger');
				submitBtn.disabled = false;
				submitBtn.textContent = 'Concluir Cadastro do Usuário';
			}
		};
	}

	/**
	 * Dedicated Full-Page View to edit an existing user (supports multiple simultaneous roles).
	 */
	function openEditUserModal(user, cohorts = []) {
		if (viewTitle) viewTitle.textContent = `Equipe & Usuários / Editar: ${user.name}`;
		window.scrollTo({ top: 0, behavior: 'smooth' });

		const currentRoles = Array.isArray(user.roles) && user.roles.length > 0 ? user.roles : [user.primary_role || 'stackead_student'];

		contentRoot.innerHTML = `
			<div class="page-form-wrapper">
				<div class="page-form-topbar">
					<button type="button" class="btn-back-page btn-cancel-page">
						<i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
						Voltar para Equipe & Usuários
					</button>
					<button type="button" class="btn btn-secondary" id="btn-edit-open-cohorts">
						<i data-lucide="network"></i> Gerenciar Turmas (${(user.memberships || []).length})
					</button>
				</div>

				<div style="margin-bottom: 24px;">
					<h2 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">Editar Usuário: ${escapeHtml(user.name)}</h2>
					<p style="color: var(--text-muted); font-size: 0.95rem;">Atribua ou ajuste múltiplos papéis simultâneos e credenciais sem perder os vínculos existentes.</p>
				</div>

				<form id="form-edit-user">
					<div class="page-form-section">
						<div class="page-form-section-title">
							<i data-lucide="user-cog" style="color: var(--primary);"></i>
							1. Dados Cadastrais & Senha
						</div>
						<p class="page-form-section-desc">Atualize o nome de exibição, e-mail ou redefina a senha de acesso se necessário.</p>

						<div class="form-grid-2" style="margin-bottom: 18px;">
							<div>
								<label class="form-label">Nome Completo *</label>
								<input type="text" name="name" class="form-input" value="${escapeHtml(user.name)}" required>
							</div>
							<div>
								<label class="form-label">E-mail Institucional *</label>
								<input type="email" name="email" class="form-input" value="${escapeHtml(user.email)}" required>
							</div>
						</div>
						<div>
							<label class="form-label">Nova Senha (deixe em branco para manter a senha atual)</label>
							<input type="password" name="password" class="form-input" placeholder="Digite uma nova senha apenas se desejar redefinir">
						</div>
					</div>

					<div class="page-form-section">
						<div class="page-form-section-title">
							<i data-lucide="shield-check" style="color: var(--primary);"></i>
							2. Papéis Globais na Plataforma
						</div>
						<p class="page-form-section-desc">Selecione todos os papéis que esta pessoa exerce na instituição.</p>

						${renderMultiRoleSelectorHtml(currentRoles)}
					</div>

					<div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px; padding-top: 8px; flex-wrap: wrap;">
						<button type="button" class="btn btn-secondary btn-cancel-page" style="min-height: 48px; padding: 0 24px;">Cancelar e Voltar</button>
						<button type="submit" class="btn btn-primary" id="btn-submit-edit-user" style="min-height: 48px; padding: 0 28px; font-size: 1rem;">
							<i data-lucide="save"></i> Salvar Alterações
						</button>
					</div>
				</form>
			</div>
		`;

		if (window.lucide) window.lucide.createIcons();

		bindMultiRolePresets(contentRoot);
		contentRoot.querySelectorAll('.btn-cancel-page').forEach(b => b.onclick = () => renderUsersAdminView());

		const openCohortsBtn = contentRoot.querySelector('#btn-edit-open-cohorts');
		if (openCohortsBtn) {
			openCohortsBtn.onclick = () => {
				openManageUserCohortsModal(user, cohorts);
			};
		}

		const form = contentRoot.querySelector('#form-edit-user');
		form.onsubmit = async (e) => {
			e.preventDefault();
			const checkedRoles = Array.from(form.querySelectorAll('input[name="global_roles"]:checked')).map(cb => cb.value);
			if (checkedRoles.length === 0) {
				showToast('Selecione pelo menos um papel global para o usuário.', 'danger');
				return;
			}

			const submitBtn = contentRoot.querySelector('#btn-submit-edit-user');
			submitBtn.disabled = true;
			submitBtn.textContent = 'Salvando...';

			const formData = new FormData(form);
			const payload = {
				name: formData.get('name'),
				email: formData.get('email'),
				global_roles: checkedRoles,
				global_role: checkedRoles[0]
			};
			if (formData.get('password')) {
				payload.password = formData.get('password');
			}

			try {
				await fetchAPI(`users/${user.id}`, 'PUT', payload);
				showToast('Usuário e papéis atualizados com sucesso!', 'success');
				renderUsersAdminView();
			} catch (err) {
				showToast(err.message || 'Erro ao atualizar usuário.', 'danger');
				submitBtn.disabled = false;
				submitBtn.textContent = 'Salvar Alterações';
			}
		};
	}

	/**
	 * Dedicated Full-Page View to manage contextual cohort memberships for a user.
	 */
	function openManageUserCohortsModal(user, cohorts = []) {
		if (viewTitle) viewTitle.textContent = `Equipe & Usuários / Turmas: ${user.name}`;
		window.scrollTo({ top: 0, behavior: 'smooth' });

		const renderContent = () => {
			const cohortOptions = cohorts.map(c => `<option value="${c.id}">${escapeHtml(c.title)}${c.course_title ? ' (' + escapeHtml(c.course_title) + ')' : ''}</option>`).join('');

			const currentList = (user.memberships && user.memberships.length > 0)
				? user.memberships.map(m => `
					<div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 16px 20px; background: var(--bg-surface); border: 1.5px solid var(--border); border-radius: 14px; margin-bottom: 10px; flex-wrap: wrap;">
						<div>
							<strong style="color: var(--text-main); font-size: 1rem; display: block; margin-bottom: 4px;">${escapeHtml(m.cohort_title)}</strong>
							<span style="font-size: 0.85rem; color: var(--text-muted);">Papel Contextual nesta Turma: <strong style="color: var(--primary);">${m.role.toUpperCase()}</strong> · Status: ${m.status}</span>
						</div>
						<button type="button" class="btn btn-sm btn-remove-membership" data-cohort-id="${m.cohort_id}" style="padding: 8px 14px; background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.28); border-radius: 10px; cursor: pointer;">
							<i data-lucide="x"></i> Desvincular da Turma
						</button>
					</div>
				`).join('')
				: `<div style="padding: 20px; border-radius: 12px; background: var(--bg-surface); border: 1px dashed var(--border); color: var(--text-muted); font-size: 0.92rem;">Este usuário ainda não possui turmas vinculadas diretamente.</div>`;

			contentRoot.innerHTML = `
				<div class="page-form-wrapper">
					<div class="page-form-topbar">
						<button type="button" class="btn-back-page btn-cancel-page">
							<i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
							Voltar para Equipe & Usuários
						</button>
						<button type="button" class="btn btn-secondary" id="btn-cohorts-open-edit-user">
							<i data-lucide="shield"></i> Editar Papéis Globais
						</button>
					</div>

					<div style="margin-bottom: 24px;">
						<span style="font-size: 0.78rem; text-transform: uppercase; color: var(--primary); font-weight: 800; letter-spacing: 0.05em;">Papéis Contextuais por Turma</span>
						<h2 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin: 4px 0 6px;">Vínculos de Turma: ${escapeHtml(user.name)}</h2>
						<p style="color: var(--text-muted); font-size: 0.95rem;">Defina em quais turmas este usuário atua como Professor/Mestre, Coordenador ou Aluno.</p>
					</div>

					<div class="page-form-section">
						<div class="page-form-section-title" style="margin-bottom: 14px;">
							<i data-lucide="layers" style="color: var(--primary);"></i>
							Turmas Atualmente Vinculadas
						</div>
						${currentList}
					</div>

					<div class="page-form-section">
						<div class="page-form-section-title">
							<i data-lucide="plus-circle" style="color: var(--primary);"></i>
							Vincular a uma Nova Turma
						</div>
						<p class="page-form-section-desc">Escolha a turma e o papel que ${escapeHtml(user.name)} exercerá especificamente nela.</p>

						<form id="form-add-membership">
							<div class="form-grid-2" style="margin-bottom: 20px;">
								<div>
									<label class="form-label">Selecionar Turma *</label>
									<select name="cohort_id" class="form-select" required>
										${cohortOptions}
									</select>
								</div>
								<div>
									<label class="form-label">Papel Contextual nesta Turma *</label>
									<select name="role" class="form-select">
										<option value="master">Professor / Mestre (Corrige entregas, lança presenças)</option>
										<option value="coordinator">Coordenador da Turma</option>
										<option value="student">Aluno / Estudante (Submete atividades, assiste aulas)</option>
									</select>
								</div>
							</div>
							<div style="display: flex; justify-content: flex-end; gap: 12px;">
								<button type="button" class="btn btn-secondary btn-cancel-page">Concluir e Voltar</button>
								<button type="submit" class="btn btn-primary">
									<i data-lucide="plus"></i> Adicionar Vínculo de Turma
								</button>
							</div>
						</form>
					</div>
				</div>
			`;

			if (window.lucide) window.lucide.createIcons();
			contentRoot.querySelectorAll('.btn-cancel-page').forEach(b => b.onclick = () => renderUsersAdminView());

			const editRolesBtn = contentRoot.querySelector('#btn-cohorts-open-edit-user');
			if (editRolesBtn) {
				editRolesBtn.onclick = () => openEditUserModal(user, cohorts);
			}

			// Remove membership
			contentRoot.querySelectorAll('.btn-remove-membership').forEach(btn => {
				btn.onclick = async () => {
					const cid = parseInt(btn.dataset.cohortId, 10);
					try {
						await fetchAPI(`cohorts/${cid}/members/${user.id}`, 'DELETE');
						showToast('Vínculo desfeito com sucesso.', 'info');
						user.memberships = user.memberships.filter(m => m.cohort_id !== cid);
						renderContent();
					} catch (err) {
						showToast(err.message || 'Erro ao remover vínculo.', 'danger');
					}
				};
			});

			// Add membership
			const form = contentRoot.querySelector('#form-add-membership');
			if (form) {
				form.onsubmit = async (e) => {
					e.preventDefault();
					const cid = parseInt(form.cohort_id.value, 10);
					const role = form.role.value;
					try {
						await fetchAPI(`cohorts/${cid}/members`, 'POST', {
							user_id: user.id,
							role: role,
							status: 'active'
						});
						showToast('Vínculo adicionado com sucesso!', 'success');
						const targetCohort = cohorts.find(c => c.id === cid);
						user.memberships = user.memberships || [];
						user.memberships = user.memberships.filter(m => m.cohort_id !== cid);
						user.memberships.push({
							cohort_id: cid,
							cohort_title: targetCohort ? targetCohort.title : 'Turma ' + cid,
							role: role,
							status: 'active'
						});
						renderContent();
					} catch (err) {
						showToast(err.message || 'Erro ao adicionar vínculo.', 'danger');
					}
				};
			}
		};

		renderContent();
	}

	/**
	 * Delete a user safely.
	 */
	async function deleteUser(userId, userName) {
		if (!confirm(`Tem certeza que deseja excluir o usuário "${userName || ''}"? Esta ação removerá seus vínculos e acessos à plataforma.`)) {
			return;
		}

		try {
			await fetchAPI(`users/${userId}`, 'DELETE');
			showToast(`Usuário "${userName || ''}" excluído com sucesso!`, 'success');
			renderUsersAdminView();
		} catch (err) {
			showToast(err.message || 'Erro ao excluir usuário.', 'danger');
		}
	}

	/* ==========================================================================
	   Toast Notifications Helper
	   ========================================================================== */
	function showToast(message, type = 'info') {
		const container = document.getElementById('stackead-toast-container');
		if (!container) return;

		const toast = document.createElement('div');
		toast.className = `toast toast-${type}`;
		toast.innerHTML = `
			<i data-lucide="${type === 'success' ? 'check-circle' : (type === 'danger' ? 'alert-triangle' : 'info')}"></i>
			<span>${message}</span>
		`;

		container.appendChild(toast);
		if (window.lucide) window.lucide.createIcons();

		setTimeout(() => {
			toast.style.opacity = '0';
			toast.style.transform = 'translateX(100%)';
			toast.style.transition = 'all 0.3s ease';
			setTimeout(() => toast.remove(), 300);
		}, 4000);
	}

	/* ==========================================================================
	   18. Universal Canvas Image Cropper (WebP <150KB & Presets 1:1, 16:9, 3:2)
	   ========================================================================== */
	function openCropModal(options = {}) {
		const {
			preset = 'avatar',
			title = 'Recortar Imagem',
			endpoint = 'media/upload',
			onComplete = null
		} = options;

		const aspectConfig = {
			'avatar': { ratio: 1, targetW: 400, targetH: 400, label: '1:1 Circular', circular: true },
			'course_cover': { ratio: 16 / 9, targetW: 1280, targetH: 720, label: '16:9 Capa de Curso', circular: false },
			'news_cover': { ratio: 3 / 2, targetW: 1200, targetH: 800, label: '3:2 Foto Notícia', circular: false },
			'custom': { ratio: 1, targetW: 800, targetH: 800, label: 'Livre', circular: false }
		};

		const cfg = aspectConfig[preset] || aspectConfig['custom'];

		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 660px; width: 95%;">
				<div class="modal-header">
					<h3><i data-lucide="crop"></i> ${title}</h3>
					<div style="display: flex; align-items: center; gap: 10px;">
						<span class="badge badge-info" style="font-size: 11px;">${cfg.label}</span>
						<button class="btn-icon btn-close-modal">&times;</button>
					</div>
				</div>

				<div style="padding: 24px;">
					<!-- Step 1: File Selection Dropzone -->
					<div id="crop-step-dropzone" class="crop-dropzone">
						<i data-lucide="image-plus" style="width: 44px; height: 44px; color: var(--primary);"></i>
						<div>
							<strong style="font-size: 1rem; color: var(--text-main); display: block; margin-bottom: 4px;">Escolha uma imagem do seu dispositivo</strong>
							<p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Formatos recomendados: JPG, PNG ou WebP. Tamanho máximo: 15MB.</p>
						</div>
						<button type="button" class="btn btn-primary" id="btn-browse-crop-file" style="margin-top: 8px;">
							<i data-lucide="folder-open"></i> Selecionar Arquivo
						</button>
						<input type="file" id="input-crop-file" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">
					</div>

					<!-- Step 2: Interactive Canvas Workspace -->
					<div id="crop-step-workspace" style="display: none;">
						<div class="crop-canvas-wrapper" id="crop-canvas-wrapper" style="width: 100%; height: 380px;">
							<canvas id="crop-viewport-canvas" width="600" height="380"></canvas>
						</div>

						<div class="crop-controls-bar">
							<i data-lucide="zoom-out" style="width: 16px; height: 16px; color: var(--text-muted);"></i>
							<input type="range" id="crop-zoom-slider" min="1" max="3" step="0.02" value="1" title="Ajuste de Zoom">
							<i data-lucide="zoom-in" style="width: 16px; height: 16px; color: var(--text-muted);"></i>
							<button type="button" class="btn btn-sm btn-secondary" id="btn-crop-reset" style="padding: 4px 10px; font-size: 12px; display: flex; align-items: center; gap: 4px;">
								<i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i> Centralizar
							</button>
						</div>
						<p style="font-size: 0.8rem; color: var(--text-muted); text-align: center; margin-top: 8px; margin-bottom: 0;">
							Arraste com o mouse para enquadrar. Role a roda do mouse para ajustar o zoom.
						</p>
					</div>
				</div>

				<div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-top: 1px solid var(--border); background: var(--bg-main);">
					<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
					<div style="display: flex; gap: 10px;">
						<button type="button" class="btn btn-secondary" id="btn-crop-change" style="display: none;">
							<i data-lucide="refresh-cw"></i> Trocar Imagem
						</button>
						<button type="button" class="btn btn-primary" id="btn-crop-submit" disabled>
							<i data-lucide="check"></i> <span>Recortar & Salvar WebP</span>
						</button>
					</div>
				</div>
			</div>
		`;

		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		const dropzone = modal.querySelector('#crop-step-dropzone');
		const workspace = modal.querySelector('#crop-step-workspace');
		const fileInput = modal.querySelector('#input-crop-file');
		const browseBtn = modal.querySelector('#btn-browse-crop-file');
		const changeBtn = modal.querySelector('#btn-crop-change');
		const submitBtn = modal.querySelector('#btn-crop-submit');
		const resetBtn = modal.querySelector('#btn-crop-reset');
		const zoomSlider = modal.querySelector('#crop-zoom-slider');
		const canvasWrapper = modal.querySelector('#crop-canvas-wrapper');
		const canvas = modal.querySelector('#crop-viewport-canvas');
		const ctx = canvas.getContext('2d');

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.addEventListener('click', () => modal.remove());
		});

		browseBtn.addEventListener('click', () => fileInput.click());
		dropzone.addEventListener('click', (e) => {
			if (e.target !== browseBtn) fileInput.click();
		});

		dropzone.addEventListener('dragover', (e) => {
			e.preventDefault();
			dropzone.style.borderColor = 'var(--primary)';
		});
		dropzone.addEventListener('dragleave', () => {
			dropzone.style.borderColor = 'var(--border)';
		});
		dropzone.addEventListener('drop', (e) => {
			e.preventDefault();
			dropzone.style.borderColor = 'var(--border)';
			if (e.dataTransfer.files && e.dataTransfer.files[0]) {
				loadFile(e.dataTransfer.files[0]);
			}
		});

		fileInput.addEventListener('change', () => {
			if (fileInput.files && fileInput.files[0]) {
				loadFile(fileInput.files[0]);
			}
		});

		changeBtn.addEventListener('click', () => {
			fileInput.value = '';
			workspace.style.display = 'none';
			dropzone.style.display = 'flex';
			changeBtn.style.display = 'none';
			submitBtn.disabled = true;
		});

		// Canvas State
		let loadedImg = null;
		let imgScale = 1;
		let baseScale = 1;
		let imgX = 0;
		let imgY = 0;
		let isDragging = false;
		let dragStartX = 0;
		let dragStartY = 0;
		let cropBox = { x: 0, y: 0, w: 0, h: 0 };

		function computeCropBox() {
			const cW = canvas.width;
			const cH = canvas.height;
			const pad = 24;
			const maxW = cW - (pad * 2);
			const maxH = cH - (pad * 2);

			let bW, bH;
			if (cfg.ratio >= (maxW / maxH)) {
				bW = maxW;
				bH = Math.round(maxW / cfg.ratio);
			} else {
				bH = maxH;
				bW = Math.round(maxH * cfg.ratio);
			}

			cropBox = {
				x: Math.round((cW - bW) / 2),
				y: Math.round((cH - bH) / 2),
				w: bW,
				h: bH
			};
		}

		function loadFile(file) {
			if (!file.type.startsWith('image/')) {
				showToast('Por favor, selecione um arquivo de imagem válido.', 'danger');
				return;
			}

			const reader = new FileReader();
			reader.onload = (e) => {
				const img = new Image();
				img.onload = () => {
					loadedImg = img;
					computeCropBox();

					baseScale = Math.max(cropBox.w / img.width, cropBox.h / img.height);
					imgScale = baseScale;
					zoomSlider.value = 1;

					imgX = cropBox.x + Math.round((cropBox.w - img.width * imgScale) / 2);
					imgY = cropBox.y + Math.round((cropBox.h - img.height * imgScale) / 2);

					dropzone.style.display = 'none';
					workspace.style.display = 'block';
					changeBtn.style.display = 'inline-flex';
					submitBtn.disabled = false;

					draw();
				};
				img.src = e.target.result;
			};
			reader.readAsDataURL(file);
		}

		function draw() {
			if (!loadedImg) return;

			ctx.clearRect(0, 0, canvas.width, canvas.height);

			ctx.drawImage(
				loadedImg,
				imgX,
				imgY,
				loadedImg.width * imgScale,
				loadedImg.height * imgScale
			);

			ctx.save();
			ctx.fillStyle = 'rgba(5, 8, 16, 0.72)';

			if (cfg.circular) {
				ctx.beginPath();
				ctx.rect(0, 0, canvas.width, canvas.height);
				const radius = cropBox.w / 2;
				const centerX = cropBox.x + radius;
				const centerY = cropBox.y + radius;
				ctx.arc(centerX, centerY, radius, 0, Math.PI * 2, true);
				ctx.fill('evenodd');

				ctx.strokeStyle = 'rgba(255, 255, 255, 0.85)';
				ctx.lineWidth = 2;
				ctx.beginPath();
				ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
				ctx.stroke();
			} else {
				ctx.fillRect(0, 0, canvas.width, cropBox.y);
				ctx.fillRect(0, cropBox.y + cropBox.h, canvas.width, canvas.height - (cropBox.y + cropBox.h));
				ctx.fillRect(0, cropBox.y, cropBox.x, cropBox.h);
				ctx.fillRect(cropBox.x + cropBox.w, cropBox.y, canvas.width - (cropBox.x + cropBox.w), cropBox.h);

				ctx.strokeStyle = 'rgba(255, 255, 255, 0.9)';
				ctx.lineWidth = 2;
				ctx.strokeRect(cropBox.x, cropBox.y, cropBox.w, cropBox.h);

				ctx.strokeStyle = 'rgba(255, 255, 255, 0.35)';
				ctx.lineWidth = 1;
				ctx.setLineDash([4, 4]);

				ctx.beginPath();
				ctx.moveTo(cropBox.x + cropBox.w / 3, cropBox.y);
				ctx.lineTo(cropBox.x + cropBox.w / 3, cropBox.y + cropBox.h);
				ctx.moveTo(cropBox.x + (cropBox.w * 2) / 3, cropBox.y);
				ctx.lineTo(cropBox.x + (cropBox.w * 2) / 3, cropBox.y + cropBox.h);

				ctx.moveTo(cropBox.x, cropBox.y + cropBox.h / 3);
				ctx.lineTo(cropBox.x + cropBox.w, cropBox.y + cropBox.h / 3);
				ctx.moveTo(cropBox.x, cropBox.y + (cropBox.h * 2) / 3);
				ctx.lineTo(cropBox.x + cropBox.w, cropBox.y + (cropBox.h * 2) / 3);
				ctx.stroke();
			}

			ctx.restore();
		}

		canvasWrapper.addEventListener('mousedown', (e) => {
			if (!loadedImg) return;
			isDragging = true;
			dragStartX = e.clientX - imgX;
			dragStartY = e.clientY - imgY;
		});

		window.addEventListener('mousemove', (e) => {
			if (!isDragging || !loadedImg) return;
			imgX = e.clientX - dragStartX;
			imgY = e.clientY - dragStartY;
			draw();
		});

		window.addEventListener('mouseup', () => {
			isDragging = false;
		});

		zoomSlider.addEventListener('input', (e) => {
			if (!loadedImg) return;
			const factor = parseFloat(e.target.value);
			const newScale = baseScale * factor;

			const centerX = cropBox.x + cropBox.w / 2;
			const centerY = cropBox.y + cropBox.h / 2;

			imgX = centerX - (centerX - imgX) * (newScale / imgScale);
			imgY = centerY - (centerY - imgY) * (newScale / imgScale);
			imgScale = newScale;

			draw();
		});

		canvasWrapper.addEventListener('wheel', (e) => {
			e.preventDefault();
			if (!loadedImg) return;
			let val = parseFloat(zoomSlider.value);
			val += e.deltaY < 0 ? 0.08 : -0.08;
			val = Math.max(1, Math.min(3, val));
			zoomSlider.value = val;
			zoomSlider.dispatchEvent(new Event('input'));
		});

		resetBtn.addEventListener('click', () => {
			if (!loadedImg) return;
			zoomSlider.value = 1;
			imgScale = baseScale;
			imgX = cropBox.x + Math.round((cropBox.w - loadedImg.width * imgScale) / 2);
			imgY = cropBox.y + Math.round((cropBox.h - loadedImg.height * imgScale) / 2);
			draw();
		});

		submitBtn.addEventListener('click', async () => {
			if (!loadedImg) return;

			submitBtn.disabled = true;
			submitBtn.innerHTML = '<span class="spinner" style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin 0.8s linear infinite;"></span> Otimizando WebP...';

			try {
				const sx = Math.max(0, (cropBox.x - imgX) / imgScale);
				const sy = Math.max(0, (cropBox.y - imgY) / imgScale);
				const sw = Math.min(loadedImg.width - sx, cropBox.w / imgScale);
				const sh = Math.min(loadedImg.height - sy, cropBox.h / imgScale);

				const offCanvas = document.createElement('canvas');
				offCanvas.width = cfg.targetW;
				offCanvas.height = cfg.targetH;
				const offCtx = offCanvas.getContext('2d');

				offCtx.drawImage(loadedImg, sx, sy, sw, sh, 0, 0, cfg.targetW, cfg.targetH);

				const webpDataUrl = offCanvas.toDataURL('image/webp', 0.85);

				const payload = {
					image_base64: webpDataUrl,
					preset: preset,
					title: title
				};

				const result = await fetchAPI(endpoint, 'POST', payload);

				showToast(`Imagem WebP salva com sucesso! (${result.file_size_formatted || ''})`, 'success');
				modal.remove();

				const callback = onComplete || options.onSuccess;
				if (typeof callback === 'function') {
					callback(result);
				}
			} catch (err) {
				showToast(err.message || 'Erro ao processar imagem WebP.', 'danger');
				submitBtn.disabled = false;
				submitBtn.innerHTML = '<i data-lucide="check"></i> <span>Recortar & Salvar WebP</span>';
				if (window.lucide) window.lucide.createIcons();
			}
		});
	}

	/* ==========================================================================
	   19. User Profile Modal & Avatar Management
	   ========================================================================== */
	function openUserProfileModal() {
		const user = API.user;
		if (!user) return;

		const avatarSrc = user.avatar_url || '';
		const initials = (user.name || 'US').substring(0, 2).toUpperCase();

		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 480px; width: 92%;">
				<div class="modal-header">
					<h3><i data-lucide="user-check"></i> Meu Perfil</h3>
					<button class="btn-icon btn-close-modal">&times;</button>
				</div>
				<div style="padding: 28px 24px; text-align: center;">
					<div class="profile-avatar-preview" id="profile-avatar-trigger" title="Clique para alterar sua foto de perfil">
						${avatarSrc ? `<img src="${avatarSrc}" id="profile-modal-avatar-img" alt="${user.name}">` : `<div style="width:100%;height:100%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;">${initials}</div>`}
						<div class="profile-avatar-overlay">
							<i data-lucide="camera" style="width: 20px; height: 20px;"></i>
							<span>Alterar Foto</span>
						</div>
					</div>

					<h3 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">${user.name}</h3>
					<span class="badge badge-primary" style="margin-bottom: 16px;">${user.role_label || user.role}</span>
					<p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 24px;">${user.email}</p>

					<div style="background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px; margin-bottom: 24px; text-align: left; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 0.85rem;">
						<div>
							<span style="color: var(--text-muted); display: block;">ID da Conta:</span>
							<strong style="color: var(--text-main);">#${user.id}</strong>
						</div>
						<div>
							<span style="color: var(--text-muted); display: block;">Nível de XP:</span>
							<strong style="color: var(--primary);">Nível ${user.gamification ? user.gamification.level : 1}</strong>
						</div>
					</div>

					<div style="display: flex; gap: 10px; justify-content: center;">
						<button type="button" class="btn btn-primary" id="btn-change-avatar-action">
							<i data-lucide="camera"></i> Alterar Foto com Recorte 1:1
						</button>
						<button type="button" class="btn btn-secondary btn-close-modal">Fechar</button>
					</div>
				</div>
			</div>
		`;

		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.addEventListener('click', () => modal.remove());
		});

		const triggerCrop = () => {
			openCropModal({
				preset: 'avatar',
				title: 'Recortar Foto de Perfil (1:1)',
				endpoint: 'users/me/avatar',
				onComplete: (data) => {
					if (data.avatar_url) {
						user.avatar_url = data.avatar_url;

						// Update modal avatar
						const previewDiv = modal.querySelector('.profile-avatar-preview');
						if (previewDiv) {
							previewDiv.innerHTML = `
								<img src="${data.avatar_url}" id="profile-modal-avatar-img" alt="${user.name}">
								<div class="profile-avatar-overlay">
									<i data-lucide="camera" style="width: 20px; height: 20px;"></i>
									<span>Alterar Foto</span>
								</div>
							`;
							if (window.lucide) window.lucide.createIcons();
						}

						// Update sidebar user avatar
						const sideAvatar = document.getElementById('sidebar-user-avatar');
						if (sideAvatar) {
							sideAvatar.innerHTML = `<img src="${data.avatar_url}" alt="${user.name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
						}
					}
				}
			});
		};

		modal.querySelector('#profile-avatar-trigger').addEventListener('click', triggerCrop);
		modal.querySelector('#btn-change-avatar-action').addEventListener('click', triggerCrop);
	}

	// Initialize on DOM Ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// Global Exports
	
	/* ==========================================================================
	   Gamification Admin Management Panel (Moedas Virtuais & Badges CRUD)
	   ========================================================================== */
	async function renderGamificationAdmin() {
		try {
			const res = await fetchAPI('gamification/admin/settings');
			const config = res.config || {};
			const badges = res.badges || [];

			contentRoot.innerHTML = `
				<div class="gamification-admin-view">
					<div class="desk-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
						<div>
							<h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">Gestão de Gamificação & Moedas Virtuais</h2>
							<p style="color: var(--text-muted); font-size: 0.95rem;">Configure as taxas de premiação em moedas virtuais, XP e cadastre novos emblemas personalizados.</p>
						</div>
						<button id="btn-create-badge-modal" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
							<i data-lucide="plus-circle"></i>
							<span>Novo Emblema / Insígnia</span>
						</button>
					</div>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
						<!-- Card 1: Moeda Virtual & Economia do Aluno -->
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px;">
							<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
								<i data-lucide="coins" style="color: #fbbf24; width: 22px; height: 22px;"></i>
								<h3 style="font-size: 1.2rem; font-weight: 700; margin: 0;">Configuração da Moeda Virtual</h3>
							</div>
							<form id="form-gamification-coins">
								<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
									<div>
										<label style="display: block; font-weight: 600; font-size: 12px; margin-bottom: 4px;">Nome da Moeda:</label>
										<input type="text" id="cfg-coin-name" class="form-control" value="${escapeHtml(config.coin_name || 'ArtCoins')}" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
									</div>
									<div>
										<label style="display: block; font-weight: 600; font-size: 12px; margin-bottom: 4px;">Símbolo / Sigla:</label>
										<input type="text" id="cfg-coin-symbol" class="form-control" value="${escapeHtml(config.coin_symbol || 'AC$')}" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
									</div>
								</div>

								<h4 style="font-size: 13px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin: 16px 0 8px;">Recompensas por Conquistas Didáticas:</h4>
								<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
									<div>
										<label style="font-size: 11px; font-weight: 600;">Moedas por Aula:</label>
										<input type="number" id="cfg-coins-lesson" class="form-control" value="${config.coins_per_lesson || 10}" style="width: 100%; padding: 6px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
									</div>
									<div>
										<label style="font-size: 11px; font-weight: 600;">Moedas por Sessão Ao Vivo:</label>
										<input type="number" id="cfg-coins-session" class="form-control" value="${config.coins_per_session || 25}" style="width: 100%; padding: 6px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
									</div>
									<div>
										<label style="font-size: 11px; font-weight: 600;">Moedas por Projeto Prático:</label>
										<input type="number" id="cfg-coins-assignment" class="form-control" value="${config.coins_per_assignment || 50}" style="width: 100%; padding: 6px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
									</div>
									<div>
										<label style="font-size: 11px; font-weight: 600;">Moedas por Formatura:</label>
										<input type="number" id="cfg-coins-certificate" class="form-control" value="${config.coins_per_certificate || 200}" style="width: 100%; padding: 6px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
									</div>
								</div>
								<button type="submit" class="btn btn-primary btn-block">Salvar Regras de Moedas</button>
							</form>
						</div>

						<!-- Card 2: Visão Geral de Níveis & XP -->
						<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px;">
							<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
								<i data-lucide="zap" style="color: #60a5fa; width: 22px; height: 22px;"></i>
								<h3 style="font-size: 1.2rem; font-weight: 700; margin: 0;">Pontos de Experiência (XP)</h3>
							</div>
							<p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Os pontos de XP determinam o nível do estudante na plataforma e destravam privilégios de tutoria e destaque na comunidade.</p>
							<div style="display: flex; flex-direction: column; gap: 10px;">
								<div style="display: flex; justify-content: space-between; padding: 10px; background: rgba(255,255,255,0.02); border-radius: 6px;">
									<span>XP por Aula Concluída</span>
									<strong>+${config.xp_per_lesson || 20} XP</strong>
								</div>
								<div style="display: flex; justify-content: space-between; padding: 10px; background: rgba(255,255,255,0.02); border-radius: 6px;">
									<span>XP por Presença em Aula Ao Vivo</span>
									<strong>+${config.xp_per_session || 50} XP</strong>
								</div>
								<div style="display: flex; justify-content: space-between; padding: 10px; background: rgba(255,255,255,0.02); border-radius: 6px;">
									<span>XP por Projeto Prático Aprovado</span>
									<strong>+${config.xp_per_assignment || 100} XP</strong>
								</div>
							</div>
						</div>
					</div>

					<!-- Tabela de Badges & Emblemas -->
					<div class="card" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px;">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
							<h3 style="font-size: 1.2rem; font-weight: 700; margin: 0;">Emblemas & Insígnias Cadastradas (${badges.length})</h3>
						</div>
						<div class="table-responsive">
							<table class="table" style="width: 100%; border-collapse: collapse;">
								<thead>
									<tr style="border-bottom: 1px solid var(--border); text-align: left; font-size: 12px; color: #94a3b8; text-transform: uppercase;">
										<th style="padding: 10px;">Ícone</th>
										<th style="padding: 10px;">Título</th>
										<th style="padding: 10px;">Critério / Descrição</th>
										<th style="padding: 10px;">Recompensa</th>
										<th style="padding: 10px;">Tipo</th>
										<th style="padding: 10px; text-align: right;">Ações</th>
									</tr>
								</thead>
								<tbody>
									${badges.map(b => `
										<tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
											<td style="padding: 12px 10px;">
												<div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(59, 130, 246, 0.15); display: flex; align-items: center; justify-content: center; color: var(--primary);">
													<i data-lucide="${b.icon || 'award'}"></i>
												</div>
											</td>
											<td style="padding: 12px 10px; font-weight: 700; color: #fff;">${escapeHtml(b.title)}</td>
											<td style="padding: 12px 10px; font-size: 13px; color: #cbd5e1;">${escapeHtml(b.description)}</td>
											<td style="padding: 12px 10px; font-size: 12px;">
												<span style="color: #60a5fa; font-weight: 600;">+${b.xp_reward || 0} XP</span> &bull; 
												<span style="color: #fbbf24; font-weight: 600;">+${b.coin_reward || 0} ${config.coin_name || 'Moedas'}</span>
											</td>
											<td style="padding: 12px 10px;">
												<span class="badge ${b.is_system ? 'status-locked' : 'status-completed'}" style="font-size: 11px;">
													${b.is_system ? 'Padrão' : 'Personalizado'}
												</span>
											</td>
											<td style="padding: 12px 10px; text-align: right;">
												${!b.is_system ? `
													<button type="button" class="btn btn-sm btn-delete-badge" data-key="${b.key}" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; padding: 4px 8px; cursor: pointer;">
														<i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
													</button>
												` : '<span style="color: #64748b; font-size: 11px;">Sistema</span>'}
											</td>
										</tr>
									`).join('')}
								</tbody>
							</table>
						</div>
					</div>
				</div>
			`;

			if (window.lucide) window.lucide.createIcons();

			// Coins form submit
			const formCoins = document.getElementById('form-gamification-coins');
			if (formCoins) {
				formCoins.addEventListener('submit', async (e) => {
					e.preventDefault();
					try {
						await fetchAPI('gamification/admin/settings', 'POST', {
							coin_name: document.getElementById('cfg-coin-name').value,
							coin_symbol: document.getElementById('cfg-coin-symbol').value,
							coins_per_lesson: parseInt(document.getElementById('cfg-coins-lesson').value, 10),
							coins_per_session: parseInt(document.getElementById('cfg-coins-session').value, 10),
							coins_per_assignment: parseInt(document.getElementById('cfg-coins-assignment').value, 10),
							coins_per_certificate: parseInt(document.getElementById('cfg-coins-certificate').value, 10),
						});
						showToast('Configurações de moedas salvas!', 'success');
					} catch (err) {
						showToast('Erro ao salvar configurações.', 'danger');
					}
				});
			}

			// Delete custom badge
			document.querySelectorAll('.btn-delete-badge').forEach(btn => {
				btn.addEventListener('click', async () => {
					const key = btn.getAttribute('data-key');
					if (confirm('Deseja excluir este emblema personalizado?')) {
						try {
							await fetchAPI(`gamification/admin/badges/${key}`, 'DELETE');
							showToast('Emblema excluído!', 'success');
							renderGamificationAdmin();
						} catch (err) {
							showToast('Erro ao excluir emblema.', 'danger');
						}
					}
				});
			});

			// Create Badge Modal Trigger
			const createBadgeBtn = document.getElementById('btn-create-badge-modal');
			if (createBadgeBtn) {
				createBadgeBtn.addEventListener('click', () => openCreateBadgeModal());
			}

		} catch (err) {
			renderError('Não foi possível carregar a gestão de gamificação.');
		}
	}

	function openCreateBadgeModal() {
		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.innerHTML = `
			<div class="modal-card" style="max-width: 520px; width: 90%;">
				<div class="modal-header">
					<h3>Criar Novo Emblema Personalizado</h3>
					<button class="btn-icon btn-close-modal">&times;</button>
				</div>
				<form id="form-create-custom-badge" style="padding: 24px;">
					<div class="form-group" style="margin-bottom: 14px;">
						<label style="display: block; font-weight: 600; margin-bottom: 4px;">Título do Emblema *</label>
						<input type="text" id="badge-title" class="form-control" required style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Ex: Artista de Vanguarda">
					</div>
					<div class="form-group" style="margin-bottom: 14px;">
						<label style="display: block; font-weight: 600; margin-bottom: 4px;">Descrição / Critério de Conquista</label>
						<textarea id="badge-desc" rows="2" class="form-control" style="width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Descreva o que o estudante realizou para conquistar esta insígnia..."></textarea>
					</div>
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
						<div>
							<label style="display: block; font-weight: 600; font-size: 12px; margin-bottom: 4px;">Ícone Lucide:</label>
							<select id="badge-icon" class="form-control" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
								<option value="award">award (Medalha)</option>
								<option value="star">star (Estrela)</option>
								<option value="sparkles">sparkles (Brilho)</option>
								<option value="flame">flame (Chama)</option>
								<option value="trophy">trophy (Troféu)</option>
								<option value="heart">heart (Coração)</option>
								<option value="shield">shield (Escudo)</option>
								<option value="compass">compass (Bússola)</option>
							</select>
						</div>
						<div>
							<label style="display: block; font-weight: 600; font-size: 12px; margin-bottom: 4px;">Recompensa em XP:</label>
							<input type="number" id="badge-xp" value="50" min="0" class="form-control" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
						</div>
					</div>
					<div class="form-group" style="margin-bottom: 20px;">
						<label style="display: block; font-weight: 600; font-size: 12px; margin-bottom: 4px;">Recompensa em Moedas Virtuais:</label>
						<input type="number" id="badge-coins" value="25" min="0" class="form-control" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
					</div>
					<div style="display: flex; justify-content: flex-end; gap: 12px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary">Salvar Emblema</button>
					</div>
				</form>
			</div>
		`;
		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		modal.querySelector('#form-create-custom-badge').onsubmit = async (e) => {
			e.preventDefault();
			try {
				await fetchAPI('gamification/admin/badges', 'POST', {
					title: modal.querySelector('#badge-title').value,
					description: modal.querySelector('#badge-desc').value,
					icon: modal.querySelector('#badge-icon').value,
					xp_reward: parseInt(modal.querySelector('#badge-xp').value, 10),
					coin_reward: parseInt(modal.querySelector('#badge-coins').value, 10),
				});
				showToast('Novo emblema cadastrado!', 'success');
				modal.remove();
				renderGamificationAdmin();
			} catch (err) {
				showToast('Erro ao cadastrar emblema.', 'danger');
			}
		};
	}

	/* ==========================================================================
	   Cohorts (Turmas) Management Station (In-App)
	   ========================================================================== */
	async function openCreateCohortModal() {
		let courses = [];
		try {
			courses = await fetchAPI('courses');
		} catch (e) {
			courses = [];
		}

		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 100001; display: flex; align-items: center; justify-content: center; padding: 20px;';
		modal.innerHTML = `
			<div class="modal-card" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-xl); max-width: 640px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.7);">
				<div class="modal-header" style="padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--bg-card);">
					<h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
						<i data-lucide="users" style="color: var(--primary);"></i>
						<span>Cadastrar Nova Turma</span>
					</h3>
					<button type="button" class="btn-icon btn-close-modal" style="background: none; border: none; font-size: 1.6rem; color: var(--text-muted); cursor: pointer;">&times;</button>
				</div>
				<form id="form-create-cohort" style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
					<div class="form-group">
						<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Nome / Identificação da Turma *</label>
						<input type="text" id="cohort-f-title" class="form-control" required placeholder="Ex: Turma Alpha 2026 - Noite" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
					</div>

					<div class="form-group">
						<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Curso Base / Matriz Didática</label>
						<select id="cohort-f-course" class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
							<option value="0">Selecione um curso de referência (opcional)</option>
							${courses.map(c => `<option value="${c.id}">${escapeHtml(c.title)}</option>`).join('')}
						</select>
						<span style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px; display: block;">Se selecionado, a turma herdará o plano e matriz curricular deste curso.</span>
					</div>

					<div style="grid-template-columns: 1fr 1fr; gap: 16px; display: grid;">
						<div class="form-group">
							<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Data de Início</label>
							<input type="date" id="cohort-f-start" class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Data de Conclusão</label>
							<input type="date" id="cohort-f-end" class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
						</div>
					</div>

					<div style="grid-template-columns: 1fr 1fr; gap: 16px; display: grid;">
						<div class="form-group">
							<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Limite de Alunos</label>
							<input type="number" id="cohort-f-max" min="1" max="1000" value="50" class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Link do Grupo da Turma</label>
							<input type="url" id="cohort-f-community" class="form-control" placeholder="https://chat.whatsapp.com/..." style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
						</div>
					</div>

					<div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 12px; border-top: 1px solid var(--border); padding-top: 18px;">
						<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
						<button type="submit" class="btn btn-primary" id="btn-submit-create-cohort">Criar Turma</button>
					</div>
				</form>
			</div>
		`;

		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		modal.querySelector('#form-create-cohort').onsubmit = async (e) => {
			e.preventDefault();
			const submitBtn = modal.querySelector('#btn-submit-create-cohort');
			submitBtn.disabled = true;
			submitBtn.textContent = 'Criando turma...';

			try {
				const res = await fetchAPI('cohorts', 'POST', {
					title: modal.querySelector('#cohort-f-title').value.trim(),
					course_id: parseInt(modal.querySelector('#cohort-f-course').value, 10) || 0,
					start_date: modal.querySelector('#cohort-f-start').value,
					end_date: modal.querySelector('#cohort-f-end').value,
					max_students: parseInt(modal.querySelector('#cohort-f-max').value, 10) || 50,
					community_url: modal.querySelector('#cohort-f-community').value.trim()
				});

				showToast('Turma cadastrada com sucesso!', 'success');
				modal.remove();

				// Refresh cohorts and switch to the newly created one
				await loadUserCohorts();
				if (res && res.id) {
					state.activeCohortId = res.id;
					if (cohortSelect) cohortSelect.value = res.id;
				}
				navigate('curriculum');
			} catch (err) {
				submitBtn.disabled = false;
				submitBtn.textContent = 'Criar Turma';
			}
		};
	}

	async function openEditCohortModal(cohortId) {
		if (!cohortId) return;

		let cohort = null;
		let courses = [];
		try {
			[cohort, courses] = await Promise.all([
				fetchAPI(`cohorts/${cohortId}`),
				fetchAPI('courses').catch(() => [])
			]);
		} catch (e) {
			showToast('Não foi possível carregar os dados da turma.', 'danger');
			return;
		}

		const modal = document.createElement('div');
		modal.className = 'modal-backdrop';
		modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 100001; display: flex; align-items: center; justify-content: center; padding: 20px;';
		modal.innerHTML = `
			<div class="modal-card" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-xl); max-width: 640px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.7);">
				<div class="modal-header" style="padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--bg-card);">
					<h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
						<i data-lucide="edit-3" style="color: var(--primary);"></i>
						<span>Editar Turma: ${escapeHtml(cohort.title)}</span>
					</h3>
					<button type="button" class="btn-icon btn-close-modal" style="background: none; border: none; font-size: 1.6rem; color: var(--text-muted); cursor: pointer;">&times;</button>
				</div>
				<form id="form-edit-cohort" style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
					<div class="form-group">
						<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Nome / Identificação da Turma *</label>
						<input type="text" id="cohort-f-title" class="form-control" required value="${escapeHtml(cohort.title || '')}" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
					</div>

					<div class="form-group">
						<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Curso Base / Matriz Didática</label>
						<select id="cohort-f-course" class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
							<option value="0">Sem curso vinculado</option>
							${courses.map(c => `<option value="${c.id}" ${c.id === cohort.course_id ? 'selected' : ''}>${escapeHtml(c.title)}</option>`).join('')}
						</select>
					</div>

					<div style="grid-template-columns: 1fr 1fr; gap: 16px; display: grid;">
						<div class="form-group">
							<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Data de Início</label>
							<input type="date" id="cohort-f-start" class="form-control" value="${cohort.start_date || ''}" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Data de Conclusão</label>
							<input type="date" id="cohort-f-end" class="form-control" value="${cohort.end_date || ''}" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
						</div>
					</div>

					<div style="grid-template-columns: 1fr 1fr; gap: 16px; display: grid;">
						<div class="form-group">
							<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Limite de Alunos</label>
							<input type="number" id="cohort-f-max" min="1" max="1000" value="${cohort.max_students || 50}" class="form-control" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
						</div>
						<div class="form-group">
							<label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">Link do Grupo da Turma</label>
							<input type="url" id="cohort-f-community" class="form-control" value="${escapeHtml(cohort.community_url || '')}" placeholder="https://chat.whatsapp.com/..." style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
						</div>
					</div>

					<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; border-top: 1px solid var(--border); padding-top: 18px;">
						<button type="button" class="btn btn-sm" id="btn-delete-this-cohort" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 8px 14px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
							<i data-lucide="trash-2"></i> Excluir Turma
						</button>
						<div style="display: flex; gap: 12px;">
							<button type="button" class="btn btn-secondary btn-close-modal">Cancelar</button>
							<button type="submit" class="btn btn-primary" id="btn-submit-edit-cohort">Salvar Alterações</button>
						</div>
					</div>
				</form>
			</div>
		`;

		document.body.appendChild(modal);
		if (window.lucide) window.lucide.createIcons();

		modal.querySelectorAll('.btn-close-modal').forEach(b => {
			b.onclick = () => modal.remove();
		});

		const delBtn = modal.querySelector('#btn-delete-this-cohort');
		if (delBtn) {
			delBtn.onclick = async () => {
				if (await deleteCohort(cohortId, cohort.title)) {
					modal.remove();
				}
			};
		}

		modal.querySelector('#form-edit-cohort').onsubmit = async (e) => {
			e.preventDefault();
			const submitBtn = modal.querySelector('#btn-submit-edit-cohort');
			submitBtn.disabled = true;
			submitBtn.textContent = 'Salvando...';

			try {
				await fetchAPI(`cohorts/${cohortId}`, 'POST', {
					title: modal.querySelector('#cohort-f-title').value.trim(),
					course_id: parseInt(modal.querySelector('#cohort-f-course').value, 10) || 0,
					start_date: modal.querySelector('#cohort-f-start').value,
					end_date: modal.querySelector('#cohort-f-end').value,
					max_students: parseInt(modal.querySelector('#cohort-f-max').value, 10) || 50,
					community_url: modal.querySelector('#cohort-f-community').value.trim()
				});

				showToast('Turma atualizada com sucesso!', 'success');
				modal.remove();
				await loadUserCohorts();
				navigate('curriculum');
			} catch (err) {
				submitBtn.disabled = false;
				submitBtn.textContent = 'Salvar Alterações';
			}
		};
	}

	async function deleteCohort(cohortId, title) {
		if (!confirm(`Tem certeza que deseja excluir a turma "${title}"? Todos os vínculos de alunos e notas serão removidos.`)) {
			return false;
		}

		try {
			await fetchAPI(`cohorts/${cohortId}`, 'DELETE');
			showToast('Turma excluída com sucesso.', 'success');
			await loadUserCohorts();
			navigate('dashboard');
			return true;
		} catch (e) {
			showToast('Erro ao excluir turma.', 'danger');
			return false;
		}
	}

	Object.assign(window.StackEAD_App, {
		navigate,
		openCreateCourseModal,
		openEditCourseModal,
		deleteCourse,
		openCreateSessionModal,
		openEditSessionModal,
		deleteSession,
		openCreateAssignmentModal,
		openEditAssignmentModal,
		deleteAssignment,
		openAssignmentModal,
		openSubmitAssignmentModal,
		openQuizModal,
		openManageQuizModal,
		openCreateModuleModal,
		renderGamificationAdmin,
		openEditModuleModal,
		deleteModule,
		openCreateLessonModal,
		openEditLessonModal,
		deleteLesson,
		openCreateCohortModal,
		openEditCohortModal,
		deleteCohort,
		openCreateNewsModal,
		openEditNewsModal,
		deleteNews,
		openEditCategoryModal,
		deleteCategory,
		openAccommodationModal,
		openGradeSubmissionModal,
		renderLandingBuilderView,
		renderUsersAdminView,
		openCreateUserModal,
		openEditUserModal,
		openManageUserCohortsModal,
		deleteUser,
		openCropModal,
		openUserProfileModal,
		reviewAcc,
		upvoteTopic,
		showToast
	});
	window.StackEAD = window.StackEAD_App;
})();
