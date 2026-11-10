/**
 * StackEAD - W3C COGA Accessibility Controller
 * Manages Reading Guide, Adaptive Fonts, Spacing, Contrast and Focus Mode.
 */

(function () {
	'use strict';

	const STORAGE_KEY = 'stackead_coga_preferences';

	// Default settings
	let settings = {
		ruler: false,
		focusMode: false,
		font: 'default',
		spacing: false,
		contrast: false
	};

	// DOM Elements
	let rulerEl, dockModal, btnToggleDock, btnCloseDock;

	function init() {
		rulerEl = document.getElementById('coga-reading-guide');
		dockModal = document.getElementById('coga-dock-modal');
		btnToggleDock = document.getElementById('btn-toggle-coga');
		btnCloseDock = document.getElementById('btn-close-coga');

		loadSettings();
		applyAccommodationsFromProfile();
		setupEventListeners();
		applySettings();
	}

	function loadSettings() {
		try {
			const saved = localStorage.getItem(STORAGE_KEY);
			if (saved) {
				settings = Object.assign(settings, JSON.parse(saved));
			}
			const restBase = window.StackEAD_Config && (window.StackEAD_Config.root || window.StackEAD_Config.restUrl);
			if (restBase) {
				fetch(restBase + 'stackead/v1/accommodations/preferences', {
					headers: { 'X-WP-Nonce': window.StackEAD_Config.nonce }
				})
				.then(r => r.json())
				.then(serverPrefs => {
					if (serverPrefs && typeof serverPrefs === 'object') {
						if (serverPrefs.reading_guide !== undefined) settings.ruler = !!serverPrefs.reading_guide;
						if (serverPrefs.high_contrast !== undefined) settings.contrast = !!serverPrefs.high_contrast;
						if (serverPrefs.dyslexic_font) settings.font = 'opendyslexic';
						if (serverPrefs.line_spacing === 'expanded') settings.spacing = true;
						applySettings();
					}
				}).catch(() => {});
			}
		} catch (e) {
			console.warn('Erro ao carregar preferências de acessibilidade:', e);
		}
	}

	function saveSettings() {
		try {
			localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
			const restBase = window.StackEAD_Config && (window.StackEAD_Config.root || window.StackEAD_Config.restUrl);
			if (restBase) {
				fetch(restBase + 'stackead/v1/accommodations/preferences', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': window.StackEAD_Config.nonce
					},
					body: JSON.stringify({
						dyslexic_font: settings.font === 'opendyslexic',
						reading_guide: !!settings.ruler,
						high_contrast: !!settings.contrast,
						line_spacing: settings.spacing ? 'expanded' : 'normal',
						sound_dampening: false
					})
				}).catch(() => {});
			}
		} catch (e) {}
	}

	function applyAccommodationsFromProfile() {
		// If student has approved accommodations on server, respect them
		if (window.StackEAD_Config && window.StackEAD_Config.user && window.StackEAD_Config.user.accommodations) {
			const acc = window.StackEAD_Config.user.accommodations;
			if (acc.focus_mode) settings.focusMode = true;
			if (acc.reading_guide) settings.ruler = true;
			if (acc.default_simplified) settings.spacing = true;
		}
	}

	function setupEventListeners() {
		// Toggle Dock Modal
		if (btnToggleDock && dockModal) {
			btnToggleDock.addEventListener('click', () => {
				const isVisible = dockModal.style.display !== 'none';
				dockModal.style.display = isVisible ? 'none' : 'block';
				if (!isVisible && window.lucide) window.lucide.createIcons();
			});
		}

		if (btnCloseDock && dockModal) {
			btnCloseDock.addEventListener('click', () => {
				dockModal.style.display = 'none';
			});
		}

		// 1. Reading Ruler
		const rulerToggle = document.getElementById('coga-toggle-ruler');
		if (rulerToggle) {
			rulerToggle.checked = settings.ruler;
			rulerToggle.addEventListener('change', (e) => {
				settings.ruler = e.target.checked;
				applyRuler();
				saveSettings();
			});
		}

		window.addEventListener('mousemove', (e) => {
			if (settings.ruler && rulerEl) {
				rulerEl.style.top = (e.clientY - 19) + 'px';
			}
		});

		// 2. Focus Mode
		const focusToggle = document.getElementById('coga-toggle-focus');
		if (focusToggle) {
			focusToggle.checked = settings.focusMode;
			focusToggle.addEventListener('change', (e) => {
				settings.focusMode = e.target.checked;
				applyFocusMode();
				saveSettings();
			});
		}

		// 3. Fonts
		const fontChips = document.querySelectorAll('.coga-font-selector .btn-chip');
		fontChips.forEach(chip => {
			if (chip.getAttribute('data-font') === settings.font) {
				chip.classList.add('active');
			} else {
				chip.classList.remove('active');
			}

			chip.addEventListener('click', () => {
				fontChips.forEach(c => c.classList.remove('active'));
				chip.classList.add('active');
				settings.font = chip.getAttribute('data-font');
				applyFonts();
				saveSettings();
			});
		});

		// 4. Spacing
		const spacingToggle = document.getElementById('coga-toggle-spacing');
		if (spacingToggle) {
			spacingToggle.checked = settings.spacing;
			spacingToggle.addEventListener('change', (e) => {
				settings.spacing = e.target.checked;
				applySpacing();
				saveSettings();
			});
		}

		// 5. Contrast
		const contrastToggle = document.getElementById('coga-toggle-contrast');
		if (contrastToggle) {
			contrastToggle.checked = settings.contrast;
			contrastToggle.addEventListener('change', (e) => {
				settings.contrast = e.target.checked;
				applyContrast();
				saveSettings();
			});
		}

		// 6. Request Accommodation Button
		const btnRequestAcc = document.getElementById('btn-open-accommodation-request');
		if (btnRequestAcc) {
			btnRequestAcc.addEventListener('click', () => {
				dockModal.style.display = 'none';
				if (window.StackEAD_App && window.StackEAD_App.openAccommodationModal) {
					window.StackEAD_App.openAccommodationModal();
				}
			});
		}
	}

	function applySettings() {
		applyRuler();
		applyFocusMode();
		applyFonts();
		applySpacing();
		applyContrast();
	}

	function applyRuler() {
		if (!rulerEl) return;
		rulerEl.style.display = settings.ruler ? 'block' : 'none';
	}

	function applyFocusMode() {
		if (settings.focusMode) {
			document.body.classList.add('coga-focus-mode');
			showExitFocusBtn();
		} else {
			document.body.classList.remove('coga-focus-mode');
			removeExitFocusBtn();
		}
	}

	function showExitFocusBtn() {
		let btn = document.getElementById('coga-exit-focus-btn');
		if (!btn) {
			btn = document.createElement('button');
			btn.id = 'coga-exit-focus-btn';
			btn.innerHTML = '✕ Sair do Modo Foco';
			btn.onclick = () => {
				settings.focusMode = false;
				const focusToggle = document.getElementById('coga-toggle-focus');
				if (focusToggle) focusToggle.checked = false;
				applyFocusMode();
				saveSettings();
			};
			document.body.appendChild(btn);
		}
	}

	function removeExitFocusBtn() {
		const btn = document.getElementById('coga-exit-focus-btn');
		if (btn) btn.remove();
	}

	function applyFonts() {
		document.body.classList.remove('coga-font-lexend', 'coga-font-opendyslexic');
		if (settings.font === 'lexend') {
			document.body.classList.add('coga-font-lexend');
		} else if (settings.font === 'opendyslexic') {
			document.body.classList.add('coga-font-opendyslexic');
		}
	}

	function applySpacing() {
		if (settings.spacing) {
			document.body.classList.add('coga-spacing-expanded');
		} else {
			document.body.classList.remove('coga-spacing-expanded');
		}
	}

	function applyContrast() {
		if (settings.contrast) {
			document.documentElement.setAttribute('data-coga-contrast', 'high');
		} else {
			document.documentElement.removeAttribute('data-coga-contrast');
		}
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	window.StackEAD_COGA = {
		settings: settings,
		applySettings: applySettings
	};
})();
