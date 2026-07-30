<?php

namespace App\Application\PublicProjectionUpdaterIntegration\Contract;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolution;

interface PublicProjectionSourceLookup
{
    public function resolve(PublicProjectionDeliveryMessage $message): PublicProjectionSourceResolution;
}
