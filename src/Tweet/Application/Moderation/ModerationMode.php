<?php

declare(strict_types=1);

namespace Twitter\Tweet\Application\Moderation;

enum ModerationMode: string
{
    case LIVE = 'live';
    case DEMO = 'demo';
    case BYPASS = 'bypass';

    public static function fromString(string $value): self
    {
        return match (mb_strtolower($value)) {
            'live' => self::LIVE,
            'demo' => self::DEMO,
            'bypass' => self::BYPASS,
            default => throw new \InvalidArgumentException(sprintf(
                'Unsupported moderation mode "%s". Allowed values: live, demo, bypass.',
                $value
            )),
        };
    }

    public function isLive(): bool
    {
        return $this === self::LIVE;
    }

    public function isDemo(): bool
    {
        return $this === self::DEMO;
    }

    public function isBypass(): bool
    {
        return $this === self::BYPASS;
    }
}
