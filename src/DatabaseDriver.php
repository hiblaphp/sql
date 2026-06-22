<?php

declare(strict_types=1);

namespace Hibla\Sql\Enums;

enum DatabaseDriver: string
{
    case Mysql = 'mysql';
    case Postgres = 'pgsql';
    case Sqlite = 'sqlite';
}