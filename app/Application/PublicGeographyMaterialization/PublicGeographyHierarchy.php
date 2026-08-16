<?php

namespace App\Application\PublicGeographyMaterialization;

use Appart\Modules\Geography\Domain\Model\Place;

final readonly class PublicGeographyHierarchy
{
    /** @param non-empty-list<Place> $places */
    public function __construct(public array $places) {}
}
