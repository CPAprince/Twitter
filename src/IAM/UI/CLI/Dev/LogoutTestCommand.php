<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\CLI\Dev;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twitter\IAM\Application\Logout\LogoutCommand;
use Twitter\IAM\Application\Logout\LogoutHandler;
use Twitter\IAM\Domain\Auth\Exception\TokenInvalidException;

/**
 * @deprecated Dev-only command. TODO: remove after logout flow is covered by proper fixtures/tests.
 */
#[AsCommand(name: 'app:logout-test', description: 'Revoke refresh token (test)')]
final class LogoutTestCommand extends Command
{
    public function __construct(private readonly LogoutHandler $handler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'User UUID string')
            ->addArgument('refreshToken', InputArgument::REQUIRED, 'Raw refresh token');
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
            ($this->handler)(new LogoutCommand(userId: $userId, refreshToken: $refreshToken));
            $output->writeln('OK revoked.');

            return Command::SUCCESS;
        } catch (TokenInvalidException $e) {
            $output->writeln('ERROR: '.TokenInvalidException::ERROR_CODE.' — '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
