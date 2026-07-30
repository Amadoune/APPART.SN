<?php

namespace App\Application\ProfessionalStatusEventIntegration;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusTransitionRequest;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusTransitionContext;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;

final readonly class ProfessionalStatusAtomicEventRequest
{
    public function __construct(
        public ProfessionalStatusId $professionalId,
        public ProfessionalStatusAction $action,
        public ProfessionalStatusTransitionContext $context,
        public ProfessionalStatusOccurredAt $recordedAt,
    ) {}

    public function transitionRequest(): ProfessionalStatusTransitionRequest
    {
        return new ProfessionalStatusTransitionRequest($this->professionalId, $this->action, $this->context);
    }
}
