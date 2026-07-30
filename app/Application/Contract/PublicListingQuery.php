<?php

namespace App\Application\Contract;

use App\ReadModels\PublicListingReadModel;

interface PublicListingQuery
{
    /**
     * Resolves the page identified by its exact current public canonical path
     * (for example "annonces/appartement-moderne-dakar").
     * Historical paths and listing identifiers are not fallback identities.
     */
    public function findByCanonicalPath(string $canonicalPath): ?PublicListingReadModel;
}
