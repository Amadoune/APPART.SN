<?php

namespace Appart\Modules\Professionals\Application\UseCase;

use Appart\Modules\Professionals\Domain\Model\Professional;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use DateTimeImmutable;

final readonly class SuspendProfessional extends ProfessionalUseCase
{
    public function execute(ProfessionalId $id, DateTimeImmutable $at): Professional
    {
        $professional = $this->professional($id);
        $version = $professional->version();
        $professional->suspend($at);

        return $this->save($professional, $version);
    }
}
