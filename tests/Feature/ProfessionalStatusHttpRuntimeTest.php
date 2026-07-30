<?php

namespace Tests\Feature;

use App\Application\ProfessionalStatusEventIntegration\Contract\ProfessionalStatusAtomicTransaction;
use App\Application\ProfessionalStatusEventIntegration\ProfessionalStatusAtomicEventOrchestrator;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Http\Controllers\ProfessionalStatusHttpController;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventCatalog;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusDiagnostic;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\Contract\ProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusOrchestrationStatus;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\ProfessionalStatusTransitionRequest;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualReplayInspector;
use Closure;
use Illuminate\Routing\Route;
use Tests\TestCase;

final class ProfessionalStatusHttpRuntimeTest extends TestCase
{
    private const ENDPOINT = '/api/professional-statuses/018f6d33-3d67-7bb2-8c16-93c933c7631a/transitions';

    private CapturingProfessionalStatusHttpOrchestrator $port;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        $this->port = $this->bindDeniedIntegrator();
    }

    public function test_the_unique_runtime_route_is_composed_and_uuid_bounded(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(static fn (Route $route): bool => $route->uri() === 'api/professional-statuses/{professionalId}/transitions');

        self::assertCount(1, $routes);
        $route = $routes->first();
        self::assertInstanceOf(Route::class, $route);
        self::assertSame(['POST'], $route->methods());
        self::assertSame(ProfessionalStatusHttpController::class, $route->getActionName());
        self::assertArrayHasKey('professionalId', $route->wheres);
    }

    public function test_it_rejects_unknown_actions_and_invalid_transport_values_before_orchestration(): void
    {
        $valid = [
            'action' => 'suspend',
            'expectedVersion' => 1,
            'actorId' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'occurredAt' => '2026-07-23T10:00:00.000000Z',
            'recordedAt' => '2026-07-23T10:00:01.000000Z',
        ];

        $this->postJson(self::ENDPOINT, [...$valid, 'action' => 'unknown'])->assertUnprocessable()->assertJsonValidationErrors('action');
        $this->postJson(self::ENDPOINT, [...$valid, 'action' => 'deliver'])->assertUnprocessable()->assertJsonValidationErrors('action');
        $this->postJson(self::ENDPOINT, [...$valid, 'expectedVersion' => 0])->assertUnprocessable()->assertJsonValidationErrors('expectedVersion');
        $this->postJson(self::ENDPOINT, [...$valid, 'actorId' => 'actor'])->assertUnprocessable()->assertJsonValidationErrors('actorId');
        $this->postJson(self::ENDPOINT, [...$valid, 'occurredAt' => '2026-07-23T10:00:00Z'])->assertUnprocessable()->assertJsonValidationErrors('occurredAt');
        $this->postJson(self::ENDPOINT, [...$valid, 'recordedAt' => '2026-07-23T09:59:59.000000Z'])->assertUnprocessable()->assertJsonValidationErrors('recordedAt');
        self::assertSame([], $this->port->requests);
    }

    public function test_a_valid_request_is_translated_exactly_and_delegated_once(): void
    {
        $this->postJson(self::ENDPOINT, [
            'action' => 'suspend',
            'expectedVersion' => 7,
            'actorId' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'occurredAt' => '2026-07-23T10:00:00.123456Z',
            'recordedAt' => '2026-07-23T10:00:01.654321Z',
        ])->assertUnprocessable()->assertExactJson([
            'status' => 'denied',
            'diagnostic' => 'incompatible_state',
        ]);

        self::assertCount(1, $this->port->requests);
        $request = $this->port->requests[0];
        self::assertSame('018f6d33-3d67-7bb2-8c16-93c933c7631a', $request->professionalId->value);
        self::assertSame('suspend', $request->action->value);
        self::assertSame(7, $request->context->expectedVersion->value);
        self::assertSame('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $request->context->actor->value);
        self::assertSame('2026-07-23T10:00:00.123456Z', $request->context->occurredAt->canonical());
    }

    public function test_it_rejects_an_invalid_professional_identifier_at_the_route_boundary(): void
    {
        $this->postJson('/api/professional-statuses/not-a-uuid/transitions', [])->assertNotFound();
    }

    private function bindDeniedIntegrator(): CapturingProfessionalStatusHttpOrchestrator
    {
        $port = new CapturingProfessionalStatusHttpOrchestrator;
        $integrator = new ProfessionalStatusAtomicEventOrchestrator(
            $port,
            $this->createMock(ProfessionalStatusContextualReplayInspector::class),
            new ImmediateProfessionalStatusHttpTransaction,
            new ProfessionalStatusEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $this->createMock(PublicProjectionOutboxWriter::class),
            PublicProjectionOutboxConsumerId::fromString('http-professional-status-test'),
        );
        $this->app->instance(ProfessionalStatusAtomicEventOrchestrator::class, $integrator);

        return $port;
    }
}

final class CapturingProfessionalStatusHttpOrchestrator implements ProfessionalStatusOrchestrator
{
    /** @var list<ProfessionalStatusTransitionRequest> */
    public array $requests = [];

    public function execute(ProfessionalStatusTransitionRequest $request): ProfessionalStatusOrchestrationResult
    {
        $this->requests[] = $request;

        return new ProfessionalStatusOrchestrationResult(
            ProfessionalStatusOrchestrationStatus::Denied,
            ProfessionalStatusDiagnostic::IncompatibleState,
        );
    }
}

final readonly class ImmediateProfessionalStatusHttpTransaction implements ProfessionalStatusAtomicTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}
