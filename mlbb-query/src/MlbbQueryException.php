<?php

namespace Wasit\MlbbQuery;

use RuntimeException;
use Throwable;

class MlbbQueryException extends RuntimeException
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
