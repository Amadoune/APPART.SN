<?php

namespace Appart\Modules\AdministrationAudit\Domain\Event;

use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use DateTimeImmutable;

interface AdministrativeActionEvent
{
    public function actionId(): AdministrativeActionId;

    public function occurredAt(): DateTimeImmutable;

    public function aggregateVersion(): int;

    public function eventIndex(): int;
}
