<?php

declare(strict_types=1);

namespace Hibla\Sql;

use Hibla\Promise\Interfaces\PromiseInterface;

interface SqlClientInterface extends QueryInterface
{
    /**
     * Begins a database transaction with automatic connection pool management.
     *
     * For most use cases, prefer transaction() over beginTransaction() —
     * it handles commit, rollback, and retries automatically.
     * Use beginTransaction() only when you need manual control over the
     * transaction lifecycle.
     *
     * @param IsolationLevelInterface|null $isolationLevel Optional isolation level.
     * @return PromiseInterface<Transaction>
     */
    public function beginTransaction(?IsolationLevelInterface $isolationLevel = null): PromiseInterface;

    /**
     * Executes a callback within a database transaction with automatic
     * commit, rollback, and retry management.
     *
     * The callback receives a Transaction instance and may return any value,
     * including a PromiseInterface. If the callback throws or the returned
     * promise rejects, the transaction is automatically rolled back.
     *
     * Retry behaviour is controlled via TransactionOptions. By default only
     * DeadlockException triggers a retry — all other SQL-layer exceptions
     * (ConstraintViolationException, ConnectionException, etc.) are considered
     * permanent and rethrown immediately regardless of the attempts setting.
     * Application-level exceptions can be made retryable via
     * TransactionOptions::withRetryableExceptions().
     *
     * @template TResult
     * @param callable(Transaction): TResult $callback
     * @param TransactionOptions|null $options Transaction options. When null,
     *        TransactionOptions::default() is used (1 attempt, no isolation
     *        level override, no custom retryable exceptions).
     * @return PromiseInterface<TResult>
     *
     * @throws \InvalidArgumentException If TransactionOptions contains invalid configuration.
     * @throws \Throwable The final exception if all attempts are exhausted,
     *         or immediately if the exception is non-retryable.
     */
    public function transaction(
        callable $callback,
        ?TransactionOptions $options = null,
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
     */
    public function clearStatementCache(): void;

    /**
     * Closes all connections and shuts down the pool.
     */
    public function close(): void;
}
