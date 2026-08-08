<?php

namespace App\Http\Controllers;

use App\Http\ReliabilityOperations\ReliabilityOperationsHttpRuntimeV1;
use App\Http\ReliabilityOperations\ReliabilityOperationsResponseFactory;
use App\Http\Requests\ReliabilityOperationsOperationalReadinessRequest;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\OperationalReadinessReaderV1;
use Illuminate\Http\JsonResponse;

final class ReliabilityOperationsOperationalReadinessController extends Controller implements ReliabilityOperationsHttpRuntimeV1
{
    public function __construct(private readonly OperationalReadinessReaderV1 $reader, private readonly ReliabilityOperationsResponseFactory $responses) {}

    public function __invoke(ReliabilityOperationsOperationalReadinessRequest $request): JsonResponse
    {
        return $this->responses->operationalReadiness($this->reader->read($request->observedAt()));
    }
}
