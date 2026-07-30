<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;

final readonly class AccountStatusOrchestrationResult
{
    public function __construct(
        public AccountStatusOrchestrationStatus $status,
        public ?AccountStatusState $state,
    ) {}
}
