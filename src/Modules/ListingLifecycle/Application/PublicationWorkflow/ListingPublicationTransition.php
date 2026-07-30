<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

final readonly class ListingPublicationTransition
{
    public function __construct(
        public ListingPublicationState $from,
        public ListingPublicationState $to,
        public ListingPublicationAction $action,
    ) {}
}
