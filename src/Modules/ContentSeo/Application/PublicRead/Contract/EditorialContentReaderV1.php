<?php

namespace Appart\Modules\ContentSeo\Application\PublicRead\Contract;

use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentResultV1;

interface EditorialContentReaderV1
{
    public function read(
        ContentSeoPublicResourceKey $resource,
        ContentSeoObservedAt $observedAt,
    ): EditorialContentResultV1;
}
