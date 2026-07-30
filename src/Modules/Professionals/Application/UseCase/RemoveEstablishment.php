<?php

namespace Appart\Modules\Professionals\Application\UseCase;

use Appart\Modules\Professionals\Domain\Model\Professional;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use DateTimeImmutable;

final readonly class RemoveEstablishment extends ProfessionalUseCase
{
    public function execute(ProfessionalId $id, EstablishmentId $establishmentId, DateTimeImmutable $at): Professional
    {
        $professional = $this->professional($id);
        $version = $professional->version();
        $professional->removeEstablishment($establishmentId, $at);

        return $this->save($professional, $version);
    }
}
