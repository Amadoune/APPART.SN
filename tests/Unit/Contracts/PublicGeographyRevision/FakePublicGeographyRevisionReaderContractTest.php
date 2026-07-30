<?php

namespace Tests\Unit\Contracts\PublicGeographyRevision;

use App\Application\PublicGeographyRevision\Contract\PublicGeographyRevisionReader;
use App\Application\PublicGeographyRevision\PublicGeographyRevision;

final class FakePublicGeographyRevisionReaderContractTest extends PublicGeographyRevisionReaderContract
{
    private FakePublicGeographyRevisionReader $fake;

    protected function setUp(): void
    {
        $this->fake = new FakePublicGeographyRevisionReader;
    }

    protected function reader(): PublicGeographyRevisionReader
    {
        return $this->fake;
    }

    protected function store(string $placeId, PublicGeographyRevision $revision): void
    {
        $this->fake->revisions[$placeId] = $revision;
    }
}

final class FakePublicGeographyRevisionReader implements PublicGeographyRevisionReader
{
    /** @var array<string, PublicGeographyRevision> */
    public array $revisions = [];

    public function stableRevisionForPlace(string $placeId): ?PublicGeographyRevision
    {
        return $this->revisions[$placeId] ?? null;
    }
}
