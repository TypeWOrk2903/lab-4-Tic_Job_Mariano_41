<?php

declare(strict_types=1);

namespace WebMovies\Models;

use PDO;
use PDOException;
use WebMovies\Support\Connect;

/**
 * User — Model de utilizadores.
 *
 * SQL de criação da tabela:
 *
 *   CREATE TABLE IF NOT EXISTS users (
 *     id         INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
 *     name       VARCHAR(120)    NOT NULL,
 *     email      VARCHAR(180)    NOT NULL UNIQUE,
 *     password   VARCHAR(255)    NOT NULL,
 *     forget     VARCHAR(64)     DEFAULT NULL,
 *     created_at TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
 *   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 */
final class User extends Model
{
    protected string $entity = 'users';
    protected array  $safe   = ['id', 'created_at'];

    // Propriedades mapeadas da linha do banco
    public ?int    $id         = null;
    public ?string $name       = null;
    public ?string $email      = null;
    public ?string $password   = null;
    public ?string $forget     = null;
    public ?string $created_at = null;

    // ── Configuração ─────────────────────────────────────────────────────

    /**
     * Prepara dados de um novo utilizador para INSERT.
     * A senha é hashed com bcrypt — nunca armazene texto simples.
     */
    public function registerdata(string $name, string $email, string $password): ?static
    {
        return $this->fill([
            'name'     => trim($name),
            'email'    => mb_strtolower(trim($email)),
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
        ]);
    }

    // ── Consultas ─────────────────────────────────────────────────────────

    /**
     * Busca um utilizador pelo e-mail.
     * Retorna a instância populada ou null se não existir.
     */
    public function findByEmail(string $email): ?static
    {
        $pdo = Connect::getInstance();
        if (!$pdo) return null;

        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM {$this->entity} WHERE email = :email LIMIT 1"
            );
            $stmt->execute([':email' => mb_strtolower(trim($email))]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) return null;

            $user = new static();
            foreach ($row as $col => $val) {
                if (property_exists($user, $col)) {
                    $user->{$col} = $val;
                }
            }
            $user->setId((int) $row['id']);
            return $user;

        } catch (PDOException) {
            return null;
        }
    }

    /**
     * Verifica se um e-mail já está registado.
     */
    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    // ── Autenticação ──────────────────────────────────────────────────────

    /**
     * Autentica o utilizador por e-mail e senha.
     * Retorna a instância do utilizador ou null em caso de falha.
     * A mensagem de erro é propositalmente genérica (OWASP).
     */
    public function auth(string $email, string $password): ?static
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;

        if (!password_verify($password, (string) $user->password)) {
            return null;
        }

        return $user;
    }

    // ── Recuperação de senha ──────────────────────────────────────────────

    /**
     * Gera e persiste um token de recuperação de senha.
     * Retorna o token (para envio por e-mail) ou null se o e-mail não existir.
     * Resposta genérica para não revelar se o e-mail está cadastrado.
     */
    public function generateForgetToken(string $email): ?string
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;   // Não revela ao chamador — só ao controller

        $token = bin2hex(random_bytes(32));   // 64 chars, criptograficamente seguro

        $pdo = Connect::getInstance();
        if (!$pdo) return null;

        try {
            $stmt = $pdo->prepare(
                "UPDATE {$this->entity} SET forget = :token WHERE email = :email"
            );
            $stmt->execute([':token' => $token, ':email' => $user->email]);
            return $token;
        } catch (PDOException) {
            return null;
        }
    }
}
