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
use Twitter\IAM\Infrastructure\Persistence\Auth\RefreshTokenHasher;
use Twitter\Shared\Infrastructure\Persistence\UuidBinaryConverter;

#[AsCommand(name: 'app:seed-refresh', description: 'Seed one refresh token row for logout testing')]
final class SeedRefreshTokenCommand extends Command
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
        $userId = (string) $input->getArgument('userId');
        $refreshToken = (string) $input->getArgument('refreshToken');

        $expiresAt = (new \DateTimeImmutable())->modify('+1 day');
        $now = new \DateTimeImmutable();

        $this->connection->insert('refresh_tokens', [
            'id' => Uuid::v7()->toBinary(),
            'user_id' => UuidBinaryConverter::toBytes($userId),
            'token_hash' => $this->hasher->hash($refreshToken),
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'revoked_at' => null,
            'created_at' => $now->format('Y-m-d H:i:s'),
        ], [
            'id' => ParameterType::BINARY,
            'user_id' => ParameterType::BINARY,
            'token_hash' => ParameterType::BINARY,
        ]);

        $output->writeln('OK seeded refresh_tokens row.');
        $output->writeln('userId: '.$userId);
        $output->writeln('refreshToken: '.$refreshToken);

        return Command::SUCCESS;
    }
}
