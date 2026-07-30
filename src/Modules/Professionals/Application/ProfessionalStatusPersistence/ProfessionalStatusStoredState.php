<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusPersistence;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use InvalidArgumentException;

final readonly class ProfessionalStatusStoredState
{
    public function __construct(public ProfessionalStatusId $professionalId, public ProfessionalStatusState $state, public int $version)
    {
        if ($version < 1) {
            throw new InvalidArgumentException('Professional status version must be positive.');
        }
    }
}
