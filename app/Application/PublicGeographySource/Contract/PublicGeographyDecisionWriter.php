<?php

namespace App\Application\PublicGeographySource\Contract;

use App\Application\PublicGeographySource\PublicGeographyDecision;
use App\Application\PublicGeographySource\PublicGeographyWriteResult;

interface PublicGeographyDecisionWriter
{
    public function store(PublicGeographyDecision $decision): PublicGeographyWriteResult;
}
