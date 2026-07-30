<?php

namespace Tests\Unit\Contracts\PublicGeographyRevision;

use App\Application\PublicGeographyRevision\Contract\PublicGeographyRevisionReader;
use App\Application\PublicGeographyRevision\PublicGeographyRevision;
use App\Application\PublicGeographyRevision\PublicGeographyRevisionStrategy;
use PHPUnit\Framework\TestCase;

abstract class PublicGeographyRevisionReaderContract extends TestCase
{
    abstract protected function reader(): PublicGeographyRevisionReader;

    abstract protected function store(string $placeId, PublicGeographyRevision $revision): void;

    public function test_unknown_place_has_no_invented_revision(): void
    {
        self::assertNull($this->reader()->stableRevisionForPlace('place:unknown'));
    }

    public function test_reader_returns_the_exact_stable_revision(): void
    {
        $revision = (new PublicGeographyRevisionStrategy)->revise(3, '{"name":"Dakar"}', 'place:3:renamed');
        $this->store('place:dakar', $revision);

        self::assertTrue($revision->sameFactAs($this->reader()->stableRevisionForPlace('place:dakar')));
    }
}
