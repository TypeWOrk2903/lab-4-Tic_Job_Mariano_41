<?php /** @var string $pageTitle @var string|null $error */ ?>
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
<body class="dark-theme font-sans min-h-screen flex items-center justify-center px-4"
      style="background:var(--bg-radial),var(--color-bg)">

  <div class="w-full max-w-sm">

    <!-- Logo -->
    <div class="text-center mb-8">
      <a href="<?= CONF_URL_BASE ?>/"
         class="inline-flex font-display text-3xl font-bold tracking-wider">
        <span style="color:var(--color-text)">Web</span>
        <span style="color:var(--color-cyan)">Movies</span>
      </a>
      <p class="mt-2 text-sm" style="color:var(--color-text-muted)">
        Entre para favoritar e receber recomendações personalizadas.
      </p>
    </div>

    <!-- Card neumórfico -->
    <div class="rounded-2xl p-8" style="background:var(--color-panel);box-shadow:0 2px 8px rgba(0,0,0,0.1)">

      <?php if ($error): ?>
      <div class="flex items-center gap-2 mb-5 px-4 py-3 rounded-xl text-sm"
           style="background:rgba(244,63,94,.12);color:#f43f5e;border:1px solid rgba(244,63,94,.25)">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="<?= CONF_URL_BASE ?>/login" novalidate>

        <!-- E-mail -->
        <div class="mb-4">
          <label for="email" class="block text-xs font-semibold mb-1.5 tracking-wide uppercase"
                 style="color:var(--color-text-muted)">E-mail</label>
          <div class="relative">
            <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-xs pointer-events-none"
               style="color:var(--color-text-muted)"></i>
            <input id="email" name="email" type="email" required
                   class="w-full pl-9 pr-4 py-2.5 rounded-lg text-sm outline-none"
                   style="background:var(--color-bg);color:var(--color-text);
                          border:1.5px solid var(--color-border);box-shadow:inset 0 1px 3px rgba(0,0,0,0.05)"
                   placeholder="seu@email.com" />
          </div>
        </div>

        <!-- Senha -->
        <div class="mb-6">
          <label for="password" class="block text-xs font-semibold mb-1.5 tracking-wide uppercase"
                 style="color:var(--color-text-muted)">Senha</label>
          <div class="relative">
            <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-xs pointer-events-none"
               style="color:var(--color-text-muted)"></i>
            <input id="password" name="password" type="password" required minlength="6"
                   class="w-full pl-9 pr-4 py-2.5 rounded-full text-sm outline-none"
                   style="background:var(--color-bg);color:var(--color-text);
                          border:1.5px solid var(--color-border);box-shadow:inset 0 1px 3px rgba(0,0,0,0.05)"
                   placeholder="••••••••" />
          </div>
        </div>

        <button type="submit"
                class="w-full py-2.5 rounded-full text-sm font-bold uppercase tracking-wider
                       cursor-pointer border-none hover:brightness-110 active:scale-95 transition-all duration-150"
                style="background:var(--color-cyan);color:#fff;box-shadow:0 4px 12px rgba(0,255,255,0.25)">
          <i class="fa-solid fa-right-to-bracket mr-1"></i> Entrar
        </button>

      </form>
    </div>

    <p class="text-center mt-6 text-xs" style="color:var(--color-text-muted)">
      Ainda não tem conta?
      <a href="#" style="color:var(--color-cyan)" class="hover:underline">Cadastre-se</a>
    </p>
    <p class="text-center mt-2">
      <a href="<?= CONF_URL_BASE ?>/" class="text-xs" style="color:var(--color-text-subtle)">
        <i class="fa-solid fa-arrow-left text-[0.6rem]"></i> Voltar ao catálogo
      </a>
    </p>
  </div>

  <script>
    // Aplica dark-theme do localStorage antes do flash
    const t = localStorage.getItem('wm-theme');
    if (t) { document.body.classList.remove('dark-theme','light-theme'); document.body.classList.add(t); }
  </script>
</body>
</html>
