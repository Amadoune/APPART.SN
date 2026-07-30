<?php

namespace Tests\Unit\Modules\AdministrationAudit\Support;

use Appart\Modules\AdministrationAudit\Application\Contract\AdministrativeActionRegistry;
use Appart\Modules\AdministrationAudit\Domain\Exception\AdministrativeActionIdentityConflict;
use Appart\Modules\AdministrationAudit\Domain\Exception\ConcurrentAdministrativeActionModification;
use Appart\Modules\AdministrationAudit\Domain\Model\AdministrativeAction;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;

final class FakeAdministrativeActionRegistry implements AdministrativeActionRegistry
{
    /** @var array<string,AdministrativeAction> */
    private array $actions = [];

    private bool $failNextSave = false;

    public function find(AdministrativeActionId $id): ?AdministrativeAction
    {
        return isset($this->actions[$id->value]) ? clone $this->actions[$id->value] : null;
    }

    public function add(AdministrativeAction $action): void
    {
        if (isset($this->actions[$action->id()->value])) {
            throw new AdministrativeActionIdentityConflict;
        }

        $this->actions[$action->id()->value] = $this->cleanSnapshot($action);
    }

    public function save(AdministrativeAction $action, int $expectedVersion): void
    {
        if ($this->failNextSave) {
            $this->failNextSave = false;

            throw new ConcurrentAdministrativeActionModification;
        }

        $stored = $this->actions[$action->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentAdministrativeActionModification;
        }

        $this->actions[$action->id()->value] = $this->cleanSnapshot($action);
    }

    public function failNextSave(): void
    {
        $this->failNextSave = true;
    }

    private function cleanSnapshot(AdministrativeAction $action): AdministrativeAction
    {
        $snapshot = clone $action;
        $snapshot->releaseEvents();

        return $snapshot;
    }
}
