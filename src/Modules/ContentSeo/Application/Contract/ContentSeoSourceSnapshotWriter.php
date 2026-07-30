<?php

namespace Appart\Modules\ContentSeo\Application\Contract;

use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSnapshotWriteResult;
use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSourceDecision;

interface ContentSeoSourceSnapshotWriter
{
    public function store(ContentSeoSourceDecision $snapshot): ContentSeoSnapshotWriteResult;
}
