<?php

namespace Tests\Unit\Contracts\DecisionTimeSource;

use App\Application\DecisionTimeSource\Contract\DecisionTimeReader;
use App\Infrastructure\DecisionTimeSource\ContentSeoSnapshotDecisionTimeMapper;
use App\Infrastructure\DecisionTimeSource\ContentSeoSnapshotDecisionTimeReader;
use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotReadResult;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSourceDecision;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use Tests\Support\ContentSeoSourceSnapshotFixture;

final class ContentSeoSnapshotDecisionTimeReaderContractTest extends DecisionTimeReaderContract
{
    private ?ContentSeoSnapshotReadResult $result = null;

    protected function reader(): DecisionTimeReader
    {
        $snapshotReader = new readonly class($this->result) implements ContentSeoSourceSnapshotReader
        {
            public function __construct(private ?ContentSeoSnapshotReadResult $result) {}

            public function readByListing(ListingId $listingId): ContentSeoSnapshotReadResult
            {
                return $this->result ?? ContentSeoSnapshotReadResult::missing($listingId);
            }
        };

        return new ContentSeoSnapshotDecisionTimeReader($snapshotReader, new ContentSeoSnapshotDecisionTimeMapper);
    }

    protected function givenDecisionAt(DateTimeImmutable $decisionAt): void
    {
        $fixture = ContentSeoSourceSnapshotFixture::make();
        $snapshot = new ContentSeoSourceDecision(
            $fixture->snapshotId,
            $fixture->listingId,
            $fixture->version,
            $fixture->listing,
            $fixture->search,
            $fixture->property,
            $fixture->canonicalHistory,
            $decisionAt,
        );
        $this->result = ContentSeoSnapshotReadResult::found($fixture->listingId, $snapshot);
    }

    protected function givenCorrupted(): void
    {
        $id = ListingId::fromString(self::LISTING);
        $this->result = ContentSeoSnapshotReadResult::corrupted($id);
    }
}
