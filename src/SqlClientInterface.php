<?php

declare(strict_types=1);

namespace Hibla\Sql;

use Hibla\Promise\Interfaces\PromiseInterface;

interface SqlClientInterface extends QueryInterface
{
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
