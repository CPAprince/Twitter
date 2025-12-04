<?php

namespace Twitter\HealthCheck\Entity;

use DateTimeImmutable;

class HealthCheckReport
{
    public ?int $id = null {
        get => $this->id;
    }

    public string $status {
        get => $this->status;
    }

    public DateTimeImmutable $createdAt {
        get => $this->createdAt;
    }

    public function __construct(HealthCheckReportStatus $status = HealthCheckReportStatus::OK)
    {
        $this->status = $status->value;
        $this->createdAt = new DateTimeImmutable();
    }
}
