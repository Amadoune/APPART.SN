<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle;

final readonly class ProfessionalStatusTransition
{
    public function __construct(
        public ProfessionalStatusState $from,
        public ProfessionalStatusState $to,
        public ProfessionalStatusAction $action,
    ) {}
}
