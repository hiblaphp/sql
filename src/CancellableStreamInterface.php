<?php

declare(strict_types=1);

namespace Hibla\Sql;

/**
 * Defines a stream that can be explicitly cancelled before it naturally completes.
 */
interface CancellableStreamInterface
{
    /**
     * Cancels the stream and releases underlying server resources.
     */
    public function cancel(): void;

    /**
     * Checks if the stream has been cancelled.
     */
    public function isCancelled(): bool;
}