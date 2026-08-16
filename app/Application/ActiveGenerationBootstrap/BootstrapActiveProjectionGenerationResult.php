<?php

namespace App\Application\ActiveGenerationBootstrap;

final readonly class BootstrapActiveProjectionGenerationResult
{
    public function __construct(
        public BootstrapActiveProjectionGenerationStatus $status,
        public string $generationId,
        public string $scopeChecksum,
        public int $processed = 0,
        public int $applied = 0,
        public int $alreadyApplied = 0,
        public int $manifestCount = 0,
        public bool $validationPassed = false,
        public ?string $activeReaderStatus = null,
        public ?ActiveGenerationBootstrapState $state = null,
    ) {}
}
