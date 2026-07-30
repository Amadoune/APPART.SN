<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use InvalidArgumentException;

final readonly class ProfessionalStatusContextualAppendInspection
{
    public function __construct(
        public ProfessionalStatusId $professionalId,
        public int $version,
        public ProfessionalStatusTransition $transition,
        public ProfessionalStatusActorId $actor,
        public ProfessionalStatusOccurredAt $occurredAt,
        public ProfessionalStatusContextChecksum $checksum,
    ) {
        if ($version < 2) {
            throw new InvalidArgumentException('A professional status contextual append version must be at least two.');
        }
    }
}
