<?php

namespace App\Application\PublicMediaRevision;

final readonly class PublicMediaRevisionPolicy
{
    public function decide(?PublicMediaRevision $stable, PublicMediaRevision $candidate): PublicMediaRevisionDecision
    {
        if ($stable === null) {
            return new PublicMediaRevisionDecision(PublicMediaRevisionPromotion::Initial, $candidate);
        }

        $promotion = match ($candidate->version->compareTo($stable->version)) {
            PublicMediaRevisionRelation::Newer => PublicMediaRevisionPromotion::Advance,
            PublicMediaRevisionRelation::Older => PublicMediaRevisionPromotion::Obsolete,
            PublicMediaRevisionRelation::Equal => $candidate->sameFactAs($stable)
                ? PublicMediaRevisionPromotion::AlreadyStable
                : PublicMediaRevisionPromotion::Divergent,
        };

        return new PublicMediaRevisionDecision($promotion, $candidate);
    }
}
