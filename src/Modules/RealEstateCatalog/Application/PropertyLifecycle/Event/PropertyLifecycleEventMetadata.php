<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event;

use InvalidArgumentException;

final readonly class PropertyLifecycleEventMetadata
{
    public function __construct(
        public PropertyLifecycleEventInstant $occurredAt,
        public PropertyLifecycleEventInstant $recordedAt,
    ) {
        if ($recordedAt->value < $occurredAt->value) {
            throw new InvalidArgumentException('Property lifecycle event cannot be recorded before it occurred.');
        }
    }

    /** @return array{occurredAt:string,recordedAt:string} */
    public function fields(): array
    {
        return ['occurredAt' => $this->occurredAt->value, 'recordedAt' => $this->recordedAt->value];
    }
}
