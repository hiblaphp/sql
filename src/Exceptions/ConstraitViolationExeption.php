<?php

namespace Hibla\Sql\Exceptions;

use RuntimeException;

/**
 * Thrown when there's a constraint violation (UNIQUE, FOREIGN KEY, NOT NULL, CHECK).
 */
class ConstraintViolationException extends QueryException {}