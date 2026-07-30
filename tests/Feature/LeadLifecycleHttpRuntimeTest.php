<?php

namespace Tests\Feature;

use App\Application\LeadLifecycleEventIntegration\Contract\LeadLifecycleAtomicTransaction;
use App\Application\LeadLifecycleEventIntegration\LeadLifecycleAtomicEventOrchestrator;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Http\Controllers\LeadLifecycleHttpController;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleDiagnostic;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventCatalog;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\Contract\LeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleOrchestrationStatus;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\LeadLifecycleTransitionRequest;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualReplayInspector;
use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class LeadLifecycleHttpRuntimeTest extends TestCase
{
    private const string LEAD_ID = '018f6b5c-7a91-4c3e-8d20-123456789abc';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    }

    public function test_valid_request_is_translated_exactly_and_delegated_once(): void
    {
        $port = $this->bindDeniedIntegrator();

        $this->postJson($this->endpoint(), $this->validPayload())
            ->assertStatus(422)
            ->assertExactJson(['status' => 'denied', 'diagnostic' => 'transition_forbidden']);

        self::assertCount(1, $port->requests);
        self::assertSame(self::LEAD_ID, $port->requests[0]->leadId->value);
        self::assertSame(LeadLifecycleAction::Deliver, $port->requests[0]->action);
        self::assertSame(7, $port->requests[0]->expectedVersion);
        self::assertSame('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $port->requests[0]->context->actor->value);
        self::assertSame('2026-07-22T10:11:12.123456+00:00', $port->requests[0]->context->occurredAt->value->format('Y-m-d\TH:i:s.uP'));
    }

    /** @return iterable<string, array{string}> */
    public static function actions(): iterable
    {
        yield 'deliver' => ['deliver'];
        yield 'reject' => ['reject'];
        yield 'close' => ['close'];
    }

    #[DataProvider('actions')]
    public function test_all_executable_actions_are_explicitly_accepted(string $action): void
    {
        $port = $this->bindDeniedIntegrator();
        $payload = $this->validPayload();
        $payload['action'] = $action;

        $this->postJson($this->endpoint(), $payload)->assertUnprocessable();
        self::assertSame($action, $port->requests[0]->action->value);
    }

    /** @return iterable<string, array{array<string,mixed>}> */
    public static function invalidPayloads(): iterable
    {
        yield 'unknown action' => [['action' => 'unknown']];
        yield 'unsupported action' => [['action' => 'publish']];
        yield 'invalid version' => [['expectedVersion' => 0]];
        yield 'invalid actor' => [['actorId' => 'actor']];
        yield 'non canonical occurred at' => [['occurredAt' => '2026-07-22T10:11:12Z']];
        yield 'invalid recorded at' => [['recordedAt' => 'today']];
        yield 'recorded before occurrence' => [['recordedAt' => '2026-07-22T10:11:11.123456Z']];
        yield 'missing fields' => [[]];
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_transport_never_calls_integrator(array $override): void
    {
        $port = $this->bindDeniedIntegrator();
        $payload = $override === [] ? [] : array_replace($this->validPayload(), $override);

        $this->postJson($this->endpoint(), $payload)->assertUnprocessable();
        self::assertSame([], $port->requests);
    }

    public function test_invalid_route_uuid_is_rejected_before_dispatch(): void
    {
        $port = $this->bindDeniedIntegrator();
        $this->postJson('/api/lead-lifecycles/not-a-uuid/transitions', $this->validPayload())->assertNotFound();
        self::assertSame([], $port->requests);
    }

    public function test_controller_and_integrator_resolve_from_container(): void
    {
        $this->bindDeniedIntegrator();
        self::assertInstanceOf(LeadLifecycleHttpController::class, $this->app->make(LeadLifecycleHttpController::class));
        self::assertInstanceOf(LeadLifecycleAtomicEventOrchestrator::class, $this->app->make(LeadLifecycleAtomicEventOrchestrator::class));
    }

    private function bindDeniedIntegrator(): CapturingLeadLifecycleOrchestrator
    {
        $port = new CapturingLeadLifecycleOrchestrator;
        $integrator = new LeadLifecycleAtomicEventOrchestrator(
            $port,
            $this->createMock(LeadLifecycleContextualReplayInspector::class),
            new ImmediateLeadHttpAtomicTransaction,
            new LeadLifecycleEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $this->createMock(PublicProjectionOutboxWriter::class),
            PublicProjectionOutboxConsumerId::fromString('http-lead-test'),
        );
        $this->app->instance(LeadLifecycleAtomicEventOrchestrator::class, $integrator);

        return $port;
    }

    private function endpoint(): string
    {
        return '/api/lead-lifecycles/'.self::LEAD_ID.'/transitions';
    }

    /** @return array{action:string,expectedVersion:int,actorId:string,occurredAt:string,recordedAt:string} */
    private function validPayload(): array
    {
        return ['action' => 'deliver', 'expectedVersion' => 7, 'actorId' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'occurredAt' => '2026-07-22T10:11:12.123456Z', 'recordedAt' => '2026-07-22T10:11:13.123456Z'];
    }
}

final class CapturingLeadLifecycleOrchestrator implements LeadLifecycleOrchestrator
{
    /** @var list<LeadLifecycleTransitionRequest> */
    public array $requests = [];

    public function execute(LeadLifecycleTransitionRequest $request): LeadLifecycleOrchestrationResult
    {
        $this->requests[] = $request;

        return new LeadLifecycleOrchestrationResult(LeadLifecycleOrchestrationStatus::Denied, LeadLifecycleDiagnostic::TransitionForbidden);
    }
}

final readonly class ImmediateLeadHttpAtomicTransaction implements LeadLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}
