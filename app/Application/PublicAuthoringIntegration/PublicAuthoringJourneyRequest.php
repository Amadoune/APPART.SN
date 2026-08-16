<?php

namespace App\Application\PublicAuthoringIntegration;

use DateTimeImmutable;

final readonly class PublicAuthoringJourneyRequest
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public PublicAuthoringJourneyOperation $operation,
        public string $intentId,
        public string $accountId,
        public ?string $propertyId,
        public ?string $listingId,
        public int $expectedVersion,
        public array $data,
        public DateTimeImmutable $occurredAt,
        public ?int $expectedAuthoringVersion = null,
    ) {}
}
