<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;

final readonly class AccountStatusContextV1
{
    public AccountStatusContextVersion $contractVersion;

    public function __construct(
        public AccountId $accountId,
        public AccountStatusState $currentState,
        public AccountStatusVersion $expectedVersion,
        public AccountStatusVersion $observedVersion,
        public AccountStatusAction $action,
        public AccountStatusActorId $actorId,
        public AccountStatusOccurredAt $occurredAt,
        public AccountStatusIntentId $intentId,
    ) {
        $this->contractVersion = AccountStatusContextVersion::V1;
    }

    public function isStructurallyValid(): bool
    {
        return $this->expectedVersion->isValid()
            && $this->observedVersion->isValid()
            && $this->actorId->isValid()
            && $this->intentId->isValid();
    }
}
