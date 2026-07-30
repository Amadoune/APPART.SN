<?php

namespace Appart\Modules\ModerationReports\Application\ModerationPersistence;

use DateTimeImmutable;

final readonly class ModerationCasePersistenceState
{
    /**
     * @param  list<ModerationPersistenceRecord>  $reports
     * @param  list<ModerationPersistenceRecord>  $findings
     * @param  list<ModerationPersistenceRecord>  $decisions
     */
    public function __construct(
        public string $caseId,
        public string $targetType,
        public string $targetId,
        public string $status,
        public ?string $currentDecisionId,
        public int $version,
        public string $intentId,
        public string $intentChecksum,
        public DateTimeImmutable $updatedAt,
        public array $reports,
        public array $findings,
        public array $decisions,
    ) {}
}
