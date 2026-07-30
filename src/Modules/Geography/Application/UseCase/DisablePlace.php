<?php

namespace Appart\Modules\Geography\Application\UseCase;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\Exception\PlaceNotFound;
use Appart\Modules\Geography\Domain\Model\Place;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use DateTimeImmutable;

final readonly class DisablePlace
{
    public function __construct(private PlaceRegistry $registry) {}

    public function execute(PlaceId $id, DateTimeImmutable $occurredAt): Place
    {
        $place = $this->registry->find($id) ?? throw PlaceNotFound::forId($id);
        $expectedVersion = $place->version();
        $place->disable($occurredAt);
        $this->registry->save($place, $expectedVersion);

        return $place;
    }
}
