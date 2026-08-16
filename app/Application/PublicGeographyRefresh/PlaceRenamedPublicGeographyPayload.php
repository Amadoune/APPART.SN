<?php

namespace App\Application\PublicGeographyRefresh;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\Geography\Domain\Event\PlaceRenamed;

final readonly class PlaceRenamedPublicGeographyPayload implements PublicProjectionDeliveryPayload
{
    public function __construct(public string $eventId, public string $placeId, public int $aggregateVersion, public string $occurredAt) {}

    public static function fromEvent(PlaceRenamed $event): self
    {
        $occurredAt = $event->occurredAt()->format('Y-m-d\TH:i:s.uP');
        $eventId = hash('sha256', 'place-renamed-v1|'.$event->placeId()->value.'|'.$event->aggregateVersion().'|'.$occurredAt);

        return new self($eventId, $event->placeId()->value, $event->aggregateVersion(), $occurredAt);
    }

    /** @return array{eventId:string,placeId:string,aggregateVersion:int,occurredAt:string} */
    public function fields(): array
    {
        return ['eventId' => $this->eventId, 'placeId' => $this->placeId, 'aggregateVersion' => $this->aggregateVersion, 'occurredAt' => $this->occurredAt];
    }

    public function checksum(): string
    {
        return hash('sha256', json_encode($this->fields(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
