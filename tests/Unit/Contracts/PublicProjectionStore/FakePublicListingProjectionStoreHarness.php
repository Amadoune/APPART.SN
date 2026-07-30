<?php

namespace Tests\Unit\Contracts\PublicProjectionStore;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGeneration;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionGenerationState;
use Tests\Unit\Contracts\PublicProjectionStore\Support\FakePublicListingProjectionStore;

final class FakePublicListingProjectionStoreHarness implements PublicListingProjectionStoreHarness
{
    private PublicProjectionGenerationId $active;

    private PublicProjectionGenerationId $candidate;

    private FakePublicListingProjectionStore $store;

    public function __construct()
    {
        $this->active = PublicProjectionGenerationId::fromString('96000000-0000-4000-8000-000000000001');
        $this->candidate = PublicProjectionGenerationId::fromString('96000000-0000-4000-8000-000000000002');
        $this->store = new FakePublicListingProjectionStore(
            new PublicProjectionGeneration($this->active, PublicProjectionGenerationState::Active),
            new PublicProjectionGeneration($this->candidate, PublicProjectionGenerationState::Candidate),
        );
    }

    public function writer(): PublicListingProjectionWriter
    {
        return $this->store;
    }

    public function query(): PublicListingQuery
    {
        return $this->store;
    }

    public function activeGenerationId(): PublicProjectionGenerationId
    {
        return $this->active;
    }

    public function candidateGenerationId(): PublicProjectionGenerationId
    {
        return $this->candidate;
    }

    public function snapshot(PublicProjectionGenerationId $generationId, string $canonicalPath): ?PublicListingProjectionRecord
    {
        return $this->store->snapshot($generationId->value, $canonicalPath);
    }
}
