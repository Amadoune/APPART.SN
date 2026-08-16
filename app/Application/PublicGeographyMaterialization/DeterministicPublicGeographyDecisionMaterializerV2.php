<?php

namespace App\Application\PublicGeographyMaterialization;

use App\Application\PublicGeographyMaterialization\Contract\MaterializePublicGeographyDecisionV2;
use App\Application\PublicGeographySource\Contract\PublicGeographyDecisionWriter;
use App\Application\PublicGeographySource\PublicGeographyWriteResult;
use Appart\Modules\Geography\Domain\Exception\InvalidPlaceState;
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Throwable;

final readonly class DeterministicPublicGeographyDecisionMaterializerV2 implements MaterializePublicGeographyDecisionV2
{
    public function __construct(private ListingRegistry $listings, private PropertyRegistry $properties, private PublicGeographyHierarchyReader $hierarchy, private PublicGeographyDecisionV2Assembler $assembler, private PublicGeographyDecisionWriter $writer) {}

    public function materialize(ListingId $listingId): PublicGeographyMaterializationResult
    {
        try {
            $listing = $this->listings->find($listingId);
            if ($listing === null) {
                return new PublicGeographyMaterializationResult(PublicGeographyMaterializationStatus::SourceMissing);
            }
            $property = $this->properties->find(PropertyId::fromString($listing->propertyId()->value));
            if ($property === null || $property->address() === null) {
                return new PublicGeographyMaterializationResult(PublicGeographyMaterializationStatus::SourceMissing);
            }
            $decision = $this->assembler->assemble($this->hierarchy->read($property->address()->placeId->value), 'listing:'.$listingId->value.':public-geography-v2');
            $status = match ($this->writer->store($decision)) {
                PublicGeographyWriteResult::Applied => PublicGeographyMaterializationStatus::Applied,
                PublicGeographyWriteResult::AlreadyApplied => PublicGeographyMaterializationStatus::AlreadyApplied,
                PublicGeographyWriteResult::RejectedObsolete => PublicGeographyMaterializationStatus::RejectedObsolete,
                PublicGeographyWriteResult::Divergent => PublicGeographyMaterializationStatus::Divergent,
            };

            return new PublicGeographyMaterializationResult($status, $decision->terminalPlaceId, $decision->revision->watermarkVersion());
        } catch (InvalidPlaceState|\InvalidArgumentException|\OverflowException) {
            return new PublicGeographyMaterializationResult(PublicGeographyMaterializationStatus::SourceCorrupted);
        } catch (Throwable) {
            return new PublicGeographyMaterializationResult(PublicGeographyMaterializationStatus::DependencyUnavailable);
        }
    }
}
