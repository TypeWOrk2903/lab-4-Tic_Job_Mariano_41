<?php

declare(strict_types=1);

namespace WebMovies\Controllers\Web;

use WebMovies\Models\User;
use WebMovies\Support\Request;

/**
 * AuthController — Autenticação da área pública.
 *
 * Rotas:
 *   GET  /register → registerForm()
 *   POST /register → registerSubmit()
 *   GET  /login    → loginForm()
 *   POST /login    → loginSubmit()
 *   GET  /forget   → forgetForm()
 *   POST /forget   → forgetSubmit()
 *   GET  /logout   → logOut()
 */
final class AuthController
{
    // ── Sessão ───────────────────────────────────────────────────────────

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function isLoggedIn(): bool
    {
        $this->startSession();
        return !empty($_SESSION['user_id']);
    }

    // ── View helper ──────────────────────────────────────────────────────

    private function view(string $template, array $data = []): void
    {
        $viewPath = CONF_VIEW_PATH . '/web/' . ltrim($template, '/') . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "View não encontrada: {$viewPath}";
            return;
        }

        extract($data, EXTR_SKIP);
        require $viewPath;
    }

    private function redirect(string $path): never
    {
        header('Location: ' . CONF_URL_BASE . $path);
        exit;
    }

    // ── Registro ─────────────────────────────────────────────────────────

    public function registerForm(Request $request): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/');
        }

        $this->startSession();
        $this->view('register', [
            'pageTitle' => 'Criar Conta | WebMovies',
            'error'     => $_SESSION['auth_error'] ?? null,
            'success'   => $_SESSION['auth_success'] ?? null,
            'old'       => $_SESSION['auth_old'] ?? [],
        ]);

        unset($_SESSION['auth_error'], $_SESSION['auth_success'], $_SESSION['auth_old']);
    }

    public function registerSubmit(Request $request): void
    {
        $this->startSession();

        $name    = trim($_POST['name']     ?? '');
        $email   = trim($_POST['email']    ?? '');
        $pass    = $_POST['password']      ?? '';
        $confirm = $_POST['confirm']       ?? '';

        // ── Validações ──────────────────────────────────────────────────
        if (mb_strlen($name) < 3) {
            $this->flashError('O nome deve ter pelo menos 3 caracteres.', compact('name', 'email'));
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Informe um e-mail válido.', compact('name', 'email'));
            $this->redirect('/register');
        }

        if (!$this->isStrongPassword($pass)) {
            $this->flashError(
                'A senha deve ter no mínimo 8 caracteres, letra maiúscula, letra minúscula e um número.',
                compact('name', 'email')
            );
            $this->redirect('/register');
        }

        if ($pass !== $confirm) {
            $this->flashError('As senhas não coincidem.', compact('name', 'email'));
            $this->redirect('/register');
        }

        // ── Verifica e-mail único ────────────────────────────────────────
        $userModel = new User();

        if ($userModel->emailExists($email)) {
            $this->flashError('Este e-mail já está em uso.', compact('name', 'email'));
            $this->redirect('/register');
        }

        // ── Persiste ────────────────────────────────────────────────────
        $userModel->registerdata($name, $email, $pass);

        if (!$userModel->save()) {
            $this->flashError('Erro ao criar conta. Tente novamente mais tarde.', compact('name', 'email'));
            $this->redirect('/register');
        }

        // Login automático após cadastro bem-sucedido
        $newUser = (new User())->findByEmail($email);

        if (!$newUser) {
            $this->flashError('Conta criada, mas não foi possível iniciar sessão. Tente fazer login.', []);
            $this->redirect('/login');
        }

        $this->createSession($newUser);
        $this->redirect('/admin');
    }

    // ── Login ─────────────────────────────────────────────────────────────

    public function loginForm(Request $request): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/');
        }

        $this->startSession();
        $this->view('login', [
            'pageTitle'       => 'Entrar | WebMovies',
            'error'           => $_SESSION['auth_error'] ?? null,
            'old'             => $_SESSION['auth_old'] ?? [],
            'showForgetLink'  => ($_SESSION['login_attempts'] ?? 0) >= 3,
        ]);

        unset($_SESSION['auth_error'], $_SESSION['auth_old']);
    }

    public function loginSubmit(Request $request): void
    {
        $this->startSession();

        $email = trim($_POST['email']    ?? '');
        $pass  = $_POST['password']      ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
            $this->flashError('Preencha todos os campos.', ['email' => $email]);
            $this->redirect('/login');
        }

        $user = (new User())->auth($email, $pass);

        if (!$user) {
            // Incrementa contador de tentativas
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            // Mensagem genérica — não revela se o e-mail existe (OWASP A07)
            $this->flashError('E-mail ou senha incorretos.', ['email' => $email]);
            $this->redirect('/login');
        }

        // Reset do contador após login bem-sucedido
        unset($_SESSION['login_attempts']);
        $this->createSession($user);
        $this->redirect('/admin');
    }

    // ── Recuperação de senha ──────────────────────────────────────────────

    public function forgetForm(Request $request): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/');
        }

        $this->startSession();
        $this->view('forget', [
            'pageTitle' => 'Recuperar Senha | WebMovies',
            'error'     => $_SESSION['auth_error']   ?? null,
            'success'   => $_SESSION['auth_success'] ?? null,
        ]);

        unset($_SESSION['auth_error'], $_SESSION['auth_success']);
    }

    public function forgetSubmit(Request $request): void
    {
        $this->startSession();

        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['auth_error'] = 'Informe um e-mail válido.';
            $this->redirect('/forget');
        }

        // Gera o token (internamente); não revela se o e-mail existe
        (new User())->generateForgetToken($email);

        // Resposta sempre genérica — previne enumeração de e-mails (OWASP A07)
        $_SESSION['auth_success'] = 'Se este e-mail estiver cadastrado, você receberá as instruções em breve.';
        $this->redirect('/forget');
    }

    // ── Logout ────────────────────────────────────────────────────────────

    public function logOut(Request $request): void
    {
        $this->startSession();
        session_destroy();
        $this->redirect('/');
    }

    // ── Helpers privados ─────────────────────────────────────────────────

    /**
     * Cria a sessão autenticada.
     * Inclui o filtro parental para controle da TMDB API no frontend.
     * Regenera o ID de sessão para prevenir Session Fixation (OWASP A07).
     */
    private function createSession(User $user): void
    {
        $_SESSION['user_id']         = $user->id;
        $_SESSION['user_name']       = $user->name;
        $_SESSION['user_email']      = $user->email;
        $_SESSION['user_role']       = 'admin';
        $_SESSION['parental_filter'] = true;
    }

    /**
     * Armazena mensagem de erro e dados antigos do formulário na sessão.
     */
    private function flashError(string $message, array $old = []): void
    {
        $_SESSION['auth_error'] = $message;
        $_SESSION['auth_old']   = $old;
    }

    /**
     * Valida senha forte:
     *  - Mínimo 8 caracteres
     *  - Pelo menos uma letra maiúscula
     *  - Pelo menos uma letra minúscula
     *  - Pelo menos um número
     *  - Pelo menos um caractere especial
     */
    private function isStrongPassword(string $password): bool
    {
        return mb_strlen($password) >= 8
            && preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/[0-9]/', $password) === 1;
    }
}
