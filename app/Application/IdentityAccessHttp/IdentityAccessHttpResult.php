<?php

namespace App\Application\IdentityAccessHttp;

final readonly class IdentityAccessHttpResult
{
    /** @param array<string, bool|int|string|null> $publicData */
    public function __construct(
        public IdentityAccessHttpStatus $status,
        public array $publicData = [],
        public ?string $sessionSecret = null,
        public ?int $sessionExpiresInSeconds = null,
    ) {}
}
