<?php

namespace Appart\Modules\ListingLifecycle\Application\Creation;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CreateListingDraftCommandV1
{
    public function __construct(
        public string $intentId,
        public string $listingId,
        public string $propertyId,
        public string $revisionId,
        public string $actorId,
        public DateTimeImmutable $occurredAt,
    ) {
        foreach (['intentId' => $intentId, 'listingId' => $listingId, 'propertyId' => $propertyId, 'revisionId' => $revisionId] as $field => $value) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', strtolower(trim($value))) !== 1) {
                throw new InvalidArgumentException("Invalid {$field}.");
            }
        }
        if (trim($actorId) === '' || mb_strlen($actorId) > 100) {
            throw new InvalidArgumentException('Invalid actorId.');
        }
    }

    public function checksum(): string
    {
        $canonical = [
            'actorId' => trim($this->actorId),
            'intentId' => strtolower(trim($this->intentId)),
            'listingId' => strtolower(trim($this->listingId)),
            'occurredAt' => $this->occurredAt->format('Y-m-d\TH:i:s.uP'),
            'origin' => 'advertiser',
            'propertyId' => strtolower(trim($this->propertyId)),
            'reason' => 'INITIAL_DRAFT',
            'revisionId' => strtolower(trim($this->revisionId)),
            'trigger' => 'AUTHORING_CREATE',
            'version' => 1,
        ];

        return hash('sha256', (string) json_encode($canonical, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
