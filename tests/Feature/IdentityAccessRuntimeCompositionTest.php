<?php

namespace Tests\Feature;

use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\Contract\AccountAvailabilityInspector;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\Contract\AccountClosureStateReader;
use Appart\Modules\IdentityAccess\Application\AccountAvailability\DeterministicAccountAvailabilityInspector;
use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\Contract\IdentityAccessRuntimeHealthInspector;
use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\IdentityAccessRuntimeHealthStatus;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountClosureStateReader;
use PDO;
use ReflectionProperty;
use Tests\TestCase;

final class IdentityAccessRuntimeCompositionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(PDO::class, static fn (): PDO => new PDO('sqlite::memory:'));
    }

    public function test_owner_provider_builds_one_lazy_graph_with_shared_pdo(): void
    {
        self::assertFalse($this->app->resolved(AccountClosureStateReader::class));
        self::assertFalse($this->app->resolved(AccountAvailabilityInspector::class));

        $reader = $this->app->make(AccountClosureStateReader::class);
        $inspector = $this->app->make(AccountAvailabilityInspector::class);

        self::assertInstanceOf(PostgreSqlAccountClosureStateReader::class, $reader);
        self::assertInstanceOf(DeterministicAccountAvailabilityInspector::class, $inspector);
        self::assertSame($reader, $this->app->make(AccountClosureStateReader::class));
        self::assertSame($inspector, $this->app->make(AccountAvailabilityInspector::class));
        self::assertSame(
            $this->app->make(PDO::class),
            (new ReflectionProperty($reader, 'connection'))->getValue($reader),
        );
        self::assertSame(
            $reader,
            (new ReflectionProperty($inspector, 'closures'))->getValue($inspector),
        );
    }

    public function test_identity_access_runtime_is_healthy_without_changing_the_frozen_catalogue(): void
    {
        $connection = $this->app->make(PDO::class);
        $report = $this->app->make(IdentityAccessRuntimeHealthInspector::class)->inspect();

        self::assertSame(IdentityAccessRuntimeHealthStatus::Healthy, $report->status);
        self::assertSame([], $report->unhealthyComponents);
        self::assertCount(60, PublicProjectionRuntimeRequirements::certified());
        self::assertFalse($connection->inTransaction());
    }
}
