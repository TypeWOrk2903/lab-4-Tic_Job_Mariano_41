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
    const API_KEY = 'd1e10648';
    const BLOCKED = new Set(['NC-17','X','XXX','R','TV-MA']);

    const root    = document.getElementById('movie-detail-root');
    const imdbId  = root.dataset.imdb;
    const content = document.getElementById('detail-content');
    const skeleton= document.getElementById('detail-skeleton');
    const errEl   = document.getElementById('detail-error');

    async function load() {
      if (!imdbId) { skeleton.hidden = true; errEl.classList.remove('hidden'); return; }

      try {
        const res  = await fetch(`https://www.omdbapi.com/?apikey=${API_KEY}&i=${encodeURIComponent(imdbId)}&plot=full`);
        const data = await res.json();

        if (data.Response === 'False' || BLOCKED.has(data.Rated)) {
          throw new Error('Não disponível');
        }

        const poster = data.Poster && data.Poster !== 'N/A' ? data.Poster : '<?= CONF_URL_BASE ?>/themes/assets/images/no-poster.svg';
        const rating = parseFloat(data.imdbRating) || 0;
        const stars  = Math.round(rating / 2);

        content.innerHTML = `
          <img src="${poster}" alt="${data.Title}"
               class="rounded-2xl shrink-0 w-48 md:w-60 aspect-[2/3] object-cover self-start"
               style="box-shadow:var(--neu-shadow)" />
          <div class="flex-1 pt-2 space-y-4">
            <h1 class="font-display text-3xl sm:text-4xl font-bold leading-tight"
                style="color:var(--color-text)">${data.Title} <span class="text-lg font-normal" style="color:var(--color-text-muted)">(${data.Year})</span></h1>

            <div class="flex flex-wrap gap-2 text-xs">
              ${(data.Genre ?? '').split(',').map(g => `
                <span class="px-3 py-1 rounded-full font-semibold"
                      style="background:var(--color-cyan-dim);color:var(--color-cyan)">${g.trim()}</span>
              `).join('')}
            </div>

            <div class="flex items-center gap-3">
              <span class="text-2xl font-bold" style="color:var(--color-amber)">${rating.toFixed(1)}</span>
              <span class="text-sm" style="color:var(--color-text-muted)">${'★'.repeat(stars)}${'☆'.repeat(5-stars)} IMDb</span>
              <span class="text-xs px-2 py-0.5 rounded" style="background:var(--color-panel);color:var(--color-text-muted);box-shadow:var(--neu-shadow-sm)">${data.Rated ?? '—'}</span>
            </div>

            <p class="text-sm leading-relaxed" style="color:var(--color-text-muted)">${data.Plot ?? ''}</p>

            <dl class="grid grid-cols-2 gap-3 text-xs">
              ${[['Diretor', data.Director],['Elenco', data.Actors],['País', data.Country],['Idioma', data.Language],['Duração', data.Runtime]].map(([k,v]) => v && v !== 'N/A' ? `
                <div class="rounded-xl p-3" style="background:var(--color-panel);box-shadow:var(--neu-shadow-sm)">
                  <dt class="font-bold uppercase tracking-wide mb-0.5" style="color:var(--color-cyan)">${k}</dt>
                  <dd style="color:var(--color-text)">${v}</dd>
                </div>` : '').join('')}
            </dl>

            <?php if ($isLoggedIn): ?>
            <button type="button" id="fav-btn"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm
                           font-bold uppercase cursor-pointer border-none transition-all duration-150"
                    style="background:var(--color-amber);color:#fff;box-shadow:var(--glow-amber)"
                    data-imdb="${data.imdbID}">
              <i class="fa-solid fa-heart"></i> Adicionar aos Favoritos
            </button>
            <?php else: ?>
            <a href="<?= CONF_URL_BASE ?>/login"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm
                      font-bold uppercase cursor-pointer border-none"
               style="background:var(--color-panel);color:var(--color-text);box-shadow:var(--neu-shadow)">
              <i class="fa-solid fa-heart"></i> Entre para Favoritar
            </a>
            <?php endif; ?>
          </div>`;

        document.title = `${data.Title} | WebMovies`;
        skeleton.hidden = true;
        content.classList.remove('hidden');
        content.classList.add('flex');
      } catch {
        skeleton.hidden = true;
        errEl.classList.remove('hidden');
      }
    }

    load();
  </script>
</body>
</html>
