<?php

declare(strict_types=1);

namespace Hibla\Sql\Exceptions;

use RuntimeException;

/**
 * Thrown when trying to prepare an invalid SQL statement.
 */
class PreparedException extends RuntimeException
{
}
