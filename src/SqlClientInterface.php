<?php

namespace Hibla\Sql;

use Hibla\Promise\Interfaces\PromiseInterface;

interface SqlClientInterface
{
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
     * This is the primary method for write operations.
     *
     * @param string $sql SQL statement to execute
     * @param array<int, mixed> $params Optional parameters
     * @return PromiseInterface<int> Number of affected rows
     */
    public function execute(string $sql, array $params = []): PromiseInterface;

    /**
     * Streams a query row-by-row without buffering in memory.
     *
     * @param string $sql SQL query to execute
     * @param array<int, mixed> $params Optional parameters
     * @return PromiseInterface<RowStream> Resolves to a RowStream object
     */
    public function stream(string $sql, array $params = []): PromiseInterface;

    /**
     * Executes a SQL statement and returns the last inserted auto-increment ID.
     * Convenience method primarily for INSERT operations.
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
     * @param string|int $column Column name or index (default: 0)
     * @param array<int, mixed> $params Optional parameters
     * @return PromiseInterface<mixed>
     */
    public function fetchValue(string $sql, string|int $column = 0, array $params = []): PromiseInterface;


    /**
     * Prepares a SQL statement for multiple executions.
     *
     * @param string $sql SQL query with placeholders
     * @return PromiseInterface<PreparedStatement>
     */
    public function prepare(string $sql): PromiseInterface;

    /**
     * Begins a database transaction with automatic connection pool management.
     *
     * @param IsolationLevelInterface|null $isolationLevel Optional transaction isolation level
     * @return PromiseInterface<Transaction>
     */
    public function beginTransaction(?IsolationLevelInterface $isolationLevel = null): PromiseInterface; // <Transaction>

    /**
     * Executes a callback within a database transaction with automatic management and retries.
     *
     * @template TResult
     *
     * @param callable(Transaction): TResult $callback
     * @param int $attempts Number of times to attempt the transaction (default: 1)
     * @param IsolationLevelInterface|null $isolationLevel
     * @return PromiseInterface<TResult>
     */
    public function transaction(
        callable $callback,
        int $attempts = 1,
        ?IsolationLevelInterface $isolationLevel = null
    ): PromiseInterface;

    /**
     * Performs a health check on all idle connections in the pool.
     *
     * @return PromiseInterface<array<string, int>>
     */
    public function healthCheck(): PromiseInterface;

    /**
     * Gets statistics about the connection pool.
     *
     * @return array<string, int|bool>
     */
    public function getStats(): array;

    /**
     * Clears the prepared statement cache for all connections.
     *
     * @return void
     */
    public function clearStatementCache(): void;

    /**
     * Closes all connections and shuts down the pool.
     *
     * @return void
     */
    public function close(): void;
}
