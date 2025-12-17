<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model\Exception;

use Twitter\IAM\Domain\User\Model\Email;

final class InvalidEmailException extends \Exception
{
    public function __construct(string $message = 'Invalid email address', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
