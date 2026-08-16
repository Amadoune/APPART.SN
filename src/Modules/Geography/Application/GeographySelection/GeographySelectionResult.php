<?php

namespace Appart\Modules\Geography\Application\GeographySelection;

final readonly class GeographySelectionResult
{
    /** @param list<GeographySelectionItem> $items */
    public function __construct(public GeographySelectionStatus $status, public array $items = [], public ?string $nextCursor = null) {}
}
