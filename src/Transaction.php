<?php

declare(strict_types=1);

namespace Hibla\Sql;

use Hibla\Promise\Interfaces\PromiseInterface;

/**
 * Interface for database transaction operations.
 *
 * Provides a unified API for transaction control, query execution,
 * and savepoint management.
 */
interface Transaction
{
    /**
     * Executes a SELECT query and returns all matching rows.
     *
     * @param string $sql SQL query to execute with optional ? placeholders
     * @param array<int, mixed> $params Optional parameters for prepared statement
     * @return PromiseInterface<Result>
     */
    public function query(string $sql, array $params = []): PromiseInterface;

    /**
     * Executes a SQL statement (INSERT, UPDATE, DELETE, etc.).
     *
     * @param string $sql SQL statement to execute with optional ? placeholders
     * @param array<int, mixed> $params Optional parameters for prepared statement
     * @return PromiseInterface<Result>
     */
    public function execute(string $sql, array $params = []): PromiseInterface;

    /**
     * Executes a SELECT query and returns the first matching row.
     *
     * @param string $sql SQL query to execute with optional ? placeholders
     * @param array<int, mixed> $params Optional parameters for prepared statement
     * @return PromiseInterface<array<string, mixed>|null>
     */
    public function fetchOne(string $sql, array $params = []): PromiseInterface;

    /**
     * Executes a query and returns a single column value from the first row.
     *
     * @param string $sql SQL query to execute with optional ? placeholders
     * @param string|int $column Column name or index (default: 0)
     * @param array<int, mixed> $params Optional parameters for prepared statement
     * @return PromiseInterface<mixed>
     */
    public function fetchValue(string $sql, string|int $column = 0, array $params = []): PromiseInterface;

    /**
     * Prepares a statement for execution within the transaction.
     *
     * @param string $sql SQL statement with ? placeholders
     * @return PromiseInterface<PreparedStatement>
     */
    public function prepare(string $sql): PromiseInterface;

    /**
     * Registers a callback to be executed only if the transaction is successfully committed.
     *
     * @param callable(): void $callback The closure to execute on commit.
     */
    public function onCommit(callable $callback): void;

    /**
     * Registers a callback to be executed only if the transaction is rolled back.
     *
     * @param callable(): void $callback The closure to execute on rollback.
     */
    public function onRollback(callable $callback): void;

    /**
     * Commits the transaction, making all changes permanent.
     *
     * @return PromiseInterface<void>
     */
    public function commit(): PromiseInterface;

    /**
     * Rolls back the transaction, discarding all changes.
     *
     * @return PromiseInterface<void>
     */
    public function rollback(): PromiseInterface;

    /**
     * Creates a savepoint within the transaction.
     *
     * @param string $identifier The name of the savepoint.
     * @return PromiseInterface<void>
     */
    public function savepoint(string $identifier): PromiseInterface;

    /**
     * Rolls back the transaction to a named savepoint.
     *
     * @param string $identifier The name of the savepoint to roll back to.
     * @return PromiseInterface<void>
     */
    public function rollbackTo(string $identifier): PromiseInterface;

    /**
     * Releases a named savepoint.
     *
     * @param string $identifier The name of the savepoint to release.
     * @return PromiseInterface<void>
     */
    public function releaseSavepoint(string $identifier): PromiseInterface;

    /**
     * Checks if the transaction is still active.
     */
    public function isActive(): bool;

    /**
     * Checks if the parent connection has been closed.
     */
    public function isClosed(): bool;
}