/**
 * WebMovies – TMDB API Integration
 * Design inspirado em AdoroCinema, Ingresso.com e Rotten Tomatoes.
 */

const API_KEY       = 'b0e9f75142eb4a69493d8fba03cf29f5';
const BASE_URL      = 'https://api.themoviedb.org/3';
const IMG_BASE      = 'https://image.tmdb.org/t/p/w500';
const BACKDROP_BASE = 'https://image.tmdb.org/t/p/original';
const CONF_URL_BASE = `${location.protocol}//${location.host}/WebMovies`;
// IDs de Gêneros bloqueados (27 = Horror, 53 = Thriller)
const BLOCKED_GENRES = [27, 53];

// Idioma ativo — persiste no localStorage
let LANG = localStorage.getItem('wm-lang') || 'pt-BR';

// Cache de gêneros: { id → nome } (inválido ao trocar idioma)
let GENRE_MAP = {};

/* ─── Toggle de Idioma ─────────────────────────── */
export async function toggleLanguage() {
    LANG      = LANG === 'pt-BR' ? 'en-US' : 'pt-BR';
    GENRE_MAP = {};                         // invalida cache de gêneros
    localStorage.setItem('wm-lang', LANG);
    _updateLangBtn();
    await loadSections();
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
        msg.textContent = 'Nenhum filme encontrado.';
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
        <div class="wm-card__poster">
            <img loading="lazy" />
            <div class="wm-card__overlay"></div>
            <div class="wm-card__score-wrap">
                <span class="wm-score ${cls}">${pct}%</span>
            </div>
        </div>
        <div class="wm-card__body">
            <h3 class="wm-card__title"></h3>
            <div class="wm-card__meta">
                <span class="wm-card__year"></span>
                ${genreNames.map(() => `<span class="wm-tag"></span>`).join('')}
            </div>
        </div>`;

    // Configuração da Imagem e Textos
    const img = article.querySelector('img');
    img.src = movie.poster_path
        ? `${IMG_BASE}${movie.poster_path}`
        : `${CONF_URL_BASE}/themes/painel/assets/images/no-poster.png`;
    img.alt = title;
    img.onerror = () => { img.src = `${CONF_URL_BASE}/themes/painel/assets/images/no-poster.png`; img.onerror = null; };
    article.querySelector('.wm-card__title').textContent = title;
    article.querySelector('.wm-card__year').textContent  = year;
    article.querySelectorAll('.wm-tag').forEach((el, i) => {
        el.textContent = genreNames[i];
    });

    // Navega para a página de detalhe com ID mascarado (mesmo salt do PHP helpers.php)
    const goToDetail = () => {
        const salt   = 'webmovies_ipil_2026';
        const masked = btoa(`${movie.id}|${salt}`)
            .replace(/\+/g, '-')
            .replace(/\//g, '_')
            .replace(/=+$/, '');
        window.location.href = `${CONF_URL_BASE}/filme?id=${masked}`;
    };
    article.addEventListener('click', goToDetail);
    article.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); goToDetail(); } });

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

    if (bg) {
        bg.style.opacity = '0';
        bg.src = `${BACKDROP_BASE}${movie.backdrop_path}`;
        bg.onload = () => { bg.style.opacity = '.4'; };
        bg.alt = movie.title || '';
        bg.style.cursor = 'pointer';
        bg.onclick = () => {
            const salt   = 'webmovies_ipil_2026';
            const masked = btoa(`${movie.id}|${salt}`)
                .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
            window.location.href = `${CONF_URL_BASE}/filme?id=${masked}`;
        };
    }
    if (title) title.textContent = movie.title || movie.name || '';
    if (syn)   syn.textContent   = movie.overview || '';
    if (year)  year.textContent  = (movie.release_date || '').slice(0, 4);
    if (votes) votes.textContent = movie.vote_count
        ? `${movie.vote_count.toLocaleString('pt-BR')} avaliações`
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

    const gridPopular = document.querySelector('[data-grid="popular"]');
    let debounceTimer;

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            const query = input.value.trim();

            if (!query) {
                await loadSection(gridPopular, '/discover/movie', {
                    sort_by: 'popularity.desc',
                    'certification_country': 'BR',
                    'certification.lte': 'L',
                    page: 1,
                });
                return;
            }

            renderSkeletons(gridPopular, 12);
            try {
                const results = await apiFetch('/search/movie', { query });
                renderMovies(gridPopular, results);
            } catch (err) {
                console.error('[Search Error]:', err.message);
                renderMovies(gridPopular, []);
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

    // Skeletons imediatos em todos os grids
    Object.values(grids).forEach(g => renderSkeletons(g, 12));

    // Carrega gêneros primeiro (necessário para os cards)
    await loadGenreMap();

    // Carrega todas as seções + hero em paralelo
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
    await loadGenreMap();

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