<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplianceControlRequest;
use App\Http\SecurityCompliance\SecurityComplianceHttpRuntimeV1;
use App\Http\SecurityCompliance\SecurityComplianceResponseFactory;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\ComplianceControlReaderV1;
use Illuminate\Http\JsonResponse;

final class ComplianceControlController extends Controller implements SecurityComplianceHttpRuntimeV1
{
    public function __construct(private readonly ComplianceControlReaderV1 $reader, private readonly SecurityComplianceResponseFactory $responses) {}

    public function __invoke(ComplianceControlRequest $request): JsonResponse
    {
        return $this->responses->complianceControl($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
