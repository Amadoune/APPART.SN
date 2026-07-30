<?php

namespace Appart\Modules\Professionals\Domain\Event;

use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalName;
use Appart\Modules\Professionals\Domain\ValueObject\RegistrationNumber;
use DateTimeImmutable;

final readonly class ProfessionalRegistered extends AbstractProfessionalEvent
{
    public function __construct(ProfessionalId $id, public ProfessionalName $name, public RegistrationNumber $registrationNumber, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
