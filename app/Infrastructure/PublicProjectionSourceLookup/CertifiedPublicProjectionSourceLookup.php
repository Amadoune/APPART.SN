<?php

namespace App\Infrastructure\PublicProjectionSourceLookup;

use App\Application\MultiTargetDelivery\MultiTargetPropagationRequest;
use App\Application\MultiTargetDelivery\MultiTargetPropagationSource;
use App\Application\ProjectionRuntimeSource\Contract\InspectablePublicListingProjectionSource;
use App\Application\ProjectionRuntimeSource\ProjectionSourceAssemblyStatus;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryContentSeoPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryMediaPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryPropertyPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliverySearchPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionSourceLookup\Contract\MediaCollectionPropertyResolver;
use App\Application\PublicProjectionSourceLookup\MediaCollectionPropertyStatus;
use App\Application\PublicProjectionStore\PublicProjectionPromotionReadiness;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionSourceLookup;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceDiagnostic;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolution;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolutionStatus;

final readonly class CertifiedPublicProjectionSourceLookup implements PublicProjectionSourceLookup
{
    public function __construct(
        private InspectablePublicListingProjectionSource $listingSources,
        private MediaCollectionPropertyResolver $mediaOwnership,
    ) {}

    public function resolve(PublicProjectionDeliveryMessage $message): PublicProjectionSourceResolution
    {
        if ($message->payload instanceof PublicProjectionDeliveryListingPayload) {
            return $this->listing($message->payload->listingId);
        }
        if ($message->payload instanceof PublicProjectionDeliverySearchPayload) {
            return $this->listing($message->payload->listingId);
        }
        if ($message->payload instanceof PublicProjectionDeliveryContentSeoPayload) {
            return $this->listing($message->payload->listingId);
        }
        if ($message->payload instanceof PublicProjectionDeliveryPropertyPayload) {
            return $this->property($message->payload->propertyId);
        }
        if ($message->payload instanceof PublicProjectionDeliveryMediaPayload) {
            return $this->media($message->payload->mediaCollectionId);
        }

        return $this->failure(PublicProjectionSourceResolutionStatus::InvalidIdentity, PublicProjectionSourceDiagnostic::InvalidIdentity);
    }

    private function listing(string $listingId): PublicProjectionSourceResolution
    {
        $result = $this->listingSources->inspect($listingId);
        if ($result->status !== ProjectionSourceAssemblyStatus::Found) {
            return $this->assemblyFailure($result->status);
        }
        if ($result->watermark === null || $result->readiness === null || $result->sources === null) {
            return new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::WatermarkIncomplete);
        }

        return match ($result->readiness) {
            PublicProjectionPromotionReadiness::Ready => new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::Resolved, $listingId),
            PublicProjectionPromotionReadiness::MissingPublicGeographyVersion => new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::MissingPublicGeographyRevision),
            PublicProjectionPromotionReadiness::MissingPublicMediaVersion => new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::MissingPublicMediaRevision),
            PublicProjectionPromotionReadiness::MissingPublicGeographyAndMediaVersions => new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::MissingPublicGeographyAndMediaRevisions),
        };
    }

    private function property(string $propertyId): PublicProjectionSourceResolution
    {
        if (! self::isUuid($propertyId)) {
            return $this->failure(PublicProjectionSourceResolutionStatus::InvalidIdentity, PublicProjectionSourceDiagnostic::InvalidIdentity);
        }

        return new PublicProjectionSourceResolution(
            PublicProjectionSourceResolutionStatus::MultiTargetResolved,
            multiTargetRequest: new MultiTargetPropagationRequest(MultiTargetPropagationSource::Property, $propertyId, $propertyId),
        );
    }

    private function media(string $mediaCollectionId): PublicProjectionSourceResolution
    {
        if (! self::isUuid($mediaCollectionId)) {
            return $this->failure(PublicProjectionSourceResolutionStatus::InvalidIdentity, PublicProjectionSourceDiagnostic::InvalidIdentity);
        }
        $ownership = $this->mediaOwnership->resolve($mediaCollectionId);
        if ($ownership->status === MediaCollectionPropertyStatus::Missing) {
            return $this->failure(PublicProjectionSourceResolutionStatus::MediaOwnershipMissing, PublicProjectionSourceDiagnostic::MediaOwnershipMissing);
        }
        if ($ownership->status === MediaCollectionPropertyStatus::Ambiguous) {
            return $this->failure(PublicProjectionSourceResolutionStatus::MediaOwnershipAmbiguous, PublicProjectionSourceDiagnostic::MediaOwnershipAmbiguous);
        }
        if ($ownership->status === MediaCollectionPropertyStatus::Corrupted || $ownership->propertyId === null) {
            return $this->failure(PublicProjectionSourceResolutionStatus::Corrupted, PublicProjectionSourceDiagnostic::Corrupted);
        }
        $propertyId = $ownership->propertyId;
        if (! self::isUuid($propertyId)) {
            return $this->failure(PublicProjectionSourceResolutionStatus::Corrupted, PublicProjectionSourceDiagnostic::Corrupted);
        }

        return new PublicProjectionSourceResolution(
            PublicProjectionSourceResolutionStatus::MultiTargetResolved,
            multiTargetRequest: new MultiTargetPropagationRequest(MultiTargetPropagationSource::Media, $mediaCollectionId, $propertyId),
        );
    }

    private function assemblyFailure(ProjectionSourceAssemblyStatus $status): PublicProjectionSourceResolution
    {
        return match ($status) {
            ProjectionSourceAssemblyStatus::InvalidListingIdentity => $this->failure(PublicProjectionSourceResolutionStatus::InvalidIdentity, PublicProjectionSourceDiagnostic::InvalidIdentity),
            ProjectionSourceAssemblyStatus::ListingMissing,
            ProjectionSourceAssemblyStatus::PropertyMissing,
            ProjectionSourceAssemblyStatus::MediaOwnershipMissing,
            ProjectionSourceAssemblyStatus::MediaCollectionMissing,
            ProjectionSourceAssemblyStatus::SearchMissing,
            ProjectionSourceAssemblyStatus::ContentSeoMissing,
            ProjectionSourceAssemblyStatus::ActiveGenerationMissing,
            ProjectionSourceAssemblyStatus::DecisionTimeMissing => new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::SourceUnavailable),
            ProjectionSourceAssemblyStatus::MediaOwnershipAmbiguous => $this->failure(PublicProjectionSourceResolutionStatus::MediaOwnershipAmbiguous, PublicProjectionSourceDiagnostic::MediaOwnershipAmbiguous),
            ProjectionSourceAssemblyStatus::SearchCorrupted,
            ProjectionSourceAssemblyStatus::ContentSeoCorrupted,
            ProjectionSourceAssemblyStatus::PublicGeographyCorrupted,
            ProjectionSourceAssemblyStatus::PublicMediaCorrupted,
            ProjectionSourceAssemblyStatus::ActiveGenerationCorrupted,
            ProjectionSourceAssemblyStatus::DecisionTimeCorrupted,
            ProjectionSourceAssemblyStatus::DecisionTimeDivergent,
            ProjectionSourceAssemblyStatus::SourceIdentityDivergent => $this->failure(PublicProjectionSourceResolutionStatus::Corrupted, PublicProjectionSourceDiagnostic::Corrupted),
            ProjectionSourceAssemblyStatus::Found => $this->failure(PublicProjectionSourceResolutionStatus::Corrupted, PublicProjectionSourceDiagnostic::Corrupted),
        };
    }

    private function failure(PublicProjectionSourceResolutionStatus $status, PublicProjectionSourceDiagnostic $diagnostic): PublicProjectionSourceResolution
    {
        return new PublicProjectionSourceResolution($status, diagnostic: $diagnostic);
    }

    private static function isUuid(string $identity): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $identity) === 1;
    }
}
