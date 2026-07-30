<?php

namespace Appart\Modules\AdministrationAudit\Application\PublicAuditAppend;

use InvalidArgumentException;

final readonly class AdministrationAuditRecordIdV1
{
    private function __construct(public string $value) {}

    public static function deterministic(
        AdministrationAuditSourceOwnerV1 $sourceOwner,
        AdministrationAuditOperationV1 $operation,
        string $correlationId,
    ): self {
        self::assertUuid($correlationId, 'correlationId');
        $hex = substr(hash(
            'sha256',
            'administration-audit-append-v1|'.$sourceOwner->value.'|'.$operation->value.'|'.strtolower($correlationId),
        ), 0, 32);
        $hex[12] = '5';
        $hex[16] = dechex((hexdec($hex[16]) & 0x3) | 0x8);

        return new self(sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20),
        ));
    }

    public static function assertUuid(string $value, string $field): void
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', strtolower($value)) !== 1) {
            throw new InvalidArgumentException($field.' must be a UUID.');
        }
    }
}
