<?php

namespace Appart\Modules\SearchDiscovery\Domain\Policy;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\FreshnessDecision;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SourceRevisionSet;

final readonly class SearchFreshnessPolicy
{
    public function compare(SourceRevisionSet $incoming, SourceRevisionSet $current): FreshnessDecision
    {
        $newer = 0;
        $older = 0;
        $same = 0;

        foreach ($incoming->all() as $revision) {
            $existing = $current->for($revision->source);
            if ($revision->sameFact($existing)) {
                $same++;
            } elseif ($revision->version > $existing->version && $revision->effectiveAt >= $existing->effectiveAt) {
                $newer++;
            } else {
                $older++;
            }
        }

        if ($same === 3) {
            return FreshnessDecision::Duplicate;
        }
        if ($older > 0 && $newer > 0) {
            return FreshnessDecision::Inconsistent;
        }
        if ($older > 0) {
            return FreshnessDecision::Stale;
        }

        return FreshnessDecision::Newer;
    }
}
