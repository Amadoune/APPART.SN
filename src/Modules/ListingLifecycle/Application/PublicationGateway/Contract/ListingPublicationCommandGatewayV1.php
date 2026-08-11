<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract;

use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandResultV1;
use DateTimeImmutable;

interface ListingPublicationCommandGatewayV1
{
    public function beginReview(string $listingId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt): ListingPublicationCommandResultV1;

    public function approveAndPublish(string $listingId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt): ListingPublicationCommandResultV1;
}
