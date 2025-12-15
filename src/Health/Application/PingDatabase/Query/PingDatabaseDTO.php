<?php

declare(strict_types=1);

namespace Twitter\Health\Application\PingDatabase\Query;

final readonly class PingDatabaseDTO implements \JsonSerializable
{
    public \DateTimeImmutable $date;

    public function __construct(
        public string $status,
        public string $message,
    ) {
        $this->date = new \DateTimeImmutable();
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'pingedAt' => $this->date->format(\DateTimeInterface::RFC3339),
        ];
    }
}
