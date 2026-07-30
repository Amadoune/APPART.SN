<?php

namespace Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalProfileWriteResult;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalPublicProfileState;

interface ProfessionalPublicProfileStore
{
    public function read(string $professionalId): ?ProfessionalPublicProfileState;

    public function save(ProfessionalPublicProfileState $candidate, int $expectedVersion): ProfessionalProfileWriteResult;
}
