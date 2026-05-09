<?php
/**
 * @var string      $pageTitle
 * @var string      $adminName
 * @var string|null $success
 * @var string|null $error
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="<?= CONF_URL_BASE ?>/themes/painel/assets/css/admin.css" />
</head>
<body>

<div class="adm-layout">

  <!-- ── TOP BAR ──────────────────────────────── -->
  <header class="adm-topbar">
    <div class="adm-logo">Web<em>Movies</em> <span style="font-size:.6rem;letter-spacing:.08em;opacity:.5;vertical-align:middle">ADMIN</span></div>
    <div class="adm-topbar-right">
      <a href="<?= CONF_URL_BASE ?>/" target="_blank" style="font-size:.75rem;color:var(--adm-muted)">
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
      </a>
      <div class="adm-avatar"><?= htmlspecialchars(strtoupper(mb_substr($adminName, 0, 1))) ?></div>
    </div>
  </header>

  <!-- ── SIDEBAR ──────────────────────────────── -->
  <aside class="adm-sidebar" role="navigation" aria-label="Menu do painel">
    <a href="<?= CONF_URL_BASE ?>/admin" class="adm-nav-item">
      <i class="fa-solid fa-gauge-high"></i> Dashboard
    </a>
    <a href="<?= CONF_URL_BASE ?>/admin/settings" class="adm-nav-item active">
      <i class="fa-solid fa-gear"></i> Configurações
    </a>
    <div class="adm-nav-divider"></div>
    <a href="<?= CONF_URL_BASE ?>/admin/logout" class="adm-nav-item"
       style="color:var(--adm-danger)"
       onclick="return confirm('Sair do painel?')">
      <i class="fa-solid fa-right-from-bracket"></i> Sair
    </a>
  </aside>

  <!-- ── CONTEÚDO PRINCIPAL ────────────────────── -->
  <main class="adm-main" id="main-content">
    <h1 class="adm-page-title">Configurações <span>/ Painel</span></h1>

    <?php if ($success): ?>
    <div class="adm-alert adm-alert--success">
      <i class="fa-solid fa-circle-check"></i>
      <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="adm-alert adm-alert--error">
      <i class="fa-solid fa-circle-exclamation"></i>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= CONF_URL_BASE ?>/admin/settings"
          style="max-width:540px;background:var(--adm-panel);border:1px solid var(--adm-border);
                 border-radius:var(--adm-radius);padding:1.75rem;box-shadow:var(--adm-shadow)">

      <div class="adm-form-group">
        <label for="site_name" class="adm-form-label">Nome do Site</label>
        <input id="site_name" name="site_name" type="text" class="adm-form-input"
               value="<?= htmlspecialchars(CONF_SITE_NAME) ?>" placeholder="WebMovies" />
      </div>

      <div class="adm-form-group">
        <label for="omdb_key" class="adm-form-label">Chave OMDb API</label>
        <input id="omdb_key" name="omdb_key" type="text" class="adm-form-input"
               value="d1e10648" autocomplete="off" placeholder="xxxxxxxx" />
        <p style="margin-top:.35rem;font-size:.72rem;color:var(--adm-muted)">
          Obtenha uma chave gratuita em
          <a href="https://www.omdbapi.com/apikey.aspx" target="_blank" rel="noopener">omdbapi.com</a>.
        </p>
      </div>

      <div class="adm-form-group">
        <label for="base_url" class="adm-form-label">URL base do site</label>
        <input id="base_url" name="base_url" type="url" class="adm-form-input"
               value="<?= htmlspecialchars(CONF_URL_BASE) ?>" placeholder="http://localhost/WebMovies" />
      </div>

      <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:.5rem">
        <button type="submit" class="adm-btn adm-btn--primary">
          <i class="fa-solid fa-floppy-disk"></i> Salvar
        </button>
        <a href="<?= CONF_URL_BASE ?>/admin" class="adm-btn adm-btn--ghost">
          <i class="fa-solid fa-xmark"></i> Cancelar
        </a>
      </div>

    </form>

  </main>
</div>

</body>
</html>
