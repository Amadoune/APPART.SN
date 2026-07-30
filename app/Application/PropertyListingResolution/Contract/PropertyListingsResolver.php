<?php

namespace App\Application\PropertyListingResolution\Contract;

use App\Application\PropertyListingResolution\PropertyListingsPage;

interface PropertyListingsResolver
{
    public function readPage(string $propertyId, ?string $checkpoint, int $limit): PropertyListingsPage;
}
