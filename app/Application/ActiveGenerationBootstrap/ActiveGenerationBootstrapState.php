<?php

namespace App\Application\ActiveGenerationBootstrap;

final readonly class ActiveGenerationBootstrapState
{
    /** @param list<string> $candidateGenerationIds */
    public function __construct(
        public int $generationsCount,
        public int $activeCount,
        public int $projectionsCount,
        public ?string $activeGenerationId,
        public array $candidateGenerationIds,
    ) {}
}
