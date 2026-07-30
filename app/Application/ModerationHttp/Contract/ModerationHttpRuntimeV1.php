<?php

namespace App\Application\ModerationHttp\Contract;

use App\Application\ModerationHttp\ModerationHttpOperation;
use App\Application\ModerationHttp\ModerationHttpResult;

interface ModerationHttpRuntimeV1
{
    /** @param array<string, mixed> $input */
    public function execute(
        ModerationHttpOperation $operation,
        string $accountId,
        ?string $resourceId,
        ?string $intentId,
        array $input,
    ): ModerationHttpResult;
}
