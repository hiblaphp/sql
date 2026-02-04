<?php

namespace Hibla\Sql\Exceptions;

use RuntimeException;

/**
 * Thrown when trying to prepare an invalid SQL statement.
 */
class PrepareException extends RuntimeException {}