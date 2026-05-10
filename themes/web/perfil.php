<?php
/**
 * @var string      $pageTitle
 * @var bool        $isLoggedIn
 * @var string|null $userLoggedIn
 * @var object|null $user
 * @var array       $genres        Todos os géneros da BD
 * @var int[]       $userTmdbIds   tmdb_ids selecionados pelo utilizador
 * @var string|null $error
 * @var string|null $success
 */
$avatarUrl = !empty($user->avatar)
    ? CONF_URL_BASE . '/themes/web/assets/' . htmlspecialchars($user->avatar)
    : CONF_URL_BASE . '/themes/painel/assets/images/no-avatar.png';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: {
        fontFamily: { display: ['"Oswald"', 'sans-serif'], sans: ['"Outfit"', 'sans-serif'] }
      }}
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= CONF_URL_BASE ?>/themes/painel/assets/css/variables.css" />
  <link rel="stylesheet" href="<?= CONF_URL_BASE ?>/themes/painel/assets/css/themes.css" />
  <link rel="stylesheet" href="<?= CONF_URL_BASE ?>/themes/painel/assets/css/main.css" />
  <style>
    .wm-avatar-ring { box-shadow: 0 0 0 3px var(--color-cyan), var(--neu-shadow); }
    .genre-chip { transition: background .15s, color .15s, box-shadow .15s; }
    .genre-chip.active {
      background: var(--color-cyan) !important;
      color: #fff !important;
      box-shadow: var(--glow-cyan) !important;
    }
  </style>
</head>
<body class="dark-theme font-sans min-h-screen">

  <!-- ── HEADER ───────────────────────────────────── -->
  <header class="sticky top-0 z-50 grid grid-cols-[auto_1fr_auto] items-center
                 gap-4 px-4 sm:px-6 py-3 backdrop-blur-md border-b wm-header-bg"
          style="border-color:var(--color-border)" role="banner">
    <a href="<?= CONF_URL_BASE ?>/" class="flex font-display text-lg font-bold tracking-wider select-none shrink-0">
      <span style="color:var(--color-text)">Web</span><span style="color:var(--color-cyan)">Movies</span>
    </a>
    <div></div>
    <div class="flex items-center gap-3 shrink-0">
      <button type="button"
              class="wm-theme-btn w-10 h-10 rounded-full flex items-center justify-center border-none"
              style="background:var(--color-panel);color:var(--color-text);box-shadow:var(--neu-shadow-sm)"
              aria-label="Alternar tema"></button>
      <a href="<?= CONF_URL_BASE ?>/home" class="text-xs" style="color:var(--color-text-muted)">
        <i class="fa-solid fa-arrow-left mr-1"></i> Voltar
      </a>
      <a href="<?= CONF_URL_BASE ?>/logout" class="text-xs" style="color:var(--color-text-muted)"
         title="Sair"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
  </header>

  <main class="max-w-2xl mx-auto px-4 sm:px-6 py-10 space-y-10">

    <!-- ── Título ── -->
    <h1 class="font-display text-2xl sm:text-3xl font-bold" style="color:var(--color-text)">
      MEU <span style="color:var(--color-cyan)">PERFIL</span>
    </h1>

    <!-- ── Feedback ── -->
    <?php if (!empty($error)): ?>
    <div class="flex items-center gap-2 px-4 py-3 rounded-xl text-sm"
         style="background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.3)">
      <i class="fa-solid fa-circle-exclamation"></i>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
    <div class="flex items-center gap-2 px-4 py-3 rounded-xl text-sm"
         style="background:rgba(34,197,94,.12);color:#4ade80;border:1px solid rgba(34,197,94,.3)">
      <i class="fa-solid fa-circle-check"></i>
      <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>

    <!-- ── Dados pessoais ── -->
    <section class="rounded-2xl p-6 space-y-6"
             style="background:var(--color-panel);box-shadow:var(--neu-shadow)">
      <h2 class="font-display text-lg font-bold tracking-wide" style="color:var(--color-text)">
        <i class="fa-solid fa-user mr-2" style="color:var(--color-cyan)"></i>Dados Pessoais
      </h2>

      <form method="POST" action="<?= CONF_URL_BASE ?>/perfil" enctype="multipart/form-data"
            class="space-y-5">

        <!-- Avatar preview + upload -->
        <div class="flex flex-col sm:flex-row items-center gap-5">
          <img id="avatar-preview" src="<?= $avatarUrl ?>" alt="Avatar"
               class="w-24 h-24 rounded-full object-cover wm-avatar-ring" />
          <div class="flex flex-col gap-2">
            <label class="text-xs font-semibold" style="color:var(--color-text-muted)">
              Foto de perfil (JPG, PNG ou WebP · máx. 2 MB)
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer px-4 py-2 rounded-full text-xs font-bold"
                   style="background:var(--color-panel);color:var(--color-text);box-shadow:var(--neu-shadow-sm)">
              <i class="fa-solid fa-upload"></i> Escolher imagem
              <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp"
                     class="sr-only" id="avatar-input" />
            </label>
          </div>
        </div>

        <!-- Nome -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-semibold" style="color:var(--color-text-muted)" for="name">Nome</label>
          <input type="text" id="name" name="name"
                 value="<?= htmlspecialchars($user->name ?? '') ?>"
                 class="wm-input w-full px-4 py-2.5 rounded-xl text-sm outline-none"
                 style="background:var(--color-bg);color:var(--color-text);border:1.5px solid var(--color-border);box-shadow:var(--neu-shadow-sm)"
                 required minlength="3" />
        </div>

        <!-- E-mail (só leitura) -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-semibold" style="color:var(--color-text-muted)">E-mail</label>
          <p class="px-4 py-2.5 rounded-xl text-sm" style="color:var(--color-text-muted);background:var(--color-bg);border:1.5px solid var(--color-border)">
            <?= htmlspecialchars($user->email ?? '') ?>
          </p>
        </div>

        <!-- Nova senha -->
        <div class="grid sm:grid-cols-2 gap-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold" style="color:var(--color-text-muted)" for="password">
              Nova Senha <span style="color:var(--color-text-muted)">(opcional)</span>
            </label>
            <input type="password" id="password" name="password" autocomplete="new-password"
                   class="wm-input w-full px-4 py-2.5 rounded-xl text-sm outline-none"
                   style="background:var(--color-bg);color:var(--color-text);border:1.5px solid var(--color-border);box-shadow:var(--neu-shadow-sm)"
                   placeholder="8+ chars, A-Z, a-z, 0-9" />
          </div>
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold" style="color:var(--color-text-muted)" for="confirm">
              Confirmar Senha
            </label>
            <input type="password" id="confirm" name="confirm" autocomplete="new-password"
                   class="wm-input w-full px-4 py-2.5 rounded-xl text-sm outline-none"
                   style="background:var(--color-bg);color:var(--color-text);border:1.5px solid var(--color-border);box-shadow:var(--neu-shadow-sm)" />
          </div>
        </div>

        <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-sm font-bold uppercase"
                style="background:var(--color-cyan);color:#fff;box-shadow:var(--glow-cyan)">
          <i class="fa-solid fa-floppy-disk"></i> Guardar Alterações
        </button>
      </form>
    </section>

    <!-- ── Géneros favoritos ── -->
    <section id="genres" class="rounded-2xl p-6 space-y-5"
             style="background:var(--color-panel);box-shadow:var(--neu-shadow)">
      <h2 class="font-display text-lg font-bold tracking-wide" style="color:var(--color-text)">
        <i class="fa-solid fa-film mr-2" style="color:var(--color-amber)"></i>Géneros Favoritos
      </h2>
      <p class="text-xs" style="color:var(--color-text-muted)">
        Selecione os géneros que prefere — a página inicial mostrará filmes dessas categorias.
      </p>

      <form method="POST" action="<?= CONF_URL_BASE ?>/perfil/genres" id="genre-form">
        <div class="flex flex-wrap gap-2 mb-5" id="genre-chips">
          <?php foreach ($genres as $g): ?>
            <?php
              $active  = in_array($g['tmdb_id'], $userTmdbIds, true);
              $classes = 'genre-chip px-3 py-1.5 rounded-full text-xs font-semibold cursor-pointer select-none';
              $style   = $active
                ? 'background:var(--color-cyan);color:#fff;box-shadow:var(--glow-cyan)'
                : 'background:var(--color-bg);color:var(--color-text-muted);box-shadow:var(--neu-shadow-sm)';
            ?>
            <span class="<?= $classes ?> <?= $active ? 'active' : '' ?>"
                  style="<?= $style ?>"
                  data-id="<?= $g['id'] ?>"
                  data-tmdb="<?= $g['tmdb_id'] ?>">
              <?= htmlspecialchars($g['name_pt']) ?>
            </span>
          <?php endforeach; ?>
        </div>

        <!-- Inputs hidden gerados via JS -->
        <div id="genre-inputs"></div>

        <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-sm font-bold uppercase"
                style="background:var(--color-amber);color:#fff;box-shadow:var(--glow-amber)">
          <i class="fa-solid fa-heart"></i> Guardar Géneros
        </button>
      </form>
    </section>

  </main>

  <script type="module" src="<?= CONF_URL_BASE ?>/themes/painel/assets/js/app.js"></script>
  <script>
    // ── Avatar preview ───────────────────────────────
    document.getElementById('avatar-input').addEventListener('change', function () {
      const file = this.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => { document.getElementById('avatar-preview').src = e.target.result; };
      reader.readAsDataURL(file);
    });

    // ── Genre chips toggle ───────────────────────────
    const selected = new Set(
      [...document.querySelectorAll('.genre-chip.active')].map(c => c.dataset.id)
    );

    document.getElementById('genre-chips').addEventListener('click', e => {
      const chip = e.target.closest('.genre-chip');
      if (!chip) return;
      const id = chip.dataset.id;
      if (selected.has(id)) {
        selected.delete(id);
        chip.classList.remove('active');
        chip.style.background = 'var(--color-bg)';
        chip.style.color      = 'var(--color-text-muted)';
        chip.style.boxShadow  = 'var(--neu-shadow-sm)';
      } else {
        selected.add(id);
        chip.classList.add('active');
        chip.style.background = 'var(--color-cyan)';
        chip.style.color      = '#fff';
        chip.style.boxShadow  = 'var(--glow-cyan)';
      }
    });

    document.getElementById('genre-form').addEventListener('submit', () => {
      const container = document.getElementById('genre-inputs');
      container.innerHTML = '';
      selected.forEach(id => {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'genre_ids[]';
        input.value = id;
        container.appendChild(input);
      });
    });
  </script>
</body>
</html>
