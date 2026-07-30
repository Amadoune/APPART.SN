<?php

namespace Tests\Unit\Application\PublicProjectionUpdaterIntegration\Support;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionSourceLookup;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolution;

final readonly class FakePublicProjectionSourceLookup implements PublicProjectionSourceLookup
{
    public function __construct(private PublicProjectionSourceResolution $resolution) {}

    public function resolve(PublicProjectionDeliveryMessage $message): PublicProjectionSourceResolution
    {
        return $this->resolution;
    }
}
