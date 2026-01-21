<?php

declare(strict_types=1);

namespace Twitter\Profile\Domain\Profile\Model;

use Twitter\Profile\Domain\Profile\Exception\ProfileNotFoundException;

interface ProfileRepository
{
    public function add(Profile $profile);

    /**
     * @throws ProfileNotFoundException
     */
    public function getByUserId(string $userId): Profile;

    /**
     * @param string[] $userIds
     * @return array<string, string>
     */
    public function getNamesByUserIds(array $userIds): array;
}
