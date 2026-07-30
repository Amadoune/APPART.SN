<?php

namespace App\Application\MultiTargetDelivery\Contract;

use App\Application\MultiTargetDelivery\MultiTargetPropagationPlan;
use App\Application\MultiTargetDelivery\MultiTargetPropagationRequest;

interface MultiTargetPropagationStrategy
{
    public function plan(MultiTargetPropagationRequest $request, ?string $checkpoint, int $limit): MultiTargetPropagationPlan;
}
