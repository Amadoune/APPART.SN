<?php

namespace Appart\Modules\AdministrationAudit\Infrastructure\Persistence;

final readonly class DecisionSnapshot
{
    public function __construct(
        public string $id,
        public string $outcome,
        public string $decidedBy,
        public string $reason,
        public string $decidedAt,
    ) {}
}
