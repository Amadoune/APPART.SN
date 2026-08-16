<?php

namespace App\Application\PublicGeographyMaterialization;

use Appart\Modules\Geography\Application\Contract\PlaceRegistry;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use RuntimeException;

final readonly class PublicGeographyHierarchyReader
{
    public function __construct(private PlaceRegistry $places) {}

    public function read(string $terminalPlaceId): PublicGeographyHierarchy
    {
        $chain = [];
        $seen = [];
        $current = PlaceId::fromString($terminalPlaceId);

        while (true) {
            if (isset($seen[$current->value])) {
                throw new RuntimeException('Public Geography hierarchy contains a cycle.');
            }
            $seen[$current->value] = true;
            $place = $this->places->find($current) ?? throw new RuntimeException('Public Geography hierarchy source is missing.');
            $chain[] = $place;
            $parent = $place->parent();
            if ($parent === null) {
                if ($place->type()->value !== 'country') {
                    throw new RuntimeException('Public Geography hierarchy root is invalid.');
                }
                break;
            }
            $current = $parent->placeId;
        }

        return new PublicGeographyHierarchy(array_reverse($chain));
    }
}
