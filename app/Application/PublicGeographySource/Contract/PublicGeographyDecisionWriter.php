<?php

namespace App\Application\PublicGeographySource\Contract;

use App\Application\PublicGeographySource\PublicGeographyDecision;
use App\Application\PublicGeographySource\PublicGeographyDecisionV2;
use App\Application\PublicGeographySource\PublicGeographyWriteResult;

interface PublicGeographyDecisionWriter
{
    public function store(PublicGeographyDecision|PublicGeographyDecisionV2 $decision): PublicGeographyWriteResult;
}
