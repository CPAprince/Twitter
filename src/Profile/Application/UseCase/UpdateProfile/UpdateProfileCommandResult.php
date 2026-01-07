<?php

declare(strict_types=1);

namespace Twitter\Profile\Application\UseCase\UpdateProfile;

final readonly class UpdateProfileCommandResult
{
    public function __construct(
        public string $name,
        public string $bio,
    ) {}
}
