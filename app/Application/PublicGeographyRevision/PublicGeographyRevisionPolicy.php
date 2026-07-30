<?php

namespace App\Application\PublicGeographyRevision;

final readonly class PublicGeographyRevisionPolicy
{
    public function decide(?PublicGeographyRevision $stable, PublicGeographyRevision $candidate): PublicGeographyRevisionDecision
    {
        if ($stable === null) {
            return new PublicGeographyRevisionDecision(PublicGeographyRevisionPromotion::Initial, $candidate);
        }

        $promotion = match ($candidate->version->compareTo($stable->version)) {
            PublicGeographyRevisionRelation::Newer => PublicGeographyRevisionPromotion::Advance,
            PublicGeographyRevisionRelation::Older => PublicGeographyRevisionPromotion::Obsolete,
            PublicGeographyRevisionRelation::Equal => $candidate->sameFactAs($stable)
                ? PublicGeographyRevisionPromotion::AlreadyStable
                : PublicGeographyRevisionPromotion::Divergent,
        };

        return new PublicGeographyRevisionDecision($promotion, $candidate);
    }
}
