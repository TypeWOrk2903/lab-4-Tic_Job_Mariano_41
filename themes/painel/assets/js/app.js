/**
 * WebMovies – Entry point
 * Inicializa tema, eventos e carregamento de dados.
 */
import ThemeManager from './theme.js';
import { loadSections, initSearch, toggleLanguage } from './movies.js';

document.addEventListener('DOMContentLoaded', () => {
  /* 1. Tema */
  ThemeManager.init();

  const themeBtn = document.querySelector('.wm-theme-btn');
  if (themeBtn) {
    themeBtn.addEventListener('click', () => ThemeManager.toggle());
  }

  /* 2. Idioma – conecta todos os botões [data-lang-btn] */
  document.querySelectorAll('[data-lang-btn]').forEach(btn => {
    btn.addEventListener('click', () => toggleLanguage());
  });

  /* 3. Busca */
  initSearch();

  /* 4. Dados TMDB */
  loadSections();
});
