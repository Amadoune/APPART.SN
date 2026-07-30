<?php

namespace Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\Contract;

use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPersistenceWriteResult;
use Appart\Modules\ListingLifecycle\Application\AuthoringPersistence\AuthoringPortfolioItem;

interface AuthoringPortfolioStore
{
    /** @return list<AuthoringPortfolioItem> */
    public function listFor(string $accountId): array;

    public function project(AuthoringPortfolioItem $item): AuthoringPersistenceWriteResult;
}
