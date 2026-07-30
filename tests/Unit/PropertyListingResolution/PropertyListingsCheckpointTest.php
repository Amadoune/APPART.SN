<?php

namespace Tests\Unit\PropertyListingResolution;

use App\Application\PropertyListingResolution\PropertyListingsDiagnostic;
use App\Infrastructure\PropertyListingResolution\PropertyListingsCheckpoint;
use PHPUnit\Framework\TestCase;

final class PropertyListingsCheckpointTest extends TestCase
{
    private const string PROPERTY = '9b000000-0000-4000-8000-000000000001';

    private const string OTHER_PROPERTY = '9b000000-0000-4000-8000-000000000002';

    private const string LISTING = '9a000000-0000-4000-8000-000000000001';

    public function test_checkpoint_is_deterministic_opaque_and_bound_to_property(): void
    {
        $codec = new PropertyListingsCheckpoint;
        $checkpoint = $codec->encode(self::PROPERTY, self::LISTING);

        self::assertStringNotContainsString(self::PROPERTY, $checkpoint);
        self::assertSame($checkpoint, $codec->encode(self::PROPERTY, self::LISTING));
        self::assertSame(self::LISTING, $codec->decode(self::PROPERTY, $checkpoint)['after']);
        self::assertSame(PropertyListingsDiagnostic::CheckpointForAnotherProperty, $codec->decode(self::OTHER_PROPERTY, $checkpoint)['diagnostic']);
        self::assertSame(PropertyListingsDiagnostic::InvalidCheckpoint, $codec->decode(self::PROPERTY, 'not-a-checkpoint')['diagnostic']);
    }
}
