<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent;

final readonly class AdministrativeActionLifecycleEventChecksum
{
    private function __construct(public string $value) {}

    /** @param array<string, mixed> $canonical */
    public static function derive(array $canonical): self
    {
        return new self(hash('sha256', json_encode(
            $canonical,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        )));
    }
}
