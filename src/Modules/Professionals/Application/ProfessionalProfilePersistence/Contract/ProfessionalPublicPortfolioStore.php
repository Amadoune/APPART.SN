<?php

namespace Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalProfileWriteResult;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicPortfolioState;

interface ProfessionalPublicPortfolioStore
{
    public function read(string $professionalId): ?ProfessionalPublicPortfolioState;

    public function save(ProfessionalPublicPortfolioState $candidate, int $expectedVersion): ProfessionalProfileWriteResult;
}
