<?php

namespace Appart\Modules\ContentSeo\Domain\Policy;

use Appart\Modules\ContentSeo\Domain\Exception\DuplicateSeoFact;
use Appart\Modules\ContentSeo\Domain\Exception\InconsistentSeoSources;
use Appart\Modules\ContentSeo\Domain\Exception\StaleSeoProjection;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevisions;

final readonly class SeoFreshnessPolicy
{
    public function assertNewer(SeoSourceRevisions $incoming, SeoSourceRevisions $current): void
    {
        $incomingItems = $incoming->all();
        $coherenceIds = array_unique(array_map(fn ($revision) => $revision->coherenceId, $incomingItems));
        $timestamps = array_map(fn ($revision) => $revision->effectiveAt->getTimestamp(), $incomingItems);
        if (count($coherenceIds) !== 1 || max($timestamps) - min($timestamps) > 300) {
            throw new InconsistentSeoSources;
        }
        $same = $newer = $older = 0;
        foreach ($incomingItems as $revision) {
            $existing = $current->for($revision->source);
            if ($revision->identicalTo($existing)) {
                $same++;
            } elseif ($revision->version > $existing->version && $revision->effectiveAt >= $existing->effectiveAt) {
                $newer++;
            } else {
                $older++;
            }
        }
        if ($same === 3) {
            throw new DuplicateSeoFact;
        }
        if ($older > 0 && $newer > 0) {
            throw new InconsistentSeoSources;
        }
        if ($older > 0) {
            throw new StaleSeoProjection;
        }
    }
}
