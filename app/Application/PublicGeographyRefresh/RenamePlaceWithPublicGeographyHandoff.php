<?php

namespace App\Application\PublicGeographyRefresh;

use App\Application\PlaceLifecycleEventIntegration\Contract\PlaceLifecycleAtomicTransaction;
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
use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\Event\PlaceRenamed;
use Appart\Modules\Geography\Domain\Exception\PlaceNotFound;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use DateTimeImmutable;
use RuntimeException;

final readonly class RenamePlaceWithPublicGeographyHandoff
{
    public function __construct(private PlaceRegistry $places, private PlaceLifecycleAtomicTransaction $transaction, private PublicProjectionDeliveryCatalogMessageFactory $messages, private PublicProjectionOutboxWriter $outbox, private PublicProjectionOutboxConsumerId $consumerId) {}

    public function rename(PlaceId $placeId, PlaceName $name, DateTimeImmutable $occurredAt): Place
    {
        return $this->transaction->run(function () use ($placeId, $name, $occurredAt): Place {
            $place = $this->places->find($placeId) ?? throw PlaceNotFound::forId($placeId);
            $expectedVersion = $place->version();
            $place->rename($name, $occurredAt);
            $events = $place->releaseEvents();
            $event = $events[array_key_last($events)] ?? null;
            if (! $event instanceof PlaceRenamed) {
                throw new RuntimeException('Rename did not produce its certified event.');
            }
            $this->places->save($place, $expectedVersion);
            $payload = PlaceRenamedPublicGeographyPayload::fromEvent($event);
            $fact = new PublicProjectionDeliveryPublishableFact(
                PublicProjectionDeliveryEventType::fromString('place.lifecycle.renamed'),
                PublicProjectionDeliveryPayloadVersion::fromInt(1),
                PublicProjectionDeliverySourceModule::fromString('Geography'),
                PublicProjectionDeliveryAggregateType::fromString('PlaceLifecycle'),
                PublicProjectionDeliveryAggregateId::fromString($placeId->value),
                new PublicProjectionDeliveryOrder($event->aggregateVersion(), PublicProjectionDeliveryEventIndex::fromInt(1)),
                $occurredAt,
                $payload,
            );
            $written = $this->outbox->append($this->messages->create($fact, $occurredAt), $this->consumerId);
            if (! in_array($written, [PublicProjectionOutboxWriteResult::Applied, PublicProjectionOutboxWriteResult::AlreadyApplied], true)) {
                throw new RuntimeException('PlaceRenamed outbox handoff was rejected.');
            }

            return $place;
        });
    }
}
