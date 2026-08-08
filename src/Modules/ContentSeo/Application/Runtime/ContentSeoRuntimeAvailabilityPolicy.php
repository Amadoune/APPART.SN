<?php

namespace Appart\Modules\ContentSeo\Application\Runtime;

interface ContentSeoRuntimeAvailabilityPolicy
{
    public function inspect(): ContentSeoRuntimeAvailability;
}
