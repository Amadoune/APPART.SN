<?php

namespace Appart\Modules\RealEstateCatalog\Domain\Event;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\SurfaceArea;
use DateTimeImmutable;

final readonly class SurfaceChanged extends AbstractPropertyEvent
{
    public function __construct(PropertyId $id, public ?SurfaceArea $previousSurface, public ?SurfaceArea $newSurface, DateTimeImmutable $at)
    {
        parent::__construct($id, $at);
    }
}
