<?php

namespace Appart\Modules\Geography\Application\GeographySelection;

use Appart\Modules\Geography\Application\GeographySelection\Contract\GeographySelectionReaderV1;
use Appart\Modules\Geography\Application\GeographySelection\Contract\GeographySelectionSource;

final readonly class DeterministicGeographySelectionReader implements GeographySelectionReaderV1
{
    public function __construct(private GeographySelectionSource $source) {}

    public function read(GeographySelectionQuery $query): GeographySelectionResult
    {
        $cursor = $query->cursor === null ? null : GeographySelectionCursor::decode($query->cursor, $query->type, $query->parentPlaceId);
        $source = $this->source->select($query->type, $query->parentPlaceId, $cursor?->normalizationKey, $cursor?->placeId, $query->limit + 1);
        if ($source->status !== GeographySelectionSourceStatus::Available) {
            return new GeographySelectionResult(GeographySelectionStatus::from($source->status->value));
        }
        $hasMore = count($source->items) > $query->limit;
        $page = array_slice($source->items, 0, $query->limit);
        if ($page === []) {
            return new GeographySelectionResult(GeographySelectionStatus::Empty);
        }
        $items = array_map(static fn (GeographySelectionSourceItem $item): GeographySelectionItem => new GeographySelectionItem($item->placeId, $item->label, $item->type, $item->parentPlaceId), $page);
        $last = $page[array_key_last($page)];

        return new GeographySelectionResult(GeographySelectionStatus::Available, $items, $hasMore ? GeographySelectionCursor::encode($query->type, $query->parentPlaceId, $last) : null);
    }
}
