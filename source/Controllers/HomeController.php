<?php

declare(strict_types=1);

namespace WebMovies\Controllers;

use WebMovies\Support\Request;

final class HomeController
{
    public function index(Request $request): void
    {
       $head = [
            "title" => "Home | " . CONF_SITE_NAME,
            "description" => "Assista aos melhores filmes no WebMovies"
        ];

        // Conecta com o HTML na pasta themes usando a constante que criamos
        // O include faz o PHP "renderizar" o arquivo themes/index.php
        require CONF_VIEW_PATH . "/dashboard.php";
    }
}
