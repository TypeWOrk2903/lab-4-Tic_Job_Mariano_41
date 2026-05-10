<?php

declare(strict_types=1);

namespace WebMovies\Models;

use PDO;
use WebMovies\Support\Connect;

/**
 * Genre — Acesso à tabela generos e preferências do utilizador (user_genres).
 */
final class Genre
{
    // ── Todos os géneros ─────────────────────────────────────────────────

    /** Retorna todos os géneros ordenados por name_pt. */
    public static function all(): array
    {
        $pdo = Connect::getInstance();
        if (!$pdo) return [];

        $stmt = $pdo->query(
            "SELECT id, tmdb_id, name_pt, name_en, slug FROM generos ORDER BY name_pt"
        );
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    // ── Preferências do utilizador ───────────────────────────────────────

    /**
     * Retorna os tmdb_ids dos géneros preferidos do utilizador.
     * @return int[]
     */
    public static function userTmdbIds(int $userId): array
    {
        $pdo = Connect::getInstance();
        if (!$pdo) return [];

        $stmt = $pdo->prepare(
            "SELECT g.tmdb_id
               FROM user_genres ug
               JOIN generos g ON g.id = ug.genre_id
              WHERE ug.user_id = :uid"
        );
        $stmt->execute([':uid' => $userId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'tmdb_id');
    }

    /**
     * Sincroniza as preferências do utilizador:
     * apaga as antigas e insere as novas (array de genre_ids internos).
     * @param int[] $genreIds  IDs da tabela generos (não tmdb_id)
     */
    public static function syncUser(int $userId, array $genreIds): bool
    {
        $pdo = Connect::getInstance();
        if (!$pdo) return false;

        try {
            $pdo->beginTransaction();

            $pdo->prepare("DELETE FROM user_genres WHERE user_id = :uid")
                ->execute([':uid' => $userId]);

            if ($genreIds) {
                $stmt = $pdo->prepare(
                    "INSERT IGNORE INTO user_genres (user_id, genre_id) VALUES (:uid, :gid)"
                );
                foreach ($genreIds as $gid) {
                    $stmt->execute([':uid' => $userId, ':gid' => (int) $gid]);
                }
            }

            $pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $pdo->rollBack();
            return false;
        }
    }
}
