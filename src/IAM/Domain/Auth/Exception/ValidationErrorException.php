<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\Auth\Exception;

final class ValidationErrorException extends \Exception
{
    public const ERROR_CODE = 'VALIDATION_ERROR';

    public function __construct(string $message = 'Validation error.')
    {
        parent::__construct($message);
    }
}
