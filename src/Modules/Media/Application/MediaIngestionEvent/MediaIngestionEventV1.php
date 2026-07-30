<?php

namespace Appart\Modules\Media\Application\MediaIngestionEvent;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class MediaIngestionEventV1
{
    public string $eventId;

    public string $checksum;

    public MediaIngestionEventOwner $owner;

    public function __construct(
        public MediaIngestionEventType $type,
        public string $assetId,
        public int $aggregateVersion,
        public string $policyVersion,
        public DateTimeImmutable $occurredAt,
        public DateTimeImmutable $recordedAt,
        public string $correlationId,
        public string $causationId,
    ) {
        if ($aggregateVersion < 1
            || preg_match(self::uuidPattern(), $assetId) !== 1
            || preg_match(self::uuidPattern(), $correlationId) !== 1
            || preg_match(self::uuidPattern(), $causationId) !== 1
            || preg_match('/^[a-z0-9][a-z0-9._-]{0,63}$/', $policyVersion) !== 1) {
            throw new InvalidArgumentException('Invalid Media Ingestion event evidence.');
        }
        $this->owner = MediaIngestionEventOwner::Asset;
        $this->eventId = hash('sha256', implode("\n", [
            $type->value,
            $this->owner->value,
            $assetId,
            (string) $aggregateVersion,
            $policyVersion,
            $occurredAt->format('Y-m-d\TH:i:s.uP'),
            $correlationId,
            $causationId,
        ]));
        $this->checksum = hash('sha256', self::canonicalJson($this->withoutChecksum()));
    }

    /** @return array<string, int|string> */
    public function contract(): array
    {
        return [...$this->withoutChecksum(), 'checksum' => $this->checksum];
    }

    /** @return array<string, int|string> */
    private function withoutChecksum(): array
    {
        return [
            'eventId' => $this->eventId,
            'eventType' => $this->type->value,
            'payloadVersion' => 1,
            'owner' => $this->owner->value,
            'assetId' => $this->assetId,
            'aggregateVersion' => $this->aggregateVersion,
            'policyVersion' => $this->policyVersion,
            'occurredAt' => $this->occurredAt->format('Y-m-d\TH:i:s.uP'),
            'recordedAt' => $this->recordedAt->format('Y-m-d\TH:i:s.uP'),
            'correlationId' => $this->correlationId,
            'causationId' => $this->causationId,
        ];
    }

    /** @param array<string, int|string> $value */
    private static function canonicalJson(array $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private static function uuidPattern(): string
    {
        return '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
    }
}
