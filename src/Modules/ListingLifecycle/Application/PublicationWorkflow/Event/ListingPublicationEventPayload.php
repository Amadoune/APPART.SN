<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event;

use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use InvalidArgumentException;

final readonly class ListingPublicationEventPayload
{
    public function __construct(
        public ListingId $listingId,
        public ListingPublicationState $previousState,
        public ListingPublicationState $state,
        public ListingPublicationAction $action,
        public int $publicationVersion,
    ) {
        if ($publicationVersion < 2) {
            throw new InvalidArgumentException('A transition event publication version must be at least two.');
        }
    }

    /** @return array{listingId:string,previousState:string,state:string,action:string,publicationVersion:int} */
    public function fields(): array
    {
        return [
            'listingId' => $this->listingId->value,
            'previousState' => $this->previousState->value,
            'state' => $this->state->value,
            'action' => $this->action->value,
            'publicationVersion' => $this->publicationVersion,
        ];
    }
}
