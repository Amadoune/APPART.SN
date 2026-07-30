<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

use DateTimeImmutable;

final readonly class ModerationCaseViewV1
{
    public function __construct(
        public string $caseId,
        public string $targetType,
        public string $targetId,
        public string $status,
        public ?string $currentDecisionId,
        public int $version,
        public DateTimeImmutable $updatedAt,
    ) {}
}
