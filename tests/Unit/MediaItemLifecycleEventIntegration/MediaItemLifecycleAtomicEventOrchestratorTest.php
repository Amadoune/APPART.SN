<?php

namespace Tests\Unit\MediaItemLifecycleEventIntegration;

use App\Application\MediaItemLifecycleEventIntegration\Contract\MediaItemLifecycleAtomicTransaction;
use App\Application\MediaItemLifecycleEventIntegration\MediaItemLifecycleAtomicEventOrchestrator;
use App\Application\MediaItemLifecycleEventIntegration\MediaItemLifecycleAtomicEventRequest;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionDecisionVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionTransitionDecision;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleExpectedVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventCatalog;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\Contract\MediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationResult;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationStatus;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleAtomicEventOrchestratorTest extends TestCase
{
    /** @return iterable<string,array{MediaItemLifecycleOrchestrationStatus}> */
    public static function terminalResults(): iterable
    {
        foreach ([MediaItemLifecycleOrchestrationStatus::Missing, MediaItemLifecycleOrchestrationStatus::VersionConflict, MediaItemLifecycleOrchestrationStatus::Denied, MediaItemLifecycleOrchestrationStatus::StateConflict, MediaItemLifecycleOrchestrationStatus::ContextDivergence] as $status) {
            yield $status->value => [$status];
        }
    }

    #[DataProvider('terminalResults')]
    public function test_non_persisted_results_never_inspect_or_write(MediaItemLifecycleOrchestrationStatus $status): void
    {
        $orchestrator = $this->createMock(MediaItemLifecycleOrchestrator::class);
        $orchestrator->expects(self::once())->method('execute')->willReturn(new MediaItemLifecycleOrchestrationResult($status));
        $inspector = $this->createMock(MediaItemLifecycleContextualReplayInspector::class);
        $inspector->expects(self::never())->method('inspectLatest');
        $outbox = $this->createMock(PublicProjectionOutboxWriter::class);
        $outbox->expects(self::never())->method('append');

        $result = (new MediaItemLifecycleAtomicEventOrchestrator($orchestrator, $inspector, new ImmediateMediaItemLifecycleAtomicTransaction, new MediaItemLifecycleEventCatalog, new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog), $outbox, PublicProjectionOutboxConsumerId::fromString('unit-media-integration')))->transition($this->request());

        self::assertSame($status, $result->status);
    }

    public function test_persistence_corruption_is_closed(): void
    {
        $orchestrator = $this->createMock(MediaItemLifecycleOrchestrator::class);
        $orchestrator->method('execute')->willReturn(new MediaItemLifecycleOrchestrationResult(MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted));

        $result = (new MediaItemLifecycleAtomicEventOrchestrator($orchestrator, $this->createMock(MediaItemLifecycleContextualReplayInspector::class), new ImmediateMediaItemLifecycleAtomicTransaction, new MediaItemLifecycleEventCatalog, new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog), $this->createMock(PublicProjectionOutboxWriter::class), PublicProjectionOutboxConsumerId::fromString('unit-media-integration')))->transition($this->request());

        self::assertSame(MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted, $result->status);
    }

    private function request(): MediaItemLifecycleAtomicEventRequest
    {
        $id = MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000499');
        $occurred = MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T10:00:00Z'));
        $context = new MediaItemLifecycleTransitionContext(
            MediaItemLifecycleContextVersion::V1,
            MediaCollectionId::fromString('a4601000-0000-4000-8000-000000000001'),
            MediaId::fromString($id->value),
            new MediaItemLifecycleExpectedVersion(1),
            new MediaCollectionDecisionVersion(7),
            MediaItemLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
            $occurred,
            MediaCollectionTransitionDecision::notPrimary(),
        );

        return new MediaItemLifecycleAtomicEventRequest($id, MediaItemLifecycleAction::Remove, $context, MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T10:00:01Z')));
    }
}

final class ImmediateMediaItemLifecycleAtomicTransaction implements MediaItemLifecycleAtomicTransaction
{
    public function run(Closure $operation): mixed
    {
        return $operation();
    }
}
