<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use InvalidArgumentException;

final readonly class AdministrativeActionLifecycleEnrollmentCheckpoint
{
    public function __construct(
        public AdministrativeActionId $actionId,
        public int $historicalVersion,
        public AdministrativeActionLifecycleState $state,
        public AdministrativeActionLifecycleSourceChecksum $sourceChecksum,
    ) {
        if ($historicalVersion < 0) {
            throw new InvalidArgumentException('The historical version cannot be negative.');
        }
    }
}
