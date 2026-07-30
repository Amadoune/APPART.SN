<?php

namespace App\Application\MediaItemLifecycleEventIntegration;

use App\Application\MediaItemLifecycleEventIntegration\Contract\MediaItemLifecycleAtomicTransaction;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleDeliveryPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxWriteResult;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualInspectionStatus;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEvent;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventCatalog;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventId;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventMetadata;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayload;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayloadVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\Contract\MediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationResult;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleOrchestrationStatus;
use Throwable;

final readonly class MediaItemLifecycleAtomicEventOrchestrator
{
    public function __construct(
        private MediaItemLifecycleOrchestrator $orchestrator,
        private MediaItemLifecycleContextualReplayInspector $inspector,
        private MediaItemLifecycleAtomicTransaction $transaction,
        private MediaItemLifecycleEventCatalog $events,
        private PublicProjectionDeliveryCatalogMessageFactory $messages,
        private PublicProjectionOutboxWriter $outbox,
        private PublicProjectionOutboxConsumerId $consumerId,
    ) {}

    public function transition(MediaItemLifecycleAtomicEventRequest $request): MediaItemLifecycleOrchestrationResult
    {
        try {
            return $this->transaction->run(function () use ($request): MediaItemLifecycleOrchestrationResult {
                $result = $this->orchestrator->execute($request->transitionRequest());
                if ($result->status === MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted) {
                    throw new MediaItemLifecycleEventIntegrationFailure('Media item lifecycle persistence failed.');
                }
                if (! in_array($result->status, [MediaItemLifecycleOrchestrationStatus::Applied, MediaItemLifecycleOrchestrationStatus::AlreadyApplied], true)) {
                    return $result;
                }

                $inspection = $this->inspector->inspectLatest($request->mediaId);
                if ($inspection->status !== MediaItemLifecycleContextualInspectionStatus::Found || $inspection->snapshot === null) {
                    throw new MediaItemLifecycleEventIntegrationFailure('Persisted media item lifecycle transition cannot be inspected.');
                }

                $transition = $inspection->snapshot->transition;
                $occurredVersion = $inspection->snapshot->version;
                $type = $this->events->typeFor($transition);
                $payloadVersion = MediaItemLifecycleEventPayloadVersion::V1;
                $eventId = MediaItemLifecycleEventId::derive($type, $payloadVersion, $request->mediaId, $transition, $occurredVersion);
                $event = new MediaItemLifecycleEvent(
                    new MediaItemLifecycleEventMetadata($type, $payloadVersion, $request->context->actor, $request->context->occurredAt, $request->recordedAt),
                    new MediaItemLifecycleEventPayload($eventId, $request->mediaId, implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]), $transition->from, $transition->to, $transition->action, $payloadVersion->value, $occurredVersion),
                );
                $fact = new PublicProjectionDeliveryPublishableFact(
                    PublicProjectionDeliveryEventType::fromString($type->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt($payloadVersion->value),
                    PublicProjectionDeliverySourceModule::fromString('Media'),
                    PublicProjectionDeliveryAggregateType::fromString('MediaItemLifecycle'),
                    PublicProjectionDeliveryAggregateId::fromString($request->mediaId->value),
                    new PublicProjectionDeliveryOrder($occurredVersion, PublicProjectionDeliveryEventIndex::fromInt(1)),
                    $request->context->occurredAt->value,
                    new MediaItemLifecycleDeliveryPayload($event),
                );
                $written = $this->outbox->append($this->messages->create($fact, $request->recordedAt->value), $this->consumerId);
                if (! in_array($written, [PublicProjectionOutboxWriteResult::Applied, PublicProjectionOutboxWriteResult::AlreadyApplied], true)) {
                    throw new MediaItemLifecycleEventIntegrationFailure('Media item lifecycle event Outbox write was rejected.');
                }

                return $result;
            });
        } catch (Throwable) {
            return new MediaItemLifecycleOrchestrationResult(MediaItemLifecycleOrchestrationStatus::PersistenceCorrupted);
        }
    }
}
