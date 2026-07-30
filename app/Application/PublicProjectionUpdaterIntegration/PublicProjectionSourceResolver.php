<?php

namespace App\Application\PublicProjectionUpdaterIntegration;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCompatibility;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionSourceLookup;

final readonly class PublicProjectionSourceResolver
{
    public function __construct(private PublicProjectionDeliveryEventCatalog $catalog, private PublicProjectionSourceLookup $lookup) {}

    public function resolve(PublicProjectionDeliveryMessage $message): PublicProjectionSourceResolution
    {
        $compatibility = $this->catalog->compatibility($message->eventType, $message->payloadVersion);
        if (! in_array($compatibility, [PublicProjectionDeliveryCompatibility::Supported, PublicProjectionDeliveryCompatibility::DeprecatedButSupported], true)) {
            return new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::SourceUnavailable);
        }
        if (! $this->catalog->accepts($message->eventType, $message->payloadVersion, $message->sourceModule, $message->aggregateType, $message->payload)) {
            return new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::SourceUnavailable);
        }

        return $this->lookup->resolve($message);
    }
}
