<?php

namespace App\Application\PublicMediaMaterialization;

final readonly class PublicMediaOwnerSourceResult
{
    public function __construct(public PublicMediaOwnerSourceStatus $status, public ?PublicMediaOwnerSourceV2 $source = null) {}
}
