<?php

declare(strict_types=1);

namespace Hibla\Sql;

/**
 * Interface for database query result objects that support multiple result sets.
 *
 * Typically used when executing stacked queries (e.g., "SELECT 1; SELECT 2;")
 * or calling stored procedures that return multiple cursors/result sets.
 */
interface MultiResult extends Result
{
    /**
     * Returns the next result set if the query returned multiple result sets,
     * or null if no further result sets remain.
     */
    public function nextResult(): ?self;
}
