<?php

namespace Appart\Modules\ContentSeo\Application\OwnerReader;

final readonly class ContentSeoOwnerReaderResult
{
    public function __construct(public ContentSeoOwnerReaderStatus $status) {}
}
