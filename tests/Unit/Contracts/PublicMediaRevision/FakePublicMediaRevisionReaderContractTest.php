<?php

namespace Tests\Unit\Contracts\PublicMediaRevision;

use App\Application\PublicMediaRevision\Contract\PublicMediaRevisionReader;
use App\Application\PublicMediaRevision\PublicMediaRevision;

final class FakePublicMediaRevisionReaderContractTest extends PublicMediaRevisionReaderContract
{
    private FakePublicMediaRevisionReader $fake;

    protected function setUp(): void
    {
        $this->fake = new FakePublicMediaRevisionReader;
    }

    protected function reader(): PublicMediaRevisionReader
    {
        return $this->fake;
    }

    protected function store(string $mediaCollectionId, PublicMediaRevision $revision): void
    {
        $this->fake->revisions[$mediaCollectionId] = $revision;
    }
}

final class FakePublicMediaRevisionReader implements PublicMediaRevisionReader
{
    /** @var array<string, PublicMediaRevision> */
    public array $revisions = [];

    public function stableRevisionForMediaCollection(string $mediaCollectionId): ?PublicMediaRevision
    {
        return $this->revisions[$mediaCollectionId] ?? null;
    }
}
