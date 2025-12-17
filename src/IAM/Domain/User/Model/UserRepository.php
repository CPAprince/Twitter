<?php

declare(strict_types=1);

namespace Twitter\IAM\Domain\User\Model;

interface UserRepository
{
    public function save(User $user): void;

    public function existsByEmail(Email $email): bool;
}
