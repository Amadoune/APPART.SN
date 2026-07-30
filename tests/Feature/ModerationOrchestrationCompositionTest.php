<?php

namespace Tests\Feature;

use App\Application\ModerationOperationalAuditEventProduction\OperationalAuditModerationCaseOrchestratorV1;
use App\Application\ModerationOrchestration\Contract\ModerationCaseOrchestratorV1;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ModerationOrchestrationCompositionTest extends TestCase
{
    #[Test]
    public function container_resolves_one_owner_local_orchestrator_singleton(): void
    {
        $this->app->instance(PDO::class, $this->createMock(PDO::class));
        $orchestrator = $this->app->make(ModerationCaseOrchestratorV1::class);

        self::assertInstanceOf(OperationalAuditModerationCaseOrchestratorV1::class, $orchestrator);
        self::assertSame($orchestrator, $this->app->make(ModerationCaseOrchestratorV1::class));
    }
}
