<?php

namespace App\Http\Controllers;

use App\Http\ExperienceAcceptance\ExperienceAcceptanceHttpRuntimeV1;
use App\Http\ExperienceAcceptance\ExperienceAcceptanceResponseFactory;
use App\Http\Requests\ExperienceAcceptanceUserAcceptanceRequest;
use Appart\Modules\ExperienceAcceptance\Application\PublicRead\Contract\UserAcceptanceReaderV1;
use Illuminate\Http\JsonResponse;

final class ExperienceAcceptanceUserAcceptanceController extends Controller implements ExperienceAcceptanceHttpRuntimeV1
{
    public function __construct(private readonly UserAcceptanceReaderV1 $reader, private readonly ExperienceAcceptanceResponseFactory $responses) {}

    public function __invoke(ExperienceAcceptanceUserAcceptanceRequest $request): JsonResponse
    {
        return $this->responses->userAcceptance($this->reader->read($request->observedAt()));
    }
}
