<?php

namespace Appart\Modules\ListingLifecycle\Application\ModerationIntent\Contract;

use Closure;

interface ListingModerationIntentTransaction
{
    public function run(Closure $operation): mixed;
}
