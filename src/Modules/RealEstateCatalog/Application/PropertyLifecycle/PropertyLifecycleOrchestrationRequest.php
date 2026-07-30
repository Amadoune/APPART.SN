<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use InvalidArgumentException;

final readonly class PropertyLifecycleOrchestrationRequest
{
    public function __construct(
        public PropertyId $propertyId,
        public PropertyLifecycleAction $action,
        public int $expectedVersion,
    ) {
        if ($expectedVersion < 1) {
            throw new InvalidArgumentException('The expected property lifecycle version must be positive.');
        }
    }
}
