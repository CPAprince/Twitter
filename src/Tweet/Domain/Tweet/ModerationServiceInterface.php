<?php

declare(strict_types=1);

namespace Twitter\Tweet\Domain\Tweet;

interface ModerationServiceInterface
{
    public function isAllowed(string $text): bool;
}
