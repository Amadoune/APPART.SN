<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event;

use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;

final readonly class PropertyLifecycleEventCatalog
{
    /** @var array<string, PropertyLifecycleEventType> */
    private const array TRANSITION_EVENTS = [
        'draft>activate>active' => PropertyLifecycleEventType::PropertyActivated,
        'draft>archive>archived' => PropertyLifecycleEventType::PropertyArchived,
        'active>begin_maintenance>under_maintenance' => PropertyLifecycleEventType::PropertyMaintenanceStarted,
        'active>mark_unavailable>unavailable' => PropertyLifecycleEventType::PropertyMarkedUnavailable,
        'active>decommission>decommissioned' => PropertyLifecycleEventType::PropertyDecommissioned,
        'under_maintenance>complete_maintenance>active' => PropertyLifecycleEventType::PropertyMaintenanceCompleted,
        'under_maintenance>mark_unavailable>unavailable' => PropertyLifecycleEventType::PropertyMarkedUnavailable,
        'under_maintenance>decommission>decommissioned' => PropertyLifecycleEventType::PropertyDecommissioned,
        'unavailable>restore_availability>active' => PropertyLifecycleEventType::PropertyAvailabilityRestored,
        'unavailable>begin_maintenance>under_maintenance' => PropertyLifecycleEventType::PropertyMaintenanceStarted,
        'unavailable>decommission>decommissioned' => PropertyLifecycleEventType::PropertyDecommissioned,
        'decommissioned>archive>archived' => PropertyLifecycleEventType::PropertyArchived,
    ];

    /** @return list<PropertyLifecycleEvent> */
    public function eventsFor(PropertyId $propertyId, PropertyLifecycleTransition $transition, int $lifecycleVersion, PropertyLifecycleEventMetadata $metadata): array
    {
        $key = implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]);
        $type = self::TRANSITION_EVENTS[$key] ?? throw new UnsupportedPropertyLifecycleEventTransition('The transition has no certified property lifecycle event mapping.');
        $payload = new PropertyLifecycleEventPayload($propertyId, $transition->from, $transition->to, $transition->action, $lifecycleVersion);
        $payloadVersion = PropertyLifecycleEventPayloadVersion::V1;

        return [new PropertyLifecycleEvent(
            PropertyLifecycleEventId::derive($type, $payloadVersion, $payload),
            $type,
            $payloadVersion,
            $payload,
            $metadata,
        )];
    }

    public function transitionCount(): int
    {
        return count(self::TRANSITION_EVENTS);
    }
}
