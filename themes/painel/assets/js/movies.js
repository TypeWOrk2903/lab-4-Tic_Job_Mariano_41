/**
 * WebMovies – TMDB API Integration
 * Design inspirado em AdoroCinema, Ingresso.com e Rotten Tomatoes.
 */

import { t, applyI18n } from './translations.js';

const API_KEY       = 'b0e9f75142eb4a69493d8fba03cf29f5';
const BASE_URL      = 'https://api.themoviedb.org/3';
const IMG_BASE      = 'https://image.tmdb.org/t/p/w500';
const BACKDROP_BASE = 'https://image.tmdb.org/t/p/original';
const CONF_URL_BASE = `${location.protocol}//${location.host}/WebMovies`;
const BLOCKED_GENRES = [27, 53];

// Idioma ativo — persiste no localStorage
let LANG = localStorage.getItem('wm-lang') || 'pt-BR';

// Cache de gêneros: { id → nome }
let GENRE_MAP = {};

// IDs favoritos em memória (carregado 1x se logado)
let FAV_IDS = null; // null = não carregado ainda

/* ─── API Interna (favoritos / avaliações) ────── */

/** Carrega IDs favoritos do utilizador (1x por sessão de página). */
async function loadFavIds() {
    if (FAV_IDS !== null) return;
    try {
        const res = await fetch(`${CONF_URL_BASE}/api/favoritos`);
        if (!res.ok) { FAV_IDS = new Set(); return; }
        const data = await res.json();
        FAV_IDS = new Set(data.map(f => f.tmdb_id));
    } catch { FAV_IDS = new Set(); }
}

/** Toggle favorito no servidor; retorna true se agora está favoritado. */
export async function toggleFavorite(movie) {
    try {
        const res = await fetch(`${CONF_URL_BASE}/api/favorito`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                tmdb_id:     movie.id,
                title:       movie.title || movie.name || '',
                poster_path: movie.poster_path || '',
            }),
        });
        if (res.status === 401) { window.location.href = `${CONF_URL_BASE}/login`; return false; }
        const data = await res.json();
        if (FAV_IDS) {
            data.favorited ? FAV_IDS.add(movie.id) : FAV_IDS.delete(movie.id);
        }
        return data.favorited;
    } catch { return false; }
}

/** Envia avaliação (1-10) para o servidor. */
export async function submitRating(tmdbId, rating) {
    try {
        const res = await fetch(`${CONF_URL_BASE}/api/avaliar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tmdb_id: tmdbId, rating }),
        });
        if (res.status === 401) { window.location.href = `${CONF_URL_BASE}/login`; return null; }
        const data = await res.json();
        return data.rating ?? null;
    } catch { return null; }
}

/** Busca avaliação do utilizador para um filme. */
export async function fetchRating(tmdbId) {
    try {
        const res = await fetch(`${CONF_URL_BASE}/api/avaliacao?tmdb_id=${tmdbId}`);
        const data = await res.json();
        return data.rating ?? null;
    } catch { return null; }
}

/** Exibe toast de feedback. */
function showToast(msg, color = 'var(--color-cyan)') {
    const t = document.getElementById('wm-toast');
    if (!t) return;
    t.textContent = msg;
    t.style.background = color;
    t.style.color = '#fff';
    t.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
    t.classList.add('opacity-100', 'translate-y-0');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => {
        t.classList.remove('opacity-100', 'translate-y-0');
        t.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
    }, 2500);
}

/* ─── Toggle de Idioma ─────────────────────────── */
export async function toggleLanguage() {
    LANG      = LANG === 'pt-BR' ? 'en-US' : 'pt-BR';
    GENRE_MAP = {};
    FAV_IDS   = null; // força reload para obter títulos no novo idioma
    localStorage.setItem('wm-lang', LANG);
    applyI18n();
    _updateLangBtn();

    // Recarrega grid de género com chip ativo (labels já traduzidos)
    const activeChip = document.querySelector('.genre-chip-home.active');
    if (activeChip && document.querySelector('[data-grid="genre"]')) {
        const tmdbId = activeChip.dataset.tmdb;
        const label  = activeChip.textContent.trim().replace(/^[^\w\u00C0-\u024F]+/, '');
        loadByGenre(tmdbId, label);
    }

    await Promise.all([
        loadSections(),
        document.querySelector('[data-grid="favorites"]') ? loadFavoritesGrid() : Promise.resolve(),
    ]);
}

function _updateLangBtn() {
    document.querySelectorAll('[data-lang-btn]').forEach(btn => {
        const icon = btn.querySelector('i');
        const span = btn.querySelector('span');
        if (span) span.textContent = LANG === 'pt-BR' ? 'EN' : 'PT';
        if (icon) icon.className = 'fa-solid fa-language';
    });
}

/* ─── Carrega e cacheia gêneros da TMDB ─────────── */
async function loadGenreMap() {
    if (Object.keys(GENRE_MAP).length) return;
    try {
        const url = new URL(`${BASE_URL}/genre/movie/list`);
        url.searchParams.set('api_key', API_KEY);
        url.searchParams.set('language', LANG);
        const res = await fetch(url.toString());
        if (!res.ok) return;
        const data = await res.json();
        data.genres.forEach(g => { GENRE_MAP[g.id] = g.name; });
    } catch { /* silencioso */ }
}

/* ─── Score estilo Rotten Tomatoes ──────────────── */
function scoreInfo(voteAverage) {
    const pct = Math.round((voteAverage ?? 0) * 10);
    let cls = 'wm-score--rotten';
    if (pct >= 75) cls = 'wm-score--fresh';
    else if (pct >= 60) cls = 'wm-score--mixed';
    return { pct, cls };
}

/* ─── Fetch Helper para TMDB ────────────────────── */
async function apiFetch(endpoint, params = {}) {
    const url = new URL(`${BASE_URL}${endpoint}`);
    url.searchParams.set('api_key', API_KEY);
    url.searchParams.set('language', LANG);
    url.searchParams.set('region', 'BR');
    url.searchParams.set('include_adult', 'false');

    Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

    const res = await fetch(url.toString());
    if (!res.ok) throw new Error(`HTTP ${res.status} – ${endpoint}`);

    const data = await res.json();
    if (data.results) return filterSafeContent(data.results);
    return data;
}

/* ─── Filtro de Segurança Manual ───────────────── */
function filterSafeContent(movies) {
    return movies.filter(movie => {
        if (movie.adult) return false;

        const genres = movie.genre_ids ?? [];
        if (genres.some(id => BLOCKED_GENRES.includes(id))) return false;

        if (!movie.poster_path || !movie.overview) return false;

        return true;
    });
}

/* ─── Skeletons (Placeholder de Carregamento) ─── */
function renderSkeletons(grid, count = 12) {
    if (!grid) return;
    grid.innerHTML = '';
    for (let i = 0; i < count; i++) {
        const el = document.createElement('div');
        el.className = 'wm-skeleton';
        el.innerHTML = '<div class="wm-skeleton__inner"></div>';
        grid.appendChild(el);
    }
}

/* ─── Renderiza Lista de Filmes no Grid ─────────── */
function renderMovies(grid, movies) {
    if (!grid) return;
    grid.innerHTML = '';
    if (!movies?.length) {
        const msg = document.createElement('p');
        msg.className = 'col-span-full text-center';
        msg.style.color = 'var(--color-text-muted)';
        msg.textContent = t('movies.empty');
        grid.appendChild(msg);
        return;
    }
    movies.forEach(movie => grid.appendChild(buildCard(movie)));
}

/* ─── Componente: Card (estilo AdoroCinema / RT) ── */
function buildCard(movie) {
    const title  = movie.title || movie.name || 'Sem título';
    const year   = (movie.release_date || '').slice(0, 4);
    const { pct, cls } = scoreInfo(movie.vote_average);
    const isFav  = FAV_IDS?.has(movie.id) ?? false;

    const genreNames = (movie.genre_ids ?? [])
        .slice(0, 2)
        .map(id => GENRE_MAP[id])
        .filter(Boolean);

    const article = document.createElement('article');
    article.className = 'wm-card';
    article.style.cursor = 'pointer';
    article.setAttribute('role', 'button');
    article.setAttribute('tabindex', '0');
    article.setAttribute('aria-label', title);

    article.innerHTML = `
        <div class="wm-card__poster" style="position:relative">
            <img loading="lazy" />
            <div class="wm-card__overlay"></div>
            <div class="wm-card__score-wrap">
                <span class="wm-score ${cls}">${pct}%</span>
            </div>
            <!-- Botão favorito -->
            <button class="wm-fav-btn" data-fav="${isFav ? '1' : '0'}"
                    title="${isFav ? t('card.fav_remove') : t('card.fav_add_title')}"
                    style="position:absolute;top:6px;right:6px;width:28px;height:28px;border-radius:50%;
                           border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;
                           font-size:0.75rem;transition:all .2s;z-index:10;
                           background:${isFav ? '#f43f5e' : 'rgba(0,0,0,.5)'};
                           color:${isFav ? '#fff' : '#ccc'};box-shadow:0 1px 6px rgba(0,0,0,.4)">
              <i class="fa-${isFav ? 'solid' : 'regular'} fa-heart"></i>
            </button>
        </div>
        <div class="wm-card__body">
            <h3 class="wm-card__title"></h3>
            <div class="wm-card__meta">
                <span class="wm-card__year"></span>
                ${genreNames.map(() => `<span class="wm-tag"></span>`).join('')}
            </div>
            <!-- Estrelas de avaliação -->
            <div class="wm-stars" style="display:flex;gap:2px;margin-top:4px;font-size:0.65rem;" data-tmdb="${movie.id}">
                ${[1,2,3,4,5].map(i => `<i class="fa-regular fa-star wm-star" data-val="${i*2}" style="cursor:pointer;color:#f59e0b;transition:transform .1s"></i>`).join('')}
                <span class="wm-user-score" style="font-size:0.6rem;color:var(--color-text-muted);margin-left:3px;line-height:1.4"></span>
            </div>
        </div>`;

    // Imagem e textos
    const img = article.querySelector('img');
    img.src = movie.poster_path
        ? `${IMG_BASE}${movie.poster_path}`
        : `${CONF_URL_BASE}/themes/painel/assets/images/no-poster.png`;
    img.alt = title;
    img.onerror = () => { img.src = `${CONF_URL_BASE}/themes/painel/assets/images/no-poster.png`; img.onerror = null; };
    article.querySelector('.wm-card__title').textContent = title;
    article.querySelector('.wm-card__year').textContent  = year;
    article.querySelectorAll('.wm-tag').forEach((el, i) => { el.textContent = genreNames[i]; });

    // ── Navegar para detalhe
    const goToDetail = () => {
        const salt   = 'webmovies_ipil_2026';
        const masked = btoa(`${movie.id}|${salt}`)
            .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        window.location.href = `${CONF_URL_BASE}/filme?id=${masked}`;
    };
    article.addEventListener('click', goToDetail);
    article.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); goToDetail(); } });

    // ── Botão favorito (stopPropagation para não abrir o filme)
    const favBtn = article.querySelector('.wm-fav-btn');
    if (favBtn) {
        favBtn.addEventListener('click', async e => {
            e.stopPropagation();
            const now = await toggleFavorite(movie);
            favBtn.dataset.fav = now ? '1' : '0';
            favBtn.title = now ? t('card.fav_remove') : t('card.fav_add_title');
            favBtn.style.background = now ? '#f43f5e' : 'rgba(0,0,0,.5)';
            favBtn.style.color      = now ? '#fff' : '#ccc';
            favBtn.innerHTML = `<i class="fa-${now ? 'solid' : 'regular'} fa-heart"></i>`;
            showToast(now ? t('toast.fav_add') : t('toast.fav_remove'),
                      now ? '#f43f5e' : 'var(--color-panel)');

            // Redireciona para home após breve delay (favoritos são renderizados pelo PHP)
            setTimeout(() => { window.location.href = `${CONF_URL_BASE}/home`; }, 800);
        });
    }

    // ── Estrelas de avaliação
    const starsWrap = article.querySelector('.wm-stars');
    const stars     = starsWrap?.querySelectorAll('.wm-star');
    const scoreSpan = starsWrap?.querySelector('.wm-user-score');

    function paintStars(val) {
        stars?.forEach(s => {
            const v = parseInt(s.dataset.val);
            s.className = v <= val ? 'fa-solid fa-star wm-star' : 'fa-regular fa-star wm-star';
            s.style.cursor = 'pointer';
            s.style.color  = '#f59e0b';
        });
        if (scoreSpan) scoreSpan.textContent = val ? `${val}/10` : '';
    }

    if (stars?.length) {
        stars.forEach(s => {
            s.addEventListener('click', async e => {
                e.stopPropagation();
                const val  = parseInt(s.dataset.val);
                const saved = await submitRating(movie.id, val);
                if (saved !== null) {
                    paintStars(saved);
                    starsWrap.dataset.saved = saved; // persiste para mouseleave
                    showToast(`${t('toast.rating')}: ${saved}/10`, '#f59e0b');
                }
            });
            s.addEventListener('mouseenter', e => {
                e.stopPropagation();
                paintStars(parseInt(s.dataset.val));
            });
        });
        starsWrap.addEventListener('mouseleave', () => {
            // Restaura valor guardado
            const saved = starsWrap.dataset.saved ? parseInt(starsWrap.dataset.saved) : 0;
            paintStars(saved);
        });
    }

    return article;
}
/* ─── Hero com Navegação (estilo Ingresso.com) ───── */
let heroMovies = [];
let heroIndex  = 0;
let heroTimer  = null;

function applyHero(movie) {
    if (!movie) return;
    const { pct, cls } = scoreInfo(movie.vote_average);

    const bg    = document.querySelector('.wm-hero__bg');
    const title = document.querySelector('.wm-hero__title');
    const syn   = document.querySelector('.wm-hero__synopsis');
    const score = document.querySelector('[data-hero="score"]');
    const tags  = document.querySelector('[data-hero="tags"]');
    const year  = document.querySelector('[data-hero="year"]');
    const votes = document.querySelector('[data-hero="votes"]');

    const salt   = 'webmovies_ipil_2026';
    const masked = btoa(`${movie.id}|${salt}`)
        .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    const movieUrl = `${CONF_URL_BASE}/filme?id=${masked}`;

    if (bg) {
        bg.style.opacity = '0';
        bg.src = `${BACKDROP_BASE}${movie.backdrop_path}`;
        bg.onload = () => { bg.style.opacity = '.4'; };
        bg.alt = movie.title || '';
        bg.style.cursor = 'pointer';
        bg.onclick = () => { window.location.href = movieUrl; };
    }

    // Botão "VER DETALHES"
    const detailBtn = document.getElementById('hero-detail-btn');
    if (detailBtn) detailBtn.href = movieUrl;

    // Botão "FAVORITAR" (só existe quando logado)
    const heroFavBtn = document.getElementById('hero-fav-btn');
    if (heroFavBtn) {
        heroFavBtn.onclick = async () => {
            const now = await toggleFavorite(movie);
            showToast(now ? t('toast.fav_add') : t('toast.fav_remove'),
                      now ? '#f43f5e' : 'var(--color-panel)');
            setTimeout(() => { window.location.href = `${CONF_URL_BASE}/home`; }, 800);
        };
    }

    if (title) title.textContent = movie.title || movie.name || '';
    if (syn)   syn.textContent   = movie.overview || '';
    if (year)  year.textContent  = (movie.release_date || '').slice(0, 4);
    if (votes) votes.textContent = movie.vote_count
        ? `${movie.vote_count.toLocaleString(LANG)} ${t('hero.votes')}`
        : '';

    if (score) {
        score.innerHTML = '';
        const badge = document.createElement('span');
        badge.className = `wm-score wm-score--lg ${cls}`;
        badge.textContent = `${pct}%`;
        score.appendChild(badge);
    }

    if (tags) {
        tags.innerHTML = '';
        (movie.genre_ids ?? [])
            .slice(0, 3)
            .map(id => GENRE_MAP[id])
            .filter(Boolean)
            .forEach(g => {
                const span = document.createElement('span');
                span.className = 'wm-tag wm-tag--hero';
                span.textContent = g;
                tags.appendChild(span);
            });
    }
}

function heroNav(dir) {
    if (!heroMovies.length) return;
    heroIndex = (heroIndex + dir + heroMovies.length) % heroMovies.length;
    applyHero(heroMovies[heroIndex]);
    resetHeroTimer();
}

function resetHeroTimer() {
    clearInterval(heroTimer);
    heroTimer = setInterval(() => heroNav(1), 8000);
}

function initHeroNav() {
    const prev = document.querySelector('.wm-hero__nav--prev');
    const next = document.querySelector('.wm-hero__nav--next');
    if (prev) prev.addEventListener('click', () => heroNav(-1));
    if (next) next.addEventListener('click', () => heroNav(1));
}

async function loadHero() {
    try {
        heroMovies = await apiFetch('/trending/movie/day');
        heroIndex  = 0;
        if (!heroMovies.length) return;
        applyHero(heroMovies[0]);
        initHeroNav();
        resetHeroTimer();
    } catch (err) {
        console.error('[Hero Error]:', err.message);
    }
}

/* ─── Busca com Debounce ────────────────────────── */
export function initSearch() {
    const input = document.getElementById('wm-search');
    if (!input) return;

    // Para utilizadores logados usa o grid de género (mais visível no topo).
    // Para visitantes usa o grid popular.
    const gridTarget  = document.querySelector('[data-grid="genre"]')
                     ?? document.querySelector('[data-grid="popular"]');
    const genreTitle  = document.getElementById('genre-title');
    const genreSection = document.querySelector('[aria-labelledby="section-genre-grid"]');
    const chipsSection = document.querySelector('[aria-labelledby="section-genres"]');
    let debounceTimer;
    let isSearching = false;

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            const query = input.value.trim();

            if (!query) {
                // Sem pesquisa: restaura estado anterior
                if (isSearching) {
                    isSearching = false;
                    if (chipsSection)  chipsSection.style.display  = '';
                    if (genreTitle)    genreTitle.textContent = t('section.popular_chip').toUpperCase();
                    await loadSection(gridTarget, '/discover/movie', {
                        sort_by: 'popularity.desc',
                        'certification_country': 'BR',
                        'certification.lte': 'L',
                        page: 1,
                    });
                }
                return;
            }

            // Durante a pesquisa: oculta chips de género
            isSearching = true;
            if (chipsSection) chipsSection.style.display = 'none';
            if (genreTitle)   genreTitle.textContent = `${t('search.results')} "${query.toUpperCase()}"`;

            renderSkeletons(gridTarget, 12);
            // Scroll suave para o grid de resultados
            genreSection?.scrollIntoView({ behavior: 'smooth', block: 'start' });

            try {
                const results = await apiFetch('/search/movie', { query });
                renderMovies(gridTarget, results.length ? results : []);
                if (!results.length && genreTitle) {
                    genreTitle.textContent = `${t('search.no_results')} "${query.toUpperCase()}"`;
                }
            } catch (err) {
                console.error('[Search Error]:', err.message);
                renderMovies(gridTarget, []);
            }
        }, 400);
    });
}

/* ─── Helper: carrega uma seção ─────────────────── */
async function loadSection(grid, endpoint, params = {}) {
    renderSkeletons(grid, 12);
    try {
        const data = await apiFetch(endpoint, params);
        renderMovies(grid, data);
    } catch (err) {
        console.error(`[Section Error] ${endpoint}:`, err.message);
        renderMovies(grid, []);
    }
}

/* ─── Grid de Favoritos ──────────────────────────── */
export async function loadFavoritesGrid() {
    const grid = document.querySelector('[data-grid="favorites"]');
    if (!grid) return;

    try {
        // Garante que GENRE_MAP está carregado antes de construir cards
        await loadGenreMap();

        const res = await fetch(`${CONF_URL_BASE}/api/favoritos`);
        if (!res.ok) return;
        const favs = await res.json();
        FAV_IDS = new Set(favs.map(f => f.tmdb_id));

        grid.innerHTML = '';
        if (!favs.length) {
            grid.innerHTML = `<p class="col-span-full text-sm py-6 text-center" style="color:var(--color-text-muted)">
                <i class="fa-regular fa-heart mr-1"></i> ${t('favorites.empty')}
            </p>`;
            return;
        }

        // Busca dados completos da TMDB para cada favorito (em paralelo, máx 6)
        const batch = favs.slice(0, 24);
        const results = await Promise.allSettled(batch.map(f =>
            apiFetch(`/movie/${f.tmdb_id}`, { append_to_response: 'genres' })
        ));

        results.forEach((r, i) => {
            if (r.status !== 'fulfilled') return;
            const movie = r.value;
            // genre_ids não vem em /movie/{id}, usa genres array
            if (!movie.genre_ids && movie.genres) {
                movie.genre_ids = movie.genres.map(g => g.id);
            }
            grid.appendChild(buildCard(movie));
        });
    } catch (err) {
        console.error('[Favorites Error]:', err.message);
    }
}

/* ─── Inicialização: Todas as Seções ────────────── */
export async function loadSections() {
    _updateLangBtn();
    const grids = {
        popular:    document.querySelector('[data-grid="popular"]'),
        trending:   document.querySelector('[data-grid="recommended"]'),
        nowPlaying: document.querySelector('[data-grid="now-playing"]'),
        topRated:   document.querySelector('[data-grid="top-rated"]'),
        upcoming:   document.querySelector('[data-grid="upcoming"]'),
    };

    Object.values(grids).forEach(g => renderSkeletons(g, 12));

    // Carrega gêneros + favIds em paralelo antes de renderizar cards
    await Promise.all([loadGenreMap(), loadFavIds()]);

    await Promise.all([
        loadSection(grids.popular,    '/discover/movie', {
            sort_by: 'popularity.desc',
            'certification_country': 'BR',
            'certification.lte': 'L',
            page: 1,
        }),
        loadSection(grids.trending,   '/trending/movie/week'),
        loadSection(grids.nowPlaying, '/movie/now_playing', { page: 1 }),
        loadSection(grids.topRated,   '/movie/top_rated',   { page: 1 }),
        loadSection(grids.upcoming,   '/movie/upcoming',    { page: 1 }),
        loadHero(),
    ]);
}

/**
 * Carrega filmes de um género específico no grid [data-grid="genre"].
 * @param {number|string} tmdbGenreId  ID do género na TMDB (vazio = populares)
 * @param {string} genreLabel  Nome do género para o título da secção
 */
export async function loadByGenre(tmdbGenreId, genreLabel = 'POPULARES') {
    await Promise.all([loadGenreMap(), loadFavIds()]);

    const grid  = document.querySelector('[data-grid="genre"]');
    const title = document.getElementById('genre-title');
    if (!grid) return;

    if (title) title.textContent = genreLabel.toUpperCase();

    if (!tmdbGenreId) {
        // Sem filtro → populares
        await loadSection(grid, '/discover/movie', {
            sort_by: 'popularity.desc',
            'certification_country': 'BR',
            'certification.lte': 'L',
            page: 1,
        });
        return;
    }

    await loadSection(grid, '/discover/movie', {
        with_genres:   tmdbGenreId,
        sort_by:       'popularity.desc',
        'certification_country': 'BR',
        'certification.lte': 'L',
        page: 1,
    });
}