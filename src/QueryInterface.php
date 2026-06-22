<?php

declare(strict_types=1);

namespace Hibla\Sql;

use Hibla\Promise\Interfaces\PromiseInterface;

/**
 * Shared query execution methods common to both client and transaction contexts.
 */
interface QueryInterface extends StreamingQueryInterface
{
    /**
     * The database driver dialect this query interface communicates with. 
     * This is used for determining the correct SQL syntax and features for query-builders.
     */
    public DatabaseDriver $driver { get; }

    /**
     * Executes any SQL statement and returns full Result object.
     * Use this when you need complete metadata (columns, rows, insert ID, etc.).
     *
     * @param string $sql SQL statement to execute
     * @param array<int, mixed> $params Optional parameters
     * @return PromiseInterface<Result>
     */
    public function query(string $sql, array $params = []): PromiseInterface;

    /**
     * Executes a SQL statement (INSERT, UPDATE, DELETE, etc.) and returns affected rows.
     *
     * @param string $sql SQL statement to execute
     * @param array<int, mixed> $params Optional parameters
     * @return PromiseInterface<int> Number of affected rows
     */
    public function execute(string $sql, array $params = []): PromiseInterface;

    /**
     * Executes a SQL statement and returns the last inserted auto-increment ID.
     *
     * @param string $sql SQL statement to execute
     * @param array<int, mixed> $params Optional parameters
     * @return PromiseInterface<int> Last insert ID
     */
    public function executeGetId(string $sql, array $params = []): PromiseInterface;

    /**
     * Executes a SELECT query and returns the first matching row.
     *
     * @param string $sql SQL query to execute
     * @param array<int, mixed> $params Optional parameters
     * @return PromiseInterface<array<string, mixed>|null>
     */
    public function fetchOne(string $sql, array $params = []): PromiseInterface;

    /**
     * Executes a query and returns a single column value from the first row.
     *
     * @param string $sql SQL query to execute
     * @param string|int|null $column Column name or index (default: null, returns first column)
     * @param array<int, mixed> $params Optional parameters
     * @return PromiseInterface<mixed>
     */
    public function fetchValue(string $sql, string|int|null $column = null, array $params = []): PromiseInterface;

    /**
     * Prepares a SQL statement for execution.
     *
     * @param string $sql SQL query with placeholders
     * @return PromiseInterface<PreparedStatement>
     */
    public function prepare(string $sql): PromiseInterface;
}
