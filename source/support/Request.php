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
        $path = self::resolvePath();

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

    private static function resolvePath(): string
    {
        $route = $_GET['route'] ?? null;

        if (is_string($route) && $route !== '') {
            return $route;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = str_replace('\\', '/', dirname($scriptName));
        $basePath = $basePath === '/' || $basePath === '.' ? '' : rtrim($basePath, '/');

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
            $path = $path === '' ? '/' : $path;
        }

        return $path;
    }
}
