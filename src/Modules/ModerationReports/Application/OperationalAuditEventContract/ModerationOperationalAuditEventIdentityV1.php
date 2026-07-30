<?php

namespace Appart\Modules\ModerationReports\Application\OperationalAuditEventContract;

use DateTimeImmutable;
use InvalidArgumentException;

final class ModerationOperationalAuditEventIdentityV1
{
    /** @param array<string, int|string> $payload */
    public static function checksum(
        ModerationOperationalAuditEventTypeV1 $type,
        string $caseId,
        int $aggregateVersion,
        array $payload,
        string $policyVersion,
        DateTimeImmutable $occurredAt,
        DateTimeImmutable $recordedAt,
        string $correlationId,
        string $causationId,
    ): string {
        ksort($payload, SORT_STRING);

        return hash('sha256', self::canonical([
            'aggregateVersion' => $aggregateVersion,
            'caseId' => strtolower($caseId),
            'causationId' => strtolower($causationId),
            'correlationId' => strtolower($correlationId),
            'eventType' => $type->value,
            'occurredAt' => $occurredAt->format('Y-m-d\TH:i:s.uP'),
            'payload' => $payload,
            'policyVersion' => $policyVersion,
            'recordedAt' => $recordedAt->format('Y-m-d\TH:i:s.uP'),
        ]));
    }

    public static function eventId(
        ModerationOperationalAuditEventTypeV1 $type,
        string $caseId,
        int $aggregateVersion,
        string $causationId,
    ): string {
        return self::uuid(hash('sha256', implode("\n", [
            'moderation-operational-audit-event-v1',
            $type->value,
            strtolower($caseId),
            (string) $aggregateVersion,
            strtolower($causationId),
        ])));
    }

    public static function assertUuid(string $value, string $field): void
    {
        if (preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value,
        ) !== 1) {
            throw new InvalidArgumentException($field.' must be a UUID.');
        }
    }

    /** @param array<string, mixed> $value */
    private static function canonical(array $value): string
    {
        ksort($value, SORT_STRING);

        return json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    private static function uuid(string $hex): string
    {
        $hex = substr($hex, 0, 32);
        $hex[12] = '5';
        $hex[16] = dechex((hexdec($hex[16]) & 3) | 8);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20),
        );
    }
}
