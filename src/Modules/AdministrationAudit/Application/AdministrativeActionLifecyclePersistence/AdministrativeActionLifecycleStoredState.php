<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

final readonly class AdministrativeActionLifecycleStoredState
{
    public function __construct(
        public AdministrativeActionId $actionId,
        public int $version,
        public AdministrativeActionLifecycleState $state,
        public string $entryChecksum,
    ) {}
}
