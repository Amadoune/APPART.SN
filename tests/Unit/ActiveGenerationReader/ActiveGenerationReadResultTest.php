<?php

namespace Tests\Unit\ActiveGenerationReader;

use App\Application\ActiveGenerationReader\ActiveGenerationReadResult;
use App\Application\ActiveGenerationReader\ActiveGenerationReadStatus;
use App\Application\PublicProjectionStore\PublicProjectionGeneration;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionGenerationState;
use PHPUnit\Framework\TestCase;

final class ActiveGenerationReadResultTest extends TestCase
{
    public function test_closed_results_expose_generation_only_when_found(): void
    {
        $generation = new PublicProjectionGeneration(
            PublicProjectionGenerationId::fromString('99000000-0000-4000-8000-000000000001'),
            PublicProjectionGenerationState::Active,
        );

        self::assertSame($generation, ActiveGenerationReadResult::found($generation)->generation);
        self::assertSame(ActiveGenerationReadStatus::Missing, ActiveGenerationReadResult::missing()->status);
        self::assertSame(ActiveGenerationReadStatus::Corrupted, ActiveGenerationReadResult::corrupted()->status);
        self::assertNull(ActiveGenerationReadResult::corrupted()->generation);
    }
}
