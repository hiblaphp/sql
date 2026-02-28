<?php

declare(strict_types=1);

namespace Hibla\Sql\Exceptions;

/**
 * Thrown when a deadlock is detected during transaction execution.
 *
 * Implements RetryableException — safe to retry once the competing
 * transaction has been rolled back by the server.
 */
class DeadlockException extends TransactionException implements RetryableException
{
}
