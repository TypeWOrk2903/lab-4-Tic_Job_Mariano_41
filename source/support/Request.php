<?php

declare(strict_types=1);

namespace WebMovies\Support;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $path
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return new self($method, self::normalizePath($path));
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    private static function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');

        return $normalized === '/' ? $normalized : rtrim($normalized, '/');
    }
}
