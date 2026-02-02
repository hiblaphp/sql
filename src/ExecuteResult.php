<?php

declare(strict_types=1);

namespace Hibla\Sql;

/**
 * Contract for execute operation results (INSERT, UPDATE, DELETE, etc.).
 */
interface ExecuteResult
{
    /**
     * Gets the number of rows affected by the operation.
     */
    public function getAffectedRows(): int;

    /**
     * Gets the last inserted auto-increment ID.
     * Returns 0 if not applicable or no auto-increment column.
     */
    public function getLastInsertId(): int;

    /**
     * Checks if any rows were affected.
     */
    public function hasAffectedRows(): bool;

    /**
     * Checks if an auto-increment ID was generated.
     */
    public function hasLastInsertId(): bool;
}