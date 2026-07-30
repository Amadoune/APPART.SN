<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event;

final readonly class PropertyLifecycleEventId
{
    private function __construct(public string $value) {}

    public static function derive(PropertyLifecycleEventType $type, PropertyLifecycleEventPayloadVersion $payloadVersion, PropertyLifecycleEventPayload $payload): self
    {
        $identity = implode('|', [
            $payload->propertyId->value,
            (string) $payload->lifecycleVersion,
            $type->value,
            (string) $payloadVersion->value,
        ]);

        return new self('property-lifecycle-'.hash('sha256', $identity));
    }
}
