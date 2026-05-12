<?php

declare(strict_types=1);

namespace WebMovies\Controllers\Web;

use WebMovies\Support\Request;
use WebMovies\Support\Session;
use WebMovies\Models\Favorite;
use WebMovies\Models\Rating;

/**
 * ApiController — Endpoints JSON para favoritos e avaliações.
 *
 * POST /api/favorito  { tmdb_id, title, poster_path }  → toggle favorito
 * GET  /api/favoritos                                   → lista favoritos do user
 * POST /api/avaliar   { tmdb_id, rating (1-10) }        → salva/atualiza avaliação
 * GET  /api/avaliacao?tmdb_id=X                         → avaliação do user para um filme
 */
final class ApiController
{
    private function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function userId(): ?int
    {
        $s = new Session();
        return $s->isLoggedIn() ? (int) $s->get('user_id') : null;
    }

    // ── POST /api/favorito ────────────────────────────────────────────────

    public function favorito(Request $request): void
    {
        $userId = $this->userId();
        if (!$userId) {
            $this->json(['error' => 'Não autenticado.'], 401);
        }

        $body   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $tmdbId = filter_var($body['tmdb_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $title  = substr(strip_tags($body['title']       ?? ''), 0, 255);
        $poster = substr(strip_tags($body['poster_path'] ?? ''), 0, 255);

        if (!$tmdbId) {
            $this->json(['error' => 'tmdb_id inválido.'], 422);
        }

        try {
            $favorited = Favorite::toggle($userId, $tmdbId, $title, $poster);
            $this->json(['favorited' => $favorited]);
        } catch (\PDOException) {
            $this->json(['error' => 'Erro interno.'], 500);
        }
    }

    // ── GET /api/favoritos ────────────────────────────────────────────────

    public function favoritos(Request $request): void
    {
        $userId = $this->userId();
        if (!$userId) {
            $this->json(['error' => 'Não autenticado.'], 401);
        }

        try {
            $this->json(Favorite::allByUser($userId));
        } catch (\PDOException) {
            $this->json(['error' => 'Erro interno.'], 500);
        }
    }

    // ── POST /api/avaliar ─────────────────────────────────────────────────

    public function avaliar(Request $request): void
    {
        $userId = $this->userId();
        if (!$userId) {
            $this->json(['error' => 'Não autenticado.'], 401);
        }

        $body   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $tmdbId = filter_var($body['tmdb_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $rating = filter_var($body['rating']  ?? 0, FILTER_VALIDATE_FLOAT);

        if (!$tmdbId || $rating === false || $rating < 1 || $rating > 10) {
            $this->json(['error' => 'Dados inválidos.'], 422);
        }

        try {
            $saved = Rating::save($userId, $tmdbId, $rating);
            $this->json(['rating' => $saved]);
        } catch (\PDOException) {
            $this->json(['error' => 'Erro interno.'], 500);
        }
    }

    // ── GET /api/avaliacao?tmdb_id=X ──────────────────────────────────────

    public function avaliacao(Request $request): void
    {
        $userId = $this->userId();
        if (!$userId) {
            $this->json(['rating' => null]);
        }

        $tmdbId = filter_var($_GET['tmdb_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$tmdbId) {
            $this->json(['rating' => null]);
        }

        try {
            $this->json(['rating' => Rating::get($userId, $tmdbId)]);
        } catch (\PDOException) {
            $this->json(['rating' => null]);
        }
    }

    // ── POST /favorito/remover  (form sem JS) ─────────────────────────────

    public function removerFavorito(Request $request): void
    {
        $userId = $this->userId();
        if (!$userId) {
            header('Location: ' . CONF_URL_BASE . '/login');
            exit;
        }

        $tmdbId = filter_var($_POST['tmdb_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($tmdbId) {
            try {
                Favorite::toggle($userId, $tmdbId, '', '');
            } catch (\PDOException) { /* silencioso */ }
        }

        header('Location: ' . CONF_URL_BASE . '/home');
        exit;
    }
}
