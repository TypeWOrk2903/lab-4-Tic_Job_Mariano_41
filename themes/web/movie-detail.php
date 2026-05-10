<?php /** @var string $pageTitle @var string $imdbId @var bool $isLoggedIn */ ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Outfit:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= CONF_URL_BASE ?>/themes/painel/assets/css/variables.css" />
  <link rel="stylesheet" href="<?= CONF_URL_BASE ?>/themes/painel/assets/css/themes.css" />
  <link rel="stylesheet" href="<?= CONF_URL_BASE ?>/themes/painel/assets/css/main.css" />
</head>
<body class="dark-theme font-sans min-h-screen" style="background:var(--bg-radial),var(--color-bg)">

  <!-- HEADER compacto -->
  <header class="sticky top-0 z-50 flex items-center gap-4 px-5 py-3 backdrop-blur-md border-b wm-header-bg"
          style="border-color:var(--color-border)">
    <a href="<?= CONF_URL_BASE ?>/"
       class="font-display text-lg font-bold tracking-wider select-none">
      <span style="color:var(--color-text)">Web</span><span style="color:var(--color-cyan)">Movies</span>
    </a>
    <a href="<?= CONF_URL_BASE ?>/" class="ml-auto text-xs flex items-center gap-1.5"
       style="color:var(--color-text-muted)">
      <i class="fa-solid fa-arrow-left text-[0.65rem]"></i> Catálogo
    </a>
  </header>

  <!-- CONTEÚDO do filme (preenchido via JS) -->
  <main class="max-w-screen-lg mx-auto px-4 sm:px-6 py-12" id="movie-detail-root"
        data-imdb="<?= htmlspecialchars($imdbId) ?>">

    <!-- Skeleton enquanto carrega -->
    <div id="detail-skeleton" class="flex flex-col md:flex-row gap-8 animate-pulse">
      <div class="rounded-2xl shrink-0 w-48 md:w-64 aspect-[2/3]"
           style="background:var(--color-panel);box-shadow:var(--neu-shadow)"></div>
      <div class="flex-1 space-y-4 pt-4">
        <div class="h-8 rounded-full w-2/3" style="background:var(--color-panel)"></div>
        <div class="h-4 rounded-full w-1/4" style="background:var(--color-panel)"></div>
        <div class="h-4 rounded-full w-full" style="background:var(--color-panel)"></div>
        <div class="h-4 rounded-full w-5/6" style="background:var(--color-panel)"></div>
        <div class="h-4 rounded-full w-4/5" style="background:var(--color-panel)"></div>
      </div>
    </div>

    <!-- Conteúdo real (injetado pelo JS) -->
    <div id="detail-content" class="hidden flex-col md:flex-row gap-8"></div>

    <!-- Erro -->
    <div id="detail-error" class="hidden text-center py-20" style="color:var(--color-text-muted)">
      <i class="fa-solid fa-film text-5xl mb-4 block opacity-30"></i>
      <p>Filme não encontrado.</p>
      <a href="<?= CONF_URL_BASE ?>/" class="mt-4 inline-block text-sm underline"
         style="color:var(--color-cyan)">Voltar ao catálogo</a>
    </div>

  </main>

 <script type="module">
  const API_KEY  = 'b0e9f75142eb4a69493d8fba03cf29f5';
  const BASE_URL = 'https://api.themoviedb.org/3';
  const IMG_URL  = 'https://image.tmdb.org/t/p/w500';
  const LANG     = localStorage.getItem('wm-lang') || 'pt-BR';

  const root     = document.getElementById('movie-detail-root');
  const movieId  = root.dataset.imdb;
  const content  = document.getElementById('detail-content');
  const skeleton = document.getElementById('detail-skeleton');
  const errEl    = document.getElementById('detail-error');

  async function load() {
    if (!movieId) { 
      skeleton.classList.add('hidden'); 
      errEl.classList.remove('hidden'); 
      return; 
    }

    try {
      const response = await fetch(`${BASE_URL}/movie/${movieId}?api_key=${API_KEY}&language=${LANG}&append_to_response=release_dates`);
      const data = await response.json();

      if (!data || data.adult === true || data.status_code === 34) {
        throw new Error('Conteúdo não disponível ou restrito');
      }

      const noPoster = '<?= CONF_URL_BASE ?>/themes/painel/assets/images/no-poster.png';
      const poster = data.poster_path ? `${IMG_URL}${data.poster_path}` : noPoster;
      const rating = data.vote_average || 0;
      const pct    = Math.round(rating * 10);
      const stars  = Math.round(rating / 2);
      const year   = data.release_date ? data.release_date.split('-')[0] : 'N/A';
      let scoreCls = 'wm-score--rotten';
      if (pct >= 75) scoreCls = 'wm-score--fresh';
      else if (pct >= 60) scoreCls = 'wm-score--mixed';

      // Injeção do HTML
      content.innerHTML = `
        <img src="${poster}" alt="${data.title}"
             onerror="this.src='<?= CONF_URL_BASE ?>/themes/painel/assets/images/no-poster.png';this.onerror=null"
             class="rounded-2xl shrink-0 w-48 md:w-60 aspect-[2/3] object-cover self-start"
             style="box-shadow:var(--neu-shadow)" />
             
        <div class="flex-1 pt-2 space-y-4">
          <h1 class="font-display text-3xl sm:text-4xl font-bold leading-tight"
              style="color:var(--color-text)">
              ${data.title} 
              <span class="text-lg font-normal" style="color:var(--color-text-muted)">(${year})</span>
          </h1>

          <div class="flex flex-wrap gap-2 text-xs">
            ${(data.genres || []).map(g => `
              <span class="px-3 py-1 rounded-full font-semibold"
                    style="background:var(--color-cyan-dim);color:var(--color-cyan)">${g.name}</span>
            `).join('')}
          </div>

          <div class="flex items-center gap-3">
            <span class="wm-score wm-score--lg ${scoreCls}">${pct}%</span>
            <span class="text-sm" style="color:var(--color-text-muted)">${'★'.repeat(stars)}${'☆'.repeat(5-stars)} TMDB</span>
            <span class="text-xs px-2 py-0.5 rounded" style="background:var(--color-panel);color:var(--color-text-muted);box-shadow:var(--neu-shadow-sm)">
                ${data.adult ? '+18' : 'Livre'}
            </span>
          </div>

          <p class="text-sm leading-relaxed" style="color:var(--color-text-muted)">${data.overview || 'Sem descrição disponível.'}</p>

          <dl class="grid grid-cols-2 gap-3 text-xs">
            <div class="rounded-xl p-3" style="background:var(--color-panel);box-shadow:var(--neu-shadow-sm)">
              <dt class="font-bold uppercase tracking-wide mb-0.5" style="color:var(--color-cyan)">Duração</dt>
              <dd style="color:var(--color-text)">${data.runtime} min</dd>
            </div>
            <div class="rounded-xl p-3" style="background:var(--color-panel);box-shadow:var(--neu-shadow-sm)">
              <dt class="font-bold uppercase tracking-wide mb-0.5" style="color:var(--color-cyan)">Idioma</dt>
              <dd style="color:var(--color-text)">${data.original_language.toUpperCase()}</dd>
            </div>
            <div class="rounded-xl p-3" style="background:var(--color-panel);box-shadow:var(--neu-shadow-sm)">
              <dt class="font-bold uppercase tracking-wide mb-0.5" style="color:var(--color-cyan)">Popularidade</dt>
              <dd style="color:var(--color-text)">${Math.round(data.popularity)} pts</dd>
            </div>
          </dl>

          <?php if ($isLoggedIn): ?>
          <button type="button" id="fav-btn"
                  class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold uppercase cursor-pointer border-none transition-all duration-150 active:scale-95"
                  style="background:var(--color-amber);color:#fff;box-shadow:var(--glow-amber)"
                  data-id="${data.id}">
            <i class="fa-solid fa-heart"></i> Adicionar aos Favoritos
          </button>
          <?php else: ?>
          <a href="<?= CONF_URL_BASE ?>/login"
             class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold uppercase cursor-pointer border-none"
             style="background:var(--color-panel);color:var(--color-text);box-shadow:var(--neu-shadow)">
            <i class="fa-solid fa-heart"></i> Entre para Favoritar
          </a>
          <?php endif; ?>
        </div>`;

      document.title = `${data.title} | WebMovies`;
      skeleton.classList.add('hidden');
      content.classList.remove('hidden');
      content.classList.add('flex');

    } catch (error) {
      console.error(error);
      skeleton.classList.add('hidden');
      errEl.classList.remove('hidden');
    }
  }

  load();
</script>
</body>
</html>
