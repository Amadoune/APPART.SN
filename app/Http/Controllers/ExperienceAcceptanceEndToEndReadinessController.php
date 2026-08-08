<?php

namespace App\Http\Controllers;

use App\Http\ExperienceAcceptance\ExperienceAcceptanceHttpRuntimeV1;
use App\Http\ExperienceAcceptance\ExperienceAcceptanceResponseFactory;
use App\Http\Requests\ExperienceAcceptanceEndToEndReadinessRequest;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\EndToEndReadinessReaderV1;
use Illuminate\Http\JsonResponse;

final class ExperienceAcceptanceEndToEndReadinessController extends Controller implements ExperienceAcceptanceHttpRuntimeV1
{
    public function __construct(private readonly EndToEndReadinessReaderV1 $reader, private readonly ExperienceAcceptanceResponseFactory $responses) {}

    public function __invoke(ExperienceAcceptanceEndToEndReadinessRequest $request): JsonResponse
    {
        return $this->responses->endToEndReadiness($this->reader->read($request->observedAt()));
    }
}
