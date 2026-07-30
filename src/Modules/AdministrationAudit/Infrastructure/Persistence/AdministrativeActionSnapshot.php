<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence;

final readonly class AdministrativeActionSnapshot
{
    /** @param list<AuditEntrySnapshot> $auditEntries */
    public function __construct(
        public string $id,
        public string $authorId,
        public string $targetId,
        public string $actionType,
        public bool $requiresFourEyes,
        public string $lastChangedAt,
        public string $status,
        public ?string $reason,
        public ?ApprovalSnapshot $approval,
        public ?DecisionSnapshot $decision,
        public array $auditEntries,
        public int $version,
    ) {}
}
