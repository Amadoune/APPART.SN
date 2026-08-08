<?php

namespace App\Http\Controllers;

use App\Http\ReliabilityOperations\ReliabilityOperationsHttpRuntimeV1;
use App\Http\ReliabilityOperations\ReliabilityOperationsResponseFactory;
use App\Http\Requests\ReliabilityOperationsMaintenanceOperationsRequest;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\MaintenanceOperationsReaderV1;
use Illuminate\Http\JsonResponse;

final class ReliabilityOperationsMaintenanceOperationsController extends Controller implements ReliabilityOperationsHttpRuntimeV1
{
    public function __construct(private readonly MaintenanceOperationsReaderV1 $reader, private readonly ReliabilityOperationsResponseFactory $responses) {}

    public function __invoke(ReliabilityOperationsMaintenanceOperationsRequest $request): JsonResponse
    {
        return $this->responses->maintenanceOperations($this->reader->read($request->observedAt()));
    }
}
