<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusEvent;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use InvalidArgumentException;

final readonly class AccountStatusEventPayloadV1
{
    public AccountStatusEventPayloadVersion $version;

    public function __construct(
        public AccountStatusEventId $eventId,
        public AccountId $accountId,
        public AccountStatusState $previousState,
        public AccountStatusAction $action,
        public AccountStatusState $currentState,
        public int $occurredVersion,
    ) {
        $this->version = AccountStatusEventPayloadVersion::V1;

        if ($occurredVersion < 1) {
            throw new InvalidArgumentException('An Account Status event version must be positive.');
        }
    }

    public function transition(): AccountStatusTransition
    {
        return new AccountStatusTransition($this->previousState, $this->action, $this->currentState);
    }

    /** @return array{accountId: string, previousState: string, action: string, currentState: string, occurredVersion: int} */
    public function fields(): array
    {
        return [
            'accountId' => $this->accountId->value,
            'previousState' => $this->previousState->value,
            'action' => $this->action->value,
            'currentState' => $this->currentState->value,
            'occurredVersion' => $this->occurredVersion,
        ];
    }
}
