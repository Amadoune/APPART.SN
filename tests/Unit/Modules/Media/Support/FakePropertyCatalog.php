<?php

namespace Tests\Unit\Modules\Media\Support;

use Appart\Modules\Media\Application\Contract\PropertyCatalog;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;

final readonly class FakePropertyCatalog implements PropertyCatalog
{
    /** @param list<string> $propertyIds */
    public function __construct(private array $propertyIds) {}

    public function exists(PropertyId $propertyId): bool
    {
        return in_array($propertyId->value, $this->propertyIds, true);
    }
}
