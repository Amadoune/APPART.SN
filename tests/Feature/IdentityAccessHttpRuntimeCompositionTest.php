<?php

namespace Tests\Feature;

use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\DeterministicIdentityAccessHttpRuntime;
use Tests\TestCase;

final class IdentityAccessHttpRuntimeCompositionTest extends TestCase
{
    public function test_the_certified_runtime_is_bound_lazily_as_a_singleton(): void
    {
        self::assertFalse($this->app->resolved(IdentityAccessHttpRuntime::class));
        $runtime = $this->app->make(IdentityAccessHttpRuntime::class);

        self::assertInstanceOf(DeterministicIdentityAccessHttpRuntime::class, $runtime);
        self::assertSame($runtime, $this->app->make(IdentityAccessHttpRuntime::class));
    }
}
