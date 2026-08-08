<?php

namespace Appart\Modules\ListingLifecycle\Application\ListingEligibilityPublicRead;

final readonly class ListingEligibilityResultV1
{
    private function __construct(public ListingEligibilityStatusV1 $status) {}

    public static function eligible(): self
    {
        return new self(ListingEligibilityStatusV1::Eligible);
    }

    public static function notEligible(): self
    {
        return new self(ListingEligibilityStatusV1::NotEligible);
    }

    public static function missing(): self
    {
        return new self(ListingEligibilityStatusV1::Missing);
    }

    public static function corrupted(): self
    {
        return new self(ListingEligibilityStatusV1::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ListingEligibilityStatusV1::DependencyUnavailable);
    }
}
