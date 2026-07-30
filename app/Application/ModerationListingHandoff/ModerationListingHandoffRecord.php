<?php

namespace App\Application\ModerationListingHandoff;

use DateTimeImmutable;

final readonly class ModerationListingHandoffRecord
{
    public function __construct(
        public string $messageId,
        public string $caseId,
        public string $decisionId,
        public string $commandId,
        public string $checksum,
        public ModerationListingHandoffStatus $status,
        public DateTimeImmutable $recordedAt,
    ) {}
}
