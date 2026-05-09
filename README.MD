# WebMovies

Projeto PHP configurado com:

- Composer
- Autoload PSR-4 (namespaces)
- Front Controller (`index.php`)
- Sistema de rotas orientado a classes

## Requisitos

- PHP 8.1+
- Composer instalado globalmente
- Apache com `mod_rewrite` ativo (XAMPP)

## Estrutura usada

- `source/Controllers`: controllers
- `source/models`: modelos (dominio)
- `source/support`: classes de suporte (router, request, etc.)

## Instalar dependencias e gerar autoload

```bash
composer install
```

## Como funciona

1. O `index.php` carrega `vendor/autoload.php`.
2. O `Router` registra as rotas.
3. Cada rota aponta para um metodo de classe (controller).
4. O `.htaccess` redireciona URLs para o front controller.

## Exemplo de rota

No `index.php`:

```php
$router->get('/', [HomeController::class, 'index']);
```

No controller:

```php
namespace WebMovies\Controllers;

final class HomeController
{
	public function index(\WebMovies\Support\Request $request): void
	{
		echo 'WebMovies';
	}
}
```

## Adicionando nova rota

1. Crie o controller em `source/Controllers`.
2. Use namespace `WebMovies\Controllers`.
3. Registre no `index.php` com `$router->get(...)` ou `$router->post(...)`.
