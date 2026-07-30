<?php

namespace Appart\Modules\Professionals\Application\UseCase;

use Appart\Modules\Professionals\Application\Contract\ProfessionalRegistry;
use Appart\Modules\Professionals\Domain\Exception\ProfessionalNotFound;
use Appart\Modules\Professionals\Domain\Model\Professional;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;

abstract readonly class ProfessionalUseCase
{
    public function __construct(protected ProfessionalRegistry $professionals) {}

    protected function professional(ProfessionalId $id): Professional
    {
        return $this->professionals->find($id) ?? throw new ProfessionalNotFound;
    }

    protected function save(Professional $professional, int $version): Professional
    {
        $this->professionals->save($professional, $version);

        return $professional;
    }
}
