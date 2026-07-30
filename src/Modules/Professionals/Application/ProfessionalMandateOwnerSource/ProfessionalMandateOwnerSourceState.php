<?php

namespace Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource;

use DateTimeImmutable;

final readonly class ProfessionalMandateOwnerSourceState
{
    /**
     * @param  non-empty-string  $accountId
     * @param  list<non-empty-string>  $professionalIds  Canonical sorted unique UUIDs.
     * @param  non-empty-string  $intentId
     * @param  non-empty-string  $intentChecksum
     */
    public function __construct(
        public string $accountId,
        public array $professionalIds,
        public int $version,
        public string $intentId,
        public string $intentChecksum,
        public DateTimeImmutable $recordedAt,
    ) {}
}
