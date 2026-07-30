<?php

namespace Appart\Modules\Professionals\Application\UseCase;

use Appart\Modules\Professionals\Domain\Model\Professional;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentName;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use DateTimeImmutable;

final readonly class AddEstablishment extends ProfessionalUseCase
{
    public function execute(ProfessionalId $id, EstablishmentId $establishmentId, EstablishmentName $name, DateTimeImmutable $at): Professional
    {
        $professional = $this->professional($id);
        $version = $professional->version();
        $professional->addEstablishment($establishmentId, $name, $at);
        $this->professionals->saveWithEstablishmentReservation($professional, $establishmentId, $version);

        return $professional;
    }
}
