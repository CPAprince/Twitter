<?php

declare(strict_types=1);

namespace Twitter\HealthCheck\Entity;

enum HealthCheckReportStatus: string
{
    case OK = 'ok';
}
