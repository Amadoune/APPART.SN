<?php

namespace App\Http\SearchQueryResolution;

use App\Http\Requests\SearchQueryResolutionRequest;
use Illuminate\Http\JsonResponse;

interface SearchQueryResolutionHttpRuntimeV1
{
    public function __invoke(SearchQueryResolutionRequest $request): JsonResponse;
}
