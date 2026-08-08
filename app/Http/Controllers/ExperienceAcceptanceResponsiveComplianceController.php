<?php

namespace App\Http\Controllers;

use App\Http\ExperienceAcceptance\ExperienceAcceptanceHttpRuntimeV1;
use App\Http\ExperienceAcceptance\ExperienceAcceptanceResponseFactory;
use App\Http\Requests\ExperienceAcceptanceResponsiveComplianceRequest;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ResponsiveComplianceReaderV1;
use Illuminate\Http\JsonResponse;

final class ExperienceAcceptanceResponsiveComplianceController extends Controller implements ExperienceAcceptanceHttpRuntimeV1
{
    public function __construct(private readonly ResponsiveComplianceReaderV1 $reader, private readonly ExperienceAcceptanceResponseFactory $responses) {}

    public function __invoke(ExperienceAcceptanceResponsiveComplianceRequest $request): JsonResponse
    {
        return $this->responses->responsiveCompliance($this->reader->read($request->observedAt()));
    }
}
