<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicFacts\Contract;

use Appart\Modules\ListingLifecycle\Application\PublicFacts\AuthoringPublicFactSnapshot;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublicFactHandoffResult;
use Appart\Modules\ListingLifecycle\Application\PublicFacts\PublishedPublicFacts;
use DateTimeImmutable;

interface AuthoringPublicFactHandoffV1
{
    public function prepare(AuthoringPublicFactSnapshot $snapshot): PublicFactHandoffResult;

    public function candidate(string $listingId): ?AuthoringPublicFactSnapshot;

    public function seal(string $listingId, string $publishedRevisionId, DateTimeImmutable $publishedAt): PublicFactHandoffResult;

    public function published(string $listingId): ?PublishedPublicFacts;
}
