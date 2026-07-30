<?php

namespace Tests\Unit\Contracts\PublicMediaRevision;

use App\Application\PublicMediaRevision\Contract\PublicMediaRevisionReader;
use App\Application\PublicMediaRevision\PublicMediaRevision;
use App\Application\PublicMediaRevision\PublicMediaRevisionStrategy;
use PHPUnit\Framework\TestCase;

abstract class PublicMediaRevisionReaderContract extends TestCase
{
    abstract protected function reader(): PublicMediaRevisionReader;

    abstract protected function store(string $mediaCollectionId, PublicMediaRevision $revision): void;

    public function test_unknown_collection_has_no_invented_revision(): void
    {
        self::assertNull($this->reader()->stableRevisionForMediaCollection('media-collection:unknown'));
    }

    public function test_reader_returns_the_exact_stable_revision(): void
    {
        $revision = (new PublicMediaRevisionStrategy)->revise(3, '{"cover":"media:1"}', 'media-collection:3:cover-selected');
        $this->store('media-collection:listing-1', $revision);
        $stored = $this->reader()->stableRevisionForMediaCollection('media-collection:listing-1');

        self::assertNotNull($stored);
        self::assertTrue($revision->sameFactAs($stored));
    }
}
