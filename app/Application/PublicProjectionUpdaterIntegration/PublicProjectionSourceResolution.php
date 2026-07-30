<?php

namespace App\Application\PublicProjectionUpdaterIntegration;

use App\Application\MultiTargetDelivery\MultiTargetPropagationRequest;
use InvalidArgumentException;

final readonly class PublicProjectionSourceResolution
{
    public function __construct(
        public PublicProjectionSourceResolutionStatus $status,
        public ?string $listingId = null,
        public ?MultiTargetPropagationRequest $multiTargetRequest = null,
        public PublicProjectionSourceDiagnostic $diagnostic = PublicProjectionSourceDiagnostic::None,
    ) {
        if (($status === PublicProjectionSourceResolutionStatus::Resolved) !== ($listingId !== null)
            || ($status === PublicProjectionSourceResolutionStatus::MultiTargetResolved) !== ($multiTargetRequest !== null)
            || ($listingId !== null && $multiTargetRequest !== null)) {
            throw new InvalidArgumentException('A source resolution must expose exactly the target shape declared by its status.');
        }
        $failed = in_array($status, [
            PublicProjectionSourceResolutionStatus::InvalidIdentity,
            PublicProjectionSourceResolutionStatus::Corrupted,
            PublicProjectionSourceResolutionStatus::MediaOwnershipMissing,
            PublicProjectionSourceResolutionStatus::MediaOwnershipAmbiguous,
        ], true);
        if ($failed === ($diagnostic === PublicProjectionSourceDiagnostic::None)) {
            throw new InvalidArgumentException('A failed source resolution must expose a typed diagnostic.');
        }
    }
}
