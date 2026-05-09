<?php

declare(strict_types=1);

namespace WebMovies\Models;

use PDO;
use PDOException;
use PDOStatement;
use WebMovies\Support\Connect;

/**
 * Model — Classe Abstrata Base (CRUD via PDO)
 *
 * Todo model filho deve:
 *   1. Declarar a propriedade  protected string $entity  com o nome da tabela.
 *   2. Declarar a propriedade  protected array  $safe    com as colunas
 *      que NÃO devem ser inseridas/atualizadas automaticamente (ex.: 'id').
 *
 * Uso básico em um model filho:
 *
 *   $user = new User();
 *   $user->bootstrap('nome', 'João', 'email', 'joao@mail.com');
 *   $user->save();          // INSERT
 *
 *   $user->setId(5);
 *   $user->bootstrap('nome', 'João Silva');
 *   $user->save();          // UPDATE WHERE id = 5
 */
abstract class Model
{
    // ── Configurações do model filho ───────────────────────────────────────

    /** Nome da tabela no banco de dados. */
    protected string $entity = '';

    /**
     * Colunas protegidas: jamais são enviadas no INSERT/UPDATE automático.
     * Os models filhos podem sobrescrever ou ampliar este array.
     */
    protected array $safe = ['id'];

    // ── Estado interno ─────────────────────────────────────────────────────

    /** Dados do registro atual (nome da coluna => valor). */
    private array $data = [];

    /** ID do registro atual (null = novo). */
    private ?int $id = null;

    /** Última mensagem de erro de operação. */
    private ?string $fail = null;

    // ── Acesso ao estado ───────────────────────────────────────────────────

    /**
     * Define o id do registro (usado pelo save() para decidir UPDATE).
     */
    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Popula o array de dados a partir de pares coluna => valor.
     *
     * Exemplo:  $model->bootstrap('title', 'Duna', 'rating', 8.5)
     */
    public function bootstrap(string ...$pairs): static
    {
        if (count($pairs) % 2 !== 0) {
            throw new \InvalidArgumentException(
                'bootstrap() requer um número par de argumentos (coluna, valor, …).'
            );
        }
        $this->data = [];
        $chunks = array_chunk($pairs, 2);
        foreach ($chunks as [$column, $value]) {
            $this->data[$column] = $value;
        }
        return $this;
    }

    /**
     * Define os dados diretamente a partir de um array associativo.
     */
    public function fill(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getFail(): ?string
    {
        return $this->fail;
    }

    // ── Save (INSERT ou UPDATE) ────────────────────────────────────────────

    /**
     * Persiste o registro:
     *   – Sem id  → INSERT
     *   – Com id  → UPDATE WHERE id = :id
     *
     * @return bool  true em sucesso, false em falha (ver getFail()).
     */
    public function save(): bool
    {
        if (empty($this->entity)) {
            $this->fail = 'A propriedade $entity não foi definida no model ' . static::class . '.';
            return false;
        }

        if ($this->id === null) {
            $newId = $this->create($this->entity, $this->data);
            if ($newId !== null) {
                $this->id = $newId;
                return true;
            }
            return false;
        }

        return $this->update(
            $this->entity,
            $this->data,
            'id = :id',
            "id={$this->id}"
        );
    }

    // ── CRUD protegido (uso pelos models filhos) ───────────────────────────

    /**
     * INSERT INTO `$table` (colunas…) VALUES (:placeholders…)
     *
     * @param string $table  Nome da tabela.
     * @param array  $data   Array associativo [coluna => valor].
     * @return int|null      ID inserido ou null em caso de falha.
     */
    protected function create(string $table, array $data): ?int
    {
        $data = $this->filterSafe($data);

        if (empty($data)) {
            $this->fail = 'Nenhum dado fornecido para INSERT.';
            return null;
        }

        $columns      = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql          = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = Connect::getInstance()->prepare($sql);
            $stmt->execute($this->buildNamedParams($data));
            return (int) Connect::getInstance()->lastInsertId();
        } catch (PDOException $e) {
            $this->fail = $e->getMessage();
            return null;
        }
    }

    /**
     * SELECT via prepared statement.
     *
     * @param string      $query   SQL com placeholders (ex.: "SELECT * FROM movies WHERE id = :id").
     * @param string|null $params  Pares URL-encoded (ex.: "id=1&status=active").
     * @return PDOStatement|null
     */
    protected function read(string $query, ?string $params = null): ?PDOStatement
    {
        try {
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->execute($this->parseParams($params));
            return $stmt;
        } catch (PDOException $e) {
            $this->fail = $e->getMessage();
            return null;
        }
    }

    /**
     * UPDATE `$table` SET col = :col, … WHERE $terms
     *
     * @param string $table   Nome da tabela.
     * @param array  $data    Dados a atualizar [coluna => valor].
     * @param string $terms   Cláusula WHERE sem a palavra WHERE (ex.: "id = :id").
     * @param string $params  Pares URL-encoded para o WHERE (ex.: "id=5").
     * @return bool
     */
    protected function update(string $table, array $data, string $terms, string $params): bool
    {
        $data = $this->filterSafe($data);

        if (empty($data)) {
            $this->fail = 'Nenhum dado fornecido para UPDATE.';
            return false;
        }

        $setParts = array_map(
            fn(string $col) => "{$col} = :{$col}",
            array_keys($data)
        );
        $setClause = implode(', ', $setParts);
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$terms}";

        // Mescla dados do SET com parâmetros do WHERE
        $bindings = array_merge(
            $this->buildNamedParams($data),
            $this->parseParams($params)
        );

        try {
            $stmt = Connect::getInstance()->prepare($sql);
            $stmt->execute($bindings);
            return true;
        } catch (PDOException $e) {
            $this->fail = $e->getMessage();
            return false;
        }
    }

    /**
     * DELETE FROM `$table` WHERE $terms
     *
     * @param string $table   Nome da tabela.
     * @param string $terms   Cláusula WHERE sem a palavra WHERE (ex.: "id = :id").
     * @param string $params  Pares URL-encoded para o WHERE (ex.: "id=5").
     * @return bool
     */
    protected function delete(string $table, string $terms, string $params): bool
    {
        $sql = "DELETE FROM {$table} WHERE {$terms}";

        try {
            $stmt = Connect::getInstance()->prepare($sql);
            $stmt->execute($this->parseParams($params));
            return true;
        } catch (PDOException $e) {
            $this->fail = $e->getMessage();
            return false;
        }
    }

    // ── Helpers internos ───────────────────────────────────────────────────

    /**
     * Remove as colunas listadas em $this->safe do array de dados.
     */
    private function filterSafe(array $data): array
    {
        return array_diff_key($data, array_flip($this->safe));
    }

    /**
     * Converte array [coluna => valor] em array [:coluna => valor]
     * para uso no execute() do PDO.
     */
    private function buildNamedParams(array $data): array
    {
        $params = [];
        foreach ($data as $key => $value) {
            $params[":{$key}"] = $value;
        }
        return $params;
    }

    /**
     * Converte uma string URL-encoded ("id=5&status=1") em array PDO
     * (":id" => "5", ":status" => "1").
     *
     * Retorna [] se $params for null ou vazio.
     */
    private function parseParams(?string $params): array
    {
        if (empty($params)) {
            return [];
        }

        $parsed = [];
        parse_str($params, $parsed);
        return $this->buildNamedParams($parsed);
    }
}
