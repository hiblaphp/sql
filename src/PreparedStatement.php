<?php

declare(strict_types=1);

namespace Hibla\Sql;

use Hibla\Promise\Interfaces\PromiseInterface;

/**
 * Contract for prepared SQL statements.
 */
interface PreparedStatement
{
    /**
     * Execute the prepared statement with the given parameters.
     *
     * @param array<int, mixed> $params The parameters to bind to the statement.
     * @return PromiseInterface<Result>
     */
    public function execute(array $params = []): PromiseInterface;

    /**
     * Execute the prepared statement returning a stream.
     *
     * @param array<int, mixed> $params
     * @return PromiseInterface<RowStream>
     */
    public function executeStream(array $params = []): PromiseInterface;

    /**
     * Close the prepared statement.
     *
     * @return PromiseInterface<void>
     */
    public function close(): PromiseInterface;
}
