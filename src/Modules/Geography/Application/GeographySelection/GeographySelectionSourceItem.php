<?php

namespace Appart\Modules\Geography\Application\GeographySelection;

final readonly class GeographySelectionSourceItem
{
    public function __construct(public string $placeId, public string $label, public string $type, public ?string $parentPlaceId, public string $normalizationKey) {}
}
