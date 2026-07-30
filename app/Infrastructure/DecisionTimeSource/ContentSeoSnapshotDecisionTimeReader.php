<?php

namespace App\Infrastructure\DecisionTimeSource;

use App\Application\DecisionTimeSource\Contract\DecisionTimeReader;
use App\Application\DecisionTimeSource\DecisionTimeReadResult;
use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Throwable;

final readonly class ContentSeoSnapshotDecisionTimeReader implements DecisionTimeReader
{
    public function __construct(
        private ContentSeoSourceSnapshotReader $snapshotReader,
        private ContentSeoSnapshotDecisionTimeMapper $mapper,
    ) {}

    public function readByListing(string $listingId): DecisionTimeReadResult
    {
        try {
            return $this->mapper->map($this->snapshotReader->readByListing(ListingId::fromString($listingId)));
        } catch (Throwable) {
            return DecisionTimeReadResult::corrupted($listingId);
        }
    }
}
