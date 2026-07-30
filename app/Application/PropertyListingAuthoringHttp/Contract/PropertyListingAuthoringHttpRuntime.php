<?php

namespace App\Application\PropertyListingAuthoringHttp\Contract;

use App\Application\PropertyListingAuthoringHttp\PropertyListingAuthoringHttpOperation;
use App\Application\PropertyListingAuthoringHttp\PropertyListingAuthoringHttpResult;

interface PropertyListingAuthoringHttpRuntime
{
    /** @param array<string, mixed> $input */
    public function execute(
        PropertyListingAuthoringHttpOperation $operation,
        string $accountId,
        ?string $resourceId,
        ?string $intentId,
        array $input,
    ): PropertyListingAuthoringHttpResult;
}
