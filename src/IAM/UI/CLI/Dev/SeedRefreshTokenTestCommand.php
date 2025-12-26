<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\CLI\Dev;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use Twitter\IAM\Infrastructure\Auth\RefreshTokenHasher;
use Twitter\Shared\Infrastructure\Persistence\Doctrine\UuidBinaryConverter;

/**
 * @deprecated Dev-only command. TODO: remove after logout flow is covered by proper fixtures/tests.
 */
#[AsCommand(name: 'app:seed-refresh', description: 'Seed one refresh token row for logout testing')]
final class SeedRefreshTokenTestCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RefreshTokenHasher $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'UUID string (v7) of user')
            ->addArgument('refreshToken', InputArgument::REQUIRED, 'Raw refresh token string');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = trim((string) $input->getArgument('userId'));
        $refreshToken = trim((string) $input->getArgument('refreshToken'));

        if ('' === $userId || '' === $refreshToken) {
            $output->writeln('ERROR: userId and refreshToken are required.');

            return Command::FAILURE;
        }

        try {
            // Validate UUID format and convert to binary(16)
            $userIdBytes = UuidBinaryConverter::toBytes($userId);
        } catch (\InvalidArgumentException) {
            $output->writeln('ERROR: Invalid userId UUID.');

            return Command::FAILURE;
        }

        $expiresAt = (new \DateTimeImmutable())->modify('+1 day');
        $now = new \DateTimeImmutable();

        $this->connection->insert('refresh_tokens', [
            'id' => Uuid::v7()->toBinary(),
            'user_id' => $userIdBytes,
            'token_hash' => $this->hasher->hash($refreshToken),
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'revoked_at' => null,
            'created_at' => $now->format('Y-m-d H:i:s'),
        ], [
            'id' => ParameterType::BINARY,
            'user_id' => ParameterType::BINARY,
            'token_hash' => ParameterType::BINARY,
            'expires_at' => ParameterType::STRING,
            'revoked_at' => ParameterType::NULL,
            'created_at' => ParameterType::STRING,
        ]);

        $output->writeln('OK seeded refresh_tokens row.');
        $output->writeln('userId: '.$userId);
        $output->writeln('refreshToken: '.$refreshToken);
        $output->writeln('expiresAt: '.$expiresAt->format(\DateTimeInterface::ATOM));

        return Command::SUCCESS;
    }
}
