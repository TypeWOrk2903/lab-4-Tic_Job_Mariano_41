<?php
/**
 * @var string        $pageTitle
 * @var bool          $isLoggedIn
 * @var string|null   $userLoggedIn
 * @var string|null   $userAvatar
 * @var array         $genres        [{id, tmdb_id, name_pt}, ...]
 * @var int[]         $userTmdbIds   IDs TMDB dos géneros preferidos do utilizador
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="WebMovies – Descubra e recomende os melhores filmes." />
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: {
        fontFamily: { display: ['"Oswald"', 'sans-serif'], sans: ['"Outfit"', 'sans-serif'] }
      }}
    }
  </script>

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet" />

  <!-- Tokens neumórficos -->
  <link rel="stylesheet" href="<?= CONF_URL_BASE ?>/themes/painel/assets/css/variables.css" />
  <link rel="stylesheet" href="<?= CONF_URL_BASE ?>/themes/painel/assets/css/themes.css" />
  <link rel="stylesheet" href="<?= CONF_URL_BASE ?>/themes/painel/assets/css/main.css" />
</head>
<body class="dark-theme font-sans min-h-screen">

  <!-- ── HEADER ─────────────────────────────────── -->
  <header class="sticky top-0 z-50 grid grid-cols-[auto_1fr_auto] items-center
                 gap-4 px-4 sm:px-6 py-3 backdrop-blur-md border-b wm-header-bg"
          style="border-color: var(--color-border)" role="banner">

    <!-- Col 1: Logo -->
    <a href="<?= CONF_URL_BASE ?>/" aria-label="WebMovies – início"
       class="flex font-display text-lg sm:text-xl font-bold tracking-wider select-none shrink-0">
      <span style="color:var(--color-text)">Web</span><span style="color:var(--color-cyan)">Movies</span>
    </a>

    <!-- Col 2: Busca (centralizada) -->
    <label class="relative flex items-center w-full max-w-lg mx-auto" for="wm-search">
      <i class="fa-solid fa-magnifying-glass absolute left-3 text-xs pointer-events-none"
         style="color:var(--color-text-muted)" aria-hidden="true"></i>
      <input id="wm-search" class="wm-search w-full pl-9 pr-4 py-2 rounded-full text-sm outline-none"
             type="search" placeholder="Buscar filmes…" autocomplete="off"
             data-i18n-placeholder="search.placeholder"
             style="background:var(--color-panel);color:var(--color-text);
                    border:1.5px solid transparent;box-shadow:var(--neu-shadow-sm)" />
    </label>

    <!-- Col 3: Ações (idioma + tema + user) -->
    <div class="flex items-center gap-2 shrink-0">

      <!-- Tema -->
      <button type="button"
              class="wm-theme-btn w-10 h-10 rounded-full flex items-center justify-center
                     text-base cursor-pointer border-none transition-all duration-200"
              style="background:var(--color-panel);color:var(--color-text);box-shadow:var(--neu-shadow-sm)"
              aria-label="Alternar tema"></button>
      <!-- Idioma -->
      <button data-lang-btn type="button" class="wm-btn-neumorph"
              title="Alternar idioma" aria-label="Alternar idioma">
        <i class="fa-solid fa-language"></i>
        <span class="hidden sm:inline">EN</span>
      </button>

      <!-- User -->
      <?php if ($isLoggedIn): ?>
        <div class="relative group">
          <?php
            $avatarUrl = !empty($userAvatar)
              ? CONF_URL_BASE . '/themes/web/assets/' . htmlspecialchars($userAvatar)
              : CONF_URL_BASE . '/themes/painel/assets/images/no-avatar.png';
          ?>
          <button type="button" class="flex items-center gap-2 px-2 py-1 rounded-full border-none cursor-pointer"
                  style="background:var(--color-panel);box-shadow:var(--neu-shadow-sm)">
            <img src="<?= $avatarUrl ?>" alt="Avatar"
                 class="w-7 h-7 rounded-full object-cover"
                 style="border:2px solid var(--color-cyan)" />
            <span class="text-xs hidden md:block" style="color:var(--color-text-muted)">
              <?= htmlspecialchars($userLoggedIn ?? '') ?>
            </span>
            <i class="fa-solid fa-chevron-down text-[0.6rem]" style="color:var(--color-text-muted)"></i>
          </button>
          <!-- Dropdown -->
          <div class="absolute right-0 top-full mt-2 w-44 rounded-xl overflow-hidden opacity-0 pointer-events-none
                      group-focus-within:opacity-100 group-focus-within:pointer-events-auto
                      group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-150 z-50"
               style="background:var(--color-panel);box-shadow:var(--neu-shadow)">
            <a href="<?= CONF_URL_BASE ?>/perfil"
               class="flex items-center gap-2 px-4 py-2.5 text-xs hover:bg-white/5 transition-colors"
               style="color:var(--color-text)">
              <i class="fa-solid fa-user-gear w-4 text-center" style="color:var(--color-cyan)"></i>
              <span data-i18n="nav.profile">Meu Perfil</span>
            </a>
            <hr style="border-color:var(--color-border)" />
            <a href="<?= CONF_URL_BASE ?>/logout"
               class="flex items-center gap-2 px-4 py-2.5 text-xs hover:bg-white/5 transition-colors"
               style="color:#f87171">
              <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
              <span data-i18n="nav.logout">Sair</span>
            </a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= CONF_URL_BASE ?>/login"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold
                  tracking-wide uppercase cursor-pointer border-none transition-all duration-150"
           style="background:var(--color-cyan);color:#fff;box-shadow:var(--glow-cyan)">
          <i class="fa-solid fa-user"></i>
          <span class="hidden sm:inline" data-i18n="nav.login">Entrar</span>
        </a>
      <?php endif; ?>
    </div>
  </header>

  <!-- ── HERO ───────────────────────────────────── -->
  <section class="relative min-h-[68vh] flex items-end overflow-hidden" aria-label="Filme em destaque">
    <img class="wm-hero__bg absolute inset-0 w-full h-full object-cover" style="opacity:.32" src="" alt="" aria-hidden="true" />
    <div class="absolute inset-0"
         style="background:linear-gradient(to top,var(--color-bg) 10%,rgba(0,0,0,.22) 50%,transparent 80%)"></div>

    <div class="relative z-10 max-w-2xl px-5 sm:px-8 py-14 md:py-20">
      <span class="inline-flex items-center gap-1.5 text-[0.65rem] font-bold tracking-[0.18em]
                   uppercase px-3 py-1.5 rounded-full mb-4"
            style="background:var(--color-cyan-dim);color:var(--color-cyan)">
        <i class="fa-solid fa-fire-flame-curved"></i> <span data-i18n="hero.featured">EM DESTAQUE</span>
      </span>
      <h1 class="wm-hero__title font-display text-4xl sm:text-5xl font-bold leading-tight mb-4"
          style="color:var(--color-text)">Carregando…</h1>
      <p class="wm-hero__synopsis text-sm leading-relaxed mb-8 max-w-xl line-clamp-3"
         style="color:var(--color-text-muted)"></p>
      <div class="flex flex-wrap gap-3">
        <a href="#" id="hero-detail-btn"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-[0.75rem]
                  font-bold uppercase cursor-pointer border-none transition-all duration-150"
           style="background:var(--color-cyan);color:#fff;box-shadow:var(--glow-cyan)">
          <i class="fa-solid fa-circle-info"></i> <span data-i18n="hero.details">VER DETALHES</span>
        </a>
        <?php if ($isLoggedIn): ?>
        <button type="button" id="hero-fav-btn"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-[0.75rem]
                       font-bold uppercase cursor-pointer border-none transition-all duration-150"
                style="background:var(--color-amber);color:#fff;box-shadow:var(--glow-amber)">
          <i class="fa-solid fa-heart"></i> <span data-i18n="hero.favorite">FAVORITAR</span>
        </button>
        <?php else: ?>
        <a href="<?= CONF_URL_BASE ?>/login"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-[0.75rem]
                  font-bold uppercase cursor-pointer border-none transition-all duration-200"
           style="background:var(--color-panel);color:var(--color-text);box-shadow:var(--neu-shadow)">
          <i class="fa-solid fa-heart"></i> <span data-i18n="hero.favorite">FAVORITAR</span>
        </a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ── CATÁLOGO ───────────────────────────────── -->
  <main class="max-w-screen-xl mx-auto px-4 sm:px-6 py-10" id="main-content">

    <?php if ($isLoggedIn): ?>
    <!-- ── Meus Favoritos ─── -->
    <section class="mb-10" aria-labelledby="section-favorites">
      <div class="flex items-center gap-3 mb-4">
        <i class="fa-solid fa-heart text-xl" style="color:#f43f5e"></i>
        <h2 class="font-display text-xl sm:text-2xl font-bold tracking-wide"
            id="section-favorites" style="color:var(--color-text)">
          <span data-i18n="section.fav1">MEUS</span> <span style="color:var(--color-cyan)" data-i18n="section.fav2">FAVORITOS</span>
        </h2>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
           data-grid="favorites" aria-live="polite">
        <?php if (empty($favorites)): ?>
        <p class="col-span-full text-sm py-4 text-center" style="color:var(--color-text-muted)">
          <i class="fa-regular fa-heart mr-1"></i> <span data-i18n="favorites.empty">Nenhum favorito ainda. Clique no ❤ de um filme para guardar.</span>
        </p>
        <?php else: ?>
          <?php foreach ($favorites as $fav): ?>
            <?php
              $posterUrl = $fav['poster_path']
                ? 'https://image.tmdb.org/t/p/w342' . $fav['poster_path']
                : CONF_URL_BASE . '/themes/painel/assets/images/no-poster.png';
              $salt   = 'webmovies_ipil_2026';
              $masked = rtrim(strtr(base64_encode($fav['tmdb_id'] . '|' . $salt), '+/', '-_'), '=');
            ?>
            <a href="<?= CONF_URL_BASE ?>/filme?id=<?= $masked ?>"
               class="wm-card group relative block rounded-xl overflow-hidden"
               style="box-shadow:var(--neu-shadow);background:var(--color-panel)"
               title="<?= htmlspecialchars($fav['title']) ?>">
              <div class="relative">
                <img src="<?= htmlspecialchars($posterUrl) ?>"
                     alt="<?= htmlspecialchars($fav['title']) ?>"
                     loading="lazy"
                     class="w-full aspect-[2/3] object-cover"
                     onerror="this.src='<?= CONF_URL_BASE ?>/themes/painel/assets/images/no-poster.png';this.onerror=null" />
                <!-- Botão remover favorito -->
                <form method="POST" action="<?= CONF_URL_BASE ?>/favorito/remover"
                      class="absolute top-1.5 right-1.5" onclick="event.stopPropagation()">
                  <input type="hidden" name="tmdb_id" value="<?= (int)$fav['tmdb_id'] ?>">
                  <button type="submit"
                          title="Remover favorito"
                          class="w-7 h-7 rounded-full flex items-center justify-center border-none cursor-pointer text-xs transition-all"
                          style="background:#f43f5e;color:#fff;box-shadow:0 1px 6px rgba(0,0,0,.4)">
                    <i class="fa-solid fa-heart"></i>
                  </button>
                </form>
              </div>
              <p class="px-2 py-1.5 text-xs font-semibold truncate" style="color:var(--color-text)">
                <?= htmlspecialchars($fav['title']) ?>
              </p>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- ── Seletor de Categorias (logados) ─── -->
    <section class="mb-10" aria-labelledby="section-genres">
      <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
        <div class="flex items-center gap-3">
          <i class="fa-solid fa-sliders text-base" style="color:var(--color-cyan)"></i>
          <h2 class="font-display text-lg sm:text-xl font-bold tracking-wide"
              id="section-genres" style="color:var(--color-text)">
            <span data-i18n="section.filter1">FILTRAR POR</span> <span style="color:var(--color-cyan)" data-i18n="section.filter2">CATEGORIA</span>
          </h2>
        </div>
        <a href="<?= CONF_URL_BASE ?>/perfil#genres"
           class="text-xs" style="color:var(--color-text-muted)">
          <i class="fa-solid fa-gear mr-1"></i><span data-i18n="section.manage">Gerir preferências</span>
        </a>
      </div>

      <!-- Chips de género (todos) -->
      <div class="flex flex-wrap gap-2" id="genre-chips-home">
        <span class="genre-chip-home px-3 py-1.5 rounded-full text-xs font-semibold cursor-pointer select-none"
              data-tmdb=""
              style="background:var(--color-cyan);color:#fff;box-shadow:var(--glow-cyan)">
          <i class="fa-solid fa-fire-flame-curved mr-1"></i><span data-i18n="section.popular_chip">Populares</span>
        </span>
        <?php foreach ($genres as $g): ?>
          <?php $preferred = in_array($g['tmdb_id'], $userTmdbIds, true); ?>
          <span class="genre-chip-home <?= $preferred ? 'preferred' : '' ?> px-3 py-1.5 rounded-full text-xs font-semibold cursor-pointer select-none transition-all duration-150"
                data-tmdb="<?= $g['tmdb_id'] ?>"
                style="background:var(--color-bg);color:<?= $preferred ? 'var(--color-amber)' : 'var(--color-text-muted)' ?>;box-shadow:var(--neu-shadow-sm);<?= $preferred ? 'border:1px solid var(--color-amber)' : '' ?>">
            <?= htmlspecialchars($g['name_pt']) ?>
          </span>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Grid filtrado por categoria -->
    <section class="mb-14" aria-labelledby="section-genre-grid">
      <div class="flex items-center gap-3 mb-6">
        <i class="fa-solid fa-film text-xl" style="color:var(--color-cyan)"></i>
        <h2 class="font-display text-xl sm:text-2xl font-bold tracking-wide"
            id="section-genre-grid" style="color:var(--color-text)">
          <span id="genre-title">POPULARES</span> <span style="color:var(--color-cyan)" data-i18n="section.genre_suffix">DO DIA</span>
        </h2>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
           data-grid="genre" aria-live="polite"></div>
    </section>
    <?php endif; ?>

    <section class="mb-14" aria-labelledby="section-popular">
      <div class="flex items-center gap-3 mb-6">
        <i class="fa-solid fa-fire text-xl" style="color:var(--color-amber)"></i>
        <h2 class="font-display text-xl sm:text-2xl font-bold tracking-wide"
            id="section-popular" style="color:var(--color-text)">
          <span data-i18n="section.popular1">RECOMENDAÇÕES</span> <span style="color:var(--color-cyan)" data-i18n="section.popular2">DO DIA</span>
        </h2>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
           data-grid="popular" aria-live="polite"></div>
    </section>

  </main>

  <!-- TOAST -->
  <div id="wm-toast"
       class="fixed bottom-6 left-1/2 z-[9999] px-5 py-2.5 rounded-full text-sm font-semibold
              whitespace-nowrap -translate-x-1/2 translate-y-4 opacity-0 pointer-events-none transition-all duration-300"
       style="background:var(--color-panel);color:var(--color-text);box-shadow:var(--neu-shadow)"
       role="status" aria-live="assertive" aria-atomic="true"></div>

  <script type="module" src="<?= CONF_URL_BASE ?>/themes/painel/assets/js/app.js"></script>
</body>
</html>
