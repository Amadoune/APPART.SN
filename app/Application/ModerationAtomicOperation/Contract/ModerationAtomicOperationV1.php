<?php

namespace App\Application\ModerationAtomicOperation\Contract;

use App\Application\ModerationAtomicOperation\ModerationAtomicWorkResult;

interface ModerationAtomicOperationV1
{
    /** @param callable(): ModerationAtomicWorkResult $work */
    public function execute(callable $work): ModerationAtomicWorkResult;
}
