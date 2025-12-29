<?php

declare(strict_types=1);

namespace Twitter\Profile\Domain\Profile\Model;

interface ProfileRepository
{
    public function add(Profile $profile);
}
