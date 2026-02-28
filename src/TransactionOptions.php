<?php

declare(strict_types=1);

namespace Hibla\Sql;

use Hibla\Sql\Exceptions\AuthenticationException;
use Hibla\Sql\Exceptions\ConnectionException;
use Hibla\Sql\Exceptions\ConstraintViolationException;
use Hibla\Sql\Exceptions\PreparedException;
use Hibla\Sql\Exceptions\QueryException;
use Hibla\Sql\Exceptions\RetryableException;
use Hibla\Sql\Exceptions\TimeoutException;
use Hibla\Sql\Exceptions\TransactionException;

/**
 * Immutable configuration object for transaction execution.
 *
 * Passed to SqlClientInterface::transaction() to control retry behaviour,
 * isolation level, and application-level retryable exceptions.
 *
 * All with*() methods return a new instance, so options can be safely
 * shared and composed without mutation concerns.
 *
 * ## Retry decision hierarchy
 *
 *   1. Marker interface — any exception implementing RetryableException
 *      retries automatically. This includes DeadlockException and
 *      LockWaitTimeoutException out of the box. Application exceptions
 *      can opt in by implementing the interface directly:
 *
 *        class MyOptimisticLockException extends \RuntimeException
 *            implements RetryableException {}
 *
 *   2. Known permanent SQL failures — exceptions the SQL layer has explicitly
 *      classified as non-retryable. These are never retried regardless of
 *      what the user predicate returns, protecting against accidental retries
 *      on errors that will never resolve (e.g. UNIQUE violations):
 *        - ConstraintViolationException
 *        - AuthenticationException
 *        - ConnectionException
 *        - PreparedException
 *        - QueryException
 *        - TransactionException
 *        - TimeoutException (LockWaitTimeoutException is carved out via tier 1)
 *
 *   3. User predicate — for third-party exceptions that cannot implement
 *      RetryableException and are not known SQL failures, a callable or
 *      class list can be supplied via withRetryableExceptions(). The user
 *      predicate only applies to exceptions that pass through tier 2.
 *
 * Example:
 *
 *   $options = TransactionOptions::default()
 *       ->withAttempts(3)
 *       ->withIsolationLevel(IsolationLevel::Serializable)
 *       ->withRetryableExceptions([ThirdPartyException::class]);
 *
 *   $db->transaction($callback, $options);
 */
final class TransactionOptions
{
    /**
     * The original raw value passed by the caller, preserved so that
     * with*() methods can pass it back into the constructor unchanged
     * without re-normalising an already-normalised predicate.
     *
     * @var callable(\Throwable): bool|array<class-string<\Throwable>>|null
     */
    private readonly mixed $retryableExceptions;

    /**
     * Normalised callable built once at construction time from the raw
     * $retryableExceptions argument.
     *
     * Stored as mixed because callable is not a valid PHP property type.
     * The @var docblock carries the full type for PHPStan so no manual
     * is_callable() checks are needed at call time.
     *
     * @var (callable(\Throwable): bool)|null
     */
    private readonly mixed $normalisedPredicate;

    /**
     * @param int $attempts
     *        Number of times to attempt the transaction before giving up.
     *        Must be at least 1.
     *
     * @param IsolationLevelInterface|null $isolationLevel
     *        Optional isolation level applied at the start of each attempt.
     *        When null, the server default isolation level is used.
     *
     * @param callable(\Throwable): bool|array<class-string<\Throwable>>|null $retryableExceptions
     *        Extends the built-in retry logic for third-party exceptions that
     *        cannot implement the RetryableException marker interface.
     *
     *        Accepts either:
     *          - An array of Throwable class-strings: retries if the exception
     *            is an instanceof any class in the list.
     *          - A callable: fn(\Throwable): bool — full predicate control.
     *          - null: no extension, only the RetryableException marker applies.
     *
     *        Note: the user predicate only applies to exceptions not already
     *        classified by the SQL layer. Known permanent failures such as
     *        ConstraintViolationException are never retried regardless of
     *        what the predicate returns.
     *
     *        For exceptions you own, prefer implementing RetryableException
     *        directly — it requires no configuration at the call site.
     *
     * @throws \InvalidArgumentException If attempts is less than 1.
     * @throws \InvalidArgumentException If retryableExceptions is an empty array.
     * @throws \InvalidArgumentException If retryableExceptions array contains a
     *         non-string, a non-existent class, or a class that does not
     *         implement Throwable.
     */
    public function __construct(
        public readonly int $attempts = 1,
        public readonly ?IsolationLevelInterface $isolationLevel = null,
        callable|array|null $retryableExceptions = null,
    ) {
        if ($this->attempts < 1) {
            throw new \InvalidArgumentException(
                'TransactionOptions: attempts must be at least 1, got ' . $this->attempts . '.'
            );
        }

        if (\is_array($retryableExceptions)) {
            $this->validateRetryableExceptions($retryableExceptions);
        }

        $this->retryableExceptions = $retryableExceptions;
        $this->normalisedPredicate = $this->normaliseRetryable($retryableExceptions);
    }

    /**
     * Returns a default options instance with no retries, no isolation level
     * override, and no custom retryable exceptions.
     */
    public static function default(): self
    {
        return new self();
    }

    /**
     * Returns a new instance with the given number of attempts.
     *
     * @throws \InvalidArgumentException If attempts is less than 1.
     */
    public function withAttempts(int $attempts): self
    {
        return new self($attempts, $this->isolationLevel, $this->retryableExceptions);
    }

    /**
     * Returns a new instance with the given isolation level.
     */
    public function withIsolationLevel(IsolationLevelInterface $isolationLevel): self
    {
        return new self($this->attempts, $isolationLevel, $this->retryableExceptions);
    }

    /**
     * Returns a new instance with the given retryable exceptions config.
     *
     * Use this only for third-party exceptions you cannot modify. For
     * exceptions you own, implement RetryableException directly instead.
     *
     * Note: known permanent SQL failures (ConstraintViolationException,
     * ConnectionException, etc.) are never retried even if listed here.
     *
     * @param callable(\Throwable): bool|array<class-string<\Throwable>> $retryableExceptions
     *
     * @throws \InvalidArgumentException If the array is empty, contains non-strings,
     *         non-existent classes, or classes that do not implement Throwable.
     */
    public function withRetryableExceptions(callable|array $retryableExceptions): self
    {
        return new self($this->attempts, $this->isolationLevel, $retryableExceptions);
    }

    /**
     * Returns a new instance with retryable exceptions removed.
     *
     * Useful when composing from a shared base options object and a specific
     * call site does not want any custom retry predicate applied.
     */
    public function withoutRetryableExceptions(): self
    {
        return new self($this->attempts, $this->isolationLevel, null);
    }

    /**
     * Determines whether a failed transaction attempt should be retried.
     *
     * Follows a strict three-tier hierarchy — see class docblock for full details.
     *
     * This method is intended to be called by SqlClientInterface implementations
     * inside their transaction retry loop. Driver code should never replicate
     * this logic — call this method instead.
     */
    public function shouldRetry(\Throwable $e): bool
    {
        // Tier 1 — marker interface. The exception explicitly opts in to retry.
        // Checked first so RetryableException subclasses of SQL exceptions
        // (e.g. DeadlockException extends TransactionException) are caught
        // before tier 2 would block them.
        if ($e instanceof RetryableException) {
            return true;
        }

        // Tier 2 — known permanent SQL failures. These are never retried
        // regardless of what the user predicate returns. Protects against
        // accidental promotion of errors that will never resolve on retry.
        if ($this->isKnownNonRetryable($e)) {
            return false;
        }

        // Tier 3 — unknown exception. Delegate to the user-supplied predicate
        // for third-party exceptions that cannot implement our marker interface.
        return $this->normalisedPredicate !== null && ($this->normalisedPredicate)($e);
    }

    /**
     * Returns true for exceptions the SQL layer has explicitly classified as
     * permanent failures that the user predicate cannot override.
     *
     * LockWaitTimeoutException (extends TimeoutException) and DeadlockException
     * (extends TransactionException) are intentionally excluded — they implement
     * RetryableException and are caught by tier 1 before this method is reached.
     *
     * Unknown exceptions return false here so the user predicate in tier 3
     * gets a chance to handle them.
     */
    private function isKnownNonRetryable(\Throwable $e): bool
    {
        return $e instanceof ConstraintViolationException
            || $e instanceof AuthenticationException
            || $e instanceof ConnectionException
            || $e instanceof PreparedException
            || $e instanceof QueryException
            || $e instanceof TransactionException
            || $e instanceof TimeoutException;
    }

    /**
     * Normalises the raw retryableExceptions value into a single consistent
     * callable so shouldRetry() never needs to branch on its shape.
     *
     * @param callable(\Throwable): bool|array<class-string<\Throwable>>|null $retryable
     * @return (callable(\Throwable): bool)|null
     */
    private function normaliseRetryable(callable|array|null $retryable): mixed
    {
        if ($retryable === null) {
            return null;
        }

        if (\is_callable($retryable)) {
            return $retryable;
        }

        return static function (\Throwable $e) use ($retryable): bool {
            foreach ($retryable as $class) {
                if ($e instanceof $class) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Validates that every entry in the retryableExceptions array is:
     *   1. A string.
     *   2. A class or interface that exists and is autoloaded.
     *   3. A subclass or implementation of Throwable.
     *
     * Validated eagerly in the constructor so errors surface at configuration
     * time rather than silently failing to match on the first retry attempt.
     *
     * @param array<mixed> $exceptions
     * @phpstan-assert array<class-string<\Throwable>> $exceptions
     *
     * @throws \InvalidArgumentException
     */
    private function validateRetryableExceptions(array $exceptions): void
    {
        if (\count($exceptions) === 0) {
            throw new \InvalidArgumentException(
                'TransactionOptions: retryableExceptions array cannot be empty. Pass null to disable.'
            );
        }

        foreach ($exceptions as $index => $class) {
            if (! \is_string($class)) {
                throw new \InvalidArgumentException(\sprintf(
                    'TransactionOptions: retryableExceptions[%s] must be a class-string, got "%s".',
                    $index,
                    \get_debug_type($class)
                ));
            }

            if (! class_exists($class) && ! interface_exists($class)) {
                throw new \InvalidArgumentException(\sprintf(
                    'TransactionOptions: retryableExceptions[%s] "%s" does not exist. '
                    . 'Ensure the class is autoloaded before constructing TransactionOptions.',
                    $index,
                    $class
                ));
            }

            if (! is_a($class, \Throwable::class, true)) {
                throw new \InvalidArgumentException(\sprintf(
                    'TransactionOptions: retryableExceptions[%s] "%s" must implement Throwable. '
                    . 'Only exception and error classes are valid.',
                    $index,
                    $class
                ));
            }
        }
    }
}
