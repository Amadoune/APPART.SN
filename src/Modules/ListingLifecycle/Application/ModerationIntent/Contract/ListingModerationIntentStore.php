<?php

namespace Appart\Modules\ListingLifecycle\Application\ModerationIntent\Contract;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\ListingModerationIntentReservationV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\ListingModerationIntentStateV1;
use DateTimeImmutable;

interface ListingModerationIntentStore
{
    public function reserve(
        string $commandId,
        string $checksum,
        DateTimeImmutable $recordedAt,
    ): ListingModerationIntentReservationV1;

    public function find(string $commandId): ?ListingModerationIntentStateV1;

    public function complete(
        string $commandId,
        string $checksum,
        ListingModerationCommandResultV1 $result,
        DateTimeImmutable $recordedAt,
    ): bool;
}
