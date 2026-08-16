<?php

namespace Tests\Feature;

use App\Application\ActiveGenerationBootstrap\Contract\ActiveGenerationBootstrapStateReader;
use App\Application\ActiveGenerationBootstrap\Contract\BootstrapActiveProjectionGenerationV1;
use App\Application\ActiveGenerationBootstrap\DeterministicActiveProjectionGenerationBootstrapV1;
use App\Infrastructure\ActiveGenerationBootstrap\PostgreSqlActiveGenerationBootstrapStateReader;
use Illuminate\Contracts\Console\Kernel;
use Tests\TestCase;

final class ActiveGenerationBootstrapBindingTest extends TestCase
{
    public function test_operator_bootstrap_composition_is_bound_without_startup_execution(): void
    {
        self::assertInstanceOf(DeterministicActiveProjectionGenerationBootstrapV1::class, $this->app->make(BootstrapActiveProjectionGenerationV1::class));
        self::assertInstanceOf(PostgreSqlActiveGenerationBootstrapStateReader::class, $this->app->make(ActiveGenerationBootstrapStateReader::class));
        self::assertArrayHasKey('appart:projection:generation:bootstrap', $this->artisanCommands());
    }

    /** @return array<string, object> */
    private function artisanCommands(): array
    {
        return $this->app->make(Kernel::class)->all();
    }
}
