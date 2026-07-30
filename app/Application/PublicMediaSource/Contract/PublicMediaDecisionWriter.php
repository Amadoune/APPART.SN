<?php

namespace App\Application\PublicMediaSource\Contract;

use App\Application\PublicMediaSource\PublicMediaDecision;
use App\Application\PublicMediaSource\PublicMediaWriteResult;

interface PublicMediaDecisionWriter
{
    public function store(PublicMediaDecision $decision): PublicMediaWriteResult;
}
