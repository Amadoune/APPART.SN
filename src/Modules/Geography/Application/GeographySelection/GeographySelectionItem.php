<?php

namespace Appart\Modules\Geography\Application\GeographySelection;

final readonly class GeographySelectionItem
{
    public function __construct(public string $placeId, public string $label, public string $type, public ?string $parentPlaceId) {}
}
