<?php

declare(strict_types=1);

namespace WebMovies\Controllers\Web;

use WebMovies\Models\User;
use WebMovies\Models\Genre;
use WebMovies\Support\Request;
use WebMovies\Support\Session;

/**
 * ProfileController — Perfil do utilizador.
 *
 * Rotas:
 *   GET  /perfil         → show()
 *   POST /perfil         → update()
 *   POST /perfil/genres  → saveGenres()
 */
final class ProfileController
{
    private function session(): Session
    {
        return new Session();
    }

    private function requireLogin(): void
    {
        if (!$this->session()->isLoggedIn()) {
            header('Location: ' . CONF_URL_BASE . '/login');
            exit;
        }
    }

    private function redirect(string $path, string $fragment = ''): never
    {
        header('Location: ' . CONF_URL_BASE . $path . $fragment);
        exit;
    }

    private function view(string $template, array $data = []): void
    {
        $viewPath = CONF_VIEW_PATH . '/web/' . ltrim($template, '/') . '.php';
        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "View não encontrada: {$viewPath}";
            return;
        }
        extract($data, EXTR_SKIP);
        require $viewPath;
    }

    // ── GET /perfil ───────────────────────────────────────────────────────

    public function show(Request $request): void
    {
        $this->requireLogin();
        $s      = $this->session();
        $userId = (int) $s->get('user_id');

        $user   = (new User())->findById($userId);
        $genres = Genre::all();
        $userTmdbIds = Genre::userTmdbIds($userId);

        $this->view('perfil', [
            'pageTitle'    => 'Meu Perfil | WebMovies',
            'isLoggedIn'   => true,
            'userLoggedIn' => $s->get('user_name'),
            'user'         => $user,
            'genres'       => $genres,
            'userTmdbIds'  => $userTmdbIds,
            'error'        => $s->get('profile_error'),
            'success'      => $s->get('profile_success'),
        ]);

        $s->remove('profile_error')->remove('profile_success');
    }

    // ── POST /perfil ──────────────────────────────────────────────────────

    public function update(Request $request): void
    {
        $this->requireLogin();
        $s      = $this->session();
        $userId = (int) $s->get('user_id');

        $name    = trim($_POST['name']     ?? '');
        $pass    = $_POST['password']      ?? '';
        $confirm = $_POST['confirm']       ?? '';

        // Valida nome
        if (mb_strlen($name) < 3) {
            $s->set('profile_error', 'O nome deve ter pelo menos 3 caracteres.');
            $this->redirect('/perfil');
        }

        // Valida senha (só se preenchida)
        if ($pass !== '') {
            if (mb_strlen($pass) < 8
                || !preg_match('/[A-Z]/', $pass)
                || !preg_match('/[a-z]/', $pass)
                || !preg_match('/[0-9]/', $pass)
            ) {
                $s->set('profile_error', 'Senha deve ter 8+ caracteres, maiúscula, minúscula e número.');
                $this->redirect('/perfil');
            }
            if ($pass !== $confirm) {
                $s->set('profile_error', 'As senhas não coincidem.');
                $this->redirect('/perfil');
            }
        }

        // Avatar upload
        $avatarPath = null;
        if (!empty($_FILES['avatar']['tmp_name'])) {
            [$ok, $payload] = $this->handleAvatarUpload($_FILES['avatar'], $userId);
            if (!$ok) {
                $s->set('profile_error', $payload);
                $this->redirect('/perfil');
            }
            $avatarPath = $payload;
        }

        $userModel = new User();

        // Atualiza perfil (nome + senha)
        $userModel->updateProfile($userId, $name, $pass !== '' ? $pass : null);

        // Atualiza avatar se enviado
        if ($avatarPath !== null) {
            $userModel->updateAvatar($userId, $avatarPath);
            $s->set('user_avatar', $avatarPath);
        }

        // Atualiza nome na sessão
        $s->set('user_name', $name);

        $s->set('profile_success', 'Perfil atualizado com sucesso!');
        $this->redirect('/perfil');
    }

    // ── POST /perfil/genres ───────────────────────────────────────────────

    public function saveGenres(Request $request): void
    {
        $this->requireLogin();
        $s      = $this->session();
        $userId = (int) $s->get('user_id');

        // genre_ids são os IDs internos da tabela generos
        $raw      = $_POST['genre_ids'] ?? [];
        $genreIds = array_map('intval', is_array($raw) ? $raw : []);

        Genre::syncUser($userId, $genreIds);

        $s->set('profile_success', 'Preferências de género guardadas!');
        $this->redirect('/perfil', '#genres');
    }

    // ── Upload de avatar (privado) ────────────────────────────────────────

    /**
     * Valida e move o ficheiro de avatar.
     * @return array{0:bool, 1:string}  [true, caminho] em sucesso | [false, mensagem_erro]
     */
    private function handleAvatarUpload(array $file, int $userId): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [false, 'Erro no upload do ficheiro.'];
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return [false, 'A imagem deve ter no máximo 2 MB.'];
        }

        $mime    = mime_content_type($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            return [false, 'Formato não suportado. Use JPG, PNG ou WebP.'];
        }

        $ext      = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        };
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $dir      = __DIR__ . '/../../../themes/web/assets/uploads/avatars/';
        $dest     = $dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return [false, 'Falha ao guardar a imagem. Verifique as permissões da pasta.'];
        }

        return [true, 'uploads/avatars/' . $filename];
    }
}
