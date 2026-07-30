<?php

namespace App\Application\DecisionTimeSource\Contract;

use App\Application\DecisionTimeSource\DecisionTimeReadResult;

interface DecisionTimeReader
{
    public function readByListing(string $listingId): DecisionTimeReadResult;
}
