<?php

namespace App\Http\SearchQueryResolution;

use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionResultV1;
use Appart\Modules\SearchDiscovery\Application\SearchQueryResolution\SearchQueryResolutionStatusV1;
use Illuminate\Http\JsonResponse;

final readonly class SearchQueryResolutionResponseFactory
{
    public function make(SearchQueryResolutionResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            SearchQueryResolutionStatusV1::Found,
            SearchQueryResolutionStatusV1::Empty => 200,
            SearchQueryResolutionStatusV1::Corrupted,
            SearchQueryResolutionStatusV1::DependencyUnavailable => 503,
        };

        return new JsonResponse(
            ['status' => $result->status->value],
            $status,
            ['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff'],
        );
    }
}
