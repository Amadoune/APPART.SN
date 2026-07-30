<?php

namespace App\Application\PropertyLifecycleEventIntegration;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventInstant;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventMetadata;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleOrchestrationRequest;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use InvalidArgumentException;

final readonly class PropertyLifecycleEventOrchestrationRequest
{
    public function __construct(
        public PropertyId $propertyId,
        public PropertyLifecycleAction $action,
        public int $expectedVersion,
        public PropertyLifecycleEventInstant $occurredAt,
        public PropertyLifecycleEventInstant $recordedAt,
    ) {
        if ($expectedVersion < 1) {
            throw new InvalidArgumentException('The expected property lifecycle version must be positive.');
        }
    }

    public function transitionRequest(): PropertyLifecycleOrchestrationRequest
    {
        return new PropertyLifecycleOrchestrationRequest($this->propertyId, $this->action, $this->expectedVersion);
    }

    public function metadata(): PropertyLifecycleEventMetadata
    {
        return new PropertyLifecycleEventMetadata($this->occurredAt, $this->recordedAt);
    }
}
