<?php

namespace Appart\Modules\Professionals\Application\ProfessionalProfilePersistence;

use DateTimeImmutable;

final readonly class ProfessionalPublicPortfolioState
{
    /** @param list<string> $listingIds */
    public function __construct(
        public string $professionalId,
        public array $listingIds,
        public int $checkpoint,
        public int $version,
        public string $intentId,
        public string $intentChecksum,
        public DateTimeImmutable $updatedAt,
    ) {}
}
