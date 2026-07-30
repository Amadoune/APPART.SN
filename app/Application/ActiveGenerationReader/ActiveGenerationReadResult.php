<?php

namespace App\Application\ActiveGenerationReader;

use App\Application\PublicProjectionStore\PublicProjectionGeneration;

final readonly class ActiveGenerationReadResult
{
    private function __construct(
        public ActiveGenerationReadStatus $status,
        public ?PublicProjectionGeneration $generation,
    ) {}

    public static function found(PublicProjectionGeneration $generation): self
    {
        return new self(ActiveGenerationReadStatus::Found, $generation);
    }

    public static function missing(): self
    {
        return new self(ActiveGenerationReadStatus::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(ActiveGenerationReadStatus::Corrupted, null);
    }
}
