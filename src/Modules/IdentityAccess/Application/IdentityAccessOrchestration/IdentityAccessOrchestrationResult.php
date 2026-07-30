<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration;

final readonly class IdentityAccessOrchestrationResult
{
    public function __construct(
        public IdentityAccessAtomicOperation $operation,
        public IdentityAccessOrchestrationStatus $status,
    ) {}
}
