<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusEvent;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use DomainException;

final readonly class ProfessionalStatusEventCatalog
{
    public function typeFor(ProfessionalStatusTransition $transition): ProfessionalStatusEventType
    {
        return match ([$transition->from, $transition->action, $transition->to]) {
            [ProfessionalStatusState::Active, ProfessionalStatusAction::Suspend, ProfessionalStatusState::Suspended] => ProfessionalStatusEventType::Suspended,
            [ProfessionalStatusState::Suspended, ProfessionalStatusAction::Reactivate, ProfessionalStatusState::Active] => ProfessionalStatusEventType::Reactivated,
            default => throw new DomainException('Transition is not certified for a professional status event.'),
        };
    }
}
