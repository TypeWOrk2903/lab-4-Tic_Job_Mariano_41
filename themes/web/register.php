<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="<?=CONF_URL_BASE?>/themes/web/assets/css/regsiter.css">
</head>
<body>
    <main class="wm-auth">
    <div class="wm-auth__container">
        <form class="wm-auth__form" id="register-form">
            <h2 class="wm-auth__title" data-i18n="create_account">Criar Conta</h2>
            <p class="wm-auth__subtitle" data-i18n="join_us">Junte-se à nossa comunidade</p>

            <div class="wm-auth__field">
                <label for="name" data-i18n="label_name">Nome Completo</label>
                <input type="text" id="name" name="name" required placeholder="Ex: Job Mariano">
            </div>

            <div class="wm-auth__field">
                <label for="email" data-i18n="label_email">E-mail</label>
                <input type="email" id="email" name="email" required placeholder="seu@email.com">
            </div>

            <div class="wm-auth__field">
                <label for="password" data-i18n="label_password">Senha</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="wm-btn wm-btn--primary" data-i18n="btn_register">
                Registrar
            </button>

            <p class="wm-auth__footer">
                <span data-i18n="already_have_account">Já tem uma conta?</span> 
                <a href="login.php" class="wm-link" data-i18n="link_login">Fazer Login</a>
            </p>
        </form>
    </div>
</main>
</body>
</html>