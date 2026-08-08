<?php

namespace App\Http\Controllers;

use App\Http\Requests\SecurityAuditRequest;
use App\Http\SecurityCompliance\SecurityComplianceHttpRuntimeV1;
use App\Http\SecurityCompliance\SecurityComplianceResponseFactory;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecurityAuditReaderV1;
use Illuminate\Http\JsonResponse;

final class SecurityAuditController extends Controller implements SecurityComplianceHttpRuntimeV1
{
    public function __construct(private readonly SecurityAuditReaderV1 $reader, private readonly SecurityComplianceResponseFactory $responses) {}

    public function __invoke(SecurityAuditRequest $request): JsonResponse
    {
        return $this->responses->securityAudit($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
