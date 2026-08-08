<?php

namespace Appart\Modules\ContentSeo\Application\OwnerReader\Contract;

use Appart\Modules\ContentSeo\Application\OwnerReader\ContentSeoOwnerReaderResult;
use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentReadResult;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoReadResult;

interface ContentSeoOwnerReaderV1
{
    public function editorial(EditorialContentReadResult $result): ContentSeoOwnerReaderResult;

    public function operational(OperationalSeoReadResult $result): ContentSeoOwnerReaderResult;
}
