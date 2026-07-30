<?php

namespace App\Application\ModerationOrchestration\Contract;

final readonly class ModerationCommandResult
{
    public function __construct(
        public ModerationCommandStatus $status,
        public ?string $caseId = null,
        public ?int $version = null,
    ) {}
}
