<?php

namespace App\Http\Controllers;

use App\Http\ExperienceAcceptance\ExperienceAcceptanceHttpRuntimeV1;
use App\Http\ExperienceAcceptance\ExperienceAcceptanceResponseFactory;
use App\Http\Requests\ExperienceAcceptanceAccessibilityComplianceRequest;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\AccessibilityComplianceReaderV1;
use Illuminate\Http\JsonResponse;

final class ExperienceAcceptanceAccessibilityComplianceController extends Controller implements ExperienceAcceptanceHttpRuntimeV1
{
    public function __construct(private readonly AccessibilityComplianceReaderV1 $reader, private readonly ExperienceAcceptanceResponseFactory $responses) {}

    public function __invoke(ExperienceAcceptanceAccessibilityComplianceRequest $request): JsonResponse
    {
        return $this->responses->accessibilityCompliance($this->reader->read($request->observedAt()));
    }
}
