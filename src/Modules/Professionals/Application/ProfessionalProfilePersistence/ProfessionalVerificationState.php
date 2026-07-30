<?php

namespace Appart\Modules\Professionals\Application\ProfessionalProfilePersistence;

use DateTimeImmutable;

final readonly class ProfessionalVerificationState
{
    /** @param list<string> $evidenceReferences */
    public function __construct(
        public string $professionalId,
        public VerificationDisposition $disposition,
        public array $evidenceReferences,
        public string $policyVersion,
        public ?string $decisionAuthorityId,
        public ?DateTimeImmutable $expiresAt,
        public int $decisionSequence,
        public int $version,
        public string $intentId,
        public string $intentChecksum,
        public DateTimeImmutable $updatedAt,
    ) {}
}
