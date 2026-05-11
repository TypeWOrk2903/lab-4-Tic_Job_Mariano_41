<?php

declare(strict_types=1);

namespace WebMovies\Models;

use PDO;
use WebMovies\Support\Connect;

/**
 * Rating — Acesso à tabela `ratings`.
 */
final class Rating
{
    /**
     * Guarda ou atualiza a avaliação do utilizador para um filme.
     * O valor é arredondado para .0 ou .5.
     * Retorna o valor efetivamente guardado.
     */
    public static function save(int $userId, int $tmdbId, float $rating): float
    {
        $rating = round($rating * 2) / 2;

        Connect::getInstance()->prepare(
            "INSERT INTO ratings (user_id, tmdb_id, rating)
             VALUES (:u, :t, :r)
             ON DUPLICATE KEY UPDATE rating = :r2, created_at = CURRENT_TIMESTAMP"
        )->execute([':u' => $userId, ':t' => $tmdbId, ':r' => $rating, ':r2' => $rating]);

        return $rating;
    }

    /**
     * Retorna a avaliação do utilizador para um filme, ou null se não existir.
     */
    public static function get(int $userId, int $tmdbId): ?float
    {
        $stmt = Connect::getInstance()->prepare(
            "SELECT rating FROM ratings WHERE user_id = :u AND tmdb_id = :t"
        );
        $stmt->execute([':u' => $userId, ':t' => $tmdbId]);
        $row = $stmt->fetchColumn();
        return $row !== false ? (float) $row : null;
    }
}
