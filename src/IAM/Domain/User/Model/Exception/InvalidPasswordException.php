<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model\Exception;

final class InvalidPasswordException extends \Exception
{
    public function __construct(string $message = 'Invalid password', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
