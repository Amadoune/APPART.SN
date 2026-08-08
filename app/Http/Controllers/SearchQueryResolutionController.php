<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchQueryResolutionRequest;
use App\Http\SearchQueryResolution\SearchQueryResolutionHttpRuntimeV1;
use App\Http\SearchQueryResolution\SearchQueryResolutionResponseFactory;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolutionPublicReader\PublicSearchQueryResolutionReaderV1;
use Illuminate\Http\JsonResponse;

final class SearchQueryResolutionController extends Controller implements SearchQueryResolutionHttpRuntimeV1
{
    public function __construct(
        private readonly PublicSearchQueryResolutionReaderV1 $reader,
        private readonly SearchQueryResolutionResponseFactory $responses,
    ) {}

    public function __invoke(SearchQueryResolutionRequest $request): JsonResponse
    {
        return $this->responses->make(
            $this->reader->read($request->searchQuery(), $request->observedAt()),
        );
    }
}
