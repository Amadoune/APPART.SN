<?php

namespace Appart\Modules\Professionals\Domain\Event;

use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\MandateId;
use Appart\Modules\Professionals\Domain\ValueObject\MandateRole;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use Appart\Modules\Professionals\Domain\ValueObject\RepresentativeId;
use DateTimeImmutable;

final readonly class MandateGranted extends AbstractProfessionalEvent
{
    public function __construct(ProfessionalId $id, public MandateId $mandateId, public EstablishmentId $establishmentId, public RepresentativeId $representativeId, public MandateRole $role, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
