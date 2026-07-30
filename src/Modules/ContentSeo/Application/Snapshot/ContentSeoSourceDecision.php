<?php

namespace Appart\Modules\ContentSeo\Application\Snapshot;

use Appart\Modules\ContentSeo\Domain\Model\CanonicalHistoryEntry;
use Appart\Modules\ContentSeo\Domain\Model\ListingSeoSource;
use Appart\Modules\ContentSeo\Domain\Model\PropertySeoSource;
use Appart\Modules\ContentSeo\Domain\Model\SearchSeoSource;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceKind;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class ContentSeoSourceDecision
{
    /** @param list<CanonicalHistoryEntry> $canonicalHistory */
    public function __construct(
        public string $snapshotId,
        public ListingId $listingId,
        public int $version,
        public ListingSeoSource $listing,
        public SearchSeoSource $search,
        public PropertySeoSource $property,
        public array $canonicalHistory,
        public DateTimeImmutable $decisionAt,
    ) {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $snapshotId) !== 1 || $version < 1) {
            throw new InvalidArgumentException('Invalid Content/SEO source snapshot identity or version.');
        }
        if ($listing->listingId->value !== $listingId->value || $search->listingId->value !== $listingId->value || $property->listingId->value !== $listingId->value) {
            throw new InvalidArgumentException('Content/SEO source snapshot identities are inconsistent.');
        }
        if ($listing->revision->source !== SeoSourceKind::Listing || $search->revision->source !== SeoSourceKind::Search || $property->revision->source !== SeoSourceKind::Property) {
            throw new InvalidArgumentException('Content/SEO source snapshot revision ownership is inconsistent.');
        }
    }
}
