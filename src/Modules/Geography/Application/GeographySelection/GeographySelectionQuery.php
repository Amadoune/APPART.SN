<?php

namespace Appart\Modules\Geography\Application\GeographySelection;

use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use InvalidArgumentException;

final readonly class GeographySelectionQuery
{
    public PlaceType $type;

    public ?PlaceId $parentPlaceId;

    public function __construct(PlaceType $type, ?PlaceId $parentPlaceId, public ?string $cursor, public int $limit)
    {
        if ($limit < 1 || $limit > 100) {
            throw new InvalidArgumentException('Geography selection limit must be between 1 and 100.');
        }
        if (($type === PlaceType::Country) !== ($parentPlaceId === null)) {
            throw new InvalidArgumentException('Geography selection parent does not match the requested type.');
        }
        $this->type = $type;
        $this->parentPlaceId = $parentPlaceId;
    }
}
