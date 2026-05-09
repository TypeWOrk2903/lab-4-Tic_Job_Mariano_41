<?php

declare(strict_types=1);

namespace WebMovies\Controllers;

use WebMovies\Support\Request;

final class HomeController
{
    public function index(Request $request): void
    {
        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');

        echo '<h1>WebMovies</h1>';
        echo '<p>Projeto configurado com Composer, rotas e namespaces.</p>';
        echo '<small>Rota atual: ' . htmlspecialchars($request->path(), ENT_QUOTES, 'UTF-8') . '</small>';
    }
}
