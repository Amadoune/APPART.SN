<?php

namespace Appart\Modules\ContentSeo\Domain\Model;

use Appart\Modules\ContentSeo\Domain\ValueObject\ExpiredListingTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingSeoState;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoPageTreatment;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoSourceRevision;
use DateTimeImmutable;

final readonly class ListingSeoSource
{
    public function __construct(
        public ListingId $listingId,
        public ListingSeoState $state,
        public string $headline,
        public string $description,
        public string $canonicalPath,
        public SeoSourceRevision $revision,
        public ?DateTimeImmutable $publishedAt = null,
        public ?DateTimeImmutable $expiresAt = null,
        public ExpiredListingTreatment $expiredTreatment = ExpiredListingTreatment::NotApplicable,
        public SeoPageTreatment $nonIndexablePageTreatment = SeoPageTreatment::Remove,
    ) {}
}
