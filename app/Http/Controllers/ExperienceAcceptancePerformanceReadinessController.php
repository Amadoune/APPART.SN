<?php

namespace App\Http\Controllers;

use App\Http\ExperienceAcceptance\ExperienceAcceptanceHttpRuntimeV1;
use App\Http\ExperienceAcceptance\ExperienceAcceptanceResponseFactory;
use App\Http\Requests\ExperienceAcceptancePerformanceReadinessRequest;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\PerformanceReadinessReaderV1;
use Illuminate\Http\JsonResponse;

final class ExperienceAcceptancePerformanceReadinessController extends Controller implements ExperienceAcceptanceHttpRuntimeV1
{
    public function __construct(private readonly PerformanceReadinessReaderV1 $reader, private readonly ExperienceAcceptanceResponseFactory $responses) {}

    public function __invoke(ExperienceAcceptancePerformanceReadinessRequest $request): JsonResponse
    {
        return $this->responses->performanceReadiness($this->reader->read($request->observedAt()));
    }
}
