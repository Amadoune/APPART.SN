<?php

namespace Tests\Unit\Contracts\SearchDecision;

use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader;
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecision;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionReadResult;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionWriteResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

final class FakeSearchDecisionStoreContractTest extends SearchDecisionStoreContract
{
    private FakeSearchDecisionStore $store;

    protected function setUp(): void
    {
        $this->store = new FakeSearchDecisionStore;
    }

    protected function reader(): SearchDecisionReader
    {
        return $this->store;
    }

    protected function writer(): SearchDecisionWriter
    {
        return $this->store;
    }
}

final class FakeSearchDecisionStore implements SearchDecisionReader, SearchDecisionWriter
{
    /** @var array<string, SearchDecision> */
    private array $decisions = [];

    public function readByListing(ListingId $listingId): SearchDecisionReadResult
    {
        $decision = $this->decisions[$listingId->value] ?? null;

        return $decision === null ? SearchDecisionReadResult::missing($listingId) : SearchDecisionReadResult::found($listingId, $decision);
    }

    public function store(SearchDecision $decision): SearchDecisionWriteResult
    {
        $existing = $this->decisions[$decision->listingId->value] ?? null;
        if ($existing !== null && $decision->version < $existing->version) {
            return SearchDecisionWriteResult::RejectedObsolete;
        }
        if ($existing !== null && $decision->version === $existing->version) {
            return $existing == $decision ? SearchDecisionWriteResult::AlreadyApplied : SearchDecisionWriteResult::Divergent;
        }
        $this->decisions[$decision->listingId->value] = $decision;

        return SearchDecisionWriteResult::Applied;
    }
}
