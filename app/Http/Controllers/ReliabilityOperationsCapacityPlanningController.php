<?php

namespace App\Http\Controllers;

use App\Http\ReliabilityOperations\ReliabilityOperationsHttpRuntimeV1;
use App\Http\ReliabilityOperations\ReliabilityOperationsResponseFactory;
use App\Http\Requests\ReliabilityOperationsCapacityPlanningRequest;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\CapacityPlanningReaderV1;
use Illuminate\Http\JsonResponse;

final class ReliabilityOperationsCapacityPlanningController extends Controller implements ReliabilityOperationsHttpRuntimeV1
{
    public function __construct(private readonly CapacityPlanningReaderV1 $reader, private readonly ReliabilityOperationsResponseFactory $responses) {}

    public function __invoke(ReliabilityOperationsCapacityPlanningRequest $request): JsonResponse
    {
        return $this->responses->capacityPlanning($this->reader->read($request->observedAt()));
    }
}
