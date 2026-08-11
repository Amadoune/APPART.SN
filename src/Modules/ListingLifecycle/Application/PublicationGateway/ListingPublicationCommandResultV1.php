<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationGateway;

final readonly class ListingPublicationCommandResultV1
{
    public function __construct(
        public ListingPublicationCommandStatus $status,
        public ?int $workflowVersion = null,
        public ?int $aggregateVersion = null,
    ) {}
}
