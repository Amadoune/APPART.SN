<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessEvent;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class IdentityAccessEventV1
{
    public string $eventId;

    public string $checksum;

    public IdentityAccessEventOwner $owner;

    public function __construct(
        public IdentityAccessEventType $type,
        public AccountId $accountId,
        public int $aggregateVersion,
        public DateTimeImmutable $occurredAt,
        public DateTimeImmutable $recordedAt,
        public string $correlationId,
        public string $causationId,
    ) {
        if ($aggregateVersion < 1
            || preg_match(self::uuidPattern(), $correlationId) !== 1
            || preg_match(self::uuidPattern(), $causationId) !== 1) {
            throw new InvalidArgumentException('Invalid Identity & Access event evidence.');
        }

        $this->owner = IdentityAccessEventOwner::for($type);
        $canonicalEvidence = implode("\n", [
            $type->value,
            $this->owner->value,
            $accountId->value,
            (string) $aggregateVersion,
            $occurredAt->format('Y-m-d\TH:i:s.uP'),
            $correlationId,
            $causationId,
        ]);
        $this->eventId = hash('sha256', $canonicalEvidence);
        $this->checksum = hash('sha256', self::canonicalJson($this->contractWithoutChecksum()));
    }

    /** @return array<string, int|string> */
    public function contract(): array
    {
        return [...$this->contractWithoutChecksum(), 'checksum' => $this->checksum];
    }

    /** @return array<string, int|string> */
    private function contractWithoutChecksum(): array
    {
        return [
            'eventId' => $this->eventId,
            'eventType' => $this->type->value,
            'payloadVersion' => 1,
            'owner' => $this->owner->value,
            'accountId' => $this->accountId->value,
            'aggregateVersion' => $this->aggregateVersion,
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
