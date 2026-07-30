<?php

namespace Tests\Unit\Contracts\ContentSeoSnapshot;

use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotWriter;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotReadStatus;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotWriteResult;
use PHPUnit\Framework\TestCase;
use Tests\Support\ContentSeoSourceSnapshotFixture;

abstract class ContentSeoSourceSnapshotStoreContract extends TestCase
{
    abstract protected function reader(): ContentSeoSourceSnapshotReader;

    abstract protected function writer(): ContentSeoSourceSnapshotWriter;

    public function test_missing_found_and_deterministic_read_are_explicit(): void
    {
        $snapshot = ContentSeoSourceSnapshotFixture::make();
        self::assertSame(ContentSeoSnapshotReadStatus::Missing, $this->reader()->readByListing($snapshot->listingId)->status);
        self::assertSame(ContentSeoSnapshotWriteResult::Applied, $this->writer()->store($snapshot));
        $first = $this->reader()->readByListing($snapshot->listingId);
        self::assertSame(ContentSeoSnapshotReadStatus::Found, $first->status);
        self::assertEquals($snapshot, $first->snapshot);
        self::assertEquals($first, $this->reader()->readByListing($snapshot->listingId));
    }

    public function test_idempotence_divergence_and_obsolescence_are_distinct(): void
    {
        self::assertSame(ContentSeoSnapshotWriteResult::Applied, $this->writer()->store(ContentSeoSourceSnapshotFixture::make()));
        self::assertSame(ContentSeoSnapshotWriteResult::AlreadyApplied, $this->writer()->store(ContentSeoSourceSnapshotFixture::make()));
        self::assertSame(ContentSeoSnapshotWriteResult::Divergent, $this->writer()->store(ContentSeoSourceSnapshotFixture::make(headline: 'Autre décision')));
        self::assertSame(ContentSeoSnapshotWriteResult::Applied, $this->writer()->store(ContentSeoSourceSnapshotFixture::make(version: 2)));
        self::assertSame(ContentSeoSnapshotWriteResult::RejectedObsolete, $this->writer()->store(ContentSeoSourceSnapshotFixture::make(version: 1)));
    }
}
