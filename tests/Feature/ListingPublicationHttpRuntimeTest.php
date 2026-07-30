<?php

namespace Tests\Feature;

use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventIntegration\ListingPublicationEventOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationDiagnostic;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationDiagnosticCode;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationResult;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ListingPublicationHttpRuntimeTest extends TestCase
{
    private const LISTING_ID = '018f6b5c-7a91-4c3e-8d20-123456789abc';

    /** @return iterable<string, array{ListingPublicationOrchestrationResult,int,string,?string}> */
    public static function resultMappings(): iterable
    {
        $transition = new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Submitted, ListingPublicationAction::Submit);

        yield 'applied' => [ListingPublicationOrchestrationResult::applied($transition), 200, 'applied', null];
        yield 'already applied' => [ListingPublicationOrchestrationResult::alreadyApplied($transition), 200, 'already_applied', null];
        yield 'denied' => [ListingPublicationOrchestrationResult::denied(ListingPublicationDiagnostic::transitionForbidden()), 422, 'denied', 'transition_forbidden'];
        yield 'conflict' => [ListingPublicationOrchestrationResult::concurrencyConflict(ListingPublicationOrchestrationDiagnosticCode::VersionConflict), 409, 'concurrency_conflict', 'version_conflict'];
        yield 'failure' => [ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::InfrastructureFailure), 503, 'persistence_failure', 'infrastructure_failure'];
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    }

    #[DataProvider('resultMappings')]
    public function test_every_application_result_has_a_stable_http_mapping(ListingPublicationOrchestrationResult $result, int $httpStatus, string $status, ?string $diagnostic): void
    {
        $stub = new CapturingListingPublicationEventOrchestrator($result);
        $this->app->instance(ListingPublicationEventOrchestrator::class, $stub);

        $response = $this->postJson('/api/listing-publications/'.self::LISTING_ID.'/transitions', $this->validPayload());

        $response->assertStatus($httpStatus)->assertExactJson(['status' => $status, 'diagnostic' => $diagnostic]);
        self::assertCount(1, $stub->requests);
    }

    public function test_request_is_translated_without_transforming_identity_action_version_or_metadata(): void
    {
        $result = ListingPublicationOrchestrationResult::denied(ListingPublicationDiagnostic::incompatibleState());
        $stub = new CapturingListingPublicationEventOrchestrator($result);
        $this->app->instance(ListingPublicationEventOrchestrator::class, $stub);

        $this->postJson('/api/listing-publications/'.self::LISTING_ID.'/transitions', $this->validPayload())->assertUnprocessable();

        $request = $stub->requests[0];
        self::assertSame(self::LISTING_ID, $request->transition->listingId->value);
        self::assertSame(ListingPublicationAction::Submit, $request->transition->action);
        self::assertSame(7, $request->transition->expectedVersion);
        self::assertSame('2026-07-20T10:11:12.123456Z', $request->metadata->occurredAt->value);
        self::assertSame('2026-07-20T10:11:13.123456Z', $request->metadata->recordedAt->value);
    }

    /** @return iterable<string, array{string}> */
    public static function certifiedActions(): iterable
    {
        foreach (ListingPublicationAction::cases() as $action) {
            if ($action !== ListingPublicationAction::Unknown) {
                yield $action->value => [$action->value];
            }
        }
    }

    #[DataProvider('certifiedActions')]
    public function test_every_certified_action_is_accepted_without_implicit_action(string $action): void
    {
        $stub = new CapturingListingPublicationEventOrchestrator(ListingPublicationOrchestrationResult::denied(ListingPublicationDiagnostic::transitionForbidden()));
        $this->app->instance(ListingPublicationEventOrchestrator::class, $stub);
        $payload = $this->validPayload();
        $payload['action'] = $action;

        $this->postJson('/api/listing-publications/'.self::LISTING_ID.'/transitions', $payload)->assertUnprocessable();

        self::assertSame($action, $stub->requests[0]->transition->action->value);
    }

    public function test_invalid_or_missing_transport_fields_never_call_the_orchestrator(): void
    {
        $stub = new CapturingListingPublicationEventOrchestrator(ListingPublicationOrchestrationResult::persistenceFailure(ListingPublicationOrchestrationDiagnosticCode::InfrastructureFailure));
        $this->app->instance(ListingPublicationEventOrchestrator::class, $stub);

        $response = $this->postJson('/api/listing-publications/'.self::LISTING_ID.'/transitions', [
            'action' => 'unknown',
            'expectedVersion' => 0,
            'occurredAt' => 'today',
        ]);
        $response->assertUnprocessable();
        self::assertSame([], $stub->requests);
    }

    /** @return array{action:string,expectedVersion:int,occurredAt:string,recordedAt:string} */
    private function validPayload(): array
    {
        return [
            'action' => 'submit',
            'expectedVersion' => 7,
            'occurredAt' => '2026-07-20T10:11:12.123456Z',
            'recordedAt' => '2026-07-20T10:11:13.123456Z',
        ];
    }
}

final class CapturingListingPublicationEventOrchestrator implements ListingPublicationEventOrchestrator
{
    /** @var list<ListingPublicationEventOrchestrationRequest> */
    public array $requests = [];

    public function __construct(private readonly ListingPublicationOrchestrationResult $result) {}

    public function transition(ListingPublicationEventOrchestrationRequest $request): ListingPublicationOrchestrationResult
    {
        $this->requests[] = $request;

        return $this->result;
    }
}
