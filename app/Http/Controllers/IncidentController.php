<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncidentRequest;
use App\Http\SecurityCompliance\SecurityComplianceHttpRuntimeV1;
use App\Http\SecurityCompliance\SecurityComplianceResponseFactory;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\IncidentReaderV1;
use Illuminate\Http\JsonResponse;

final class IncidentController extends Controller implements SecurityComplianceHttpRuntimeV1
{
    public function __construct(private readonly IncidentReaderV1 $reader, private readonly SecurityComplianceResponseFactory $responses) {}

    public function __invoke(IncidentRequest $request): JsonResponse
    {
        return $this->responses->incident($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
