<?php

namespace Tests\Feature;

use App\Application\AdministrativeActionLifecycleEventIntegration\AdministrativeActionLifecycleAtomicEventOrchestrator;
use App\Application\AdministrativeActionLifecycleEventIntegration\Contract\AdministrativeActionLifecycleAtomicTransaction;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Http\Controllers\AdministrativeActionLifecycleHttpController;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleDiagnostic;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventCatalog;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationResult;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleOrchestrationStatus;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\AdministrativeActionLifecycleTransitionRequest;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\Contract\AdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualReplayInspector;
use Closure;
use Illuminate\Routing\Route;
use Tests\TestCase;

final class AdministrativeActionLifecycleHttpRuntimeTest extends TestCase
{
    private const ACTION_ID = 'a4700000-0000-4000-8000-000000000470';

    private const ENDPOINT = '/api/administrative-action-lifecycles/'.self::ACTION_ID.'/transitions';

    private CapturingAdministrativeActionLifecycleHttpOrchestrator $port;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        $this->port = $this->bindDeniedIntegrator();
    }

    public function test_the_unique_runtime_route_is_composed_and_uuid_bounded(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(static fn (Route $route): bool => $route->uri() === 'api/administrative-action-lifecycles/{actionId}/transitions');

        self::assertCount(1, $routes);
        $route = $routes->first();
        self::assertInstanceOf(Route::class, $route);
        self::assertSame(['POST'], $route->methods());
        self::assertSame(AdministrativeActionLifecycleHttpController::class, $route->getActionName());
        self::assertArrayHasKey('actionId', $route->wheres);
    }

    public function test_invalid_transport_is_rejected_before_orchestration(): void
    {
        $valid = $this->recordPayload();

        $this->postJson(self::ENDPOINT, [...$valid, 'action' => 'unknown'])->assertUnprocessable()->assertJsonValidationErrors('action');
        $this->postJson(self::ENDPOINT, [...$valid, 'contextVersion' => 2])->assertUnprocessable()->assertJsonValidationErrors('contextVersion');
        $this->postJson(self::ENDPOINT, [...$valid, 'expectedVersion' => 0])->assertUnprocessable()->assertJsonValidationErrors('expectedVersion');
        $this->postJson(self::ENDPOINT, [...$valid, 'actorId' => 'other-actor'])->assertUnprocessable()->assertJsonValidationErrors('actorId');
        $this->postJson(self::ENDPOINT, [...$valid, 'historicalReason' => 'short'])->assertUnprocessable()->assertJsonValidationErrors('historicalReason');
        $this->postJson(self::ENDPOINT, [...$valid, 'reasonEvidence' => 'unavailable'])->assertUnprocessable()->assertJsonValidationErrors('reasonEvidence');
        $this->postJson(self::ENDPOINT, [...$valid, 'decisionActorId' => 'different-actor'])->assertUnprocessable()->assertJsonValidationErrors('decisionActorId');
        $this->postJson(self::ENDPOINT, [...$valid, 'approvalId' => 'a4700000-0000-4000-8000-000000000001'])->assertUnprocessable()->assertJsonValidationErrors('approvalId');
        $this->postJson(self::ENDPOINT, [...$valid, 'recordedAt' => '2026-07-25T09:59:59.000000Z'])->assertUnprocessable()->assertJsonValidationErrors('recordedAt');
        self::assertSame([], $this->port->requests);
    }

    public function test_direct_recording_is_translated_exactly_and_delegated_once(): void
    {
        $this->postJson(self::ENDPOINT, $this->recordPayload())->assertUnprocessable()->assertExactJson([
            'status' => 'denied',
            'diagnostic' => 'missing_reason',
        ]);

        self::assertCount(1, $this->port->requests);
        $request = $this->port->requests[0];
        self::assertSame(self::ACTION_ID, $request->actionId->value);
        self::assertSame('record', $request->action->value);
        self::assertSame(1, $request->context->contractVersion->value);
        self::assertSame(7, $request->context->expectedVersion->value);
        self::assertSame('author-001', $request->context->actor->value);
        self::assertSame('present', $request->context->decisionContext->reasonEvidence->value);
        self::assertSame('direct_recording', $request->context->decisionContext->authority->disposition->value);
        self::assertNull($request->context->decisionIdentities->approvalId);
        self::assertNull($request->context->decisionIdentities->decisionId);
        self::assertSame('2026-07-25T10:00:00.123456Z', $request->context->occurredAt->canonical());
    }

    public function test_approval_is_transported_with_explicit_identifiers_without_reconstruction(): void
    {
        $payload = [
            ...$this->recordPayload(),
            'action' => 'approve',
            'expectedVersion' => 8,
            'actorId' => 'decision-actor-001',
            'recordingDisposition' => 'independent_approval_required',
            'decisionActorId' => 'decision-actor-001',
            'approvalId' => 'a4700000-0000-4000-8000-000000000001',
            'decisionId' => 'a4700000-0000-4000-8000-000000000002',
        ];

        $this->postJson(self::ENDPOINT, $payload)->assertUnprocessable();

        self::assertCount(1, $this->port->requests);
        $context = $this->port->requests[0]->context;
        self::assertSame('approve', $context->decisionIdentities->action->value);
        self::assertSame($payload['approvalId'], $context->decisionIdentities->approvalId?->value);
        self::assertSame($payload['decisionId'], $context->decisionIdentities->decisionId?->value);
        self::assertSame('decision-actor-001', $context->actor->value);
    }

    public function test_invalid_action_identifier_is_rejected_at_route_boundary(): void
    {
        $this->postJson('/api/administrative-action-lifecycles/not-a-uuid/transitions', [])->assertNotFound();
    }

    /** @return array<string, int|string> */
    private function recordPayload(): array
    {
        return [
            'action' => 'record',
            'contextVersion' => 1,
            'expectedVersion' => 7,
            'actorId' => 'author-001',
            'occurredAt' => '2026-07-25T10:00:00.123456Z',
            'recordedAt' => '2026-07-25T10:00:01.654321Z',
            'historicalReason' => 'Explicit administrative HTTP transition reason.',
            'reasonEvidence' => 'present',
            'recordingDisposition' => 'direct_recording',
            'authorId' => 'author-001',
            'decisionActorId' => 'author-001',
        ];
    }

    private function bindDeniedIntegrator(): CapturingAdministrativeActionLifecycleHttpOrchestrator
    {
        $port = new CapturingAdministrativeActionLifecycleHttpOrchestrator;
        $integrator = new AdministrativeActionLifecycleAtomicEventOrchestrator(
            $port,
            $this->createMock(AdministrativeActionContextualReplayInspector::class),
            new ImmediateAdministrativeActionLifecycleHttpTransaction,
            new AdministrativeActionLifecycleEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $this->createMock(PublicProjectionOutboxWriter::class),
            PublicProjectionOutboxConsumerId::fromString('http-administrative-action-lifecycle-test'),
        );
        $this->app->instance(AdministrativeActionLifecycleAtomicEventOrchestrator::class, $integrator);

        return $port;
    }
}

final class CapturingAdministrativeActionLifecycleHttpOrchestrator implements AdministrativeActionLifecycleOrchestrator
{
    /** @var list<AdministrativeActionLifecycleTransitionRequest> */
    public array $requests = [];

    public function execute(
        AdministrativeActionLifecycleTransitionRequest $request,
    ): AdministrativeActionLifecycleOrchestrationResult {
        $this->requests[] = $request;

        return new AdministrativeActionLifecycleOrchestrationResult(
            AdministrativeActionLifecycleOrchestrationStatus::Denied,
            AdministrativeActionLifecycleDiagnostic::MissingReason,
        );
    }
}

final readonly class ImmediateAdministrativeActionLifecycleHttpTransaction implements AdministrativeActionLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}
