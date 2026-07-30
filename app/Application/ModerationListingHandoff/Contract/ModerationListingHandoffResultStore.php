<?php

namespace App\Application\ModerationListingHandoff\Contract;

use App\Application\ModerationListingHandoff\ModerationListingHandoffRecord;

interface ModerationListingHandoffResultStore
{
    public function append(ModerationListingHandoffRecord $record): bool;

    public function latest(string $messageId): ?ModerationListingHandoffRecord;
}
