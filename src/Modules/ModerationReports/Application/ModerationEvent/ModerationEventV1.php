<?php

namespace Appart\Modules\ModerationReports\Application\ModerationEvent;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class ModerationEventV1
{
    public string $eventId;

    public string $checksum;

    /** @param array<string, int|string|null> $payload */
    public function __construct(
        public ModerationEventTypeV1 $type, public string $caseId, public int $aggregateVersion,
        public array $payload, public string $policyVersion, public DateTimeImmutable $occurredAt,
        public DateTimeImmutable $recordedAt, public string $correlationId, public string $causationId,
    ) {
        if ($aggregateVersion < 1) {
            throw new InvalidArgumentException('Aggregate version must be positive.');
        }
        $json = self::canonical($this->fieldsWithoutIdentity());
        $this->checksum = hash('sha256', $json);
        $this->eventId = self::uuid(hash('sha256', implode("\n", [
            'moderation-event-v1', $type->value, $caseId, (string) $aggregateVersion, $causationId,
        ])));
    }

    /** @return array<string, mixed> */
    public function contract(): array
    {
        return ['eventId' => $this->eventId, ...$this->fieldsWithoutIdentity(), 'checksum' => $this->checksum];
    }

    /** @return array<string, mixed> */
    private function fieldsWithoutIdentity(): array
    {
        $payload = $this->payload;
        ksort($payload, SORT_STRING);

        return [
            'eventType' => $this->type->value, 'caseId' => $this->caseId,
            'aggregateVersion' => $this->aggregateVersion, 'payload' => $payload,
            'policyVersion' => $this->policyVersion,
            'occurredAt' => $this->occurredAt->format('Y-m-d\TH:i:s.uP'),
            'recordedAt' => $this->recordedAt->format('Y-m-d\TH:i:s.uP'),
            'correlationId' => $this->correlationId, 'causationId' => $this->causationId,
        ];
    }

    /** @param array<string, mixed> $value */
    private static function canonical(array $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private static function uuid(string $hex): string
    {
        $hex = substr($hex, 0, 32);
        $hex[12] = '5';
        $hex[16] = dechex((hexdec($hex[16]) & 3) | 8);

        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20));
    }
}
