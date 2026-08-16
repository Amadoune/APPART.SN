<?php

namespace App\Application\PropertyAuthoringGeographySelection;

use App\Application\PropertyAuthoringGeographySelection\Contract\GeographySelectionReplayValidatorV1;
use Appart\Modules\Geography\Application\GeographySelection\Contract\GeographySelectionReaderV1;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionQuery;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionStatus;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Throwable;

final readonly class DeterministicGeographySelectionReplayValidatorV1 implements GeographySelectionReplayValidatorV1
{
    public function __construct(private GeographySelectionReaderV1 $reader) {}

    public function validate(string $geographicPlaceId, string $type, ?string $parentPlaceId, ?string $cursor, int $limit): GeographySelectionReplayResult
    {
        try {
            $placeType = PlaceType::from($type);
            $parent = $parentPlaceId === null ? null : PlaceId::fromString($parentPlaceId);
            $declared = PlaceId::fromString($geographicPlaceId);
            $result = $this->reader->read(new GeographySelectionQuery($placeType, $parent, $cursor, $limit));
            if ($result->status === GeographySelectionStatus::DependencyUnavailable) {
                return new GeographySelectionReplayResult(GeographySelectionReplayStatus::DependencyUnavailable);
            }
            if ($result->status !== GeographySelectionStatus::Available) {
                return new GeographySelectionReplayResult(GeographySelectionReplayStatus::Invalid);
            }
            foreach ($result->items as $item) {
                if ($item->placeId === $declared->value
                    && $item->type === $placeType->value
                    && $item->parentPlaceId === $parent?->value) {
                    return new GeographySelectionReplayResult(GeographySelectionReplayStatus::Validated);
                }
            }

            return new GeographySelectionReplayResult(GeographySelectionReplayStatus::Invalid);
        } catch (Throwable) {
            return new GeographySelectionReplayResult(GeographySelectionReplayStatus::Invalid);
        }
    }
}
