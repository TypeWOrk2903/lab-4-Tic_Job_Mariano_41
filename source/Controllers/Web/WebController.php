<?php

declare(strict_types=1);

namespace WebMovies\Controllers\Web;

use WebMovies\Support\Request;
use WebMovies\Support\Session;
use WebMovies\Models\Genre;

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

    private function session(): Session
    {
        return new Session();
    }

    private function isLoggedIn(): bool
    {
        return $this->session()->isLoggedIn();
    }

    // ── Rotas públicas ───────────────────────────────────────────────────

    /**
     * GET /   e   GET /home
     */
    public function home(Request $request): void
    {
        $s        = $this->session();
        $loggedIn = $s->isLoggedIn();
        $userId   = (int) $s->get('user_id');

        $this->view('home', [
            'pageTitle'    => 'WebMovies – Recomendações de Filmes',
            'isLoggedIn'   => $loggedIn,
            'userLoggedIn' => $s->get('user_name'),
            'userAvatar'   => $s->get('user_avatar'),
            'genres'       => Genre::all(),
            'userTmdbIds'  => $loggedIn ? Genre::userTmdbIds($userId) : [],
        ]);
    }

    /**
     * GET /filme?id=<masked>
     * Decodifica o ID mascarado antes de passar para a view.
     */
    public function movieDetail(Request $request): void
    {
        $masked  = strip_tags($_GET['id'] ?? '');
        $movieId = unmaskMovieId($masked);

        if (!$movieId) {
            http_response_code(404);
            header('Location: ' . CONF_URL_BASE . '/');
            exit;
        }

        $this->view('movie-detail', [
            'pageTitle'  => 'Detalhes do Filme | WebMovies',
            'imdbId'     => $movieId,
            'isLoggedIn' => $this->isLoggedIn(),
        ]);
    }
}
