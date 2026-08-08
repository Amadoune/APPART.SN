<?php

namespace App\Http\Controllers;

use App\Http\Requests\SecretInventoryRequest;
use App\Http\SecurityCompliance\SecurityComplianceHttpRuntimeV1;
use App\Http\SecurityCompliance\SecurityComplianceResponseFactory;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecretInventoryReaderV1;
use Illuminate\Http\JsonResponse;

final class SecretInventoryController extends Controller implements SecurityComplianceHttpRuntimeV1
{
    public function __construct(private readonly SecretInventoryReaderV1 $reader, private readonly SecurityComplianceResponseFactory $responses) {}

    public function __invoke(SecretInventoryRequest $request): JsonResponse
    {
        return $this->responses->secretInventory($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
