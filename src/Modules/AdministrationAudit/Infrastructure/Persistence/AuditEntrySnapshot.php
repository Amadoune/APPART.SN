<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence;

final readonly class AuditEntrySnapshot
{
    public function __construct(
        public int $sequence,
        public string $fact,
        public string $actorId,
        public string $reason,
        public string $recordedAt,
    ) {}
}
