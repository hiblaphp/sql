<?php

declare(strict_types=1);

namespace Hibla\Sql;

/**
 * Represents an unbuffered stream of database rows.
 *
 * @extends \IteratorAggregate<int, array<string, mixed>>
 */
interface RowStream extends \IteratorAggregate
{
    /**
     * The number of columns in the streaming result set.
     */
    public int $columnCount { get; }

    /**
     * The column names in the streaming result set.
     *
     * @var array<int, string>
     */
    public array $columns { get; }

    /**
     * Iterates over the rows.
     *
     * @return \Generator<int, array<string, mixed>>
     */
    public function getIterator(): \Generator;
}