// public/assets/js/main.js
(() => {
  // ---------- utilities ----------
  const ready = (fn) =>
    document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn, { once: true });

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => root.querySelectorAll(sel);

  const toggleSidebar = () => {
    const sidebar = $('.sidebar');
    const main = $('.main');
    const backdrop = $('.backdrop');
    if (!sidebar || !main || !backdrop) return;

    sidebar.classList.toggle('tgl');
    backdrop.classList.toggle('off-canvas');
    main.classList.toggle('tgl');
  };

  // ---------- THEME ----------
  const THEME_KEY = 'app-theme'; // 'light' | 'dark' | 'auto'
  const THEME_CYCLE = ['light', 'dark', 'auto'];

  const updateThemeButton = () => {
    const btn = $('#theme-switch');
    if (!btn) return;
    const icon = btn.querySelector('i');
    if (!icon) return;

    const mode = localStorage.getItem(THEME_KEY) || 'auto';
    icon.classList.remove('bi-brightness-high-fill', 'bi-moon-stars', 'bi-circle-half');

    if (mode === 'light') {
      icon.classList.add('bi-brightness-high-fill');
      btn.title = btn.ariaLabel = 'Light mode (click to switch)';
    } else if (mode === 'dark') {
      icon.classList.add('bi-moon-stars');
      btn.title = btn.ariaLabel = 'Dark mode (click to switch)';
    } else {
      icon.classList.add('bi-circle-half');
      btn.title = btn.ariaLabel = 'Auto (follows system)';
    }
  };

  const applyTheme = (mode) => {
    const body = document.body;
    body.classList.remove('theme-light', 'theme-dark', 'theme-auto');
    body.classList.add(`theme-${mode}`);
    localStorage.setItem(THEME_KEY, mode);
    updateThemeButton();
  };

  const initTheme = () => {
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    // initial
    applyTheme(localStorage.getItem(THEME_KEY) || 'auto');

    // react to system changes (only matters in 'auto')
    mq.addEventListener?.('change', () => {
      if ((localStorage.getItem(THEME_KEY) || 'auto') === 'auto') applyTheme('auto');
    });
  };

  // ---------- HEADER SCROLL ----------
  let headerScrollBound = false;
  const onWindowScroll = () => {
    const header = $('.content-wrapper > header');
    if (!header) return;
    const y = window.pageYOffset || document.documentElement.scrollTop || 0;
    header.classList.toggle('scrolled', y > 10);
  };

  const bindHeaderScroll = () => {
    if (!headerScrollBound) {
      window.addEventListener('scroll', onWindowScroll, { passive: true });
      headerScrollBound = true;
    }
    // set initial state for current page
    onWindowScroll();
  };

  // ---------- EVENT DELEGATION (works across Inertia swaps) ----------
  const onClick = (e) => {
    const t = e.target;

    // 1) sidebar links: active + auto-close on small screens
    const item = t.closest('.sidebar .list-group .list-group-item:not(.accordion-item)');
    if (item) {
      $$('.sidebar .list-group .list-group-item:not(.accordion-item)').forEach((el) =>
        el.classList.remove('active'),
      );
      item.classList.add('active');
      if (window.innerWidth < 1200) toggleSidebar();
      return;
    }

    // 2) off-canvas triggers (#off-canvas or .sidebar-toggle)
    if (t.closest('#off-canvas, .sidebar-toggle')) {
      toggleSidebar();
      return;
    }

    // 3) backdrop click closes
    if (t.closest('.backdrop')) {
      toggleSidebar();
      return;
    }

    // 4) password toggle
    const passBtn = t.closest('[data-toggle="togglePassword"]');
    if (passBtn) {
      // Prefer nearest input-group; fallback to #password
      const group = passBtn.closest('.input-group') || document;
      let input =
        group.querySelector('input[type="password"], input[type="text"]') ||
        $('#password');
      if (input) {
        const to = input.type === 'password' ? 'text' : 'password';
        input.type = to;
        const icon = passBtn.querySelector('i');
        if (icon) {
          icon.classList.toggle('bi-eye');
          icon.classList.toggle('bi-eye-slash');
        }
      }
      return;
    }

    // 5) theme switch
    const themeBtn = t.closest('#theme-switch');
    if (themeBtn) {
      const mode = localStorage.getItem(THEME_KEY) || 'auto';
      const idx = THEME_CYCLE.indexOf(mode);
      const next = THEME_CYCLE[(idx + 1) % THEME_CYCLE.length];
      applyTheme(next);
      return;
    }
  };

  // ---------- init per Inertia page ----------
  const initPerPage = () => {
    // ensure theme button icon reflects current mode on this page
    updateThemeButton();

    // header scroll state for this page
    bindHeaderScroll();
  };

  // ---------- bootstrap everything ----------
  ready(() => {
    // set up static stuff
    initTheme();

    // global delegated click (single listener)
    if (!window.__MAIN_JS_CLICK_BOUND__) {
      document.addEventListener('click', onClick, false);
      window.__MAIN_JS_CLICK_BOUND__ = true;
    }

    // run for initial page
    initPerPage();
  });

  // re-run after every Inertia DOM swap
  window.addEventListener('inertia:finish', initPerPage);
})();
