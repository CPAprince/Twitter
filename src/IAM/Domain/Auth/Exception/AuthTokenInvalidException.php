<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\Auth\Exception;

final class AuthTokenInvalidException extends \Exception
{
    public const ERROR_CODE = 'AUTH_TOKEN_INVALID';

    public function __construct(string $message = 'Refresh token is invalid or expired.')
    {
        parent::__construct($message);
    }
}
