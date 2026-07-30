<?php

namespace Appart\Modules\ContentSeo\Domain\Policy;

use Appart\Modules\ContentSeo\Domain\Exception\SeoViolation;
use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalDisposition;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use DateTimeImmutable;

final readonly class CanonicalHistoryPolicy
{
    /**
     * @param  list<CanonicalHistoryEntry>  $history
     * @return list<CanonicalHistoryEntry>
     */
    public function replace(array $history, CanonicalUrl $current, CanonicalUrl $next, DateTimeImmutable $at): array
    {
        if ($current->value === $next->value) {
            throw new SeoViolation('Canonical is unchanged.');
        }
        foreach ($history as $entry) {
            if ($entry->canonical->value === $next->value) {
                throw new SeoViolation('A historical canonical cannot be reused.');
            }
        }

        $result = [];
        foreach ($history as $entry) {
            $result[] = new CanonicalHistoryEntry(
                $entry->canonical,
                CanonicalDisposition::ReservedForRedirect,
                $entry->effectiveAt,
                $entry->replacedAt ?? $at,
                $next,
            );
        }
        $result[] = new CanonicalHistoryEntry($next, CanonicalDisposition::Current, $at);

        return $result;
    }
}
