<?php

/**
 * helpers.php — Funções utilitárias globais do WebMovies.
 * Carregado automaticamente pelo Composer (autoload.files).
 */

/**
 * Mascara o ID de um filme para uso seguro em URLs.
 * Usa base64 URL-safe + salt para ofuscação.
 *
 * Correspondente JS em movies.js (buildCard click handler).
 *
 * @param  int|string $movieId  ID numérico da TMDB
 * @return string               Token URL-safe
 */
function maskMovieId(int|string $movieId): string
{
    $salt = 'webmovies_ipil_2026';
    $raw  = "{$movieId}|{$salt}";
    return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
}

/**
 * Decodifica e valida um ID de filme mascarado.
 * Retorna null se o token for inválido ou adulterado.
 *
 * @param  string $masked  Token URL-safe vindo da URL (?id=...)
 * @return int|null        ID da TMDB ou null em caso de falha
 */
function unmaskMovieId(string $masked): ?int
{
    if ($masked === '') return null;

    $salt    = 'webmovies_ipil_2026';
    $padded  = strtr($masked, '-_', '+/');
    $mod     = strlen($padded) % 4;
    if ($mod) {
        $padded .= str_repeat('=', 4 - $mod);
    }

    $decoded = base64_decode($padded, strict: true);
    if ($decoded === false) return null;

    $parts = explode('|', $decoded, 2);
    if (count($parts) !== 2 || $parts[1] !== $salt) return null;

    $id = filter_var($parts[0], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id !== false ? (int) $id : null;
}
