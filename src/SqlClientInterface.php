<?php

declare(strict_types=1);

namespace Hibla\Sql;

use Hibla\Promise\Interfaces\PromiseInterface;

/**
 * Common interface for database clients across different database systems.
 *
 * This interface defines the core operations that all database clients should support,
 * allowing for interchangeable database implementations.
 */
interface SqlClientInterface
{
    /**
     * Prepares a SQL statement for multiple executions.
     *
     * @param string $sql SQL query with placeholders
     * @return PromiseInterface<PreparedStatement>
     */
    public function prepare(string $sql): PromiseInterface;

    /**
     * Executes a SELECT query and returns all matching rows.
     *
     * @param string $sql SQL query to execute with optional placeholders
     * @param array<int, mixed> $params Optional parameters for prepared statement
     * @return PromiseInterface<Result>
     */
    public function query(string $sql, array $params = []): PromiseInterface;

    /**
     * Executes a SQL statement (INSERT, UPDATE, DELETE, etc.).
     *
     * @param string $sql SQL statement to execute with optional placeholders
     * @param array<int, mixed> $params Optional parameters for prepared statement
     * @return PromiseInterface<Result>
     */
    public function execute(string $sql, array $params = []): PromiseInterface;

    /**
     * Executes a SELECT query and returns the first matching row.
     *
     * @param string $sql SQL query to execute with optional placeholders
     * @param array<int, mixed> $params Optional parameters for prepared statement
     * @return PromiseInterface<array<string, mixed>|null>
     */
    public function fetchOne(string $sql, array $params = []): PromiseInterface;

    /**
     * Executes a query and returns a single column value from the first row.
     *
     * @param string $sql SQL query to execute with optional placeholders
     * @param string|int $column Column name or index (default: 0)
     * @param array<int, mixed> $params Optional parameters for prepared statement
     * @return PromiseInterface<mixed>
     */
    public function fetchValue(string $sql, string|int $column = 0, array $params = []): PromiseInterface;

    /**
     * Begins a database transaction with automatic connection pool management.
     *
     * @param IsolationLevelInterface|null $isolationLevel Optional transaction isolation level
     * @return PromiseInterface<Transaction>
     */
    public function beginTransaction(?IsolationLevelInterface $isolationLevel = null): PromiseInterface;

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
