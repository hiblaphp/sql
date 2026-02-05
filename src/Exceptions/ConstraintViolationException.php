<?php

declare(strict_types=1);

namespace Hibla\Sql\Exceptions;

/**
 * Thrown when there's a constraint violation (UNIQUE, FOREIGN KEY, NOT NULL, CHECK).
 */
class ConstraintViolationException extends QueryException
{
}
