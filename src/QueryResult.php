<?php

declare(strict_types=1);

namespace Hibla\Sql;

/**
 * Contract for SELECT query results.
 * 
 * @extends \IteratorAggregate<int, array<string, mixed>>
 */
interface QueryResult extends \IteratorAggregate, \Countable
{
    /**
     * Fetches the next row as an associative array.
     * Returns null if there are no more rows.
     * 
     * @return array<string, mixed>|null
     */
    public function fetchAssoc(): ?array;

    /**
     * Fetches all rows as an array of associative arrays.
     * 
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(): array;

    /**
     * Fetches a single column from all rows.
     *
     * @param string|int $column Column name or index
     * @return array<int, mixed>
     */
    public function fetchColumn(string|int $column = 0): array;

    /**
     * Fetches the first row, or null if empty.
     * 
     * @return array<string, mixed>|null
     */
    public function fetchOne(): ?array;

    /**
     * Gets the number of columns in the result set.
     */
    public function getColumnCount(): int;

    /**
     * Checks if the result set is empty.
     */
    public function isEmpty(): bool;
}