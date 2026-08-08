<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrivacyPolicyRequest;
use App\Http\SecurityCompliance\SecurityComplianceHttpRuntimeV1;
use App\Http\SecurityCompliance\SecurityComplianceResponseFactory;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\PrivacyPolicyReaderV1;
use Illuminate\Http\JsonResponse;

final class PrivacyPolicyController extends Controller implements SecurityComplianceHttpRuntimeV1
{
    public function __construct(private readonly PrivacyPolicyReaderV1 $reader, private readonly SecurityComplianceResponseFactory $responses) {}

    public function __invoke(PrivacyPolicyRequest $request): JsonResponse
    {
        return $this->responses->privacyPolicy($this->reader->read($request->subjectKey(), $request->observedAt()));
    }
}
