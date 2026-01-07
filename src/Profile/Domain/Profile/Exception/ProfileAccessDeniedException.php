<?php

declare(strict_types=1);

namespace Twitter\Profile\Domain\Profile\Exception;

use Exception;

final class ProfileAccessDeniedException extends Exception
{
    public function __construct(string $userId)
    {
        parent::__construct('Profile access denied for user with id "'.$userId.'"');
    }
}
