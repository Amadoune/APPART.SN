<?php

namespace App\Application\PublicSearchResults\Contract;

use App\Application\PublicSearchResults\PublicSearchResultsQuery;
use App\Application\PublicSearchResults\PublicSearchResultsResult;

interface PublicSearchResultsReaderV1
{
    public function read(PublicSearchResultsQuery $query): PublicSearchResultsResult;
}
