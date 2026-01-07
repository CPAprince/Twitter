<?php

declare(strict_types=1);

namespace Twitter\Profile\Application\UseCase\UpdateProfile;

final readonly class UpdateProfileCommand
{
    public function __construct(
        public string $userId,
        public string $name,
        public string $bio,
    ) {}
}
