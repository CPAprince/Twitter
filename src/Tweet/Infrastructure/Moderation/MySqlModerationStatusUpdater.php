<?php

declare(strict_types=1);

namespace Twitter\Tweet\Infrastructure\Moderation;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Twitter\Shared\Infrastructure\Persistence\Doctrine\UuidBinaryConverter;
use Twitter\Tweet\Application\Moderation\ModerationConfig;
use Twitter\Tweet\Application\Moderation\ModerationStatusUpdaterInterface;

final readonly class MySqlModerationStatusUpdater implements ModerationStatusUpdaterInterface
{
    public function __construct(
        private Connection $connection,
        private ModerationConfig $config,
    ) {}

    public function markApproved(array $tweetIds): void
    {
        $this->updateStatus($tweetIds, $this->config->statusApproved);
    }

    public function markRejected(array $tweetIds): void
    {
        $this->updateStatus($tweetIds, $this->config->statusRejected);
    }

    /**
     * @param string[] $tweetIds
     */
    private function updateStatus(array $tweetIds, int $targetStatus): void
    {
        if ([] === $tweetIds) {
            return;
        }

        $binaryIds = array_map(
            static fn (string $id): string => UuidBinaryConverter::toBytes($id),
            $tweetIds
        );

        $placeholders = implode(', ', array_fill(0, count($binaryIds), '?'));

        $sql = sprintf(
            'UPDATE tweets
             SET moderation_status = ?,
                 moderated_at = UTC_TIMESTAMP()
             WHERE id IN (%s)
               AND moderation_status = ?',
            $placeholders
        );

        $params = [
            $targetStatus,
            ...$binaryIds,
            $this->config->statusPending,
        ];

        $types = [
            ParameterType::INTEGER,
            ...array_fill(0, count($binaryIds), ParameterType::BINARY),
            ParameterType::INTEGER,
        ];

        $this->connection->executeStatement($sql, $params, $types);
    }
}
