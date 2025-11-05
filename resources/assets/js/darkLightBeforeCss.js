(() => {
  try {
    const KEY = 'app-theme';
    const mode = localStorage.getItem(KEY) || 'auto';

    // Apply body class as soon as DOM is ready (your original behavior)
    const apply = () => {
      const body = document.body;
      if (!body) return;

      body.classList.remove('theme-light', 'theme-dark', 'theme-auto');
      body.classList.add('theme-' + mode);

      // 🔽 also update the icon
      const icon = document.querySelector('#theme-switch i');
      if (icon) {
        icon.classList.remove('bi-brightness-high-fill', 'bi-moon-stars', 'bi-circle-half');
        icon.classList.add(
          mode === 'light' ? 'bi-brightness-high-fill'
            : mode === 'dark' ? 'bi-moon-stars'
              : 'bi-circle-half' // auto
        );
      }
    };

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', apply, { once: true });
    } else {
      apply();
    }
  } catch (e) { }
})();
