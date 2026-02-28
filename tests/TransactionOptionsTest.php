<?php

declare(strict_types=1);

use Hibla\Sql\Exceptions\AuthenticationException;
use Hibla\Sql\Exceptions\ConnectionException;
use Hibla\Sql\Exceptions\ConstraintViolationException;
use Hibla\Sql\Exceptions\DeadlockException;
use Hibla\Sql\Exceptions\LockWaitTimeoutException;
use Hibla\Sql\Exceptions\PreparedException;
use Hibla\Sql\Exceptions\QueryException;
use Hibla\Sql\Exceptions\TimeoutException;
use Hibla\Sql\Exceptions\TransactionException;
use Hibla\Sql\TransactionOptions;
use Test\Fixtures\AnotherAppException;
use Test\Fixtures\MyOptimisticLockException;
use Test\Fixtures\ThirdPartyException;

describe('TransactionOptions construction', function () {

    it('creates a default instance with expected defaults', function () {
        $options = new TransactionOptions();

        expect($options->attempts)->toBe(1)
            ->and($options->isolationLevel)->toBeNull()
        ;
    });

    it('static default() returns same defaults as new TransactionOptions()', function () {
        $default = TransactionOptions::default();

        expect($default->attempts)->toBe(1)
            ->and($default->isolationLevel)->toBeNull()
        ;
    });

    it('accepts valid attempts and isolation level', function () {
        $level = makeIsolationLevel();
        $options = new TransactionOptions(attempts: 3, isolationLevel: $level);

        expect($options->attempts)->toBe(3)
            ->and($options->isolationLevel)->toBe($level)
        ;
    });

    it('accepts an array of valid Throwable class-strings', function () {
        $options = new TransactionOptions(
            retryableExceptions: [ThirdPartyException::class],
        );

        expect($options->attempts)->toBe(1);
    });

    it('accepts a callable as retryable exceptions', function () {
        $options = new TransactionOptions(
            retryableExceptions: fn (Throwable $e): bool => $e instanceof ThirdPartyException,
        );

        expect($options->attempts)->toBe(1);
    });

    it('accepts null as retryable exceptions', function () {
        $options = new TransactionOptions(retryableExceptions: null);

        expect($options->attempts)->toBe(1);
    });

    it('accepts multiple valid exception classes in the array', function () {
        $options = new TransactionOptions(
            retryableExceptions: [
                ThirdPartyException::class,
                AnotherAppException::class,
            ],
        );

        expect($options->shouldRetry(new ThirdPartyException()))->toBeTrue()
            ->and($options->shouldRetry(new AnotherAppException()))->toBeTrue()
        ;
    });
});

describe('attempts validation', function () {

    it('throws when attempts is zero', function () {
        new TransactionOptions(attempts: 0);
    })->throws(InvalidArgumentException::class, 'attempts must be at least 1, got 0');

    it('throws when attempts is negative', function () {
        new TransactionOptions(attempts: -5);
    })->throws(InvalidArgumentException::class, 'attempts must be at least 1, got -5');

    it('accepts attempts of exactly 1', function () {
        expect((new TransactionOptions(attempts: 1))->attempts)->toBe(1);
    });

    it('accepts high attempt counts', function () {
        expect((new TransactionOptions(attempts: 100))->attempts)->toBe(100);
    });
});

describe('retryableExceptions array validation', function () {

    it('throws when the array is empty', function () {
        new TransactionOptions(retryableExceptions: []);
    })->throws(InvalidArgumentException::class, 'retryableExceptions array cannot be empty');

    it('throws when an array entry is not a string', function () {
        new TransactionOptions(retryableExceptions: [42]);
    })->throws(InvalidArgumentException::class, 'retryableExceptions[0] must be a class-string, got "int"');

    it('throws when an array entry is an object', function () {
        new TransactionOptions(retryableExceptions: [new stdClass()]);
    })->throws(InvalidArgumentException::class, 'retryableExceptions[0] must be a class-string, got "stdClass"');

    it('throws when an array entry is a class that does not exist', function () {
        new TransactionOptions(retryableExceptions: ['App\\Exceptions\\GhostException']);
    })->throws(InvalidArgumentException::class, '"App\\Exceptions\\GhostException" does not exist');

    it('throws when an array entry does not implement Throwable', function () {
        new TransactionOptions(retryableExceptions: [stdClass::class]);
    })->throws(InvalidArgumentException::class, '"stdClass" must implement Throwable');

    it('reports the correct array index in the error message', function () {
        new TransactionOptions(retryableExceptions: [
            ThirdPartyException::class,
            stdClass::class,
        ]);
    })->throws(InvalidArgumentException::class, 'retryableExceptions[1]');

    it('throws on the first invalid entry and does not process the rest', function () {
        new TransactionOptions(retryableExceptions: [
            'NonExistentClass',
            stdClass::class,
        ]);
    })->throws(InvalidArgumentException::class, 'retryableExceptions[0]');
});

describe('shouldRetry() — RetryableException marker interface', function () {

    it('retries DeadlockException', function () {
        expect(TransactionOptions::default()->shouldRetry(new DeadlockException()))->toBeTrue();
    });

    it('retries LockWaitTimeoutException', function () {
        expect(TransactionOptions::default()->shouldRetry(new LockWaitTimeoutException()))->toBeTrue();
    });

    it('retries any exception implementing RetryableException', function () {
        expect(TransactionOptions::default()->shouldRetry(new MyOptimisticLockException()))->toBeTrue();
    });

    it('retries RetryableException subclasses even with no user predicate configured', function () {
        $options = new TransactionOptions(attempts: 3);

        expect($options->shouldRetry(new MyOptimisticLockException()))->toBeTrue();
    });
});

describe('shouldRetry() — built-in non-retryable SQL exceptions', function () {

    it('does not retry ConstraintViolationException', function () {
        expect(TransactionOptions::default()->shouldRetry(new ConstraintViolationException()))->toBeFalse();
    });

    it('does not retry AuthenticationException', function () {
        expect(TransactionOptions::default()->shouldRetry(new AuthenticationException()))->toBeFalse();
    });

    it('does not retry ConnectionException', function () {
        expect(TransactionOptions::default()->shouldRetry(new ConnectionException()))->toBeFalse();
    });

    it('does not retry PreparedException', function () {
        expect(TransactionOptions::default()->shouldRetry(new PreparedException()))->toBeFalse();
    });

    it('does not retry QueryException', function () {
        expect(TransactionOptions::default()->shouldRetry(new QueryException()))->toBeFalse();
    });

    it('does not retry TransactionException', function () {
        expect(TransactionOptions::default()->shouldRetry(new TransactionException()))->toBeFalse();
    });

    it('does not retry TimeoutException', function () {
        expect(TransactionOptions::default()->shouldRetry(new TimeoutException()))->toBeFalse();
    });

    it('does not retry generic RuntimeException', function () {
        expect(TransactionOptions::default()->shouldRetry(new RuntimeException()))->toBeFalse();
    });

    it('does not retry unknown third-party exception when no predicate is configured', function () {
        expect(TransactionOptions::default()->shouldRetry(new ThirdPartyException()))->toBeFalse();
    });
});

describe('shouldRetry() — user predicate via class array', function () {

    it('retries a third-party exception listed in the array', function () {
        $options = new TransactionOptions(
            retryableExceptions: [ThirdPartyException::class],
        );

        expect($options->shouldRetry(new ThirdPartyException()))->toBeTrue();
    });

    it('does not retry an exception not listed in the array', function () {
        $options = new TransactionOptions(
            retryableExceptions: [ThirdPartyException::class],
        );

        expect($options->shouldRetry(new AnotherAppException()))->toBeFalse();
    });

    it('retries subclass of a listed exception', function () {
        $options = new TransactionOptions(
            retryableExceptions: [LogicException::class],
        );

        expect($options->shouldRetry(new AnotherAppException()))->toBeTrue();
    });

    it('retries when exception matches any class in a multi-entry array', function () {
        $options = new TransactionOptions(
            retryableExceptions: [
                ThirdPartyException::class,
                AnotherAppException::class,
            ],
        );

        expect($options->shouldRetry(new ThirdPartyException()))->toBeTrue()
            ->and($options->shouldRetry(new AnotherAppException()))->toBeTrue()
        ;
    });

    it('does not retry a known non-retryable SQL exception even when listed in the array', function () {
        $options = new TransactionOptions(
            retryableExceptions: [ConstraintViolationException::class],
        );

        expect($options->shouldRetry(new ConstraintViolationException()))->toBeFalse();
    });

    it('does not retry a known non-retryable SQL exception even when callable returns true', function () {
        $options = new TransactionOptions(
            retryableExceptions: fn (Throwable $e): bool => true,
        );

        expect($options->shouldRetry(new ConstraintViolationException()))->toBeFalse();
        expect($options->shouldRetry(new ConnectionException()))->toBeFalse();
        expect($options->shouldRetry(new AuthenticationException()))->toBeFalse();
    });
});

describe('shouldRetry() — user predicate via callable', function () {

    it('retries when the callable returns true', function () {
        $options = new TransactionOptions(
            retryableExceptions: fn (Throwable $e): bool => $e instanceof ThirdPartyException,
        );

        expect($options->shouldRetry(new ThirdPartyException()))->toBeTrue();
    });

    it('does not retry when the callable returns false', function () {
        $options = new TransactionOptions(
            retryableExceptions: fn (Throwable $e): bool => false,
        );

        expect($options->shouldRetry(new ThirdPartyException()))->toBeFalse();
    });

    it('callable receives the exact exception instance', function () {
        $captured = null;
        $exception = new ThirdPartyException('specific message');

        $options = new TransactionOptions(
            retryableExceptions: function (Throwable $e) use (&$captured): bool {
                $captured = $e;

                return true;
            },
        );

        $options->shouldRetry($exception);

        expect($captured)->toBe($exception);
    });

    it('callable can inspect exception properties for fine-grained control', function () {
        $options = new TransactionOptions(
            retryableExceptions: fn (Throwable $e): bool => $e instanceof ThirdPartyException && $e->getCode() === 503,
        );

        expect($options->shouldRetry(new ThirdPartyException(code: 503)))->toBeTrue()
            ->and($options->shouldRetry(new ThirdPartyException(code: 500)))->toBeFalse()
        ;
    });
});

describe('shouldRetry() — marker interface takes precedence', function () {

    it('retries a RetryableException even when user predicate returns false', function () {
        $options = new TransactionOptions(
            retryableExceptions: fn (Throwable $e): bool => false,
        );

        expect($options->shouldRetry(new MyOptimisticLockException()))->toBeTrue();
    });

    it('retries DeadlockException even when user predicate returns false', function () {
        $options = new TransactionOptions(
            retryableExceptions: fn (Throwable $e): bool => false,
        );

        expect($options->shouldRetry(new DeadlockException()))->toBeTrue();
    });
});

describe('withAttempts()', function () {

    it('returns a new instance with updated attempts', function () {
        $original = TransactionOptions::default();
        $updated = $original->withAttempts(5);

        expect($updated->attempts)->toBe(5)
            ->and($updated)->not->toBe($original)
        ;
    });

    it('preserves isolationLevel', function () {
        $level = makeIsolationLevel();
        $options = new TransactionOptions(attempts: 1, isolationLevel: $level);
        $updated = $options->withAttempts(3);

        expect($updated->isolationLevel)->toBe($level);
    });

    it('preserves retryable predicate behaviour', function () {
        $options = new TransactionOptions(
            attempts: 1,
            retryableExceptions: [ThirdPartyException::class],
        );

        $updated = $options->withAttempts(3);

        expect($updated->shouldRetry(new ThirdPartyException()))->toBeTrue()
            ->and($updated->attempts)->toBe(3)
        ;
    });

    it('throws when new attempts value is less than 1', function () {
        TransactionOptions::default()->withAttempts(0);
    })->throws(InvalidArgumentException::class, 'attempts must be at least 1');
});

describe('withIsolationLevel()', function () {

    it('returns a new instance with updated isolation level', function () {
        $original = TransactionOptions::default();
        $level = makeIsolationLevel();
        $updated = $original->withIsolationLevel($level);

        expect($updated->isolationLevel)->toBe($level)
            ->and($updated)->not->toBe($original)
        ;
    });

    it('preserves attempts', function () {
        $options = new TransactionOptions(attempts: 3);
        $updated = $options->withIsolationLevel(makeIsolationLevel());

        expect($updated->attempts)->toBe(3);
    });

    it('preserves retryable predicate behaviour', function () {
        $options = new TransactionOptions(
            retryableExceptions: [ThirdPartyException::class],
        );

        $updated = $options->withIsolationLevel(makeIsolationLevel());

        expect($updated->shouldRetry(new ThirdPartyException()))->toBeTrue();
    });
});

describe('withRetryableExceptions()', function () {

    it('returns a new instance', function () {
        $original = TransactionOptions::default();
        $updated = $original->withRetryableExceptions([ThirdPartyException::class]);

        expect($updated)->not->toBe($original);
    });

    it('new array predicate is active on the returned instance', function () {
        $updated = TransactionOptions::default()
            ->withRetryableExceptions([ThirdPartyException::class])
        ;

        expect($updated->shouldRetry(new ThirdPartyException()))->toBeTrue()
            ->and($updated->shouldRetry(new AnotherAppException()))->toBeFalse()
        ;
    });

    it('new callable predicate is active on the returned instance', function () {
        $updated = TransactionOptions::default()
            ->withRetryableExceptions(fn (Throwable $e): bool => $e instanceof ThirdPartyException)
        ;

        expect($updated->shouldRetry(new ThirdPartyException()))->toBeTrue();
    });

    it('preserves attempts and isolationLevel', function () {
        $level = makeIsolationLevel();
        $options = new TransactionOptions(attempts: 5, isolationLevel: $level);
        $updated = $options->withRetryableExceptions([ThirdPartyException::class]);

        expect($updated->attempts)->toBe(5)
            ->and($updated->isolationLevel)->toBe($level)
        ;
    });

    it('runs array validation on the new value', function () {
        TransactionOptions::default()->withRetryableExceptions([stdClass::class]);
    })->throws(InvalidArgumentException::class, 'must implement Throwable');
});

describe('withoutRetryableExceptions()', function () {

    it('returns a new instance with predicate removed', function () {
        $options = new TransactionOptions(
            retryableExceptions: [ThirdPartyException::class],
        );
        $updated = $options->withoutRetryableExceptions();

        expect($updated->shouldRetry(new ThirdPartyException()))->toBeFalse()
            ->and($updated)->not->toBe($options)
        ;
    });

    it('RetryableException marker still works after withoutRetryableExceptions()', function () {
        $options = new TransactionOptions(
            retryableExceptions: [ThirdPartyException::class],
        );
        $updated = $options->withoutRetryableExceptions();

        expect($updated->shouldRetry(new DeadlockException()))->toBeTrue()
            ->and($updated->shouldRetry(new MyOptimisticLockException()))->toBeTrue()
        ;
    });

    it('preserves attempts and isolationLevel', function () {
        $level = makeIsolationLevel();
        $options = new TransactionOptions(
            attempts: 4,
            isolationLevel: $level,
            retryableExceptions: [ThirdPartyException::class],
        );

        $updated = $options->withoutRetryableExceptions();

        expect($updated->attempts)->toBe(4)
            ->and($updated->isolationLevel)->toBe($level)
        ;
    });

    it('is a no-op on shouldRetry() when retryableExceptions was already null', function () {
        $options = TransactionOptions::default();
        $updated = $options->withoutRetryableExceptions();

        expect($updated->shouldRetry(new ThirdPartyException()))->toBeFalse();
    });
});

describe('immutability', function () {

    it('original instance is not modified after any with*() call', function () {
        $original = new TransactionOptions(attempts: 1, isolationLevel: null);

        $original->withAttempts(5);
        $original->withIsolationLevel(makeIsolationLevel());
        $original->withRetryableExceptions([ThirdPartyException::class]);

        expect($original->attempts)->toBe(1)
            ->and($original->isolationLevel)->toBeNull()
            ->and($original->shouldRetry(new ThirdPartyException()))->toBeFalse()
        ;
    });

    it('chained with*() calls each produce a distinct instance', function () {
        $a = TransactionOptions::default();
        $b = $a->withAttempts(2);
        $c = $b->withAttempts(3);

        expect($a->attempts)->toBe(1)
            ->and($b->attempts)->toBe(2)
            ->and($c->attempts)->toBe(3)
            ->and($a)->not->toBe($b)
            ->and($b)->not->toBe($c);
    });
});
