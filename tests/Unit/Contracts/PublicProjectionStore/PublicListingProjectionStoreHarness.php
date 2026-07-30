<?php

namespace Tests\Unit\Contracts\PublicProjectionStore;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionStore\PublicListingProjectionRecord;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;

interface PublicListingProjectionStoreHarness
{
    public function writer(): PublicListingProjectionWriter;

    public function query(): PublicListingQuery;

    public function activeGenerationId(): PublicProjectionGenerationId;

    public function candidateGenerationId(): PublicProjectionGenerationId;

    public function snapshot(PublicProjectionGenerationId $generationId, string $canonicalPath): ?PublicListingProjectionRecord;
}
