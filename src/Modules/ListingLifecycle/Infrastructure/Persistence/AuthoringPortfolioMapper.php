<?php

namespace Appart\Modules\ListingLifecycle\Infrastructure\Persistence;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPortfolioItem;

final readonly class AuthoringPortfolioMapper
{
    /** @param array<string, mixed> $row */
    public function toItem(array $row): AuthoringPortfolioItem
    {
        return new AuthoringPortfolioItem(
            (string) $row['account_id'],
            (string) $row['listing_id'],
            (string) $row['property_id'],
            (string) $row['relation'],
            (int) $row['draft_version'],
            (int) $row['ownership_version'],
            (string) $row['completeness_code'],
            (int) $row['source_checkpoint'],
        );
    }
}
