<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationGateway;

final readonly class ListingPublicationCommandLedgerRecord
{
    public function __construct(
        public string $commandId,
        public string $checksum,
        public ListingPublicationCommandStatus $status,
        public ?int $workflowVersion,
        public ?int $aggregateVersion,
    ) {}
}
