<?php

namespace Lartisan\FacebookDataDeletion\Exceptions;

use RuntimeException;

class FacebookSignedRequestException extends RuntimeException
{
    public function __construct(
        string $message,
        protected readonly int $statusCode = 400,
    ) {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
