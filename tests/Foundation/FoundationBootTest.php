<?php

namespace Tests\Foundation;

use Illuminate\Foundation\Application;
use Tests\TestCase;

final class FoundationBootTest extends TestCase
{
    public function test_the_validated_runtime_and_persistence_defaults_are_active(): void
    {
        self::assertGreaterThanOrEqual(80500, PHP_VERSION_ID);
        self::assertSame(13, (int) explode('.', Application::VERSION)[0]);
        self::assertSame('pgsql', config('database.default'));
    }
}
