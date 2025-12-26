<?php

declare(strict_types=1);

namespace Twitter\IAM\UI\REST\Request;

use Symfony\Component\HttpFoundation\Request;
use Twitter\IAM\Domain\Auth\Exception\BadRequestException;

final class LogoutRequestMapper
{
    public function fromHttp(Request $request): LogoutRequest
    {
        try {
            $payload = json_decode(
                json: $request->getContent(),
                associative: true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (\JsonException) {
            throw new BadRequestException('Invalid JSON.');
        }

        return new LogoutRequest(
            refreshToken: (string) ($payload['refreshToken'] ?? ''),
        );
    }
}
