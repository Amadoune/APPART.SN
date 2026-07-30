<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusEvent;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use InvalidArgumentException;

final readonly class ProfessionalStatusEventId
{
    private function __construct(public string $value) {}

    public static function derive(ProfessionalStatusEventType $type, ProfessionalStatusEventPayloadVersion $payloadVersion, ProfessionalStatusId $id, ProfessionalStatusTransition $transition, int $occurredVersion): self
    {
        if ($occurredVersion < 2) {
            throw new InvalidArgumentException('Professional status event version must be at least two.');
        }

        return new self(hash('sha256', implode("\n", [$type->value, (string) $payloadVersion->value, $id->value, $transition->from->value, $transition->action->value, $transition->to->value, (string) $occurredVersion])));
    }
}
