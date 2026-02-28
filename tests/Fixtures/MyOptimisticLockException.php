<?php

declare(strict_types=1);

namespace Test\Fixtures;

use Hibla\Sql\Exceptions\RetryableException;

class MyOptimisticLockException extends \RuntimeException implements RetryableException
{
}
