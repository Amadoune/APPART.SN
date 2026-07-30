<?php

namespace Appart\Modules\ListingLifecycle\Application\Creation\Contract;

use Appart\Modules\ListingLifecycle\Application\Creation\CreateListingDraftCommandV1;
use Appart\Modules\ListingLifecycle\Application\Creation\CreateListingDraftResultV1;

interface CreateListingDraftV1
{
    public function create(CreateListingDraftCommandV1 $command): CreateListingDraftResultV1;
}
