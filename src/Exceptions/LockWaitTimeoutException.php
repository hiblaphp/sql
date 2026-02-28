<?php

declare(strict_types=1);

namespace Hibla\Sql\Exceptions;

/**
 * Thrown when a transaction exceeds the server lock wait timeout
 * (e.g. innodb_lock_wait_timeout, MySQL error 1205).
 *
 * Implements RetryableException — unlike a general TimeoutException
 * (query too slow, pool acquire timeout), a lock wait timeout is a
 * transient concurrency conflict that is safe to retry once the
 * competing transaction commits or rolls back.
 */
class LockWaitTimeoutException extends TimeoutException implements RetryableException
{
}
