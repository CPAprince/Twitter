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

    public function flush(): void;
}
