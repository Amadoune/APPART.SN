<?php

namespace Appart\Modules\AdministrationAudit\Application\Contract;

use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionIdentityConflict;
use Appart\Modules\AdministrationAudit\Domain\Exception\ConcurrentAdministrativeActionModification;
use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

interface AdministrativeActionRegistry
{
    /** Returns a detached Aggregate. */
    public function find(AdministrativeActionId $id): ?AdministrativeAction;

    /**
     * Atomically adds an action.
     *
     * @throws AdministrativeActionIdentityConflict
     */
    public function add(AdministrativeAction $action): void;

    /**
     * Saves only when the stored version equals expectedVersion.
     *
     * @throws ConcurrentAdministrativeActionModification
     */
    public function save(AdministrativeAction $action, int $expectedVersion): void;
}
