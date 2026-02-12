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
interface Transaction extends QueryInterface
{
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
