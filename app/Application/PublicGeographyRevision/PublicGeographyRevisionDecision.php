<?php

namespace App\Application\PublicGeographyRevision;

final readonly class PublicGeographyRevisionDecision
{
    public function __construct(public PublicGeographyRevisionPromotion $promotion, public PublicGeographyRevision $candidate) {}

    public function isPromotable(): bool
    {
        return match ($this->promotion) {
            PublicGeographyRevisionPromotion::Initial,
            PublicGeographyRevisionPromotion::Advance => true,
            PublicGeographyRevisionPromotion::AlreadyStable,
            PublicGeographyRevisionPromotion::Obsolete,
            PublicGeographyRevisionPromotion::Divergent => false,
        };
    }
}
