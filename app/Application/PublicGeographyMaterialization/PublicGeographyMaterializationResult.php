<?php

namespace App\Application\PublicGeographyMaterialization;

final readonly class PublicGeographyMaterializationResult
{
    public function __construct(public PublicGeographyMaterializationStatus $status, public ?string $terminalPlaceId = null, public ?int $watermark = null) {}
}
