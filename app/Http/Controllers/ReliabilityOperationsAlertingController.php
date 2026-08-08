<?php

namespace App\Http\Controllers;

use App\Http\ReliabilityOperations\ReliabilityOperationsHttpRuntimeV1;
use App\Http\ReliabilityOperations\ReliabilityOperationsResponseFactory;
use App\Http\Requests\ReliabilityOperationsAlertingRequest;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\AlertingReaderV1;
use Illuminate\Http\JsonResponse;

final class ReliabilityOperationsAlertingController extends Controller implements ReliabilityOperationsHttpRuntimeV1
{
    public function __construct(private readonly AlertingReaderV1 $reader, private readonly ReliabilityOperationsResponseFactory $responses) {}

    public function __invoke(ReliabilityOperationsAlertingRequest $request): JsonResponse
    {
        return $this->responses->alerting($this->reader->read($request->observedAt()));
    }
}
