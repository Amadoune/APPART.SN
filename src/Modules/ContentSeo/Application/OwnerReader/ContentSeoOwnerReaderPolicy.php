<?php

namespace Appart\Modules\ContentSeo\Application\OwnerReader;

use Appart\Modules\ContentSeo\Application\OwnerReader\Contract\ContentSeoOwnerReaderV1;
use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentReadResult;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoReadResult;

final readonly class ContentSeoOwnerReaderPolicy implements ContentSeoOwnerReaderV1
{
    public function editorial(EditorialContentReadResult $result): ContentSeoOwnerReaderResult
    {
        return new ContentSeoOwnerReaderResult(ContentSeoOwnerReaderStatus::from($result->status->value));
    }

    public function operational(OperationalSeoReadResult $result): ContentSeoOwnerReaderResult
    {
        return new ContentSeoOwnerReaderResult(ContentSeoOwnerReaderStatus::from($result->status->value));
    }
}
