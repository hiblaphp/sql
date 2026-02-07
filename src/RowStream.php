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
     * iterates over the rows.
     * 
     * @return \Generator<int, array<string, mixed>>
     */
    public function getIterator(): \Generator;
}