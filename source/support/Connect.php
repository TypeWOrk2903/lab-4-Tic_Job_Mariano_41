<?php

declare(strict_types=1);

namespace WebMovies\Support;

use PDO;
use PDOException;

/**
 * Connect — Conexão PDO Singleton
 *
 * Garante uma única instância de PDO durante todo o ciclo de vida
 * da requisição, reutilizando a mesma conexão em todos os Models.
 *
 * Depende das constantes definidas em source/support/config.php:
 *   CONF_DB_HOST, CONF_DB_USER, CONF_DB_PASS, CONF_DB_NAME
 */
final class Connect
{
    /** Instância única do PDO. */
    private static ?PDO $instance = null;

    /** Última mensagem de erro de conexão. */
    private static ?string $error = null;

    /** Construtor privado — impede instanciação direta. */
    private function __construct() {}

    /** Clonagem proibida. */
    private function __clone() {}

    /**
     * Retorna a instância PDO, criando-a na primeira chamada.
     *
     * @return PDO|null  PDO conectado ou null em caso de falha.
     */
    public static function getInstance(): ?PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            CONF_DB_HOST,
            CONF_DB_NAME
        );

        try {
            self::$instance = new PDO(
                $dsn,
                CONF_DB_USER,
                CONF_DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]
            );
            self::$error = null;
        } catch (PDOException $e) {
            // Nunca exponha credenciais ou stack trace em produção.
            self::$instance = null;
            self::$error    = $e->getMessage();
        }

        return self::$instance;
    }

    /**
     * Retorna a última mensagem de erro de conexão, se houver.
     */
    public static function getError(): ?string
    {
        return self::$error;
    }
}
