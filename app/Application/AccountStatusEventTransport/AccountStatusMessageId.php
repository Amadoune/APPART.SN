<?php

namespace App\Application\AccountStatusEventTransport;

final readonly class AccountStatusMessageId
{
    private function __construct(public string $value) {}

    public static function derive(
        AccountStatusTransportVersion $version,
        AccountStatusTransportChecksum $checksum,
    ): self {
        return new self('account-status-delivery-'.hash(
            'sha256',
            implode("\n", [(string) $version->value, $checksum->value]),
        ));
    }
}
