<?php

namespace Tests\Feature;

use App\Application\PropertyLifecycleEventIntegration\Contract\PropertyLifecycleEventOrchestrator;
use App\Application\PropertyLifecycleEventIntegration\PropertyLifecycleEventOrchestrationRequest;
use App\Http\Controllers\PropertyLifecycleTransitionController;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleDiagnostic;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationDiagnosticCode;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationResult;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class PropertyLifecycleHttpRuntimeTest extends TestCase
{
    private const PROPERTY_ID = '018f6b5c-7a91-4c3e-8d20-123456789abc';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    }

    /** @return iterable<string, array{PropertyLifecycleOrchestrationResult,int,string,?string}> */
    public static function resultMappings(): iterable
    {
        $transition = new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Active, PropertyLifecycleAction::Activate);

        yield 'applied' => [PropertyLifecycleOrchestrationResult::applied($transition), 200, 'applied', null];
        yield 'already applied' => [PropertyLifecycleOrchestrationResult::alreadyApplied($transition), 200, 'already_applied', null];
        yield 'denied' => [PropertyLifecycleOrchestrationResult::denied(PropertyLifecycleDiagnostic::transitionForbidden()), 422, 'denied', 'transition_forbidden'];
        yield 'conflict' => [PropertyLifecycleOrchestrationResult::concurrencyConflict(PropertyLifecycleOrchestrationDiagnosticCode::VersionConflict), 409, 'concurrency_conflict', 'version_conflict'];
        yield 'failure' => [PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::InfrastructureFailure), 503, 'persistence_failure', 'infrastructure_failure'];
    }

    #[DataProvider('resultMappings')]
    public function test_every_application_result_has_a_stable_http_mapping(PropertyLifecycleOrchestrationResult $result, int $httpStatus, string $status, ?string $diagnostic): void
    {
        $stub = new CapturingPropertyLifecycleEventOrchestrator($result);
        $this->app->instance(PropertyLifecycleEventOrchestrator::class, $stub);

        $response = $this->postJson('/api/property-lifecycles/'.self::PROPERTY_ID.'/transitions', $this->validPayload());

        $response->assertStatus($httpStatus)->assertExactJson(['status' => $status, 'diagnostic' => $diagnostic]);
        self::assertCount(1, $stub->requests);
    }

    public function test_request_is_translated_without_transforming_identity_action_version_or_metadata(): void
    {
        $stub = new CapturingPropertyLifecycleEventOrchestrator(PropertyLifecycleOrchestrationResult::denied(PropertyLifecycleDiagnostic::incompatibleState()));
        $this->app->instance(PropertyLifecycleEventOrchestrator::class, $stub);

        $this->postJson('/api/property-lifecycles/'.self::PROPERTY_ID.'/transitions', $this->validPayload())->assertUnprocessable();

        $request = $stub->requests[0];
        self::assertSame(self::PROPERTY_ID, $request->propertyId->value);
        self::assertSame(PropertyLifecycleAction::Activate, $request->action);
        self::assertSame(7, $request->expectedVersion);
        self::assertSame('2026-07-21T10:11:12.123456Z', $request->occurredAt->value);
        self::assertSame('2026-07-21T10:11:13.123456Z', $request->recordedAt->value);
    }

    /** @return iterable<string, array{string}> */
    public static function certifiedActions(): iterable
    {
        foreach (PropertyLifecycleAction::cases() as $action) {
            if ($action !== PropertyLifecycleAction::Unknown) {
                yield $action->value => [$action->value];
            }
        }
    }

    #[DataProvider('certifiedActions')]
    public function test_every_executable_action_is_accepted_explicitly(string $action): void
    {
        $stub = new CapturingPropertyLifecycleEventOrchestrator(PropertyLifecycleOrchestrationResult::denied(PropertyLifecycleDiagnostic::transitionForbidden()));
        $this->app->instance(PropertyLifecycleEventOrchestrator::class, $stub);
        $payload = $this->validPayload();
        $payload['action'] = $action;

        $this->postJson('/api/property-lifecycles/'.self::PROPERTY_ID.'/transitions', $payload)->assertUnprocessable();

        self::assertSame($action, $stub->requests[0]->action->value);
    }

    /** @return iterable<string, array{array<string,mixed>}> */
    public static function invalidPayloads(): iterable
    {
        yield 'unknown action' => [['action' => 'unknown', 'expectedVersion' => 7, 'occurredAt' => '2026-07-21T10:11:12.123456Z', 'recordedAt' => '2026-07-21T10:11:13.123456Z']];
        yield 'unsupported action' => [['action' => 'publish', 'expectedVersion' => 7, 'occurredAt' => '2026-07-21T10:11:12.123456Z', 'recordedAt' => '2026-07-21T10:11:13.123456Z']];
        yield 'non positive version' => [['action' => 'activate', 'expectedVersion' => 0, 'occurredAt' => '2026-07-21T10:11:12.123456Z', 'recordedAt' => '2026-07-21T10:11:13.123456Z']];
        yield 'invalid occurred instant' => [['action' => 'activate', 'expectedVersion' => 7, 'occurredAt' => '2026-07-21T10:11:12Z', 'recordedAt' => '2026-07-21T10:11:13.123456Z']];
        yield 'invalid recorded instant' => [['action' => 'activate', 'expectedVersion' => 7, 'occurredAt' => '2026-07-21T10:11:12.123456Z', 'recordedAt' => 'today']];
        yield 'recorded before occurred' => [['action' => 'activate', 'expectedVersion' => 7, 'occurredAt' => '2026-07-21T10:11:12.123456Z', 'recordedAt' => '2026-07-21T10:11:11.123456Z']];
        yield 'missing fields' => [[]];
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_transport_never_calls_the_orchestrator(array $payload): void
    {
        $stub = new CapturingPropertyLifecycleEventOrchestrator(PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::InfrastructureFailure));
        $this->app->instance(PropertyLifecycleEventOrchestrator::class, $stub);

        $this->postJson('/api/property-lifecycles/'.self::PROPERTY_ID.'/transitions', $payload)->assertUnprocessable();

        self::assertSame([], $stub->requests);
    }

    public function test_invalid_route_uuid_is_not_dispatched_to_the_controller(): void
    {
        $stub = new CapturingPropertyLifecycleEventOrchestrator(PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::InfrastructureFailure));
        $this->app->instance(PropertyLifecycleEventOrchestrator::class, $stub);

        $this->postJson('/api/property-lifecycles/not-a-uuid/transitions', $this->validPayload())->assertNotFound();

        self::assertSame([], $stub->requests);
    }

    public function test_controller_resolves_with_only_the_event_orchestrator_port(): void
    {
        $stub = new CapturingPropertyLifecycleEventOrchestrator(PropertyLifecycleOrchestrationResult::persistenceFailure(PropertyLifecycleOrchestrationDiagnosticCode::InfrastructureFailure));
        $this->app->instance(PropertyLifecycleEventOrchestrator::class, $stub);

        $controller = $this->app->make(PropertyLifecycleTransitionController::class);

        self::assertSame($stub, new \ReflectionProperty($controller, 'orchestrator')->getValue($controller));
    }

    /** @return array{action:string,expectedVersion:int,occurredAt:string,recordedAt:string} */
    private function validPayload(): array
    {
        return [
            'action' => 'activate',
            'expectedVersion' => 7,
            'occurredAt' => '2026-07-21T10:11:12.123456Z',
            'recordedAt' => '2026-07-21T10:11:13.123456Z',
        ];
    }
}

final class CapturingPropertyLifecycleEventOrchestrator implements PropertyLifecycleEventOrchestrator
{
    /** @var list<PropertyLifecycleEventOrchestrationRequest> */
    public array $requests = [];

    public function __construct(private readonly PropertyLifecycleOrchestrationResult $result) {}

    public function transition(PropertyLifecycleEventOrchestrationRequest $request): PropertyLifecycleOrchestrationResult
    {
        $this->requests[] = $request;

        return $this->result;
    }
}
