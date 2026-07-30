<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext;

use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;

final readonly class PlaceMergeContextV1
{
    public PlaceMergeContextVersion $contractVersion;

    public function __construct(
        public PlaceId $sourceId,
        public PlaceId $targetId,
        public PlaceMergeExpectedSourceVersion $expectedSourceVersion,
        public PlaceMergeObservedTargetVersion $observedTargetVersion,
        public PlaceMergeObservedState $observedTargetState,
        public PlaceType $observedSourceType,
        public PlaceType $observedTargetType,
        public CountryCode $observedSourceCountry,
        public CountryCode $observedTargetCountry,
        public PlaceMergeActorId $actor,
        public PlaceMergeOccurredAt $occurredAt,
        public PlaceMergeIntentId $intentId,
    ) {
        $this->contractVersion = PlaceMergeContextVersion::V1;
    }
}
