<?php

namespace Appart\Modules\Geography\Application\GeographySelection;

final readonly class GeographySelectionSourceResult
{
    /** @param list<GeographySelectionSourceItem> $items */
    private function __construct(public GeographySelectionSourceStatus $status, public array $items = []) {}

    /** @param list<GeographySelectionSourceItem> $items */
    public static function available(array $items): self
    {
        return new self(GeographySelectionSourceStatus::Available, $items);
    }

    public static function empty(): self
    {
        return new self(GeographySelectionSourceStatus::Empty);
    }

    public static function missing(): self
    {
        return new self(GeographySelectionSourceStatus::Missing);
    }

    public static function corrupted(): self
    {
        return new self(GeographySelectionSourceStatus::Corrupted);
    }

    public static function unavailable(): self
    {
        return new self(GeographySelectionSourceStatus::DependencyUnavailable);
    }
}
