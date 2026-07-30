<?php

namespace Tests\Unit\Contracts\ActiveGenerationReader;

use App\Application\ActiveGenerationReader\ActiveGenerationReadStatus;
use App\Application\ActiveGenerationReader\Contract\ActiveGenerationReader;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use PHPUnit\Framework\TestCase;

abstract class ActiveGenerationReaderContract extends TestCase
{
    protected const string GENERATION = '99000000-0000-4000-8000-000000000001';

    abstract protected function reader(): ActiveGenerationReader;

    abstract protected function givenActive(PublicProjectionGenerationId $generationId): void;

    public function test_missing_active_generation_is_explicit(): void
    {
        $result = $this->reader()->read();

        self::assertSame(ActiveGenerationReadStatus::Missing, $result->status);
        self::assertNull($result->generation);
    }

    public function test_reader_returns_exact_active_generation(): void
    {
        $expected = PublicProjectionGenerationId::fromString(self::GENERATION);
        $this->givenActive($expected);
        $result = $this->reader()->read();

        self::assertSame(ActiveGenerationReadStatus::Found, $result->status);
        self::assertTrue($expected->equals($result->generation?->id ?? throw new \LogicException('Missing generation.')));
        self::assertSame('active', $result->generation->state->value);
    }
}
