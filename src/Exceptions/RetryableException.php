<?php

declare(strict_types=1);

namespace Hibla\Sql\Exceptions;

/**
 * Marker interface for exceptions where retrying the transaction makes sense.
 *
 * Only exceptions that explicitly implement this interface are retried
 * automatically by TransactionOptions::shouldRetry(). Everything else is
 * non-retryable by default — no blocklist required.
 *
 * Built-in retryable exceptions:
 *   - DeadlockException        — transient server-side concurrency conflict.
 *   - LockWaitTimeoutException — competing transaction held a row lock too long.
 *
 * Application code can make its own exceptions retryable by implementing
 * this interface without touching TransactionOptions at all:
 *
 *   class MyOptimisticLockException extends \RuntimeException
 *       implements RetryableException {}
 *
 * For third-party exceptions that cannot implement this interface, use
 * TransactionOptions::withRetryableExceptions() instead.
 */
interface RetryableException extends \Throwable
{
}
