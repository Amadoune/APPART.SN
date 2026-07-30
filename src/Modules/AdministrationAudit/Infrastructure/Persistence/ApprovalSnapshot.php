<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence;

final readonly class ApprovalSnapshot
{
    public function __construct(
        public string $id,
        public string $approverId,
        public string $approvedAt,
    ) {}
}
