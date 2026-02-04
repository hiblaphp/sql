<?php

namespace Hibla\Sql\Exceptions;

use RuntimeException;

/**
 * Thrown when a transaction operation fails (commit, rollback, savepoint).
 */
class TransactionException extends RuntimeException {}