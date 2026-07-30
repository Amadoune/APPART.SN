<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event;

use InvalidArgumentException;

final readonly class ListingPublicationEventMetadata
{
    public function __construct(
        public ListingPublicationEventInstant $occurredAt,
        public ListingPublicationEventInstant $recordedAt,
    ) {
        if ($recordedAt->value < $occurredAt->value) {
            throw new InvalidArgumentException('Listing publication event cannot be recorded before it occurred.');
        }
    }

    /** @return array{occurredAt:string,recordedAt:string} */
    public function fields(): array
    {
        return ['occurredAt' => $this->occurredAt->value, 'recordedAt' => $this->recordedAt->value];
    }
}
