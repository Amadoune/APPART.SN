<?php

namespace Appart\Modules\IdentityAccess\Application\AccountStatusEvent;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use InvalidArgumentException;

final readonly class AccountStatusEventId
{
    private function __construct(public string $value) {}

    public static function derive(
        AccountStatusEventType $type,
        AccountStatusEventPayloadVersion $payloadVersion,
        AccountId $accountId,
        AccountStatusTransition $transition,
        int $occurredVersion,
    ): self {
        if ($occurredVersion < 1) {
            throw new InvalidArgumentException('An Account Status event version must be positive.');
        }

        return new self(hash('sha256', implode("\n", [
            $type->value,
            (string) $payloadVersion->value,
            $accountId->value,
            $transition->from->value,
            $transition->action->value,
            $transition->to->value,
            (string) $occurredVersion,
        ])));
    }
}
