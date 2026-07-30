<?php

namespace Appart\Modules\Professionals\Application\UseCase;

use Appart\Modules\Professionals\Application\Contract\ProfessionalRegistry;
use Appart\Modules\Professionals\Domain\Model\Professional;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalName;
use Appart\Modules\Professionals\Domain\ValueObject\RegistrationNumber;
use DateTimeImmutable;

final readonly class RegisterProfessional
{
    public function __construct(private ProfessionalRegistry $professionals) {}

    public function execute(ProfessionalId $id, RegistrationNumber $registrationNumber, ProfessionalName $name, DateTimeImmutable $at): Professional
    {
        $professional = Professional::register($id, $registrationNumber, $name, $at);
        $this->professionals->add($professional);

        return $professional;
    }
}
