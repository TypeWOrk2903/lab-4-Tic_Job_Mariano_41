/**
 * WebMovies – Entry point
 * Inicializa tema, eventos e carregamento de dados.
 */
import ThemeManager from './theme.js';
import { loadSections, initSearch, toggleLanguage, loadByGenre } from './movies.js';

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

  /* 5. Chips de género na home (apenas quando logado) */
  const genreChipsHome = document.getElementById('genre-chips-home');
  if (genreChipsHome) {
    // Carrega grid de género com "Populares" por omissão
    loadByGenre('', 'Populares');

    genreChipsHome.addEventListener('click', e => {
      const chip = e.target.closest('.genre-chip-home');
      if (!chip) return;

      // Remove active de todos
      genreChipsHome.querySelectorAll('.genre-chip-home').forEach(c => {
        c.classList.remove('active');
        c.style.background = 'var(--color-bg)';
        c.style.color      = c.classList.contains('preferred') ? 'var(--color-amber)' : 'var(--color-text-muted)';
        c.style.boxShadow  = 'var(--neu-shadow-sm)';
      });

      // Ativa o clicado
      chip.classList.add('active');
      chip.style.background = 'var(--color-cyan)';
      chip.style.color      = '#fff';
      chip.style.boxShadow  = 'var(--glow-cyan)';

      const tmdbId = chip.dataset.tmdb;
      const label  = chip.textContent.trim().replace(/^[^\w]+/, ''); // remove ícone
      loadByGenre(tmdbId, label);
    });
  }
});
