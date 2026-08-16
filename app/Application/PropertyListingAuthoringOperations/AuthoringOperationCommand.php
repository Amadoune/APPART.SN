<?php

namespace App\Application\PropertyListingAuthoringOperations;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class AuthoringOperationCommand
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public AuthoringOperation $operation,
        public string $intentId,
        public string $actorAccountId,
        public ?string $propertyId,
        public ?string $listingId,
        public int $expectedVersion,
        public array $data,
        public DateTimeImmutable $occurredAt,
        public ?int $expectedAuthoringVersion = null,
    ) {
        foreach (['intentId' => $intentId, 'actorAccountId' => $actorAccountId] as $field => $value) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', strtolower($value)) !== 1) {
                throw new InvalidArgumentException("Invalid {$field}.");
            }
        }
        if ($expectedVersion < 0) {
            throw new InvalidArgumentException('Invalid expectedVersion.');
        }
        if ($expectedAuthoringVersion !== null && $expectedAuthoringVersion < 1) {
            throw new InvalidArgumentException('Invalid expectedAuthoringVersion.');
        }
    }

    public function checksum(): string
    {
        $data = $this->data;
        ksort($data);

        return hash('sha256', (string) json_encode([
            'actorAccountId' => strtolower($this->actorAccountId),
            'data' => $data,
            'expectedVersion' => $this->expectedVersion,
            'expectedAuthoringVersion' => $this->expectedAuthoringVersion,
            'intentId' => strtolower($this->intentId),
            'listingId' => $this->listingId === null ? null : strtolower($this->listingId),
            'occurredAt' => $this->occurredAt->format('Y-m-d\TH:i:s.uP'),
            'operation' => $this->operation->value,
            'propertyId' => $this->propertyId === null ? null : strtolower($this->propertyId),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
