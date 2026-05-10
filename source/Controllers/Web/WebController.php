<?php

declare(strict_types=1);

namespace WebMovies\Controllers\Web;

use WebMovies\Support\Request;

/**
 * WebController — Área pública do WebMovies.
 *
 * Rotas:
 *   GET  /              → home()
 *   GET  /filme         → movieDetail()   (?id=tt1234567)
 *   GET  /login         → loginForm()
 *   POST /login         → loginSubmit()
 *   GET  /logout        → logOut()
 */
final class WebController
{
    // ── View helper ─────────────────────────────────────────────────────

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

    // ── Acesso à sessão ──────────────────────────────────────────────────

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

    // ── Rotas públicas ───────────────────────────────────────────────────

    /**
     * GET /
     * Página inicial: catálogo de recomendações + barra de busca.
     */
    public function home(Request $request): void
    {
        $this->view('home', [
            'pageTitle'    => 'WebMovies – Recomendações de Filmes',
            'isLoggedIn'   => $this->isLoggedIn(),
            'userLoggedIn' => $_SESSION['user_name'] ?? null,
        ]);
    }

    /**
     * GET /filme?id=tt1234567
     * Detalhes de um filme (consumidos via JS na OMDb API).
     */
    public function movieDetail(Request $request): void
    {
        $imdbId = htmlspecialchars(
            strip_tags($_GET['id'] ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $this->view('movie-detail', [
            'pageTitle'  => 'Detalhes do Filme | WebMovies',
            'imdbId'     => $imdbId,
            'isLoggedIn' => $this->isLoggedIn(),
        ]);
    }

    // ── Autenticação ─────────────────────────────────────────────────────

    public function registerForm() : void {
            if ($this->isLoggedIn()) {
            header('Location: ' . CONF_URL_BASE . '/');
            exit;
        }

        $this->view('register', [
            'pageTitle' => 'Cadastrar-se | WebMovies',
            'error'     => $_SESSION['login_error'] ?? null,
        ]);

        unset($_SESSION['login_error']);
    }
    /**
     * GET /login
     * Exibe o formulário de login.
     */
    public function loginForm(Request $request): void
    {
        if ($this->isLoggedIn()) {
            header('Location: ' . CONF_URL_BASE . '/');
            exit;
        }

        $this->view('login', [
            'pageTitle' => 'Entrar | WebMovies',
            'error'     => $_SESSION['login_error'] ?? null,
        ]);

        unset($_SESSION['login_error']);
    }

    /**
     * POST /login
     * Processa o formulário de login (validação básica; BD a implementar).
     */
    public function loginSubmit(Request $request): void
    {
        $this->startSession();

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $pass  = $_POST['password'] ?? '';

        if (!$email || strlen($pass) < 6) {
            $_SESSION['login_error'] = 'Credenciais inválidas.';
            header('Location: ' . CONF_URL_BASE . '/login');
            exit;
        }

        // TODO: validar contra BD com password_verify()
        // Simulação — substituir pela consulta real ao Model
        $_SESSION['login_error'] = 'Usuário ou senha incorretos.';
        header('Location: ' . CONF_URL_BASE . '/login');
        exit;
    }

    /**
     * GET /logout
     */
    public function logOut(Request $request): void
    {
        $this->startSession();
        session_destroy();
        header('Location: ' . CONF_URL_BASE . '/');
        exit;
    }
}
