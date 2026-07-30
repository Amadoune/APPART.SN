<?php

namespace Appart\Modules\Geography\Domain\Model;

use Appart\Modules\Geography\Domain\ValueObject\PlaceName;
use DateTimeImmutable;

final readonly class GeographicAlias
{
    public function __construct(
        public PlaceName $name,
        public DateTimeImmutable $recordedAt,
    ) {}

    public function hasSameName(PlaceName $name): bool
    {
        return $this->name->equals($name);
    }
}
