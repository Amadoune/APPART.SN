<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent;

use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use InvalidArgumentException;

final readonly class AdministrativeActionLifecycleEventId
{
    private function __construct(public string $value) {}

    public static function derive(
        AdministrativeActionLifecycleEventType $type,
        AdministrativeActionLifecycleEventPayloadVersion $payloadVersion,
        AdministrativeActionId $actionId,
        AdministrativeActionLifecycleTransition $transition,
        int $occurredVersion,
    ): self {
        if ($occurredVersion < 1) {
            throw new InvalidArgumentException('The Administrative Action Lifecycle event version must be positive.');
        }

        return new self(hash('sha256', implode("\n", [
            $type->value,
            (string) $payloadVersion->value,
            $actionId->value,
            $transition->from->value,
            $transition->action->value,
            $transition->to->value,
            (string) $occurredVersion,
        ])));
    }
}
