<?php

declare(strict_types=1);

namespace Twitter\Shared\Infrastructure\Persistence;

final class UuidBinaryConverter
{
    public static function toBytes(string $uuid): string
    {
        $hex = strtolower(str_replace('-', '', $uuid));

        if (!preg_match('/^[0-9a-f]{32}$/', $hex)) {
            throw new \InvalidArgumentException('Invalid UUID string: '.$uuid);
        }

        $bytes = hex2bin($hex);
        if (false === $bytes || 16 !== strlen($bytes)) {
            throw new \InvalidArgumentException('Failed to convert UUID to bytes: '.$uuid);
        }

        return $bytes;
    }
}
