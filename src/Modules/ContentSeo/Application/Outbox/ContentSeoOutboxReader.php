<?php

namespace Appart\Modules\ContentSeo\Application\Outbox;

interface ContentSeoOutboxReader
{
    /** @return list<ContentSeoOutboxResult> */
    public function pending(int $limit): array;
}
