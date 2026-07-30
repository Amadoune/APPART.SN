<?php

namespace Tests\Unit\Contracts\ContentSeoSnapshot;

use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotWriter;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotReadResult;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotWriteResult;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSourceDecision;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

final class FakeContentSeoSourceSnapshotStoreContractTest extends ContentSeoSourceSnapshotStoreContract
{
    private FakeContentSeoSourceSnapshotStore $store;

    protected function setUp(): void
    {
        $this->store = new FakeContentSeoSourceSnapshotStore;
    }

    protected function reader(): ContentSeoSourceSnapshotReader
    {
        return $this->store;
    }

    protected function writer(): ContentSeoSourceSnapshotWriter
    {
        return $this->store;
    }
}

final class FakeContentSeoSourceSnapshotStore implements ContentSeoSourceSnapshotReader, ContentSeoSourceSnapshotWriter
{
    /** @var array<string, ContentSeoSourceDecision> */
    private array $snapshots = [];

    public function readByListing(ListingId $listingId): ContentSeoSnapshotReadResult
    {
        $snapshot = $this->snapshots[$listingId->value] ?? null;

        return $snapshot === null ? ContentSeoSnapshotReadResult::missing($listingId) : ContentSeoSnapshotReadResult::found($listingId, $snapshot);
    }

    public function store(ContentSeoSourceDecision $snapshot): ContentSeoSnapshotWriteResult
    {
        $current = $this->snapshots[$snapshot->listingId->value] ?? null;
        if ($current !== null && $snapshot->version < $current->version) {
            return ContentSeoSnapshotWriteResult::RejectedObsolete;
        }
        if ($current !== null && $snapshot->version === $current->version) {
            return $current == $snapshot ? ContentSeoSnapshotWriteResult::AlreadyApplied : ContentSeoSnapshotWriteResult::Divergent;
        }
        $this->snapshots[$snapshot->listingId->value] = $snapshot;

        return ContentSeoSnapshotWriteResult::Applied;
    }
}
