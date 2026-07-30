<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Event;

use Appart\Modules\RealEstateCatalog\Domain\Model\Address;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use DateTimeImmutable;

final readonly class AddressChanged extends AbstractPropertyEvent
{
    public function __construct(PropertyId $id, public ?Address $previousAddress, public Address $newAddress, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
