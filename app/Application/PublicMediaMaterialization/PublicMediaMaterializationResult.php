<?php

namespace App\Application\PublicMediaMaterialization;

use App\Application\PublicMediaSource\PublicMediaDecision;

final readonly class PublicMediaMaterializationResult
{
    public function __construct(public PublicMediaMaterializationStatus $status, public ?PublicMediaDecision $decision = null) {}
}
