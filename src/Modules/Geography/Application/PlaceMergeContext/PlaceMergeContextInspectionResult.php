<?php

namespace Appart\Modules\Geography\Application\PlaceMergeContext;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;

final readonly class PlaceMergeContextInspectionResult
{
    private function __construct(
        public PlaceId $sourceId,
        public PlaceMergeIntentId $intentId,
        public PlaceMergeContextInspectionStatus $status,
        public ?PlaceMergeContextInspection $inspection,
    ) {}

    public static function found(PlaceMergeContextInspection $inspection): self
    {
        return new self(
            $inspection->context->sourceId,
            $inspection->context->intentId,
            PlaceMergeContextInspectionStatus::Found,
            $inspection,
        );
    }

    public static function missing(PlaceId $sourceId, PlaceMergeIntentId $intentId): self
    {
        return new self($sourceId, $intentId, PlaceMergeContextInspectionStatus::Missing, null);
    }

    public static function corrupted(PlaceId $sourceId, PlaceMergeIntentId $intentId): self
    {
        return new self($sourceId, $intentId, PlaceMergeContextInspectionStatus::Corrupted, null);
    }
}
