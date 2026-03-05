<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\AI;

use OpenAI;
use Twitter\Tweet\Domain\Tweet\ModerationServiceInterface;

class OpenAIModerationService implements ModerationServiceInterface
{
    private $client;

    public function __construct(string $apiKey)
    {
        $this->client = OpenAI::client($apiKey);
    }

    public function isAllowed(string $text): bool
    {
        $response = $this->client->moderations()->create([
            'model' => 'omni-moderation-latest',
            'input' => $text,
        ]);

        return !$response->results[0]->flagged;
    }
}
