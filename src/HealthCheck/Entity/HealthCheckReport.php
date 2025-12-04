<?php

declare(strict_types=1);

namespace Twitter\HealthCheck\Entity;

final class HealthCheckReport
{
    public ?int $id = null {
        get => $this->id;
    }

    public HealthCheckReportStatus $status {
        get => $this->status;
    }

    public \DateTimeImmutable $createdAt {
        get => $this->createdAt;
    }

    public function __construct(HealthCheckReportStatus $status = HealthCheckReportStatus::OK)
    {
        $this->status = $status;
        $this->createdAt = new \DateTimeImmutable();
    }
}
