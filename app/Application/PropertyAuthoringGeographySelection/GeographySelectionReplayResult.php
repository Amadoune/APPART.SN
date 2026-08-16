<?php

namespace App\Application\PropertyAuthoringGeographySelection;

final readonly class GeographySelectionReplayResult
{
    public function __construct(public GeographySelectionReplayStatus $status) {}
}
