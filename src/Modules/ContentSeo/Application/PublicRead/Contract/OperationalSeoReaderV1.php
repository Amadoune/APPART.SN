<?php

namespace Appart\Modules\ContentSeo\Application\PublicRead\Contract;

use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoResultV1;

interface OperationalSeoReaderV1
{
    public function read(
        ContentSeoPublicResourceKey $resource,
        ContentSeoObservedAt $observedAt,
    ): OperationalSeoResultV1;
}
