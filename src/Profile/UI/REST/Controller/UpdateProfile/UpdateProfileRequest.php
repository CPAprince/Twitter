<?php

declare(strict_types=1);

namespace Twitter\Profile\UI\REST\Controller\UpdateProfile;

use Assert\Assert;

final readonly class UpdateProfileRequest
{
    public function __construct(
        private string $name,
        private string $bio,
    ) {
        Assert::lazy()->tryAll()
            ->that($this->name, 'name')->minLength(3)->maxLength(50)
            ->that($this->bio, 'bio')->maxLength(300)
            ->verifyNow();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function bio(): string
    {
        return $this->bio;
    }
}
