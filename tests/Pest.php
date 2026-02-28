<?php

declare(strict_types=1);

use Hibla\Sql\IsolationLevelInterface;

function makeIsolationLevel(string $sql = 'SERIALIZABLE'): IsolationLevelInterface
{
    return new class ($sql) implements IsolationLevelInterface {
        public function __construct(private readonly string $sql)
        {
        }

        public function toSql(): string
        {
            return $this->sql;
        }
    };
}
