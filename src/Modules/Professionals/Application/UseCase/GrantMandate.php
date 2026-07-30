<?php

namespace Appart\Modules\Professionals\Application\UseCase;

use Appart\Modules\Professionals\Domain\Model\Professional;
use Appart\Modules\Professionals\Domain\ValueObject\EstablishmentId;
use Appart\Modules\Professionals\Domain\ValueObject\MandateId;
use Appart\Modules\Professionals\Domain\ValueObject\MandateRole;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use Appart\Modules\Professionals\Domain\ValueObject\RepresentativeId;
use DateTimeImmutable;

final readonly class GrantMandate extends ProfessionalUseCase
{
    public function execute(ProfessionalId $id, MandateId $mandateId, EstablishmentId $establishmentId, RepresentativeId $representativeId, MandateRole $role, DateTimeImmutable $at): Professional
    {
        $professional = $this->professional($id);
        $version = $professional->version();
        $professional->grantMandate($mandateId, $establishmentId, $representativeId, $role, $at);

        return $this->save($professional, $version);
    }
}
