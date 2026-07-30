<?php

namespace Tests\Unit\Contracts\ActiveGenerationReader;

use App\Application\ActiveGenerationReader\ActiveGenerationReadResult;
use App\Application\ActiveGenerationReader\Contract\ActiveGenerationReader;
use App\Application\PublicProjectionStore\PublicProjectionGeneration;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use App\Application\PublicProjectionStore\PublicProjectionGenerationState;

final class FakeActiveGenerationReaderContractTest extends ActiveGenerationReaderContract
{
    private ?PublicProjectionGeneration $active = null;

    protected function reader(): ActiveGenerationReader
    {
        return new readonly class($this->active) implements ActiveGenerationReader
        {
            public function __construct(private ?PublicProjectionGeneration $active) {}

            public function read(): ActiveGenerationReadResult
            {
                return $this->active === null
                    ? ActiveGenerationReadResult::missing()
                    : ActiveGenerationReadResult::found($this->active);
            }
        };
    }

    protected function givenActive(PublicProjectionGenerationId $generationId): void
    {
        $this->active = new PublicProjectionGeneration($generationId, PublicProjectionGenerationState::Active);
    }
}
