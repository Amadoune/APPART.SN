<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\Contract;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorMutation;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorWriteResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\AdministrativeActionLifecyclePersistenceReadResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentCheckpoint;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionPersistenceCoexistence\AdministrativeActionLifecycleEnrollmentResult;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

interface AdministrativeActionLifecycleWorkflowStore
{
    public function enroll(
        AdministrativeActionLifecycleEnrollmentCheckpoint $checkpoint,
    ): AdministrativeActionLifecycleEnrollmentResult;

    public function append(
        AdministrativeActionHistoricalMirrorMutation $mutation,
    ): AdministrativeActionHistoricalMirrorWriteResult;

    public function read(
        AdministrativeActionId $actionId,
    ): AdministrativeActionLifecyclePersistenceReadResult;
}
