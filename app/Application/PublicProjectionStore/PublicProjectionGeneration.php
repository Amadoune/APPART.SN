<?php

namespace App\Application\PublicProjectionStore;

final readonly class PublicProjectionGeneration
{
    public function __construct(public PublicProjectionGenerationId $id, public PublicProjectionGenerationState $state) {}
}
