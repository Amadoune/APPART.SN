<?php

namespace App\Application\OwnerDashboard;

use App\Application\Contract\PublicListingQuery;
use App\Application\OwnerDashboard\Contract\OwnerDashboardReadSourceV1;
use App\Application\PublicSearchResults\Contract\PublicSearchResultsReaderV1;
use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use App\Application\PublicSearchResults\PublicSearchResultsStatus;
use Throwable;

final readonly class PublicProjectionOwnerDashboardReadSource implements OwnerDashboardReadSourceV1
{
    public function __construct(
        private PublicSearchResultsReaderV1 $results,
        private PublicListingQuery $listings,
    ) {}

    public function read(): OwnerDashboardReadResult
    {
        try {
            $result = $this->results->read(new PublicSearchResultsQuery(12));
            if ($result->status === PublicSearchResultsStatus::Empty) {
                return OwnerDashboardReadResult::empty();
            }
            if ($result->status !== PublicSearchResultsStatus::Available) {
                return OwnerDashboardReadResult::unavailable();
            }

            $items = [];
            foreach ($result->items as $summary) {
                $listing = $this->listings->findByCanonicalPath($summary->canonicalPath);
                if ($listing === null) {
                    return OwnerDashboardReadResult::unavailable();
                }
                $items[] = new OwnerDashboardListing(
                    canonicalPath: $summary->canonicalPath,
                    title: $summary->headline ?? 'Annonce immobilière',
                    imageUrl: $summary->primaryImageUrl,
                    transaction: $summary->transaction,
                    propertyType: $summary->propertyType,
                    city: $summary->city,
                    status: $listing->listingStatus,
                    visibility: 'public',
                    publishedAt: $listing->publishedAt,
                    expiresAt: $listing->expiresAt,
                );
            }

            return OwnerDashboardReadResult::available($items);
        } catch (Throwable) {
            return OwnerDashboardReadResult::unavailable();
        }
    }
}
