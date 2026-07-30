<?php

namespace App\Infrastructure\DecisionTimeSource;

use App\Application\DecisionTimeSource\DecisionTimeReadResult;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotReadResult;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotReadStatus;

final readonly class ContentSeoSnapshotDecisionTimeMapper
{
    public function map(ContentSeoSnapshotReadResult $result): DecisionTimeReadResult
    {
        return match ($result->status) {
            ContentSeoSnapshotReadStatus::Missing => DecisionTimeReadResult::missing($result->listingId->value),
            ContentSeoSnapshotReadStatus::Corrupted => DecisionTimeReadResult::corrupted($result->listingId->value),
            ContentSeoSnapshotReadStatus::Found => $result->snapshot === null
                ? DecisionTimeReadResult::corrupted($result->listingId->value)
                : DecisionTimeReadResult::found($result->listingId->value, $result->snapshot->decisionAt),
        };
    }
}
