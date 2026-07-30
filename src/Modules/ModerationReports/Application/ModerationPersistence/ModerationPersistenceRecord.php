<?php

namespace Appart\Modules\ModerationReports\Application\ModerationPersistence;

use DateTimeImmutable;

final readonly class ModerationPersistenceRecord
{
    /** @param array<string, mixed> $payload */
    public function __construct(
        public string $id,
        public array $payload,
        public DateTimeImmutable $recordedAt,
    ) {}
}
