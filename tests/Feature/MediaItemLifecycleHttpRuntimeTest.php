<?php

namespace Tests\Feature;

use App\Application\MediaItemLifecycleEventIntegration\Contract\MediaItemLifecycleAtomicTransaction;
use App\Application\MediaItemLifecycleEventIntegration\MediaItemLifecycleAtomicEventOrchestrator;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Http\Controllers\MediaItemLifecycleHttpController;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleDiagnostic;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventCatalog;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\Contract\MediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationResult;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationStatus;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleTransitionRequest;
use Closure;
use Illuminate\Routing\Route;
use Tests\TestCase;

final class MediaItemLifecycleHttpRuntimeTest extends TestCase
{
    private const MEDIA_ID = '018f6d33-3d67-7bb2-8c16-93c933c7631a';

    private const ENDPOINT = '/api/media-item-lifecycles/'.self::MEDIA_ID.'/transitions';

    private CapturingMediaItemLifecycleHttpOrchestrator $port;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        $this->port = $this->bindDeniedIntegrator();
    }

    public function test_the_unique_runtime_route_is_composed_and_uuid_bounded(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(static fn (Route $route): bool => $route->uri() === 'api/media-item-lifecycles/{mediaId}/transitions');

        self::assertCount(1, $routes);
        $route = $routes->first();
        self::assertInstanceOf(Route::class, $route);
        self::assertSame(['POST'], $route->methods());
        self::assertSame(MediaItemLifecycleHttpController::class, $route->getActionName());
        self::assertArrayHasKey('mediaId', $route->wheres);
    }

    public function test_invalid_transport_and_collection_decisions_are_rejected_before_orchestration(): void
    {
        $valid = $this->validPayload();

        $this->postJson(self::ENDPOINT, [...$valid, 'action' => 'unknown'])->assertUnprocessable()->assertJsonValidationErrors('action');
        $this->postJson(self::ENDPOINT, [...$valid, 'contextVersion' => 2])->assertUnprocessable()->assertJsonValidationErrors('contextVersion');
        $this->postJson(self::ENDPOINT, [...$valid, 'collectionId' => 'collection'])->assertUnprocessable()->assertJsonValidationErrors('collectionId');
        $this->postJson(self::ENDPOINT, [...$valid, 'expectedVersion' => 0])->assertUnprocessable()->assertJsonValidationErrors('expectedVersion');
        $this->postJson(self::ENDPOINT, [...$valid, 'collectionVersion' => 0])->assertUnprocessable()->assertJsonValidationErrors('collectionVersion');
        $this->postJson(self::ENDPOINT, [...$valid, 'actorId' => 'actor'])->assertUnprocessable()->assertJsonValidationErrors('actorId');
        $this->postJson(self::ENDPOINT, [...$valid, 'occurredAt' => '2026-07-24T10:00:00Z'])->assertUnprocessable()->assertJsonValidationErrors('occurredAt');
        $this->postJson(self::ENDPOINT, [...$valid, 'recordedAt' => '2026-07-24T09:59:59.000000Z'])->assertUnprocessable()->assertJsonValidationErrors('recordedAt');
        $this->postJson(self::ENDPOINT, [...$valid, 'replacementMediaId' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'])->assertUnprocessable()->assertJsonValidationErrors('replacementMediaId');
        $this->postJson(self::ENDPOINT, [...$valid, 'collectionDecision' => 'replacement_selected'])->assertUnprocessable()->assertJsonValidationErrors('replacementMediaId');
        $this->postJson(self::ENDPOINT, [...$valid, 'collectionDecision' => 'replacement_selected', 'replacementMediaId' => self::MEDIA_ID])->assertUnprocessable()->assertJsonValidationErrors('replacementMediaId');
        self::assertSame([], $this->port->requests);
    }

    public function test_a_not_primary_request_is_translated_exactly_and_delegated_once(): void
    {
        $this->postJson(self::ENDPOINT, $this->validPayload())->assertUnprocessable()->assertExactJson([
            'status' => 'denied',
            'diagnostic' => 'terminal_state',
        ]);

        self::assertCount(1, $this->port->requests);
        $request = $this->port->requests[0];
        self::assertSame(self::MEDIA_ID, $request->mediaId->value);
        self::assertSame('remove', $request->action->value);
        self::assertSame(1, $request->context->contractVersion->value);
        self::assertSame('018f6d33-3d67-7bb2-8c16-93c933c7632b', $request->context->collectionId->value);
        self::assertSame(7, $request->context->expectedVersion->value);
        self::assertSame(11, $request->context->collectionVersion->value);
        self::assertSame('not_primary', $request->context->collectionDecision->disposition->value);
        self::assertNull($request->context->collectionDecision->replacementMediaId);
        self::assertSame('2026-07-24T10:00:00.123456Z', $request->context->occurredAt->canonical());
    }

    public function test_a_replacement_decision_is_transported_without_recalculation(): void
    {
        $replacement = '018f6d33-3d67-7bb2-8c16-93c933c7633c';
        $payload = [...$this->validPayload(), 'action' => 'archive', 'collectionDecision' => 'replacement_selected', 'replacementMediaId' => $replacement];

        $this->postJson(self::ENDPOINT, $payload)->assertUnprocessable();

        self::assertCount(1, $this->port->requests);
        self::assertSame('replacement_selected', $this->port->requests[0]->context->collectionDecision->disposition->value);
        self::assertSame($replacement, $this->port->requests[0]->context->collectionDecision->replacementMediaId?->value);
    }

    public function test_an_invalid_media_identifier_is_rejected_at_the_route_boundary(): void
    {
        $this->postJson('/api/media-item-lifecycles/not-a-uuid/transitions', [])->assertNotFound();
    }

    /** @return array<string, int|string> */
    private function validPayload(): array
    {
        return [
            'action' => 'remove',
            'contextVersion' => 1,
            'collectionId' => '018f6d33-3d67-7bb2-8c16-93c933c7632b',
            'expectedVersion' => 7,
            'collectionVersion' => 11,
            'actorId' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'occurredAt' => '2026-07-24T10:00:00.123456Z',
            'recordedAt' => '2026-07-24T10:00:01.654321Z',
            'collectionDecision' => 'not_primary',
        ];
    }

    private function bindDeniedIntegrator(): CapturingMediaItemLifecycleHttpOrchestrator
    {
        $port = new CapturingMediaItemLifecycleHttpOrchestrator;
        $integrator = new MediaItemLifecycleAtomicEventOrchestrator(
            $port,
            $this->createMock(MediaItemLifecycleContextualReplayInspector::class),
            new ImmediateMediaItemLifecycleHttpTransaction,
            new MediaItemLifecycleEventCatalog,
            new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog),
            $this->createMock(PublicProjectionOutboxWriter::class),
            PublicProjectionOutboxConsumerId::fromString('http-media-item-lifecycle-test'),
        );
        $this->app->instance(MediaItemLifecycleAtomicEventOrchestrator::class, $integrator);

        return $port;
    }
}

final class CapturingMediaItemLifecycleHttpOrchestrator implements MediaItemLifecycleOrchestrator
{
    /** @var list<MediaItemLifecycleTransitionRequest> */
    public array $requests = [];

    public function execute(MediaItemLifecycleTransitionRequest $request): MediaItemLifecycleOrchestrationResult
    {
        $this->requests[] = $request;

        return new MediaItemLifecycleOrchestrationResult(MediaItemLifecycleOrchestrationStatus::Denied, MediaItemLifecycleDiagnostic::TerminalState);
    }
}

final readonly class ImmediateMediaItemLifecycleHttpTransaction implements MediaItemLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}
