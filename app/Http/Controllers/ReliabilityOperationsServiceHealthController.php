<?php

namespace App\Http\Controllers;

use App\Http\ReliabilityOperations\ReliabilityOperationsHttpRuntimeV1;
use App\Http\ReliabilityOperations\ReliabilityOperationsResponseFactory;
use App\Http\Requests\ReliabilityOperationsServiceHealthRequest;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ServiceHealthReaderV1;
use Illuminate\Http\JsonResponse;

final class ReliabilityOperationsServiceHealthController extends Controller implements ReliabilityOperationsHttpRuntimeV1
{
    public function __construct(private readonly ServiceHealthReaderV1 $reader, private readonly ReliabilityOperationsResponseFactory $responses) {}

    public function __invoke(ReliabilityOperationsServiceHealthRequest $request): JsonResponse
    {
        return $this->responses->serviceHealth($this->reader->read($request->observedAt()));
    }
}
