<?php

namespace App\Application\PropertyListingAuthoringOperations\Contract;

use App\Application\PropertyListingAuthoringOperations\AuthoringOperationCommand;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationResult;

interface PropertyListingAuthoringOperations
{
    public function execute(AuthoringOperationCommand $command): AuthoringOperationResult;
}
