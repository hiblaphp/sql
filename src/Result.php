<?php

declare(strict_types=1);

namespace Hibla\Sql;

/**
 * Interface for database query result objects.
 *
 * Provides a unified API for accessing results from all query types:
 * - SELECT queries: populated rows with metadata
 * - INSERT/UPDATE/DELETE: execution metadata (affected rows, last insert ID)
 * - Other commands: minimal metadata
 *
 * @extends \IteratorAggregate<int, array<string, mixed>>
 */
interface Result extends \IteratorAggregate
{
    /**
     * Fetches the next row as an associative array.
     * Returns null if there are no more rows or for non-SELECT queries.
     *
     * @return array<string, mixed>|null
     */
    public function fetchAssoc(): ?array;

    /**
     * Fetches all rows as an array of associative arrays.
     * Returns empty array for non-SELECT queries.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(): array;

    /**
     * Fetches a single column from all rows.
     * Returns empty array for non-SELECT queries.
     *
     * @param string|int $column Column name or index
     * @return array<int, mixed>
     */
    public function fetchColumn(string|int $column = 0): array;

    /**
     * Fetches the first row, or null if empty or non-SELECT.
     *
     * @return array<string, mixed>|null
     */
    public function fetchOne(): ?array;

    /**
     * Gets the number of rows affected by INSERT/UPDATE/DELETE operations.
     * Returns 0 for SELECT queries.
     *
     * @return int
     */
    public function getAffectedRows(): int;

    /**
     * Gets the last inserted auto-increment ID.
     * Returns 0 if not applicable or for SELECT queries.
     *
     * @return int
     */
    public function getLastInsertId(): int;

    /**
     * Checks if any rows were affected by the operation.
     * For SELECT queries, use rowCount() or isEmpty() instead.
     *
     * @return bool
     */
    public function hasAffectedRows(): bool;

    /**
     * Checks if an auto-increment ID was generated.
     *
     * @return bool
     */
    public function hasLastInsertId(): bool;

    /**
     * Gets the number of rows returned by SELECT queries.
     * For non-SELECT queries, returns 0.
     *
     * @return int
     */
    public function rowCount(): int;

    /**
     * Gets the number of columns in the result set.
     *
     * @return int
     */
    public function getColumnCount(): int;

    /**
     * Gets column names from the result set.
     *
     * @return array<int, string>
     */
    public function getColumns(): array;

    /**
     * Checks if the result set is empty (no rows returned).
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Allows iteration in foreach loops.
     * For non-SELECT queries, returns empty iterator.
     *
     * @return \Traversable<int, array<string, mixed>>
     */
    public function getIterator(): \Traversable;
}
