<?php

namespace Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\Contract;

use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalProfileWriteResult;
use Appart\Modules\Professionals\Application\ProfessionalProfilePersistence\ProfessionalVerificationState;

interface ProfessionalVerificationStore
{
    public function read(string $professionalId): ?ProfessionalVerificationState;

    public function save(ProfessionalVerificationState $candidate, int $expectedVersion): ProfessionalProfileWriteResult;
}
