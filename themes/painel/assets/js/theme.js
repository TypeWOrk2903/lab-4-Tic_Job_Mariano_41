/**
 * WebMovies – ThemeManager
 * Persiste e aplica dark-theme / light-theme no <body>.
 * Ícones via Font Awesome 6 (fa-sun / fa-moon).
 */
const ThemeManager = (() => {
  const STORAGE_KEY = 'wm-theme';
  const DARK  = 'dark-theme';
  const LIGHT = 'light-theme';

  function _resolve() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === DARK || stored === LIGHT) return stored;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? DARK : LIGHT;
  }

  function _apply(theme) {
    document.body.classList.remove(DARK, LIGHT);
    document.body.classList.add(theme);

    const btn = document.querySelector('.wm-theme-btn');
    if (btn) {
      // No dark: exibe sol (para trocar para light)
      // No light: exibe lua (para trocar para dark)
      btn.innerHTML = theme === DARK
        ? '<i class="fa-solid fa-sun"  aria-hidden="true"></i>'
        : '<i class="fa-solid fa-moon" aria-hidden="true"></i>';
    }
  }

  return {
    init()    { _apply(_resolve()); },
    toggle()  {
      const next = document.body.classList.contains(DARK) ? LIGHT : DARK;
      localStorage.setItem(STORAGE_KEY, next);
      _apply(next);
    },
    current() {
      return document.body.classList.contains(DARK) ? DARK : LIGHT;
    },
  };
})();

export default ThemeManager;

