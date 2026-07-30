<?php

namespace Tests\Unit\Contracts\SearchDecision;

use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader;
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadStatus;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionWriteResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use PHPUnit\Framework\TestCase;
use Tests\Support\SearchDecisionFixture;

abstract class SearchDecisionStoreContract extends TestCase
{
    abstract protected function reader(): SearchDecisionReader;

    abstract protected function writer(): SearchDecisionWriter;

    public function test_missing_and_found_are_distinct_and_read_is_deterministic(): void
    {
        $listing = ListingId::fromString('99200000-0000-4000-8000-000000000001');
        self::assertSame(SearchDecisionReadStatus::Missing, $this->reader()->readByListing($listing)->status);
        $decision = SearchDecisionFixture::make();
        self::assertSame(SearchDecisionWriteResult::Applied, $this->writer()->store($decision));

        $first = $this->reader()->readByListing($listing);
        $second = $this->reader()->readByListing($listing);
        self::assertSame(SearchDecisionReadStatus::Found, $first->status);
        self::assertEquals($decision, $first->decision);
        self::assertEquals($first, $second);
    }

    public function test_identical_write_is_idempotent_and_same_version_difference_is_divergent(): void
    {
        self::assertSame(SearchDecisionWriteResult::Applied, $this->writer()->store(SearchDecisionFixture::make()));
        self::assertSame(SearchDecisionWriteResult::AlreadyApplied, $this->writer()->store(SearchDecisionFixture::make()));
        self::assertSame(SearchDecisionWriteResult::Divergent, $this->writer()->store(SearchDecisionFixture::make(rank: 501)));
    }

    public function test_newer_version_replaces_and_obsolete_version_is_rejected(): void
    {
        self::assertSame(SearchDecisionWriteResult::Applied, $this->writer()->store(SearchDecisionFixture::make(version: 2)));
        self::assertSame(SearchDecisionWriteResult::RejectedObsolete, $this->writer()->store(SearchDecisionFixture::make(version: 1)));
        self::assertSame(2, $this->reader()->readByListing(SearchDecisionFixture::make()->listingId)->decision?->version);
    }
}
