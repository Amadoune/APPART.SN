<?php

namespace Tests\Feature;

use App\Application\ModerationHttp\Contract\ModerationHttpRuntimeV1;
use App\Application\ModerationHttp\DeterministicModerationHttpRuntimeV1;
use PDO;
use Tests\TestCase;

final class ModerationHttpRuntimeCompositionTest extends TestCase
{
    public function test_http_runtime_is_a_unique_lazy_singleton(): void
    {
        $this->app->instance(PDO::class, $this->createStub(PDO::class));
        $first = $this->app->make(ModerationHttpRuntimeV1::class);

        self::assertInstanceOf(DeterministicModerationHttpRuntimeV1::class, $first);
        self::assertSame($first, $this->app->make(ModerationHttpRuntimeV1::class));
    }
}
