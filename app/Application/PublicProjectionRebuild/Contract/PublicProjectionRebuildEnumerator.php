<?php

namespace App\Application\PublicProjectionRebuild\Contract;

use App\Application\PublicProjectionRebuild\PublicProjectionRebuildPage;
use App\Application\PublicProjectionRebuild\PublicProjectionRebuildScope;

interface PublicProjectionRebuildEnumerator
{
    public function page(PublicProjectionRebuildScope $scope, ?string $checkpoint, int $limit): PublicProjectionRebuildPage;
}
