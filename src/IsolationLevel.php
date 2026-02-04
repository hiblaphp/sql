<?php

namespace Hibla\Sql;

/**
 * Standard SQL transaction isolation levels.
 * Supported by MySQL, PostgreSQL, SQL Server, and most ANSI SQL databases.
 */
enum IsolationLevel: string implements IsolationLevelInterface
{
    case READ_UNCOMMITTED = 'READ UNCOMMITTED';

    case READ_COMMITTED = 'READ COMMITTED';
    
    case REPEATABLE_READ = 'REPEATABLE READ';
    
    case SERIALIZABLE = 'SERIALIZABLE';

    public function getValue(): string
    {
        return $this->value;
    }
}