<?php

namespace App\Http\PublicSearchResults;

use App\Application\PublicSearchResults\PublicSearchListingSummary;
use App\Application\PublicSearchResults\PublicSearchResultsResult;
use App\Application\PublicSearchResults\PublicSearchResultsStatus;
use Illuminate\Http\JsonResponse;

final readonly class PublicSearchResultsResponseFactory
{
    public function make(PublicSearchResultsResult $result): JsonResponse
    {
        $httpStatus = match ($result->status) {
            PublicSearchResultsStatus::Available,
            PublicSearchResultsStatus::Empty => 200,
            PublicSearchResultsStatus::Corrupted,
            PublicSearchResultsStatus::DependencyUnavailable => 503,
        };

        return new JsonResponse([
            'status' => $result->status->value,
            'items' => array_map(
                static fn (PublicSearchListingSummary $item): array => [
                    'canonicalPath' => $item->canonicalPath,
                    'listingId' => $item->listingId,
                    'headline' => $item->headline,
                    'propertyType' => $item->propertyType,
                    'primaryImageUrl' => $item->primaryImageUrl,
                    'transaction' => $item->transaction,
                    'city' => $item->city,
                    'surfaceSquareMeters' => $item->surfaceSquareMeters,
                    'roomCount' => $item->roomCount,
                ],
                $result->items,
            ),
            'nextCursor' => $result->nextCursor,
        ], $httpStatus, ['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
