<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusEvent;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use DomainException;
use InvalidArgumentException;

final readonly class AccountStatusEventCatalog
{
    public function typeFor(AccountStatusTransition $transition): AccountStatusEventType
    {
        return match ([$transition->from, $transition->action, $transition->to]) {
            [AccountStatusState::Active, AccountStatusAction::Suspend, AccountStatusState::Suspended] => AccountStatusEventType::Suspended,
            [AccountStatusState::Suspended, AccountStatusAction::Reactivate, AccountStatusState::Active] => AccountStatusEventType::Reactivated,
            default => throw new DomainException('Transition is not certified for an Account Status event.'),
        };
    }

    public function eventFor(
        AccountStatusTransition $transition,
        AccountStatusContextV1 $context,
        int $occurredVersion,
    ): AccountStatusEventV1 {
        if ($occurredVersion !== $context->expectedVersion->value + 1) {
            throw new InvalidArgumentException('The event evidence does not match its resulting lifecycle version.');
        }

        $type = $this->typeFor($transition);
        $eventId = AccountStatusEventId::derive(
            $type,
            AccountStatusEventPayloadVersion::V1,
            $context->accountId,
            $transition,
            $occurredVersion,
        );
        $payload = new AccountStatusEventPayloadV1(
            $eventId,
            $context->accountId,
            $transition->from,
            $transition->action,
            $transition->to,
            $occurredVersion,
        );

        return new AccountStatusEventV1($type, $eventId, $context->occurredAt, $payload);
    }
}
