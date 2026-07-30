<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext;

use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;

final readonly class ProfessionalStatusTransitionContext
{
    public function __construct(
        public ProfessionalStatusActorId $actor,
        public ProfessionalStatusOccurredAt $occurredAt,
        public ProfessionalStatusExpectedVersion $expectedVersion,
    ) {}
}
