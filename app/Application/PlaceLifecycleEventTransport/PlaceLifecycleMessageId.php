<?php

namespace App\Application\PlaceLifecycleEventTransport;

final readonly class PlaceLifecycleMessageId
{
    private function __construct(public string $value) {}

    public static function derive(
        PlaceLifecycleTransportVersion $version,
        PlaceLifecycleTransportChecksum $checksum,
    ): self {
        return new self('place-lifecycle-delivery-'.hash(
            'sha256',
            implode("\n", [(string) $version->value, $checksum->value]),
        ));
    }
}
