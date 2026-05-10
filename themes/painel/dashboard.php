<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="WebMovies – Descubra e avalie os melhores filmes." />
  <title><?=$pageTitle?></title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            display: ['"Oswald"', 'sans-serif'],
            sans:    ['"Outfit"', 'sans-serif'],
          },
          colors: {
            'wm-bg':    'var(--color-bg)',
            'wm-panel': 'var(--color-panel)',
            'wm-text':  'var(--color-text)',
            'wm-muted': 'var(--color-text-muted)',
            'wm-cyan':  'var(--color-cyan)',
            'wm-amber': 'var(--color-amber)',
          },
          boxShadow: {
            'neu':        'var(--neu-shadow)',
            'neu-sm':     'var(--neu-shadow-sm)',
            'neu-hover':  'var(--neu-shadow-hover)',
            'neu-inset':  'var(--neu-shadow-inset)',
            'neu-active': 'var(--neu-shadow-active)',
            'glow-cyan':  'var(--glow-cyan)',
            'glow-amber': 'var(--glow-amber)',
          },
        }
      }
    }
  </script>

  <!-- Font Awesome 6 -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Outfit:wght@300;400;600;700&display=swap"
        rel="stylesheet" />

  <!-- Tokens neumórficos (CSS custom properties) -->
  <link rel="stylesheet" href="<?=url_asset("css/variables.css")?>" />
  <link rel="stylesheet" href="<?=url_asset("css/themes.css")?>" />
  <link rel="stylesheet" href="<?=url_asset("css/main.css") ?>" />
</head>
<body class="dark-theme font-sans min-h-screen">

  <!-- ═══════════ HEADER ═══════════ -->
  <header class="sticky top-0 z-50 grid grid-cols-[auto_1fr_auto] items-center
                 gap-4 px-4 sm:px-6 py-3 backdrop-blur-md border-b wm-header-bg"
          style="border-color: var(--color-border)" role="banner">

    <!-- Col 1: Logo -->
    <a href="/" aria-label="WebMovies – página inicial"
       class="flex font-display text-lg sm:text-xl font-bold tracking-wider select-none shrink-0">
      <span style="color: var(--color-text)">Web</span>
      <span style="color: var(--color-cyan)">Movies</span>
    </a>

    <!-- Col 2: Busca -->
    <label class="relative flex items-center w-full max-w-lg mx-auto" for="wm-search">
      <i class="fa-solid fa-magnifying-glass absolute left-3 text-xs pointer-events-none"
         style="color: var(--color-text-muted)" aria-hidden="true"></i>
      <input
        id="wm-search"
        class="wm-search w-full pl-9 pr-4 py-2 rounded-full text-sm outline-none transition-shadow duration-200"
        type="search"
        placeholder="Buscar filmes…"
        autocomplete="off"
        spellcheck="false"
        aria-label="Buscar filmes"
        style="background: var(--color-panel); color: var(--color-text);
               border: 1.5px solid transparent; box-shadow: var(--neu-shadow-sm)"
      />
    </label>

    <!-- Col 3: Ações (idioma + tema + utilizador) -->
    <div class="flex items-center gap-2 shrink-0">

      <!-- Idioma -->
      <button data-lang-btn type="button" class="wm-btn-neumorph"
              title="Alternar idioma" aria-label="Alternar idioma">
        <i class="fa-solid fa-language"></i>
        <span class="hidden sm:inline">EN</span>
      </button>

      <!-- Tema -->
      <button type="button"
              class="wm-theme-btn w-10 h-10 rounded-full flex items-center justify-center
                     text-base cursor-pointer border-none transition-all duration-200"
              style="background: var(--color-panel); color: var(--color-text); box-shadow: var(--neu-shadow-sm)"
              aria-label="Alternar tema claro/escuro">
      </button>

      <!-- Utilizador / Logout -->
      <div class="relative group">
        <button type="button"
                class="flex items-center gap-2 px-3 py-2 rounded-full text-sm font-semibold
                       cursor-pointer border-none transition-all duration-200"
                style="background: var(--color-panel); color: var(--color-text); box-shadow: var(--neu-shadow-sm)"
                aria-label="Menu do utilizador">
          <i class="fa-solid fa-circle-user text-base" style="color: var(--color-cyan)"></i>
          <span class="hidden sm:inline max-w-[120px] truncate"><?= htmlspecialchars($adminName ?? 'Admin') ?></span>
          <i class="fa-solid fa-chevron-down text-[0.6rem]" style="color: var(--color-text-muted)"></i>
        </button>

        <!-- Dropdown -->
        <div class="absolute right-0 top-full mt-2 w-44 rounded-xl py-1 z-50
                    opacity-0 pointer-events-none group-focus-within:opacity-100 group-focus-within:pointer-events-auto
                    group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-150"
             style="background: var(--color-panel); box-shadow: var(--neu-shadow)">

          <a href="<?= CONF_URL_BASE ?>/admin/settings"
             class="flex items-center gap-2 px-4 py-2.5 text-sm hover:brightness-125 transition-all"
             style="color: var(--color-text)">
            <i class="fa-solid fa-gear w-4 text-center" style="color: var(--color-cyan)"></i>
            Configurações
          </a>

          <div style="height:1px; background: var(--color-border); margin: 4px 12px"></div>

          <a href="<?= CONF_URL_BASE ?>/logout"
             class="flex items-center gap-2 px-4 py-2.5 text-sm hover:brightness-125 transition-all"
             style="color: #f43f5e">
            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
            Sair
          </a>
        </div>
      </div>

    </div>
  </header>


  <!-- ═══════════ HERO ═══════════ -->
  <section class="relative min-h-[70vh] flex items-end overflow-hidden"
           aria-label="Filme em destaque">

    <img class="wm-hero__bg absolute inset-0 w-full h-full object-cover"
         src="" alt="" aria-hidden="true" />
    <div class="absolute inset-0"
         style="background: linear-gradient(to top, var(--color-bg) 10%, rgba(0,0,0,0.25) 50%, transparent 80%)">
    </div>

    <!-- Setas de navegação -->
    <button class="wm-hero__nav wm-hero__nav--prev" aria-label="Filme anterior">
      <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button class="wm-hero__nav wm-hero__nav--next" aria-label="Próximo filme">
      <i class="fa-solid fa-chevron-right"></i>
    </button>

    <div class="relative z-10 max-w-2xl px-5 sm:px-8 py-16 md:py-24">

      <span class="inline-flex items-center gap-1.5 text-[0.65rem] font-bold tracking-[0.18em]
                   uppercase px-3 py-1.5 rounded-full mb-4"
            style="background: var(--color-cyan-dim); color: var(--color-cyan)">
        <i class="fa-solid fa-fire-flame-curved"></i> EM DESTAQUE
      </span>

      <h1 class="wm-hero__title font-display text-4xl sm:text-5xl md:text-6xl font-bold leading-tight mb-3"
          style="color: var(--color-text)">Carregando…</h1>

      <!-- Meta: score + gêneros + ano + votos -->
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <span data-hero="score"></span>
        <div data-hero="tags" class="flex flex-wrap gap-2"></div>
        <span data-hero="year" class="text-xs font-semibold"
              style="color: var(--color-text-muted)"></span>
        <span data-hero="votes" class="text-xs"
              style="color: var(--color-text-muted)"></span>
      </div>

      <p class="wm-hero__synopsis text-sm sm:text-base leading-relaxed mb-8 max-w-xl line-clamp-3"
         style="color: var(--color-text-muted)"></p>

      <div class="flex flex-wrap gap-3">

        <button type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full
                       text-[0.75rem] font-bold tracking-[0.1em] uppercase cursor-pointer border-none
                       hover:brightness-110 active:scale-95 transition-all duration-150"
                style="background: var(--color-cyan); color: #fff; box-shadow: var(--glow-cyan)">
          <i class="fa-solid fa-play"></i> ASSISTIR AGORA
        </button>

        <button type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full
                       text-[0.75rem] font-bold tracking-[0.1em] uppercase cursor-pointer border-none
                       hover:brightness-110 active:scale-95 transition-all duration-150"
                style="background: var(--color-amber); color: #fff; box-shadow: var(--glow-amber)">
          <i class="fa-solid fa-star"></i> AVALIAR
        </button>

        <button type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full
                       text-[0.75rem] font-bold tracking-[0.1em] uppercase cursor-pointer border-none
                       hover:shadow-neu-hover active:scale-95 transition-all duration-200"
                style="background: var(--color-panel); color: var(--color-text); box-shadow: var(--neu-shadow)">
          <i class="fa-solid fa-circle-info"></i> SAIBA MAIS
        </button>

      </div>
    </div>
  </section>


  <!-- ═══════════ MAIN ═══════════ -->
  <main class="max-w-screen-xl mx-auto px-4 sm:px-6 py-10" id="main-content">

    <!-- Populares -->
    <section class="mb-14" aria-labelledby="section-popular">
      <div class="flex items-center gap-3 mb-6">
        <i class="fa-solid fa-fire text-xl" style="color: var(--color-amber)"></i>
        <h2 class="font-display text-xl sm:text-2xl font-bold tracking-wide"
            style="color: var(--color-text)" id="section-popular">
          POPULARES <span style="color: var(--color-cyan)">NO WEBMOVIES</span>
        </h2>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
           data-grid="popular" aria-live="polite" aria-label="Filmes populares"></div>
    </section>

    <!-- Em alta -->
    <section class="mb-14" aria-labelledby="section-trending">
      <div class="flex items-center gap-3 mb-6">
        <i class="fa-solid fa-chart-line text-xl" style="color: var(--color-cyan)"></i>
        <h2 class="font-display text-xl sm:text-2xl font-bold tracking-wide"
            style="color: var(--color-text)" id="section-trending">
          EM ALTA <span style="color: var(--color-amber)">ESTA SEMANA</span>
        </h2>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
           data-grid="recommended" aria-live="polite" aria-label="Filmes em alta"></div>
    </section>

    <!-- Em Cartaz -->
    <section class="mb-14" aria-labelledby="section-nowplaying">
      <div class="flex items-center gap-3 mb-6">
        <i class="fa-solid fa-clapperboard text-xl" style="color: var(--color-cyan)"></i>
        <h2 class="font-display text-xl sm:text-2xl font-bold tracking-wide"
            style="color: var(--color-text)" id="section-nowplaying">
          EM CARTAZ <span style="color: var(--color-amber)">NOS CINEMAS</span>
        </h2>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
           data-grid="now-playing" aria-live="polite" aria-label="Filmes em cartaz"></div>
    </section>

    <!-- Mais Bem Avaliados -->
    <section class="mb-14" aria-labelledby="section-toprated">
      <div class="flex items-center gap-3 mb-6">
        <i class="fa-solid fa-trophy text-xl" style="color: var(--color-amber)"></i>
        <h2 class="font-display text-xl sm:text-2xl font-bold tracking-wide"
            style="color: var(--color-text)" id="section-toprated">
          MAIS BEM <span style="color: var(--color-cyan)">AVALIADOS</span>
        </h2>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
           data-grid="top-rated" aria-live="polite" aria-label="Filmes mais bem avaliados"></div>
    </section>

    <!-- Próximos Lançamentos -->
    <section class="mb-14" aria-labelledby="section-upcoming">
      <div class="flex items-center gap-3 mb-6">
        <i class="fa-solid fa-calendar-days text-xl" style="color: var(--color-cyan)"></i>
        <h2 class="font-display text-xl sm:text-2xl font-bold tracking-wide"
            style="color: var(--color-text)" id="section-upcoming">
          PRÓXIMOS <span style="color: var(--color-amber)">LANÇAMENTOS</span>
        </h2>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
           data-grid="upcoming" aria-live="polite" aria-label="Próximos lançamentos"></div>
    </section>

  </main>


  <!-- TOAST -->
  <div id="wm-toast"
       class="fixed bottom-6 left-1/2 z-[9999] px-5 py-2.5 rounded-full text-sm font-semibold
              whitespace-nowrap -translate-x-1/2 translate-y-4 opacity-0 pointer-events-none
              transition-all duration-300"
       style="background: var(--color-panel); color: var(--color-text); box-shadow: var(--neu-shadow)"
       role="status" aria-live="assertive" aria-atomic="true"></div>

  <script type="module" src="<?= url_asset("js/app.js") ?>"></script>
</body>
</html>
