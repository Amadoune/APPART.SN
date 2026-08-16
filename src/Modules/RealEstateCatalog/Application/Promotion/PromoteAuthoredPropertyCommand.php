<?php

namespace Appart\Modules\RealEstateCatalog\Application\Promotion;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class PromoteAuthoredPropertyCommand
{
    public function __construct(
        public string $propertyId,
        public string $ownerAccountId,
        public int $expectedAuthoringVersion,
        public string $commandId,
        public DateTimeImmutable $occurredAt,
    ) {
        foreach ([$propertyId, $ownerAccountId, $commandId] as $id) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', strtolower($id)) !== 1) {
                throw new InvalidArgumentException('Promotion identities must be canonical UUIDs.');
            }
        }
        if ($expectedAuthoringVersion < 1) {
            throw new InvalidArgumentException('The expected Authoring version must be positive.');
        }
    }
}
