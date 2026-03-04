<?php

declare(strict_types=1);

namespace Twitter\Profile\Application\UseCase\GetProfile;

use Twitter\Profile\Domain\Profile\Exception\ProfileNotFoundException;

interface GetProfileQueryHandlerInterface
{
    /**
     * @throws ProfileNotFoundException
     */
    public function handle(GetProfileQuery $query): GetProfileQueryResult;
}
