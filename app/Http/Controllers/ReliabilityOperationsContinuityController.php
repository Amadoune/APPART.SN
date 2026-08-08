<?php

namespace App\Http\Controllers;

use App\Http\ReliabilityOperations\ReliabilityOperationsHttpRuntimeV1;
use App\Http\ReliabilityOperations\ReliabilityOperationsResponseFactory;
use App\Http\Requests\ReliabilityOperationsContinuityRequest;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ContinuityReaderV1;
use Illuminate\Http\JsonResponse;

final class ReliabilityOperationsContinuityController extends Controller implements ReliabilityOperationsHttpRuntimeV1
{
    public function __construct(private readonly ContinuityReaderV1 $reader, private readonly ReliabilityOperationsResponseFactory $responses) {}

    public function __invoke(ReliabilityOperationsContinuityRequest $request): JsonResponse
    {
        return $this->responses->continuity($this->reader->read($request->observedAt()));
    }
}
