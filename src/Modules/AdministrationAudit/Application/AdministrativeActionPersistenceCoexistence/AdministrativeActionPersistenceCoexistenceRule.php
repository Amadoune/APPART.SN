<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence;

final readonly class AdministrativeActionPersistenceCoexistenceRule
{
    public function __construct(
        public AdministrativeActionPersistenceOperation $operation,
        public AdministrativeActionPersistenceOwner $authoritativeOwner,
        public AdministrativeActionPersistenceWriteMode $writeMode,
        public bool $fallbackAllowed,
    ) {}
}
