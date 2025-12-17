<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\CLI\Dev;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twitter\IAM\Application\Logout\LogoutService;
use Twitter\IAM\Domain\Auth\Exception\AuthTokenInvalidException;

#[AsCommand(name: 'app:logout-test', description: 'Revoke refresh token (test)')]
final class LogoutTestCommand extends Command
{
    public function __construct(private readonly LogoutService $service)
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
        $userId = (string) $input->getArgument('userId');
        $refreshToken = (string) $input->getArgument('refreshToken');

        try {
            $this->service->logout($userId, $refreshToken);
            $output->writeln('OK revoked.');

            return Command::SUCCESS;
        } catch (AuthTokenInvalidException $e) {
            $output->writeln('ERROR: '.AuthTokenInvalidException::ERROR_CODE.' — '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
