<?php

namespace Tests\Feature;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxClaimOwnerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxQuarantineDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use App\Application\ReservationLifecycleEventIntegration\Contract\ReservationLifecycleAtomicTransaction;
use App\Application\ReservationLifecycleEventIntegration\ReservationLifecycleAtomicEventOrchestrator;
use App\Http\Controllers\ReservationLifecycleHttpController;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleDiagnostic;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\Contract\ReservationLifecycleEventOrchestrator;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationRequest;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\ReservationLifecycleOrchestrationResult;
use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

final class ReservationLifecycleHttpRuntimeTest extends TestCase
{
    private const string RESERVATION_ID = '018f6b5c-7a91-4c3e-8d20-123456789abc';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    }

    /** @return iterable<string, array{ReservationLifecycleOrchestrationResult,int,string,?string}> */
    public static function resultMappings(): iterable
    {
        $transition = new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit);

        yield 'applied' => [ReservationLifecycleOrchestrationResult::applied($transition), 200, 'applied', null];
        yield 'already applied' => [ReservationLifecycleOrchestrationResult::alreadyApplied($transition), 200, 'already_applied', null];
        yield 'missing' => [ReservationLifecycleOrchestrationResult::missing(), 404, 'missing', null];
        yield 'version conflict' => [ReservationLifecycleOrchestrationResult::versionConflict(), 409, 'version_conflict', null];
        yield 'state conflict' => [ReservationLifecycleOrchestrationResult::stateConflict(), 409, 'state_conflict', null];
        yield 'denied' => [ReservationLifecycleOrchestrationResult::denied(ReservationLifecycleDiagnostic::transitionForbidden()), 422, 'denied', 'transition_forbidden'];
        yield 'persistence corrupted' => [ReservationLifecycleOrchestrationResult::persistenceCorrupted(), 503, 'persistence_corrupted', null];
    }

    #[DataProvider('resultMappings')]
    public function test_all_seven_atomic_results_have_a_stable_http_mapping(ReservationLifecycleOrchestrationResult $result, int $http, string $status, ?string $diagnostic): void
    {
        $port = $this->bindIntegrator($result);

        $response = $this->postJson($this->endpoint(), $this->validPayload());

        $response->assertStatus($http)->assertExactJson(['status' => $status, 'diagnostic' => $diagnostic]);
        self::assertCount(1, $port->requests);
    }

    public function test_request_preserves_identity_action_version_and_explicit_instants(): void
    {
        $port = $this->bindIntegrator(ReservationLifecycleOrchestrationResult::denied(ReservationLifecycleDiagnostic::incompatibleState()));

        $this->postJson($this->endpoint(), $this->validPayload())->assertUnprocessable();

        self::assertSame(self::RESERVATION_ID, $port->requests[0]->reservationId->value);
        self::assertSame(ReservationLifecycleAction::Submit, $port->requests[0]->action);
        self::assertSame(7, $port->requests[0]->expectedVersion);
    }

    /** @return iterable<string, array{string}> */
    public static function executableActions(): iterable
    {
        foreach (ReservationLifecycleAction::cases() as $action) {
            if ($action !== ReservationLifecycleAction::Unknown) {
                yield $action->value => [$action->value];
            }
        }
    }

    #[DataProvider('executableActions')]
    public function test_all_seven_executable_actions_are_explicitly_accepted(string $action): void
    {
        $port = $this->bindIntegrator(ReservationLifecycleOrchestrationResult::denied(ReservationLifecycleDiagnostic::transitionForbidden()));
        $payload = $this->validPayload();
        $payload['action'] = $action;

        $this->postJson($this->endpoint(), $payload)->assertUnprocessable();
        self::assertSame($action, $port->requests[0]->action->value);
    }

    /** @return iterable<string, array{array<string,mixed>}> */
    public static function invalidPayloads(): iterable
    {
        yield 'unknown action' => [['action' => 'unknown', 'expectedVersion' => 7, 'occurredAt' => '2026-07-21T10:11:12.123456Z', 'recordedAt' => '2026-07-21T10:11:13.123456Z']];
        yield 'unsupported action' => [['action' => 'publish', 'expectedVersion' => 7, 'occurredAt' => '2026-07-21T10:11:12.123456Z', 'recordedAt' => '2026-07-21T10:11:13.123456Z']];
        yield 'non positive version' => [['action' => 'submit', 'expectedVersion' => 0, 'occurredAt' => '2026-07-21T10:11:12.123456Z', 'recordedAt' => '2026-07-21T10:11:13.123456Z']];
        yield 'invalid occurred instant' => [['action' => 'submit', 'expectedVersion' => 7, 'occurredAt' => '2026-07-21T10:11:12Z', 'recordedAt' => '2026-07-21T10:11:13.123456Z']];
        yield 'invalid recorded instant' => [['action' => 'submit', 'expectedVersion' => 7, 'occurredAt' => '2026-07-21T10:11:12.123456Z', 'recordedAt' => 'today']];
        yield 'recorded before occurred' => [['action' => 'submit', 'expectedVersion' => 7, 'occurredAt' => '2026-07-21T10:11:12.123456Z', 'recordedAt' => '2026-07-21T10:11:11.123456Z']];
        yield 'missing fields' => [[]];
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_transport_never_calls_the_integrator(array $payload): void
    {
        $port = $this->bindIntegrator(ReservationLifecycleOrchestrationResult::persistenceCorrupted());

        $this->postJson($this->endpoint(), $payload)->assertUnprocessable();
        self::assertSame([], $port->requests);
    }

    public function test_invalid_route_uuid_is_rejected_before_controller_dispatch(): void
    {
        $port = $this->bindIntegrator(ReservationLifecycleOrchestrationResult::persistenceCorrupted());

        $this->postJson('/api/reservation-lifecycles/not-a-uuid/transitions', $this->validPayload())->assertNotFound();
        self::assertSame([], $port->requests);
    }

    public function test_controller_and_atomic_integrator_resolve_from_the_container(): void
    {
        $this->bindIntegrator(ReservationLifecycleOrchestrationResult::missing());
        self::assertInstanceOf(ReservationLifecycleHttpController::class, $this->app->make(ReservationLifecycleHttpController::class));
        self::assertInstanceOf(ReservationLifecycleAtomicEventOrchestrator::class, $this->app->make(ReservationLifecycleAtomicEventOrchestrator::class));
    }

    private function bindIntegrator(ReservationLifecycleOrchestrationResult $result): CapturingReservationLifecycleOrchestrator
    {
        $port = new CapturingReservationLifecycleOrchestrator($result);
        $integrator = new ReservationLifecycleAtomicEventOrchestrator($port, new ImmediateReservationAtomicTransaction, new ReservationLifecycleEventCatalog, new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog), new AcceptingReservationOutboxWriter, PublicProjectionOutboxConsumerId::fromString('http-reservation-test'));
        $this->app->instance(ReservationLifecycleAtomicEventOrchestrator::class, $integrator);

        return $port;
    }

    private function endpoint(): string
    {
        return '/api/reservation-lifecycles/'.self::RESERVATION_ID.'/transitions';
    }

    /** @return array{action:string,expectedVersion:int,occurredAt:string,recordedAt:string} */
    private function validPayload(): array
    {
        return ['action' => 'submit', 'expectedVersion' => 7, 'occurredAt' => '2026-07-21T10:11:12.123456Z', 'recordedAt' => '2026-07-21T10:11:13.123456Z'];
    }
}

final class CapturingReservationLifecycleOrchestrator implements ReservationLifecycleEventOrchestrator
{
    /** @var list<ReservationLifecycleOrchestrationRequest> */
    public array $requests = [];

    public function __construct(private readonly ReservationLifecycleOrchestrationResult $result) {}

    public function transition(ReservationLifecycleOrchestrationRequest $request): ReservationLifecycleOrchestrationResult
    {
        $this->requests[] = $request;

        return $this->result;
    }
}

final readonly class ImmediateReservationAtomicTransaction implements ReservationLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}

final readonly class AcceptingReservationOutboxWriter implements PublicProjectionOutboxWriter
{
    public function append(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId): PublicProjectionOutboxWriteResult
    {
        return PublicProjectionOutboxWriteResult::Applied;
    }

    public function markDelivered(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }

    public function scheduleRetry(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxRetryDecision $decision): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }

    public function quarantine(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, ?PublicProjectionOutboxClaimOwnerId $ownerId, PublicProjectionOutboxQuarantineDecision $decision): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }

    public function releaseClaim(PublicProjectionDeliveryMessage $message, PublicProjectionOutboxConsumerId $consumerId, PublicProjectionOutboxClaimOwnerId $ownerId): PublicProjectionOutboxWriteResult
    {
        throw new RuntimeException('Not used.');
    }
}
