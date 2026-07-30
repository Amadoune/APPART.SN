<?php

namespace Appart\Modules\Professionals\Application\UseCase;

use Appart\Modules\Professionals\Domain\Model\Professional;
use Appart\Modules\Professionals\Domain\ValueObject\MandateId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use DateTimeImmutable;

final readonly class RevokeMandate extends ProfessionalUseCase
{
    public function execute(ProfessionalId $id, MandateId $mandateId, DateTimeImmutable $at): Professional
    {
        $professional = $this->professional($id);
        $version = $professional->version();
        $professional->revokeMandate($mandateId, $at);

        return $this->save($professional, $version);
    }
}
