<?php

namespace App\Application\ModerationRuntime\Contract;

use App\Application\ModerationRuntime\ModerationRuntimeDiagnosticCode;
use App\Application\ModerationRuntime\ModerationRuntimeStatus;

final readonly class ModerationRuntimeReport
{
    public function __construct(
        public ModerationRuntimeStatus $status,
        public ?ModerationRuntimeDiagnosticCode $diagnostic = null,
    ) {}
}
