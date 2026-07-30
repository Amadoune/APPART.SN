<?php

namespace App\Application\MediaIngestionRuntime\Contract;

use App\Application\MediaIngestionRuntime\MediaIngestionRuntimeStatus;

final readonly class MediaIngestionRuntimeReport
{
    public function __construct(
        public MediaIngestionRuntimeStatus $status,
        public ?string $code = null,
    ) {}
}
