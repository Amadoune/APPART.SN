<?php

namespace App\Application\PublicMediaRevision;

final readonly class PublicMediaRevisionDecision
{
    public function __construct(public PublicMediaRevisionPromotion $promotion, public PublicMediaRevision $candidate) {}

    public function isPromotable(): bool
    {
        return match ($this->promotion) {
            PublicMediaRevisionPromotion::Initial,
            PublicMediaRevisionPromotion::Advance => true,
            PublicMediaRevisionPromotion::AlreadyStable,
            PublicMediaRevisionPromotion::Obsolete,
            PublicMediaRevisionPromotion::Divergent => false,
        };
    }
}
