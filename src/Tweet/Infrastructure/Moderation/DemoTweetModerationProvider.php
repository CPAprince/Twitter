<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Moderation;

use Twitter\Tweet\Application\Moderation\ModerationBatchItem;
use Twitter\Tweet\Application\Moderation\ModerationConfig;
use Twitter\Tweet\Application\Moderation\ModerationDecision;
use Twitter\Tweet\Application\Moderation\TweetModerationProviderInterface;

final readonly class DemoTweetModerationProvider implements TweetModerationProviderInterface
{
    public function __construct(
        private ModerationConfig $config,
    ) {
    }

    public function moderateBatch(array $items): array
    {
        $decisions = [];

        foreach ($items as $item) {
            if (!$item instanceof ModerationBatchItem) {
                throw new \InvalidArgumentException('All items must be instances of ModerationBatchItem.');
            }

            $approved = $this->isApprovedByProbability($this->config->demoApprovePercent);

            $decisions[] = new ModerationDecision(
                tweetId: $item->tweetId,
                approved: $approved,
                reason: $approved ? null : 'Rejected in demo mode by probability policy.',
                categories: [],
            );
        }

        return $decisions;
    }

    private function isApprovedByProbability(int $approvePercent): bool
    {
        if ($approvePercent <= 0) {
            return false;
        }

        if ($approvePercent >= 100) {
            return true;
        }

        return random_int(1, 100) <= $approvePercent;
    }
}
