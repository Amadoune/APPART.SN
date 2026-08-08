<?php

namespace App\Http\Controllers;

use App\Http\ExperienceAcceptance\ExperienceAcceptanceHttpRuntimeV1;
use App\Http\ExperienceAcceptance\ExperienceAcceptanceResponseFactory;
use App\Http\Requests\ExperienceAcceptanceUserExperienceRequest;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserExperienceReaderV1;
use Illuminate\Http\JsonResponse;

final class ExperienceAcceptanceUserExperienceController extends Controller implements ExperienceAcceptanceHttpRuntimeV1
{
    public function __construct(private readonly UserExperienceReaderV1 $reader, private readonly ExperienceAcceptanceResponseFactory $responses) {}

    public function __invoke(ExperienceAcceptanceUserExperienceRequest $request): JsonResponse
    {
        return $this->responses->userExperience($this->reader->read($request->observedAt()));
    }
}
