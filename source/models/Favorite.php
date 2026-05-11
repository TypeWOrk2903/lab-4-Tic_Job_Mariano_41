<?php

declare(strict_types=1);

namespace WebMovies\Models;

use PDO;
use PDOException;
use WebMovies\Support\Connect;

/**
 * Favorite — Acesso à tabela `favorites`.
 */
final class Favorite
{
    /**
     * Toggle favorito: remove se existir, insere se não existir.
     * Retorna true se ficou favoritado, false se foi removido.
     */
    public static function toggle(int $userId, int $tmdbId, string $title, string $posterPath): bool
    {
        $pdo = Connect::getInstance();

        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = :u AND tmdb_id = :t");
        $stmt->execute([':u' => $userId, ':t' => $tmdbId]);

        if ($stmt->fetchColumn()) {
            $pdo->prepare("DELETE FROM favorites WHERE user_id = :u AND tmdb_id = :t")
                ->execute([':u' => $userId, ':t' => $tmdbId]);
            return false;
        }

        $pdo->prepare(
            "INSERT INTO favorites (user_id, tmdb_id, title, poster_path)
             VALUES (:u, :t, :ti, :p)"
        )->execute([':u' => $userId, ':t' => $tmdbId, ':ti' => $title, ':p' => $posterPath]);

        return true;
    }

    /**
     * Retorna todos os favoritos do utilizador ordenados por data desc.
     * @return array<int, array{tmdb_id: int, title: string, poster_path: string}>
     */
    public static function allByUser(int $userId): array
    {
        $stmt = Connect::getInstance()->prepare(
            "SELECT tmdb_id, title, poster_path
               FROM favorites
              WHERE user_id = :u
              ORDER BY created_at DESC"
        );
        $stmt->execute([':u' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
