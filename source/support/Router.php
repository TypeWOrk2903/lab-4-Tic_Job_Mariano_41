<?php

declare(strict_types=1);

namespace WebMovies\Support;

use Closure;
use RuntimeException;

final class Router
{
    /**
     * @var array<string, array<string, callable|array{class-string, string}|Closure>>
     */
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo '404 - Rota nao encontrada';
            return;
        }

        if (is_array($handler) && isset($handler[0], $handler[1]) && is_string($handler[0])) {
            $className = $handler[0];
            $methodName = $handler[1];

            if (!class_exists($className)) {
                throw new RuntimeException("Controller {$className} nao encontrado.");
            }

            $controller = new $className();

            if (!method_exists($controller, $methodName)) {
                throw new RuntimeException("Metodo {$methodName} nao existe em {$className}.");
            }

            $controller->{$methodName}($request);
            return;
        }

        $handler($request);
    }

    private function addRoute(string $httpMethod, string $path, callable|array $handler): void
    {
        $normalized = '/' . trim($path, '/');
        $normalized = $normalized === '/' ? $normalized : rtrim($normalized, '/');

        $this->routes[$httpMethod][$normalized] = $handler;
    }
}
