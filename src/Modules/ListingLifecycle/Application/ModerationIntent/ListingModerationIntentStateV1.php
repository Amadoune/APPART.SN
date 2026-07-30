<?php

namespace Appart\Modules\ListingLifecycle\Application\ModerationIntent;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;

final readonly class ListingModerationIntentStateV1
{
    public function __construct(
        public string $commandId,
        public string $checksum,
        public ?ListingModerationCommandResultV1 $terminalResult,
    ) {}
}
