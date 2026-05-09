/**
 * WebMovies – YTS.mx API + DOM Renderer
 * API 100% gratuita, sem necessidade de chave de acesso.
 * Docs: https://yts.mx/api
 */

const API_BASE       = 'http://www.omdbapi.com/?i=tt3896198&apikey=d1e10648';
const FALLBACK_POSTER = 'assets/images/no-poster.svg';

/* ─── Fetch genérico ──────────────────────────── */
async function apiFetch(params = {}) {
  const url = new URL(`${API_BASE}/list_movies.json`);
  Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

  const res = await fetch(url.toString());
  if (!res.ok) throw new Error(`omdbapi ${res.status}: ${res.statusText}`);

  const json = await res.json();
  if (json.status !== 'ok') throw new Error(json.status_message ?? 'Erro na API YTS');

  return json.data.movies ?? [];
}

/* ─── Toast ───────────────────────────────────── */
function showToast(message, type = 'info') {
  const toast = document.getElementById('wm-toast');
  if (!toast) return;

  toast.textContent = message;
  toast.style.color = type === 'error' ? 'var(--color-danger, #ff4d6d)' : '';

  toast.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
  toast.classList.add('opacity-100', 'translate-y-0');

  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => {
    toast.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
    toast.classList.remove('opacity-100', 'translate-y-0');
    toast.style.color = '';
  }, 3400);
}

/* ─── Skeletons ───────────────────────────────── */
function renderSkeletons(container, count = 10) {
  container.innerHTML = Array.from({ length: count })
    .map(() => `<div class="wm-skeleton"><div class="wm-skeleton__inner"></div></div>`)
    .join('');
}

/* ─── Card builder ────────────────────────────── */
function buildCard(movie) {
  const poster = movie.medium_cover_image || FALLBACK_POSTER;
  const rating = movie.rating ? movie.rating.toFixed(1) : '—';
  const title  = movie.title  || 'Sem título';
  const year   = movie.year   || '';
  const genre  = movie.genres?.[0] ?? '';

  const article = document.createElement('article');
  article.className = 'wm-card';
  article.setAttribute('aria-label', title);
  article.dataset.movieId = movie.id;

  article.innerHTML = `
    <div class="wm-card__poster">
      <img src="${poster}" alt="${title}" loading="lazy" />
      <div class="wm-card__overlay"></div>
      <span class="wm-card__badge">
        <i class="fa-solid fa-star" aria-hidden="true"></i> ${rating}
      </span>
    </div>
    <div class="wm-card__body">
      <h3 class="wm-card__title">${title}</h3>
      <p class="wm-card__genres">${genre}${genre && year ? ' · ' : ''}${year}</p>
    </div>`;

  return article;
}

/* ─── Render list ─────────────────────────────── */
function renderMovies(container, movies) {
  if (!movies.length) {
    container.innerHTML = `
      <div class="wm-state">
        <i class="fa-regular fa-face-frown" aria-hidden="true"></i>
        <p>Nenhum filme encontrado.</p>
      </div>`;
    return;
  }
  container.innerHTML = '';
  movies.forEach(m => container.appendChild(buildCard(m)));
}

/* ─── Hero ────────────────────────────────────── */
function updateHero(movie) {
  if (!movie) return;
  const bg    = document.querySelector('.wm-hero__bg');
  const title = document.querySelector('.wm-hero__title');
  const syn   = document.querySelector('.wm-hero__synopsis');

  if (bg) { bg.src = movie.background_image_original || movie.background_image || ''; bg.alt = movie.title || ''; }
  if (title) title.textContent = movie.title || '';
  if (syn)   syn.textContent   = movie.summary || '';
}

/* ─── Busca com debounce ──────────────────────── */
export function initSearch() {
  const input = document.getElementById('wm-search');
  if (!input) return;

  let timer;
  input.addEventListener('input', () => {
    clearTimeout(timer);
    const q = input.value.trim();
    if (!q) { loadSections(); return; }

    timer = setTimeout(async () => {
      const grid = document.querySelector('[data-grid="popular"]');
      const recSection = document.querySelector('[data-grid="recommended"]')?.closest('section');
      if (grid) renderSkeletons(grid, 10);
      if (recSection) recSection.style.display = 'none';

      try {
        const movies = await apiFetch({ query_term: q, limit: 20 });
        if (grid) renderMovies(grid, movies);
      } catch (e) {
        showToast('Erro na busca: ' + e.message, 'error');
        if (grid) renderMovies(grid, []);
      }
    }, 420);
  });

  input.addEventListener('keydown', e => {
    if (e.key === 'Escape') { input.value = ''; loadSections(); }
  });
}

/* ─── Carrega seções ──────────────────────────── */
export async function loadSections() {
  const popularGrid     = document.querySelector('[data-grid="popular"]');
  const recommendedGrid = document.querySelector('[data-grid="recommended"]');
  const recSection      = recommendedGrid?.closest('section');

  if (recSection) recSection.style.display = '';
  if (popularGrid)     renderSkeletons(popularGrid, 10);
  if (recommendedGrid) renderSkeletons(recommendedGrid, 10);

  try {
    const [popular, trending] = await Promise.all([
      apiFetch({ sort_by: 'rating',         limit: 20, minimum_rating: 7 }),
      apiFetch({ sort_by: 'download_count', limit: 20 }),
    ]);

    if (popularGrid)     renderMovies(popularGrid,     popular);
    if (recommendedGrid) renderMovies(recommendedGrid, trending);

    updateHero(popular?.[0]);
  } catch (e) {
    console.error('[WebMovies]', e);
    showToast('Erro ao carregar filmes: ' + e.message, 'error');
    if (popularGrid)     renderMovies(popularGrid,     []);
    if (recommendedGrid) renderMovies(recommendedGrid, []);
  }
}


/* ─── Helpers de fetch ─────────────────────────── */
async function apiFetch(endpoint, params = {}) {
  const url = new URL(`${BASE_URL}${endpoint}`);
  url.searchParams.set('api_key', API_KEY);
  url.searchParams.set('language', LANG);
  Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

  const res = await fetch(url.toString());
  if (!res.ok) throw new Error(`TMDB ${res.status}: ${res.statusText}`);
  return res.json();
}

/* ─── Toast ────────────────────────────────────── */
function showToast(message, type = 'info') {
  let toast = document.getElementById('wm-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'wm-toast';
    toast.className = 'wm-toast';
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.classList.toggle('wm-toast--error', type === 'error');
  toast.classList.add('wm-toast--visible');

  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => toast.classList.remove('wm-toast--visible'), 3400);
}

/* ─── Skeletons ─────────────────────────────────── */
function renderSkeletons(container, count = 6) {
  container.innerHTML = Array.from({ length: count })
    .map(() => `<div class="wm-skeleton"><div class="wm-skeleton__inner"></div></div>`)
    .join('');
}

/* ─── Card builder ──────────────────────────────── */
function buildCard(movie) {
  const poster = movie.poster_path
    ? `${IMG_BASE}${movie.poster_path}`
    : 'themes/assets/images/no-poster.svg';

  const rating = movie.vote_average ? movie.vote_average.toFixed(1) : '—';
  const title  = movie.title || movie.name || 'Sem título';
  const year   = (movie.release_date || movie.first_air_date || '').slice(0, 4);

  const article = document.createElement('article');
  article.className = 'wm-card';
  article.setAttribute('aria-label', title);
  article.dataset.movieId = movie.id;

  article.innerHTML = `
    <div class="wm-card__poster">
      <img src="${poster}" alt="${title}" loading="lazy" />
      <div class="wm-card__overlay"></div>
      <span class="wm-card__badge">⭐ ${rating}</span>
    </div>
    <div class="wm-card__body">
      <h3 class="wm-card__title">${title}</h3>
      <p class="wm-card__genres">${year}</p>
    </div>`;

  return article;
}

/* ─── Render list ───────────────────────────────── */
function renderMovies(container, movies) {
  if (!movies.length) {
    container.innerHTML = `
      <div class="wm-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.166 1.318
                   M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75
                   S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015
                   h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75
                   .168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z"/>
        </svg>
        <p>Nenhum filme encontrado.</p>
      </div>`;
    return;
  }
  container.innerHTML = '';
  movies.forEach(m => container.appendChild(buildCard(m)));
}

/* ─── Hero builder ──────────────────────────────── */
function updateHero(movie) {
  const hero = document.querySelector('.wm-hero');
  if (!hero || !movie) return;

  const bg    = hero.querySelector('.wm-hero__bg');
  const title = hero.querySelector('.wm-hero__title');
  const syn   = hero.querySelector('.wm-hero__synopsis');

  if (bg) {
    const imgUrl = movie.backdrop_path
      ? `https://image.tmdb.org/t/p/w1280${movie.backdrop_path}`
      : '';
    bg.src = imgUrl;
    bg.alt = movie.title || '';
  }
  if (title) title.textContent = movie.title || movie.name || '';
  if (syn)   syn.textContent   = movie.overview || '';
}

/* ─── Search ────────────────────────────────────── */
let searchTimer = null;

function initSearch() {
  const input    = document.querySelector('.wm-search');
  const popular  = document.querySelector('[data-grid="popular"]');
  const recommended = document.querySelector('[data-grid="recommended"]');
  if (!input) return;

  input.addEventListener('input', () => {
    clearTimeout(searchTimer);
    const query = input.value.trim();
    if (!query) {
      loadSections();
      return;
    }
    searchTimer = setTimeout(async () => {
      renderSkeletons(popular, 6);
      try {
        const data = await apiFetch('/search/movie', { query });
        renderMovies(popular, data.results || []);
        if (recommended) recommended.closest('.wm-section').style.display = 'none';
      } catch (err) {
        showToast('Erro na busca. Tente novamente.', 'error');
      }
    }, 420);
  });

  input.addEventListener('keydown', e => {
    if (e.key === 'Escape') { input.value = ''; loadSections(); }
  });
}

/* ─── Carregamento das seções ───────────────────── */
async function loadSections() {
  const popularGrid      = document.querySelector('[data-grid="popular"]');
  const recommendedGrid  = document.querySelector('[data-grid="recommended"]');
  const recSection       = recommendedGrid?.closest('.wm-section');

  if (recSection) recSection.style.display = '';

  if (popularGrid)     renderSkeletons(popularGrid, 6);
  if (recommendedGrid) renderSkeletons(recommendedGrid, 6);

  try {
    const [popular, trending] = await Promise.all([
      apiFetch('/movie/popular'),
      apiFetch('/trending/movie/week'),
    ]);

    const popMovies   = popular.results   || [];
    const trendMovies = trending.results  || [];

    if (popularGrid)     renderMovies(popularGrid, popMovies.slice(0, 8));
    if (recommendedGrid) renderMovies(recommendedGrid, trendMovies.slice(0, 8));

    updateHero(trendMovies[0] || popMovies[0]);
  } catch (err) {
    console.error(err);
    if (popularGrid)     renderMovies(popularGrid, []);
    if (recommendedGrid) renderMovies(recommendedGrid, []);
    showToast('Não foi possível carregar os filmes. Verifique a chave TMDB.', 'error');
  }
}

export { loadSections, initSearch, showToast };
