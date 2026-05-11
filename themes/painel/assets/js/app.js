/**
 * WebMovies – Entry point
 * Inicializa tema, eventos e carregamento de dados.
 */
import ThemeManager from './theme.js';
import { loadSections, initSearch, toggleLanguage, loadByGenre, loadFavoritesGrid } from './movies.js';
import { applyI18n } from './translations.js';

document.addEventListener('DOMContentLoaded', () => {
  /* 0. i18n — aplica idioma guardado antes de qualquer render */
  applyI18n();
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

  /* 5. Favoritos — renderizados pelo PHP; loadFavoritesGrid apenas via AJAX quando necessário */

  /* 6. Chips de género na home (apenas quando logado) */
  const genreChipsHome = document.getElementById('genre-chips-home');
  if (genreChipsHome) {
    // Activa chip inicial: primeiro preferido (se existir) ou "Populares"
    const firstPreferred = genreChipsHome.querySelector('.genre-chip-home.preferred');
    const initialChip    = firstPreferred ?? genreChipsHome.querySelector('.genre-chip-home');

    function activateChip(chip) {
      genreChipsHome.querySelectorAll('.genre-chip-home').forEach(c => {
        c.classList.remove('active');
        c.style.background = 'var(--color-bg)';
        c.style.color      = c.classList.contains('preferred') ? 'var(--color-amber)' : 'var(--color-text-muted)';
        c.style.boxShadow  = 'var(--neu-shadow-sm)';
      });
      chip.classList.add('active');
      chip.style.background = 'var(--color-cyan)';
      chip.style.color      = '#fff';
      chip.style.boxShadow  = 'var(--glow-cyan)';
    }

    if (initialChip) {
      activateChip(initialChip);
      const tmdbId = initialChip.dataset.tmdb;
      const label  = initialChip.textContent.trim().replace(/^[^\w]+/, '');
      loadByGenre(tmdbId, label);
    }

    genreChipsHome.addEventListener('click', e => {
      const chip = e.target.closest('.genre-chip-home');
      if (!chip) return;

      activateChip(chip);

      const tmdbId = chip.dataset.tmdb;
      const label  = chip.textContent.trim().replace(/^[^\w]+/, ''); // remove ícone
      loadByGenre(tmdbId, label);
    });
  }
});
