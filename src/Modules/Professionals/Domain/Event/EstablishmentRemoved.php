<?php

namespace Appart\Modules\Professionals\Domain\Event;

use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use DateTimeImmutable;

final readonly class EstablishmentRemoved extends AbstractProfessionalEvent
{
    public function __construct(ProfessionalId $id, public EstablishmentId $establishmentId, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
