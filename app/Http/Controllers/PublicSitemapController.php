<?php

namespace App\Http\Controllers;

use App\Application\Contract\PublicListingQuery;
use App\Application\PublicSearchResults\Contract\PublicSearchResultsReaderV1;
use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use App\Application\PublicSearchResults\PublicSearchResultsStatus;
use Illuminate\Http\Response;
use RuntimeException;
use Throwable;

final class PublicSitemapController extends Controller
{
    public function __construct(
        private readonly PublicSearchResultsReaderV1 $results,
        private readonly PublicListingQuery $listings,
    ) {}

    public function __invoke(): Response
    {
        try {
            $urls = [
                ['location' => route('home'), 'lastModified' => null],
                ['location' => route('public-search.experience'), 'lastModified' => null],
            ];
            $cursor = null;
            $seen = [];

            do {
                $result = $this->results->read(new PublicSearchResultsQuery(24, $cursor));
                if ($result->status === PublicSearchResultsStatus::Empty) {
                    break;
                }
                if ($result->status !== PublicSearchResultsStatus::Available) {
                    throw new RuntimeException('Public sitemap source is unavailable.');
                }
                foreach ($result->items as $summary) {
                    $listing = $this->listings->findByCanonicalPath($summary->canonicalPath);
                    if ($listing === null || $listing->indexability !== 'indexable') {
                        continue;
                    }
                    $urls[] = [
                        'location' => $listing->canonicalUrl,
                        'lastModified' => $listing->decidedAt->format('Y-m-d'),
                    ];
                }

                $cursor = $result->nextCursor;
                if ($cursor !== null && isset($seen[$cursor])) {
                    throw new RuntimeException('Public sitemap cursor cycle detected.');
                }
                if ($cursor !== null) {
                    $seen[$cursor] = true;
                }
            } while ($cursor !== null);

            return new Response(
                view('sitemap', ['urls' => $urls])->render(),
                200,
                ['Content-Type' => 'application/xml; charset=UTF-8', 'X-Content-Type-Options' => 'nosniff'],
            );
        } catch (Throwable) {
            return new Response('', 503, ['Cache-Control' => 'no-store']);
        }
    }
}
