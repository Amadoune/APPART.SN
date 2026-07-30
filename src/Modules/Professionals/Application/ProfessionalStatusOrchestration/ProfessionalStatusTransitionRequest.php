<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusTransitionContext;

final readonly class ProfessionalStatusTransitionRequest
{
    public function __construct(
        public ProfessionalStatusId $professionalId,
        public ProfessionalStatusAction $action,
        public ProfessionalStatusTransitionContext $context,
    ) {}
}
