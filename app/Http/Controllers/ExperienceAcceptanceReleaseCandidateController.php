<?php

namespace App\Http\Controllers;

use App\Http\ExperienceAcceptance\ExperienceAcceptanceHttpRuntimeV1;
use App\Http\ExperienceAcceptance\ExperienceAcceptanceResponseFactory;
use App\Http\Requests\ExperienceAcceptanceReleaseCandidateRequest;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\ReleaseCandidateReaderV1;
use Illuminate\Http\JsonResponse;

final class ExperienceAcceptanceReleaseCandidateController extends Controller implements ExperienceAcceptanceHttpRuntimeV1
{
    public function __construct(private readonly ReleaseCandidateReaderV1 $reader, private readonly ExperienceAcceptanceResponseFactory $responses) {}

    public function __invoke(ExperienceAcceptanceReleaseCandidateRequest $request): JsonResponse
    {
        return $this->responses->releaseCandidate($this->reader->read($request->observedAt()));
    }
}
