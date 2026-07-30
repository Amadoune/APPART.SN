<?php

namespace App\Application\ModerationResidualOperationalAuditContract;

use App\Application\ModerationListingHandoff\ModerationListingHandoffStatus;
use App\Application\ModerationOrchestration\Contract\ModerationCommandStatus;
use InvalidArgumentException;

final class ResidualOperationalAuditProductionPolicyV1
{
    public function producesAfterModerationCommand(
        ResidualOperationalAuditPathV1 $path,
        ModerationCommandStatus $result,
    ): bool {
        if ($path === ResidualOperationalAuditPathV1::ListingTargetCompleted) {
            throw new InvalidArgumentException('Listing completion uses its dedicated normalization.');
        }

        return $result === ModerationCommandStatus::Applied;
    }

    public function normalizeListingResult(
        ModerationListingHandoffStatus $result,
    ): ?ResidualListingCompletionV1 {
        return match ($result) {
            ModerationListingHandoffStatus::Applied,
            ModerationListingHandoffStatus::AlreadyApplied => ResidualListingCompletionV1::Applied,
            ModerationListingHandoffStatus::Requested,
            ModerationListingHandoffStatus::Rejected,
            ModerationListingHandoffStatus::VersionConflict,
            ModerationListingHandoffStatus::AuthorizationDenied,
            ModerationListingHandoffStatus::TargetIneligible,
            ModerationListingHandoffStatus::DependencyUnavailable,
            ModerationListingHandoffStatus::Quarantined => null,
        };
    }
}
