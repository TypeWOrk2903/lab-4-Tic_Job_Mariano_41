<?php

declare(strict_types=1);

namespace WebMovies\Controllers\Web;

use WebMovies\Support\Request;

/**
 * WebController — Área pública do WebMovies.
 *
 * Rotas:
 *   GET  /       → home()
 *   GET  /filme  → movieDetail()
 *
 * Autenticação delegada ao AuthController.
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
     * GET /filme?id=...
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
}
