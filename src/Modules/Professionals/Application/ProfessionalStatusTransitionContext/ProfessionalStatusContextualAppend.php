<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;

final readonly class ProfessionalStatusContextualAppend
{
    public function __construct(
        public ProfessionalStatusId $professionalId,
        public ProfessionalStatusTransition $transition,
        public ProfessionalStatusTransitionContext $context,
    ) {}

    public function nextVersion(): int
    {
        return $this->context->expectedVersion->next();
    }
}
