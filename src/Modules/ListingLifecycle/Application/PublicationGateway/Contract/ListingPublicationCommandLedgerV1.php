<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract;

use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandLedgerRecord;
use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandStatus;
use DateTimeImmutable;

interface ListingPublicationCommandLedgerV1
{
    public function find(string $commandId): ?ListingPublicationCommandLedgerRecord;

    public function reserve(string $commandId, string $listingId, string $operation, string $checksum, DateTimeImmutable $occurredAt): bool;

    public function complete(string $commandId, string $checksum, ListingPublicationCommandStatus $status, ?int $workflowVersion, ?int $aggregateVersion): bool;
}
