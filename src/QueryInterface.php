<?php

declare(strict_types=1);

namespace Hibla\Sql;

use Hibla\Promise\Interfaces\PromiseInterface;

/**
 * Shared query execution methods common to both client and transaction contexts.
 */
interface QueryInterface
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
     * Streams a query row-by-row without buffering in memory.
     *
     * @param string $sql SQL query to execute
     * @param array<int, mixed> $params Optional parameters
     * @param positive-int $bufferSize Number of rows to buffer internally per read. Defaults to 100.
     * @return PromiseInterface<RowStream>
     */
    public function stream(string $sql, array $params = [], int $bufferSize = 100): PromiseInterface;

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
     * Prepares a SQL statement for execution.
     *
     * @param string $sql SQL query with placeholders
     * @return PromiseInterface<PreparedStatement>
     */
    public function prepare(string $sql): PromiseInterface;
}
