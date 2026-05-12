# WebMovies

Plataforma web de catálogo de filmes integrada à [TMDB API](https://www.themoviedb.org/documentation/api), com autenticação de utilizadores, favoritos, avaliações por estrelas e painel administrativo.

---

## Funcionalidades

- Catálogo de filmes via TMDB (populares, em cartaz, mais bem avaliados, lançamentos, trending)
- Hero dinâmico com carrossel automático estilo Ingresso.com
- Busca com debounce e filtro por género
- Score estilo Rotten Tomatoes (verde / misto / podre)
- Favoritos por utilizador (toggle com feedback visual)
- Avaliações de 1 a 10 via estrelas interativas
- Troca de idioma PT-BR / EN-US persistida no `localStorage`
- Autenticação completa: registo, login, recuperação de senha, logout
- Perfil com upload de avatar e preferências de géneros
- Painel Admin com configurações do site
- Suporte a JS desativado (forms com `method="POST"` como fallback)

---

## Requisitos

| Dependência | Versão mínima |
|---|---|
| PHP | 8.1+ |
| Composer | qualquer |
| Apache + `mod_rewrite` | XAMPP recomendado |
| MySQL / MariaDB | qualquer versão recente |

---

## Instalação

```bash
# 1. Clonar / extrair o projeto dentro de htdocs
cd C:/xampp/htdocs/WebMovies

# 2. Instalar dependências PHP
composer install

# 3. Criar o banco de dados
#    No phpMyAdmin (ou mysql CLI), execute o script SQL do projeto:
#    CREATE DATABASE webmovies CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
#    (rode as migrations/script SQL, se houver)

# 4. Configurar credenciais em source/support/config.php
#    CONF_DB_HOST, CONF_DB_USER, CONF_DB_PASS, CONF_DB_NAME
```

Acesse em: `http://localhost/WebMovies`

---

## Estrutura do Projeto

```
WebMovies/
├── index.php                  # Front Controller + registro de rotas
├── composer.json
├── source/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   └── AdminController.php    # Painel admin
│   │   └── Web/
│   │       ├── ApiController.php      # Endpoints JSON (favoritos, avaliações)
│   │       ├── AuthController.php     # Registo, login, logout, recuperação de senha
│   │       ├── ProfileController.php  # Perfil e preferências do utilizador
│   │       └── WebController.php      # Home e detalhe de filme
│   ├── models/
│   │   ├── Model.php        # Base PDO
│   │   ├── User.php
│   │   ├── Favorite.php
│   │   ├── Rating.php
│   │   └── Genre.php
│   └── support/
│       ├── config.php       # Constantes de BD, URL e caminhos
│       ├── Connect.php      # Singleton PDO
│       ├── helpers.php
│       ├── Request.php      # Abstração HTTP
│       ├── Router.php       # Roteador GET/POST orientado a classes
│       └── Session.php      # Gerenciamento de sessão
└── themes/
    ├── painel/              # Tema do painel admin
    │   └── assets/js/
    │       ├── movies.js        # Integração TMDB + cards + favoritos + estrelas
    │       └── translations.js  # i18n PT-BR / EN-US
    └── web/                 # Tema público (home, login, registo, perfil, filme)
```

---

## Rotas

| Método | URL | Ação |
|--------|-----|------|
| GET | `/` ou `/home` | Home pública |
| GET | `/filme?id=` | Detalhe do filme |
| GET/POST | `/register` | Registo de utilizador |
| GET/POST | `/login` | Login |
| GET/POST | `/forget` | Recuperação de senha |
| GET | `/logout` | Logout |
| GET/POST | `/perfil` | Perfil do utilizador |
| POST | `/perfil/genres` | Salvar géneros favoritos |
| POST | `/api/favorito` | Toggle favorito (JSON ou form POST) |
| GET | `/api/favoritos` | Lista favoritos do utilizador (JSON) |
| POST | `/api/avaliar` | Salvar avaliação 1-10 (JSON ou form POST) |
| GET | `/api/avaliacao` | Avaliação do utilizador para um filme (JSON) |
| GET | `/admin` | Dashboard admin |
| GET/POST | `/admin/settings` | Configurações do site |

---

## Configuração (`source/support/config.php`)

```php
define('CONF_DB_HOST', 'localhost');
define('CONF_DB_USER', 'root');
define('CONF_DB_PASS', '');
define('CONF_DB_NAME', 'webmovies');

define('CONF_URL_BASE',  'http://localhost/WebMovies');
define('CONF_SITE_NAME', 'WebMovies');
define('CONF_SITE_LANG', 'pt-br');
```

---

## Adicionando uma Nova Rota

1. Crie o controller em `source/Controllers/` com namespace `WebMovies\Controllers\`.
2. Registre a rota no `index.php`:

```php
$router->get('/exemplo', [ExemploController::class, 'index']);
$router->post('/exemplo', [ExemploController::class, 'store']);
```

3. O método recebe um `Request $request` e pode chamar `extract()` + `require` de uma view em `themes/`.

---

## Tecnologias

- **Back-end:** PHP 8.1, PDO, Composer, PSR-4
- **Front-end:** JavaScript (ES Modules), Font Awesome, CSS custom properties
- **API:** TMDB API v3
- **Servidor:** Apache + `mod_rewrite` (XAMPP)
