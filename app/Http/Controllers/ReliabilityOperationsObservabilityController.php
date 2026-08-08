<?php

namespace App\Http\Controllers;

use App\Http\ReliabilityOperations\ReliabilityOperationsHttpRuntimeV1;
use App\Http\ReliabilityOperations\ReliabilityOperationsResponseFactory;
use App\Http\Requests\ReliabilityOperationsObservabilityRequest;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ObservabilityReaderV1;
use Illuminate\Http\JsonResponse;

final class ReliabilityOperationsObservabilityController extends Controller implements ReliabilityOperationsHttpRuntimeV1
{
    public function __construct(private readonly ObservabilityReaderV1 $reader, private readonly ReliabilityOperationsResponseFactory $responses) {}

    public function __invoke(ReliabilityOperationsObservabilityRequest $request): JsonResponse
    {
        return $this->responses->observability($this->reader->read($request->observedAt()));
    }
}
