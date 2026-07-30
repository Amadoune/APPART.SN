<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use InvalidArgumentException;

final readonly class AdministrativeActionContextualAppendInspection
{
    public function __construct(
        public AdministrativeActionId $actionId,
        public int $version,
        public AdministrativeActionLifecycleTransition $transition,
        public AdministrativeActionTransitionExecutionContext $context,
        public AdministrativeActionTransitionContextChecksum $checksum,
    ) {
        if ($version < 1) {
            throw new InvalidArgumentException('An inspected contextual append version must be positive.');
        }
    }
}
