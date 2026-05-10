<?php

declare(strict_types=1);

namespace WebMovies\Controllers\Admin;

use WebMovies\Support\Request;
use WebMovies\Support\Connect;

/**
 * AdminController — Painel de controlo (acesso restrito).
 *
 * Rotas:
 *   GET  /admin             → dashboard()
 *   GET  /admin/settings    → settings()
 *   POST /admin/settings    → saveSettings()
 *   GET  /admin/logout      → logOut()
 *
 * Toda rota invoca requireAuth() antes de processar.
 */
final class AdminController
{
    // ── Sessão / Autenticação ────────────────────────────────────────────

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Bloqueia o acesso se o utilizador não for administrador.
     * Redireciona para /login em caso de sessão inválida.
     */
    private function requireAuth(): void
    {
        $this->startSession();

        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: ' . CONF_URL_BASE . '/login');
            exit;
        }
    }

    // ── View helper ──────────────────────────────────────────────────────

    private function view(string $template, array $data = []): void
    {
        $viewPath = CONF_VIEW_PATH . '/painel/' . ltrim($template, '/') . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "View não encontrada: {$viewPath}";
            return;
        }

        extract($data, EXTR_SKIP);
        require $viewPath;
    }

    // ── Estatísticas ─────────────────────────────────────────────────────

    /**
     * Retorna estatísticas básicas do banco para o dashboard.
     * Retorna array com zeros se o BD não estiver disponível.
     */
    private function fetchStats(): array
    {
        $pdo = Connect::getInstance();
        if (!$pdo) {
            return ['total_favorites' => 0, 'total_users' => 0, 'recent_activity' => []];
        }

        try {
            $totalFav   = (int) $pdo->query('SELECT COUNT(*) FROM favorites')->fetchColumn();
            $totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

            $recent = $pdo
                ->query(
                    "SELECT u.name, f.imdb_id, f.created_at
                     FROM favorites f
                     JOIN users u ON u.id = f.user_id
                     ORDER BY f.created_at DESC
                     LIMIT 10"
                )
                ->fetchAll();

            return [
                'total_favorites' => $totalFav,
                'total_users'     => $totalUsers,
                'recent_activity' => $recent,
            ];
        } catch (\PDOException) {
            return ['total_favorites' => 0, 'total_users' => 0, 'recent_activity' => []];
        }
    }

    // ── Rotas ────────────────────────────────────────────────────────────

    /**
     * GET /admin
     * Dashboard principal com estatísticas e atividade recente.
     */
    public function dashboard(Request $request): void
    {
        $this->requireAuth();

        $this->view('dashboard', [
            'pageTitle'  => 'Dashboard | WebMovies Admin',
            'adminName'  => $_SESSION['user_name'] ?? 'Admin',
            'stats'      => $this->fetchStats(),
        ]);
    }

    /**
     * GET /admin/settings
     * Configurações do painel (nome do site, API key, etc.).
     */
    public function settings(Request $request): void
    {
        $this->requireAuth();

        $this->view('settings', [
            'pageTitle' => 'Configurações | WebMovies Admin',
            'adminName' => $_SESSION['user_name'] ?? 'Admin',
            'success'   => $_SESSION['settings_success'] ?? null,
            'error'     => $_SESSION['settings_error']   ?? null,
        ]);

        unset($_SESSION['settings_success'], $_SESSION['settings_error']);
    }

    /**
     * POST /admin/settings
     * Salva configurações (estrutura pronta; lógica de persistência a implementar).
     */
    public function saveSettings(Request $request): void
    {
        $this->requireAuth();

        // TODO: persistir configurações no BD ou ficheiro .env
        $_SESSION['settings_success'] = 'Configurações salvas com sucesso.';
        header('Location: ' . CONF_URL_BASE . '/admin/settings');
        exit;
    }

    /**
     * GET /admin/logout
     */
    public function logOut(Request $request): void
    {
        $this->startSession();
        session_destroy();
        header('Location: ' . CONF_URL_BASE . '/login');
        exit;
    }
}
