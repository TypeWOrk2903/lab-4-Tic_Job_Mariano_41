<?php

/**
 * CONFIGURAÇÕES DO BANCO DE DADOS
 */
define("CONF_DB_HOST", "localhost");
define("CONF_DB_USER", "root");
define("CONF_DB_PASS", "");
define("CONF_DB_NAME", "webmovies"); // Nome do BD que definimos anteriormente

/**
 * CONFIGURAÇÕES DO SITE
 */
define("CONF_SITE_NAME", "WebMovies");
define("CONF_SITE_LANG", "pt-br");
define("CONF_SITE_DOMAIN", "localhost/WebMovies");

/**
 * CONFIGURAÇÕES DE CAMINHO (URLS E DIRETÓRIOS)
 * Estas constantes facilitam a renderização do tema e assets
 */

// URL Base do projeto
define("CONF_URL_BASE", "http://localhost/WebMovies");

// Caminho para a pasta de temas (Frontend)
define("CONF_VIEW_PATH", __DIR__ . "/../../themes");

// URL para os Assets (CSS, JS, Imagens) - Use isso no seu HTML
define("CONF_URL_THEME_ASSETS", CONF_URL_BASE . "/themes/assets");

/**
 * FUNÇÃO AUXILIAR DE RENDERIZAÇÃO (Otimização)
 * Use esta função para carregar arquivos do tema de forma limpa
 */
function url_asset(string $path): string {
    return CONF_URL_THEME_ASSETS . "/" . ltrim($path, "/");
}

date_default_timezone_set("Africa/Luanda");