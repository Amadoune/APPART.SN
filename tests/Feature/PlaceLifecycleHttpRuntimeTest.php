<?php

namespace Tests\Feature;

use App\Http\PlaceLifecycleHttpResultMapper;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationResult;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrationStatus;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\Contract\PlaceLifecycleWorkflowStore;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PlaceLifecycleHttpRuntimeTest extends TestCase
{
    private const string SOURCE = '48000000-0000-4000-8000-000000000011';

    private const string TARGET = '48000000-0000-4000-8000-000000000012';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        $connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($connection);
        PostgreSqlTestEnvironment::reset($connection);
        $this->app->instance(PDO::class, $connection);
    }

    public function test_endpoint_delegates_to_the_atomic_chain_and_returns_applied(): void
    {
        $store = $this->app->make(PlaceLifecycleWorkflowStore::class);
        $store->initialize(PlaceId::fromString(self::SOURCE), PlaceLifecycleState::Enabled, 1);
        $store->initialize(PlaceId::fromString(self::TARGET), PlaceLifecycleState::Enabled, 5);

        $this->postJson(
            '/api/place-lifecycles/'.self::SOURCE.'/transitions',
            $this->payload(),
        )->assertOk()->assertExactJson([
            'status' => 'applied',
            'state' => 'merged',
        ]);
    }

    public function test_validation_is_strict_and_rejects_unknown_or_missing_evidence(): void
    {
        $unknown = $this->payload() + ['implicitTargetLookup' => true];
        $this->postJson(
            '/api/place-lifecycles/'.self::SOURCE.'/transitions',
            $unknown,
        )->assertUnprocessable()->assertJsonValidationErrors('_request');

        $missing = $this->payload();
        unset($missing['observedTargetVersion']);
        $this->postJson(
            '/api/place-lifecycles/'.self::SOURCE.'/transitions',
            $missing,
        )->assertUnprocessable()->assertJsonValidationErrors('observedTargetVersion');
    }

    public function test_source_and_target_must_be_distinct(): void
    {
        $payload = $this->payload();
        $payload['targetId'] = self::SOURCE;

        $this->postJson(
            '/api/place-lifecycles/'.self::SOURCE.'/transitions',
            $payload,
        )->assertUnprocessable()->assertJsonValidationErrors('targetId');
    }

    #[DataProvider('statusMatrix')]
    public function test_http_result_matrix_is_total_and_deterministic(
        PlaceLifecycleOrchestrationStatus $status,
        int $expectedHttp,
    ): void {
        $response = (new PlaceLifecycleHttpResultMapper)->response(
            new PlaceLifecycleOrchestrationResult($status, PlaceLifecycleState::Enabled),
        );

        self::assertSame($expectedHttp, $response->getStatusCode());
        self::assertSame($status->value, $response->getData(true)['status']);
    }

    /** @return iterable<string,array{PlaceLifecycleOrchestrationStatus,int}> */
    public static function statusMatrix(): iterable
    {
        yield 'applied' => [PlaceLifecycleOrchestrationStatus::Applied, 200];
        yield 'already applied' => [PlaceLifecycleOrchestrationStatus::AlreadyApplied, 200];
        yield 'inspection missing' => [PlaceLifecycleOrchestrationStatus::InspectionMissing, 404];
        yield 'inspection corrupted' => [PlaceLifecycleOrchestrationStatus::InspectionCorrupted, 503];
        yield 'context divergence' => [PlaceLifecycleOrchestrationStatus::ContextDivergence, 409];
        yield 'replay conflict' => [PlaceLifecycleOrchestrationStatus::ReplayConflict, 409];
        yield 'source version conflict' => [PlaceLifecycleOrchestrationStatus::SourceVersionConflict, 409];
        yield 'target version conflict' => [PlaceLifecycleOrchestrationStatus::TargetVersionConflict, 409];
        yield 'state conflict' => [PlaceLifecycleOrchestrationStatus::StateConflict, 409];
        yield 'workflow refused' => [PlaceLifecycleOrchestrationStatus::WorkflowRefused, 422];
        yield 'transition rejected' => [PlaceLifecycleOrchestrationStatus::TransitionRejected, 422];
    }

    /** @return array<string, bool|int|string> */
    private function payload(): array
    {
        return [
            'action' => 'merge',
            'currentState' => 'enabled',
            'contextVersion' => 1,
            'expectedSourceVersion' => 1,
            'targetId' => self::TARGET,
            'observedTargetVersion' => 5,
            'observedTargetState' => 'enabled',
            'observedSourceType' => 'city',
            'observedTargetType' => 'city',
            'observedSourceCountry' => 'SN',
            'observedTargetCountry' => 'SN',
            'actorId' => '48000000-0000-4000-8000-000000000013',
            'occurredAt' => '2026-07-25T15:00:00.123456Z',
            'recordedAt' => '2026-07-25T15:00:01.123456Z',
            'intentId' => '48000000-0000-4000-8000-000000000014',
        ];
    }
}
