<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicFacts;

use DateTimeImmutable;

final readonly class PublishedPublicFacts
{
    public function __construct(
        public string $listingId,
        public PublicTransactionKind $transactionKind,
        public string $publishedRevisionId,
        public DateTimeImmutable $publishedAt,
    ) {}
}
