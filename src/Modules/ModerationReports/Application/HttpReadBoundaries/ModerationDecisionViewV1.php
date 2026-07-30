<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

use DateTimeImmutable;

final readonly class ModerationDecisionViewV1
{
    /** @param array<string, scalar|null> $attributes */
    public function __construct(
        public string $decisionId,
        public array $attributes,
        public DateTimeImmutable $recordedAt,
    ) {}
}
