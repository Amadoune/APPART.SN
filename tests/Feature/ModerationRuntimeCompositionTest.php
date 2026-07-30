<?php

namespace Tests\Feature;

use App\Application\ModerationRuntime\Contract\ModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\DeterministicModerationRuntimeV1;
use App\Application\ModerationRuntime\ModerationRuntimeStatus;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationDecisionStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationQueueStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationCaseStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationDecisionStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationQueueStore;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ModerationRuntimeCompositionTest extends TestCase
{
    #[Test]
    public function container_resolves_unique_lazy_singletons_and_healthy_runtime(): void
    {
        $this->app->instance(PDO::class, $this->createMock(PDO::class));

        $runtime = $this->app->make(ModerationRuntimeV1::class);
        $queue = $this->app->make(ModerationQueueRuntimeV1::class);

        self::assertInstanceOf(DeterministicModerationRuntimeV1::class, $runtime);
        self::assertInstanceOf(DeterministicModerationQueueRuntimeV1::class, $queue);
        self::assertSame($runtime, $this->app->make(ModerationRuntimeV1::class));
        self::assertSame($queue, $this->app->make(ModerationQueueRuntimeV1::class));
        self::assertSame($queue, $runtime->queue());
        self::assertInstanceOf(PostgreSqlModerationCaseStore::class, $this->app->make(ModerationCaseStore::class));
        self::assertInstanceOf(PostgreSqlModerationDecisionStore::class, $this->app->make(ModerationDecisionStore::class));
        self::assertInstanceOf(PostgreSqlModerationQueueStore::class, $this->app->make(ModerationQueueStore::class));
        self::assertSame(ModerationRuntimeStatus::Healthy, $runtime->inspect()->status);
        self::assertSame(RuntimeHealthStatus::Healthy, $this->app->make(RuntimeHealthInspector::class)->inspect()->status);
    }
}
