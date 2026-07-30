<?php

namespace Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationActionV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use DateTimeImmutable;

interface ListingModerationCommandGatewayV1
{
    public function apply(
        ListingId $listingId,
        ListingModerationActionV1 $action,
        string $commandId,
        string $checksum,
        DateTimeImmutable $occurredAt,
    ): ListingModerationCommandResultV1;
}
