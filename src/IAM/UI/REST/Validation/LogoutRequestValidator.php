<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\Validation;

use Twitter\IAM\Domain\Auth\Exception\ValidationErrorException;
use Twitter\IAM\UI\REST\Request\LogoutRequest;

final class LogoutRequestValidator
{
    public function validate(LogoutRequest $request): void
    {
        if ('' === $request->refreshToken) {
            throw new ValidationErrorException('refreshToken is required.');
        }
    }
}
