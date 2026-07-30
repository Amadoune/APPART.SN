<?php

namespace Appart\Modules\Professionals\Domain\Event;

use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use DateTimeImmutable;

interface ProfessionalEvent
{
    public function professionalId(): ProfessionalId;

    public function occurredAt(): DateTimeImmutable;

    public function aggregateVersion(): int;

    public function eventIndex(): int;
}
