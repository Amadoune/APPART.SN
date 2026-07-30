<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceReadResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusPersistenceWriteResult;

interface ProfessionalStatusWorkflowStore
{
    public function initialize(ProfessionalStatusId $professionalId): ProfessionalStatusPersistenceWriteResult;

    public function append(ProfessionalStatusId $professionalId, ProfessionalStatusTransition $transition, int $version): ProfessionalStatusPersistenceWriteResult;

    public function read(ProfessionalStatusId $professionalId): ProfessionalStatusPersistenceReadResult;
}
