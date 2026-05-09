/**
 * WebMovies – OMDb API Integration
 * Restrição +18 aplicada na raiz: toda busca de lista
 * já verifica a classificação antes de retornar.
 */

/* ─── Constantes ───────────────────────────────── */
const API_KEY         = 'd1e10648';
const BASE_URL        = 'https://www.omdbapi.com/';
const FALLBACK_POSTER = 'themes/assets/images/no-poster.svg';

const BLOCKED_RATINGS = new Set(['NC-17', 'X', 'XXX', 'R', 'TV-MA']);
const BLOCKED_KEYWORDS = ['sexy', 'erotic', 'adult', 'porn', 'nude', 'horror 18', 'gore'];
const BLOCKED_GENRES = ['Horror', 'Thriller', 'Adult'];

/* ─── Fetch bruto (uso interno apenas) ─────────── */
async function _rawFetch(params = {}) {
    const url = new URL(BASE_URL);
    url.searchParams.set('apikey', API_KEY);
    Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

    const res  = await fetch(url.toString());
    const data = await res.json();
    if (data.Response === 'False') throw new Error(data.Error);
    return data;
}

/* ─── Validação de conteúdo seguro ─────────────── */
function isMovieSafe(movie) {
    const isRatingRestricted = !movie.Rated || movie.Rated === "N/A" || BLOCKED_RATINGS.has(movie.Rated);
    const hasBlockedKeyword = BLOCKED_KEYWORDS.some(word => movie.Title.toLowerCase().includes(word));
    const hasBlockedGenre = movie.Genre && BLOCKED_GENRES.some(genre => movie.Genre.includes(genre));
    
    return !(isRatingRestricted || hasBlockedKeyword || hasBlockedGenre);
}

/**
 * apiFetch — busca de lista com filtro +18 integrado.
 * Para cada resultado da busca, busca os detalhes e descarta
 * filmes com classificação etária restrita ou palavras-chave bloqueadas.
 *
 * @param {object} params  Parâmetros OMDb (s, type, y…)
 * @returns {Promise<object[]>}  Array de filmes seguros (com detalhes)
 */
async function apiFetch(params = {}) {
    const data = await _rawFetch(params);
    const list = data.Search ?? [];

    const details = await Promise.all(
        list.map(async m => {
            try {
                const detail = await _rawFetch({ i: m.imdbID });
                return isMovieSafe(detail) ? detail : null;
            } catch {
                return null;
            }
        })
    );

    return details.filter(Boolean);
}

/* ─── UI: Skeletons ────────────────────────────── */
function renderSkeletons(container, count = 6) {
    if (!container) return;
    container.innerHTML = Array.from({ length: count })
        .map(() => `<div class="wm-skeleton"><div class="wm-skeleton__inner"></div></div>`)
        .join('');
}

/* ─── Componente: Card OMDb ────────────────────── */
function buildCard(movie) {
    // Na OMDb, os campos são Poster, Title, Year e imdbID
    const poster = (movie.Poster && movie.Poster !== "N/A") ? movie.Poster : FALLBACK_POSTER;
    const title  = movie.Title || 'Sem título';
    const year   = movie.Year || '—';

    const article = document.createElement('article');
    article.className = 'wm-card';
    article.dataset.imdbId = movie.imdbID;

    article.innerHTML = `
        <div class="wm-card__poster">
            <img src="${poster}" alt="${title}" loading="lazy" />
            <div class="wm-card__overlay"></div>
        </div>
        <div class="wm-card__body">
            <h3 class="wm-card__title">${title}</h3>
            <p class="wm-card__genres">${year}</p>
        </div>`;
    
    return article;
}

/* ─── Lógica: Renderizar Listas ────────────────── */
function renderMovies(container, movies) {
    if (!container) return;
    container.innerHTML = '';
    
    if (!movies || movies.length === 0) {
        container.innerHTML = `<div class="wm-state"><p>Nenhum filme encontrado.</p></div>`;
        return;
    }

    movies.forEach(m => container.appendChild(buildCard(m)));
}

/* ─── Lógica: Busca ────────────────────────────── */
let searchTimer = null;
export function initSearch() {
    const input = document.querySelector('.wm-search');
    const grid = document.querySelector('[data-grid="popular"]');

    if (!input) return;

    input.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const query = input.value.trim();

        if (!query) {
            loadSections();
            return;
        }

        searchTimer = setTimeout(async () => {
            renderSkeletons(grid, 6);
            try {
                const safe = await apiFetch({ s: query, type: 'movie' });
                renderMovies(grid, safe);
            } catch (err) {
                renderMovies(grid, []);
            }
        }, 500);
    });
}

/* ─── Inicialização: Filmes Iniciais ───────────── */
export async function loadSections() {
    const grid = document.querySelector('[data-grid="popular"]');
    renderSkeletons(grid, 15);

    // 30+ termos para garantir variedade real a cada carregamento
    const ALL_TERMS = [
        'Action', 'Comedy', 'Drama', 'Sci-Fi',
        'Adventure', 'Romance', 'Fantasy', 'Mystery',
        'Animation', 'Crime', 'Biography', 'War', 'Western',
        'Musical', 'Sport', 'History', 'Family', 'Documentary',
        'Space', 'Zombie', 'Vampire', 'Superhero', 'Detective',
        'Heist', 'Survival', 'Alien', 'Robot', 'Spy',
    ];

    // Embaralha e pega 4 termos distintos
    const shuffled = ALL_TERMS.sort(() => Math.random() - 0.5);
    const picks    = shuffled.slice(0, 4);

    try {
        // Dispara 4 buscas em paralelo
        const results = await Promise.allSettled(
            picks.map(term => apiFetch({ s: term, type: 'movie' }))
        );

        // Junta todos os filmes encontrados, removendo duplicatas por imdbID
        const seen = new Set();
        const safe = results
            .filter(r => r.status === 'fulfilled' && Array.isArray(r.value))
            .flatMap(r => r.value)
            .filter(m => {
                if (seen.has(m.imdbID)) return false;
                seen.add(m.imdbID);
                return true;
            });

        renderMovies(grid, safe.slice(0, 18));
    } catch (err) {
        console.error("Erro ao carregar seção inicial.", err);
        renderMovies(grid, []);
    }
}