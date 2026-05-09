/**
 * WebMovies – Entry point
 * Inicializa tema, eventos e carregamento de dados.
 */
import ThemeManager          from './theme.js';
import { loadSections, initSearch } from './movies.js';

document.addEventListener('DOMContentLoaded', () => {
  /* 1. Tema */
  ThemeManager.init();

  const themeBtn = document.querySelector('.wm-theme-btn');
  if (themeBtn) {
    themeBtn.addEventListener('click', () => ThemeManager.toggle());
  }

  /* 2. Busca */
  initSearch();

  /* 3. Dados TMDB */
  loadSections();
});
